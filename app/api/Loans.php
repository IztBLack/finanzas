<?php
require_once APPROOT . '/libraries/ApiController.php';

class LoansApi extends ApiController {

  private $model;

  public function __construct() {
    parent::__construct();
    $this->requireAuth();
    $this->model = $this->model('Loan');
  }

  /** GET /api/loans */
  public function index() {
    $this->success($this->model->getLoans());
  }

  /** GET /api/loans/{id} */
  public function show($id) {
    $item = $this->model->getLoanById($id);
    if (!$item) $this->abort(404, 'Préstamo no encontrado.');
    $this->success($item);
  }

  /** POST /api/loans */
  public function store() {
    $data = $this->getBody();
    if (empty($data['account_id']))   $this->error('account_id es requerido.');
    if (empty($data['debtor_name']))  $this->error('debtor_name es requerido.');
    if (!isset($data['amount']))      $this->error('amount es requerido.');
    if (empty($data['loan_date']))    $this->error('loan_date es requerido.');
    $this->validateAmount($data['amount']);
    $this->assertOwnsAccount($data['account_id']);
    $data['due_date']    = $data['due_date'] ?? null;
    $data['description'] = $data['description'] ?? '';

    if ($this->model->addLoan($data)) {
      $this->success(null, 'Préstamo creado.', 201);
    }
    $this->error('Error al crear el préstamo.', 500);
  }

  /** PUT /api/loans/{id} */
  public function update($id) {
    $item = $this->model->getLoanById($id);
    if (!$item) $this->abort(404, 'Préstamo no encontrado.');

    $allowed = ['account_id', 'debtor_name', 'amount', 'loan_date', 'due_date', 'description'];
    $data = array_merge((array)$item, $this->pick($this->getBody(), $allowed), ['id' => $id]);
    $this->validateAmount($data['amount']);
    $this->assertOwnsAccount($data['account_id']);
    if ($this->model->updateLoan($data)) {
      $this->success(null, 'Préstamo actualizado.');
    }
    $this->error('Error al actualizar el préstamo.', 500);
  }

  /** DELETE /api/loans/{id} */
  public function destroy($id) {
    $item = $this->model->getLoanById($id);
    if (!$item) $this->abort(404, 'Préstamo no encontrado.');

    if ($this->model->deleteLoan($id)) {
      $this->success(null, 'Préstamo eliminado.');
    }
    $this->error('Error al eliminar el préstamo.', 500);
  }

  // ----------------------------------------------------------
  // Pagos
  // ----------------------------------------------------------

  /** GET /api/loans/{id}/payments */
  public function payments($id) {
    $loan = $this->model->getLoanById($id);
    if (!$loan) $this->abort(404, 'Préstamo no encontrado.');
    $this->success($this->model->getPaymentsByLoan($id));
  }

  /** POST /api/loans/{id}/payments */
  public function addPayment($id) {
    $loan = $this->model->getLoanById($id);
    if (!$loan) $this->abort(404, 'Préstamo no encontrado.');

    $data = $this->getBody();
    if (empty($data['account_id']))   $this->error('account_id es requerido.');
    if (!isset($data['amount']))      $this->error('amount es requerido.');
    if (empty($data['payment_date'])) $this->error('payment_date es requerido.');
    $this->validateAmount($data['amount']);
    $this->assertOwnsAccount($data['account_id']);
    $data['loan_id'] = $id;

    if ($this->model->addPayment($data)) {
      $this->success(null, 'Pago registrado.', 201);
    }
    $this->error('Error al registrar el pago.', 500);
  }

  /** DELETE /api/loans/payments/{payment_id} */
  public function deletePayment($paymentId) {
    // El modelo verifica que el pago pertenezca a un préstamo del usuario.
    if ($this->model->deletePayment($paymentId)) {
      $this->success(null, 'Pago eliminado.');
    }
    $this->abort(404, 'Pago no encontrado.');
  }
}
