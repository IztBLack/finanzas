<?php
/**
 * API Entry Point — finanzas
 * URL format: /Finanzas/public/api/{resource}/{id?}/{sub?}
 * Ejemplos:
 *   /Finanzas/public/api/transactions
 *   /Finanzas/public/api/transactions/5
 *   /Finanzas/public/api/loans/3/payments          (GET listar / POST registrar)
 *   /Finanzas/public/api/loans/payments/7          (DELETE borrar pago 7)
 */

// Bootstrap
require_once '../app/bootstrap.php';

// Limpiar y segmentar la URL
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);
$segments = $url ? explode('/', $url) : [];

$resource = strtolower($segments[0] ?? '');
$seg1     = $segments[1] ?? '';
$seg2     = $segments[2] ?? '';
$id       = is_numeric($seg1) ? (int)$seg1 : null;
$method   = $_SERVER['REQUEST_METHOD'];

// Mapa resource → archivo de controlador
$controllers = [
  'auth'          => ['file' => 'Auth',          'class' => 'AuthApi'],
  'accounts'      => ['file' => 'Accounts',      'class' => 'AccountsApi'],
  'categories'    => ['file' => 'Categories',    'class' => 'CategoriesApi'],
  'transactions'  => ['file' => 'Transactions',  'class' => 'TransactionsApi'],
  'loans'         => ['file' => 'Loans',         'class' => 'LoansApi'],
  'subscriptions' => ['file' => 'Subscriptions', 'class' => 'SubscriptionsApi'],
  'dashboard'     => ['file' => 'Dashboard',     'class' => 'DashboardApi'],
];

if (!isset($controllers[$resource])) {
  jsonError(404, 'Endpoint no encontrado.');
}

require_once APPROOT . '/api/' . $controllers[$resource]['file'] . '.php';
$class = $controllers[$resource]['class'];
$ctrl  = new $class();

// Routing por resource + método HTTP + presencia de id y sub-recurso
switch ($resource) {

  case 'auth':
    // Solo acciones explícitamente permitidas (no method_exists arbitrario)
    $action = strtolower($segments[1] ?? 'login');
    if (in_array($action, ['login', 'logout'], true)) {
      $ctrl->$action();
    } else {
      jsonError(404, 'Acción de auth no encontrada.');
    }
    break;

  case 'loans':
    if ($id && strtolower($seg2) === 'payments') {
      // GET  /api/loans/{id}/payments   → payments($id)
      // POST /api/loans/{id}/payments   → addPayment($id)
      if ($method === 'GET')  { $ctrl->payments($id); }
      elseif ($method === 'POST') { $ctrl->addPayment($id); }
      else { jsonError(405, 'Método no permitido.'); }
    } elseif (strtolower($seg1) === 'payments' && is_numeric($seg2)) {
      // DELETE /api/loans/payments/{paymentId}
      if ($method === 'DELETE') { $ctrl->deletePayment((int)$seg2); }
      else { jsonError(405, 'Método no permitido.'); }
    } else {
      routeResource($ctrl, $method, $id);
    }
    break;

  default:
    routeResource($ctrl, $method, $id);
    break;
}

/**
 * Rutea los métodos CRUD estándar. Cada rama hace return para no caer al 405.
 */
function routeResource($ctrl, $method, $id) {
  if ($method === 'GET'    && !$id) { $ctrl->index();    return; }
  if ($method === 'GET'    &&  $id) { $ctrl->show($id);  return; }
  if ($method === 'POST'   && !$id) { $ctrl->store();    return; }
  if ($method === 'PUT'    &&  $id) { $ctrl->update($id); return; }
  if ($method === 'DELETE' &&  $id) { $ctrl->destroy($id); return; }
  jsonError(405, 'Método no permitido.');
}

/** Respuesta de error JSON uniforme para el router (antes de instanciar controlador). */
function jsonError($code, $message) {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
  exit;
}
