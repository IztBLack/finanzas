<?php
/**
 * ApiController — Clase base para todos los controladores de la API REST.
 * Maneja: CORS, autenticación por token, respuestas JSON uniformes.
 */
class ApiController {

  protected $userId = null;
  protected $token  = null;   // token en claro validado en requireAuth (para logout)
  protected $db;

  // Orígenes permitidos para CORS (la app Flutter nativa no envía Origin, así que
  // no necesita esta lista; solo aplica a clientes web/PWA de confianza).
  protected $allowedOrigins = [
    'http://localhost',
    'http://localhost:3000',
    'http://127.0.0.1',
  ];

  // Vida útil del token (segundos). 90 días.
  const TOKEN_TTL_DAYS = 90;

  public function __construct() {
    $this->setCorsHeaders();

    // Responder a preflight OPTIONS inmediatamente
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
      http_response_code(204);
      exit;
    }

    require_once APPROOT . '/libraries/Database.php';
    $this->db = new Database;
  }

  // ----------------------------------------------------------
  // Auth
  // ----------------------------------------------------------

  /**
   * Valida el Bearer token del header Authorization.
   * Asigna $this->userId si es válido. Aborta con 401 si no.
   * Los tokens se guardan hasheados (SHA-256); aquí se hashea el recibido
   * y se busca por el hash, nunca por el valor en claro.
   */
  protected function requireAuth() {
    $token = $this->getBearerToken();
    if (!$token) {
      $this->abort(401, 'Token requerido.');
    }

    $hash = $this->hashToken($token);
    $this->db->query('SELECT user_id, expires_at FROM api_tokens WHERE token_hash = :hash');
    $this->db->bind(':hash', $hash);
    $row = $this->db->single();

    if (!$row) {
      $this->abort(401, 'Token inválido.');
    }

    // Rechazar (y limpiar) tokens expirados
    if (!empty($row->expires_at) && strtotime($row->expires_at) < time()) {
      $this->db->query('DELETE FROM api_tokens WHERE token_hash = :hash');
      $this->db->bind(':hash', $hash);
      $this->db->execute();
      $this->abort(401, 'Token expirado. Inicia sesión de nuevo.');
    }

    $this->userId = $row->user_id;
    $this->token  = $token;

    // Actualizar last_used_at (sin tumbar el request si falla)
    try {
      $this->db->query('UPDATE api_tokens SET last_used_at = NOW() WHERE token_hash = :hash');
      $this->db->bind(':hash', $hash);
      $this->db->execute();
    } catch (Exception $e) {
      error_log('api_tokens last_used_at update failed: ' . $e->getMessage());
    }
  }

  protected function hashToken($token) {
    return hash('sha256', $token);
  }

  protected function getBearerToken() {
    $headers = null;
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
      $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (isset($_SERVER['Authorization'])) {
      $headers = trim($_SERVER['Authorization']);
    } elseif (function_exists('apache_request_headers')) {
      $requestHeaders = apache_request_headers();
      if (isset($requestHeaders['Authorization'])) {
        $headers = trim($requestHeaders['Authorization']);
      }
    }

    if ($headers && preg_match('/Bearer\s+(.*)$/i', $headers, $matches)) {
      return $matches[1];
    }
    return null;
  }

  // ----------------------------------------------------------
  // Validación de propiedad (evita asociar datos a recursos ajenos)
  // ----------------------------------------------------------

  /** Aborta con 422 si la cuenta no existe o no pertenece al usuario autenticado. */
  protected function assertOwnsAccount($accountId) {
    $m = $this->model('Account');
    if (!$m->getAccountById($accountId)) {
      $this->error('La cuenta indicada no existe o no te pertenece.', 422);
    }
  }

  /** Aborta con 422 si la categoría no existe o no pertenece al usuario autenticado. */
  protected function assertOwnsCategory($categoryId) {
    $m = $this->model('Category');
    if (!$m->getCategoryById($categoryId)) {
      $this->error('La categoría indicada no existe o no te pertenece.', 422);
    }
  }

  // ----------------------------------------------------------
  // Validación de entrada
  // ----------------------------------------------------------

  /** Aborta con 422 si el valor no está en la lista de valores permitidos. */
  protected function validateEnum($value, array $allowed, $field) {
    if (!in_array($value, $allowed, true)) {
      $this->error("$field inválido. Valores permitidos: " . implode(', ', $allowed) . '.', 422);
    }
  }

  /** Aborta con 422 si el valor no es numérico o es negativo. */
  protected function validateAmount($value, $field = 'amount') {
    if (!is_numeric($value) || (float)$value < 0) {
      $this->error("$field debe ser un número mayor o igual a 0.", 422);
    }
  }

  /** Aborta con 422 si el entero no está dentro del rango [min, max]. */
  protected function validateIntRange($value, $min, $max, $field) {
    if (!is_numeric($value) || (int)$value < $min || (int)$value > $max) {
      $this->error("$field debe estar entre $min y $max.", 422);
    }
  }

  /** Devuelve solo las claves permitidas del arreglo (evita mass assignment). */
  protected function pick(array $data, array $allowed) {
    return array_intersect_key($data, array_flip($allowed));
  }

  // ----------------------------------------------------------
  // Respuestas JSON
  // ----------------------------------------------------------

  protected function json($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
  }

  protected function success($data = null, $message = 'OK', $code = 200) {
    $response = ['success' => true, 'message' => $message];
    if ($data !== null) $response['data'] = $data;
    $this->json($response, $code);
  }

  protected function error($message, $code = 400) {
    $this->json(['success' => false, 'message' => $message], $code);
  }

  protected function abort($code, $message) {
    $this->json(['success' => false, 'message' => $message], $code);
  }

  // ----------------------------------------------------------
  // Helpers de request
  // ----------------------------------------------------------

  protected function getBody() {
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) {
      return [];
    }
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      $this->error('JSON inválido en el cuerpo de la petición.', 400);
    }
    return $data ?? [];
  }

  protected function method() {
    return $_SERVER['REQUEST_METHOD'];
  }

  // ----------------------------------------------------------
  // CORS — solo refleja orígenes de la allowlist (no comodín)
  // ----------------------------------------------------------

  private function setCorsHeaders() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
  }

  // ----------------------------------------------------------
  // Carga de modelos (con user_id ya inyectado)
  // ----------------------------------------------------------

  protected function model($modelName) {
    require_once APPROOT . '/models/' . $modelName . '.php';
    return new $modelName($this->userId);
  }
}
