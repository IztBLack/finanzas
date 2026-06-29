<?php
require_once APPROOT . '/libraries/ApiController.php';
require_once APPROOT . '/models/User.php';

class AuthApi extends ApiController {

  const MAX_LOGIN_ATTEMPTS = 5;       // intentos fallidos permitidos
  const LOGIN_WINDOW_MINUTES = 15;    // dentro de esta ventana

  public function __construct() {
    parent::__construct();
  }

  /**
   * POST /api/auth/login
   * Body: { "email": "...", "password": "...", "device_name": "..." }
   * Returns: { "success": true, "data": { "token": "...", "user": {...} } }
   */
  public function login() {
    if ($this->method() !== 'POST') {
      $this->error('Método no permitido.', 405);
    }

    $body = $this->getBody();
    $email    = trim($body['email'] ?? '');
    $password = trim($body['password'] ?? '');
    $device   = trim($body['device_name'] ?? 'Flutter');

    if (!$email || !$password) {
      $this->error('Email y contraseña requeridos.');
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Bloquear si se superó el límite de intentos fallidos recientes
    if ($this->tooManyAttempts($email, $ip)) {
      $this->abort(429, 'Demasiados intentos. Espera unos minutos e inténtalo de nuevo.');
    }

    $userModel = new User();
    $user = $userModel->login($email, $password);

    if (!$user) {
      $this->recordFailedAttempt($email, $ip);
      $this->abort(401, 'Credenciales incorrectas.');
    }

    // Login correcto: limpiar intentos fallidos previos
    $this->clearAttempts($email, $ip);

    // Generar token en claro (se devuelve una sola vez) y guardar solo su hash
    $token = bin2hex(random_bytes(32));
    $hash  = $this->hashToken($token);

    $ttl = (int) self::TOKEN_TTL_DAYS; // constante, sin entrada de usuario
    $this->db->query(
      "INSERT INTO api_tokens (user_id, token_hash, device_name, expires_at)
       VALUES (:user_id, :token_hash, :device, DATE_ADD(NOW(), INTERVAL {$ttl} DAY))"
    );
    $this->db->bind(':user_id', $user->id);
    $this->db->bind(':token_hash', $hash);
    $this->db->bind(':device', $device);
    $this->db->execute();

    $this->success([
      'token' => $token,
      'user'  => [
        'id'    => $user->id,
        'name'  => $user->name,
        'email' => $user->email,
      ]
    ], 'Login exitoso.');
  }

  /**
   * POST /api/auth/logout
   * Revoca el token actual.
   */
  public function logout() {
    $this->requireAuth();

    // $this->token fue validado en requireAuth(); revocamos exactamente ese.
    $this->db->query('DELETE FROM api_tokens WHERE token_hash = :hash');
    $this->db->bind(':hash', $this->hashToken($this->token));
    $this->db->execute();

    $this->success(null, 'Sesión cerrada.');
  }

  // ----------------------------------------------------------
  // Rate limiting (anti fuerza bruta)
  // ----------------------------------------------------------

  private function tooManyAttempts($email, $ip) {
    $window = (int) self::LOGIN_WINDOW_MINUTES;
    $this->db->query("SELECT COUNT(*) AS n FROM login_attempts
                      WHERE email = :email AND ip = :ip
                        AND attempted_at > DATE_SUB(NOW(), INTERVAL {$window} MINUTE)");
    $this->db->bind(':email', $email);
    $this->db->bind(':ip', $ip);
    $row = $this->db->single();
    return $row && (int)$row->n >= self::MAX_LOGIN_ATTEMPTS;
  }

  private function recordFailedAttempt($email, $ip) {
    $this->db->query('INSERT INTO login_attempts (email, ip) VALUES (:email, :ip)');
    $this->db->bind(':email', $email);
    $this->db->bind(':ip', $ip);
    $this->db->execute();
  }

  private function clearAttempts($email, $ip) {
    $this->db->query('DELETE FROM login_attempts WHERE email = :email AND ip = :ip');
    $this->db->bind(':email', $email);
    $this->db->bind(':ip', $ip);
    $this->db->execute();
  }
}
