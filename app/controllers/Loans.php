<?php
class Loans extends Controller {
  public function __construct(){
    if(!isset($_SESSION['user_id'])){
      redirect('users/login');
    }
    $this->loanModel = $this->model('Loan');
    $this->accountModel = $this->model('Account');
  }

  public function index(){
    $loans = $this->loanModel->getLoans();
    $data = [
      'loans' => $loans
    ];
    $this->view('loans/index', $data);
  }

  public function show($id){
    $loan = $this->loanModel->getLoanById($id);
    if($loan->user_id != $_SESSION['user_id']){
      redirect('loans');
    }

    $payments = $this->loanModel->getPaymentsByLoan($id);
    $accounts = $this->accountModel->getAccounts();

    $data = [
      'loan' => $loan,
      'payments' => $payments,
      'accounts' => $accounts,
      'amount_err' => '',
      'account_err' => ''
    ];

    $this->view('loans/show', $data);
  }

  public function add(){
    $accounts = $this->accountModel->getAccounts();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

      $data = [
        'account_id' => trim($_POST['account_id']),
        'debtor_name' => trim($_POST['debtor_name']),
        'amount' => trim($_POST['amount']),
        'loan_date' => trim($_POST['loan_date']),
        'due_date' => trim($_POST['due_date']),
        'description' => trim($_POST['description']),
        'accounts' => $accounts,
        'account_err' => '',
        'debtor_err' => '',
        'amount_err' => '',
        'date_err' => ''
      ];

      if(empty($data['account_id'])){ $data['account_err'] = 'Selecciona de qué cuenta salió el dinero'; }
      if(empty($data['debtor_name'])){ $data['debtor_err'] = 'Ingresa el nombre del deudor'; }
      if(empty($data['amount'])){ $data['amount_err'] = 'Ingresa el monto prestado'; }
      if(empty($data['loan_date'])){ $data['date_err'] = 'Ingresa la fecha del préstamo'; }

      if(empty($data['account_err']) && empty($data['debtor_err']) && empty($data['amount_err']) && empty($data['date_err'])){
        if($this->loanModel->addLoan($data)){
          flash('loan_message', 'Préstamo Registrado');
          redirect('loans');
        } else {
          die('Something went wrong');
        }
      } else {
        $this->view('loans/add', $data);
      }
    } else {
      $data = [
        'account_id' => '',
        'debtor_name' => '',
        'amount' => '',
        'loan_date' => date('Y-m-d'),
        'due_date' => '',
        'description' => '',
        'accounts' => $accounts,
        'account_err' => '',
        'debtor_err' => '',
        'amount_err' => '',
        'date_err' => ''
      ];
      $this->view('loans/add', $data);
    }
  }

  public function edit($id){
    $accounts = $this->accountModel->getAccounts();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

      $data = [
        'id' => $id,
        'account_id' => trim($_POST['account_id']),
        'debtor_name' => trim($_POST['debtor_name']),
        'amount' => trim($_POST['amount']),
        'loan_date' => trim($_POST['loan_date']),
        'due_date' => trim($_POST['due_date']),
        'description' => trim($_POST['description']),
        'accounts' => $accounts,
        'account_err' => '',
        'debtor_err' => '',
        'amount_err' => '',
        'date_err' => ''
      ];

      if(empty($data['account_id'])){ $data['account_err'] = 'Selecciona de qué cuenta salió el dinero'; }
      if(empty($data['debtor_name'])){ $data['debtor_err'] = 'Ingresa el nombre del deudor'; }
      if(empty($data['amount'])){ $data['amount_err'] = 'Ingresa el monto prestado'; }
      if(empty($data['loan_date'])){ $data['date_err'] = 'Ingresa la fecha del préstamo'; }

      if(empty($data['account_err']) && empty($data['debtor_err']) && empty($data['amount_err']) && empty($data['date_err'])){
        if($this->loanModel->updateLoan($data)){
          flash('loan_message', 'Préstamo Actualizado');
          redirect('loans');
        } else {
          die('Something went wrong');
        }
      } else {
        $this->view('loans/edit', $data);
      }
    } else {
      $loan = $this->loanModel->getLoanById($id);
      if($loan->user_id != $_SESSION['user_id']){
        redirect('loans');
      }

      $data = [
        'id' => $id,
        'account_id' => $loan->account_id,
        'debtor_name' => $loan->debtor_name,
        'amount' => $loan->amount,
        'loan_date' => $loan->loan_date,
        'due_date' => $loan->due_date,
        'description' => $loan->description,
        'accounts' => $accounts,
        'account_err' => '',
        'debtor_err' => '',
        'amount_err' => '',
        'date_err' => ''
      ];
      $this->view('loans/edit', $data);
    }
  }

  public function delete($id){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $loan = $this->loanModel->getLoanById($id);
      if($loan->user_id != $_SESSION['user_id']){
        redirect('loans');
      }
      if($this->loanModel->deleteLoan($id)){
        flash('loan_message', 'Préstamo Eliminado');
        redirect('loans');
      } else {
        die('Something went wrong');
      }
    } else {
      redirect('loans');
    }
  }

  public function addPayment($loan_id){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
      
      $loan = $this->loanModel->getLoanById($loan_id);
      if($loan->user_id != $_SESSION['user_id']){
        redirect('loans');
      }

      $data = [
        'loan_id' => $loan_id,
        'account_id' => trim($_POST['account_id']),
        'amount' => trim($_POST['amount']),
        'payment_date' => trim($_POST['payment_date']) ?: date('Y-m-d')
      ];

      if(!empty($data['amount']) && !empty($data['account_id'])){
        if($this->loanModel->addPayment($data)){
          flash('loan_message', 'Abono Registrado Exitosamente');
        }
      }
      redirect('loans/show/'.$loan_id);
    }
  }

  public function deletePayment($id, $loan_id){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $loan = $this->loanModel->getLoanById($loan_id);
      if($loan->user_id == $_SESSION['user_id']){
        $this->loanModel->deletePayment($id, $loan_id);
        flash('loan_message', 'Abono Eliminado');
      }
    }
    redirect('loans/show/'.$loan_id);
  }
}
