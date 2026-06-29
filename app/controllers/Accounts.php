<?php
class Accounts extends Controller {
  public function __construct(){
    if(!isset($_SESSION['user_id'])){
      redirect('users/login');
    }
    $this->accountModel = $this->model('Account');
  }

  public function index(){
    $accounts = $this->accountModel->getAccounts();

    $data = [
      'accounts' => $accounts
    ];

    $this->view('accounts/index', $data);
  }

  public function add(){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

      $data = [
        'name' => trim($_POST['name']),
        'initial_balance' => trim($_POST['initial_balance'] ?? 0),
        'type' => trim($_POST['type'] ?? 'debit'),
        'credit_limit' => trim($_POST['credit_limit'] ?? ''),
        'cutoff_date' => trim($_POST['cutoff_date'] ?? ''),
        'payment_date' => trim($_POST['payment_date'] ?? ''),
        'name_err' => '',
        'balance_err' => '',
        'type_err' => ''
      ];

      if(empty($data['name'])){
        $data['name_err'] = 'Por favor ingresa un nombre para la cuenta';
      }
      if(empty($data['initial_balance']) && !is_numeric($data['initial_balance'])){
         $data['balance_err'] = 'Por favor ingresa un monto válido';
      }
      
      if($data['type'] == 'credit'){
          if(empty($data['credit_limit'])) $data['type_err'] = 'Ingresa el límite de crédito';
          if(empty($data['cutoff_date'])) $data['type_err'] = 'Ingresa la fecha de corte';
          if(empty($data['payment_date'])) $data['type_err'] = 'Ingresa la fecha límite de pago';
      } else {
          $data['credit_limit'] = null;
          $data['cutoff_date'] = null;
          $data['payment_date'] = null;
      }

      if(empty($data['name_err']) && empty($data['balance_err']) && empty($data['type_err'])){
        if($this->accountModel->addAccount($data)){
          flash('account_message', 'Cuenta Agregada');
          redirect('accounts');
        } else {
          die('Something went wrong');
        }
      } else {
        $this->view('accounts/add', $data);
      }
    } else {
      $data = [
        'name' => '',
        'initial_balance' => '',
        'type' => 'debit',
        'credit_limit' => '',
        'cutoff_date' => '',
        'payment_date' => '',
        'name_err' => '',
        'balance_err' => '',
        'type_err' => ''
      ];
      $this->view('accounts/add', $data);
    }
  }

  public function edit($id){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

      $data = [
        'id' => $id,
        'name' => trim($_POST['name']),
        'initial_balance' => trim($_POST['initial_balance'] ?? 0),
        'type' => trim($_POST['type'] ?? 'debit'),
        'credit_limit' => trim($_POST['credit_limit'] ?? ''),
        'cutoff_date' => trim($_POST['cutoff_date'] ?? ''),
        'payment_date' => trim($_POST['payment_date'] ?? ''),
        'name_err' => '',
        'balance_err' => '',
        'type_err' => ''
      ];

      if(empty($data['name'])){
        $data['name_err'] = 'Por favor ingresa un nombre para la cuenta';
      }
      if(empty($data['initial_balance']) && !is_numeric($data['initial_balance'])){
         $data['balance_err'] = 'Por favor ingresa un monto válido';
      }
      
      if($data['type'] == 'credit'){
          if(empty($data['credit_limit'])) $data['type_err'] = 'Ingresa el límite de crédito';
          if(empty($data['cutoff_date'])) $data['type_err'] = 'Ingresa la fecha de corte';
          if(empty($data['payment_date'])) $data['type_err'] = 'Ingresa la fecha límite de pago';
      } else {
          $data['credit_limit'] = null;
          $data['cutoff_date'] = null;
          $data['payment_date'] = null;
      }

      if(empty($data['name_err']) && empty($data['balance_err']) && empty($data['type_err'])){
        if($this->accountModel->updateAccount($data)){
          flash('account_message', 'Cuenta Actualizada');
          redirect('accounts');
        } else {
          die('Something went wrong');
        }
      } else {
        $this->view('accounts/edit', $data);
      }
    } else {
      $account = $this->accountModel->getAccountById($id);

      if($account->user_id != $_SESSION['user_id']){
        redirect('accounts');
      }

      $data = [
        'id' => $id,
        'name' => $account->name,
        'initial_balance' => $account->initial_balance,
        'type' => $account->type ?? 'debit',
        'credit_limit' => $account->credit_limit ?? '',
        'cutoff_date' => $account->cutoff_date ?? '',
        'payment_date' => $account->payment_date ?? '',
        'name_err' => '',
        'balance_err' => '',
        'type_err' => ''
      ];

      $this->view('accounts/edit', $data);
    }
  }

  public function delete($id){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $account = $this->accountModel->getAccountById($id);
      
      if($account->user_id != $_SESSION['user_id']){
        redirect('accounts');
      }

      if($this->accountModel->deleteAccount($id)){
        flash('account_message', 'Cuenta Eliminada');
        redirect('accounts');
      } else {
        die('Something went wrong');
      }
    } else {
      redirect('accounts');
    }
  }
}
