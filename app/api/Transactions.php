<?php
require_once APPROOT . '/libraries/ApiController.php';

class TransactionsApi extends ApiController {

  private $model;

  public function __construct() {
    parent::__construct();
    $this->requireAuth();
    $this->model = $this->model('Transaction');
  }

  /** GET /api/transactions */
  public function index() {
    $this->success($this->model->getTransactions());
  }

  /** GET /api/transactions/{id} */
  public function show($id) {
    $item = $this->model->getTransactionById($id);
    if (!$item) $this->abort(404, 'Transacción no encontrada.');
    $this->success($item);
  }

  /** POST /api/transactions */
  public function store() {
    $data = $this->getBody();
    if (empty($data['account_id']))      $this->error('account_id es requerido.');
    if (empty($data['category_id']))     $this->error('category_id es requerido.');
    if (!isset($data['amount']))         $this->error('amount es requerido.');
    if (empty($data['type']))            $this->error('type es requerido (income/expense).');
    if (empty($data['transaction_date'])) $this->error('transaction_date es requerido.');
    $this->validateEnum($data['type'], ['income', 'expense'], 'type');
    $this->validateAmount($data['amount']);
    $this->assertOwnsAccount($data['account_id']);
    $this->assertOwnsCategory($data['category_id']);
    $data['description'] = $data['description'] ?? '';

    if ($this->model->addTransaction($data)) {
      $this->success(null, 'Transacción creada.', 201);
    }
    $this->error('Error al crear la transacción.', 500);
  }

  /** PUT /api/transactions/{id} */
  public function update($id) {
    $item = $this->model->getTransactionById($id);
    if (!$item) $this->abort(404, 'Transacción no encontrada.');

    $allowed = ['account_id', 'category_id', 'amount', 'type', 'description', 'transaction_date'];
    $data = array_merge((array)$item, $this->pick($this->getBody(), $allowed), ['id' => $id]);
    $this->validateEnum($data['type'], ['income', 'expense'], 'type');
    $this->validateAmount($data['amount']);
    $this->assertOwnsAccount($data['account_id']);
    $this->assertOwnsCategory($data['category_id']);
    if ($this->model->updateTransaction($data)) {
      $this->success(null, 'Transacción actualizada.');
    }
    $this->error('Error al actualizar la transacción.', 500);
  }

  /** DELETE /api/transactions/{id} */
  public function destroy($id) {
    $item = $this->model->getTransactionById($id);
    if (!$item) $this->abort(404, 'Transacción no encontrada.');

    if ($this->model->deleteTransaction($id)) {
      $this->success(null, 'Transacción eliminada.');
    }
    $this->error('Error al eliminar la transacción.', 500);
  }
}
