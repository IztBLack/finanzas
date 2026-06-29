<?php
require_once APPROOT . '/libraries/ApiController.php';

class AccountsApi extends ApiController {

  private $accountModel;

  public function __construct() {
    parent::__construct();
    $this->requireAuth();
    $this->accountModel = $this->model('Account');
  }

  /** GET /api/accounts */
  public function index() {
    $accounts = $this->accountModel->getAccounts();
    $this->success($accounts);
  }

  /** GET /api/accounts/{id} */
  public function show($id) {
    $account = $this->accountModel->getAccountById($id);
    if (!$account) $this->abort(404, 'Cuenta no encontrada.');
    $this->success($account);
  }

  /** POST /api/accounts */
  public function store() {
    $data = $this->getBody();
    if (empty($data['name'])) $this->error('El nombre es requerido.');

    $data = array_merge([
      'initial_balance' => 0,
      'type'            => 'debit',
      'credit_limit'    => null,
      'cutoff_date'     => null,
      'payment_date'    => null,
    ], $data);

    $this->validateAmount($data['initial_balance'], 'initial_balance');
    $this->validateEnum($data['type'], ['debit', 'credit'], 'type');

    if ($this->accountModel->addAccount($data)) {
      $this->success(null, 'Cuenta creada.', 201);
    }
    $this->error('Error al crear la cuenta.', 500);
  }

  /** PUT /api/accounts/{id} */
  public function update($id) {
    $account = $this->accountModel->getAccountById($id);
    if (!$account) $this->abort(404, 'Cuenta no encontrada.');

    $allowed = ['name', 'initial_balance', 'type', 'credit_limit', 'cutoff_date', 'payment_date'];
    $data = array_merge((array)$account, $this->pick($this->getBody(), $allowed), ['id' => $id]);
    $this->validateAmount($data['initial_balance'], 'initial_balance');
    $this->validateEnum($data['type'], ['debit', 'credit'], 'type');
    if ($this->accountModel->updateAccount($data)) {
      $this->success(null, 'Cuenta actualizada.');
    }
    $this->error('Error al actualizar la cuenta.', 500);
  }

  /** DELETE /api/accounts/{id} */
  public function destroy($id) {
    $account = $this->accountModel->getAccountById($id);
    if (!$account) $this->abort(404, 'Cuenta no encontrada.');

    if ($this->accountModel->deleteAccount($id)) {
      $this->success(null, 'Cuenta eliminada.');
    }
    $this->error('Error al eliminar la cuenta.', 500);
  }
}
