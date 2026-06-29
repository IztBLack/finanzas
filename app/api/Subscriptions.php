<?php
require_once APPROOT . '/libraries/ApiController.php';

class SubscriptionsApi extends ApiController {

  private $model;

  public function __construct() {
    parent::__construct();
    $this->requireAuth();
    $this->model = $this->model('Subscription');
  }

  /** GET /api/subscriptions */
  public function index() {
    $this->success($this->model->getSubscriptions());
  }

  /** GET /api/subscriptions/{id} */
  public function show($id) {
    $item = $this->model->getSubscriptionById($id);
    if (!$item) $this->abort(404, 'Suscripción no encontrada.');
    $this->success($item);
  }

  /** POST /api/subscriptions */
  public function store() {
    $data = $this->getBody();
    if (empty($data['name']))        $this->error('name es requerido.');
    if (!isset($data['amount']))     $this->error('amount es requerido.');
    if (empty($data['account_id']))  $this->error('account_id es requerido.');
    if (empty($data['billing_day'])) $this->error('billing_day es requerido.');
    $data['billing_cycle'] = $data['billing_cycle'] ?? 'monthly';
    $data['status']        = $data['status'] ?? 'active';
    $this->validateAmount($data['amount']);
    $this->validateIntRange($data['billing_day'], 1, 31, 'billing_day');
    $this->validateEnum($data['billing_cycle'], ['monthly', 'yearly'], 'billing_cycle');
    $this->validateEnum($data['status'], ['active', 'paused'], 'status');
    $this->assertOwnsAccount($data['account_id']);

    if ($this->model->addSubscription($data)) {
      $this->success(null, 'Suscripción creada.', 201);
    }
    $this->error('Error al crear la suscripción.', 500);
  }

  /** PUT /api/subscriptions/{id} */
  public function update($id) {
    $item = $this->model->getSubscriptionById($id);
    if (!$item) $this->abort(404, 'Suscripción no encontrada.');

    $allowed = ['name', 'amount', 'account_id', 'billing_cycle', 'billing_day', 'status'];
    $data = array_merge((array)$item, $this->pick($this->getBody(), $allowed), ['id' => $id]);
    $this->validateAmount($data['amount']);
    $this->validateIntRange($data['billing_day'], 1, 31, 'billing_day');
    $this->validateEnum($data['billing_cycle'], ['monthly', 'yearly'], 'billing_cycle');
    $this->validateEnum($data['status'], ['active', 'paused'], 'status');
    $this->assertOwnsAccount($data['account_id']);
    if ($this->model->updateSubscription($data)) {
      $this->success(null, 'Suscripción actualizada.');
    }
    $this->error('Error al actualizar la suscripción.', 500);
  }

  /** DELETE /api/subscriptions/{id} */
  public function destroy($id) {
    $item = $this->model->getSubscriptionById($id);
    if (!$item) $this->abort(404, 'Suscripción no encontrada.');

    if ($this->model->deleteSubscription($id)) {
      $this->success(null, 'Suscripción eliminada.');
    }
    $this->error('Error al eliminar la suscripción.', 500);
  }
}
