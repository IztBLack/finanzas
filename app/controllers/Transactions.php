<?php
class Transactions extends Controller {
  public function __construct(){
    if(!isset($_SESSION['user_id'])){
      redirect('users/login');
    }
    $this->transactionModel = $this->model('Transaction');
    // Load related models to populate dropdowns
    $this->accountModel = $this->model('Account');
    $this->categoryModel = $this->model('Category');
  }

  public function index(){
    $transactions = $this->transactionModel->getTransactions();

    $data = [
      'transactions' => $transactions
    ];

    $this->view('transactions/index', $data);
  }

  public function add(){
    $accounts = $this->accountModel->getAccounts();
    $categories = $this->categoryModel->getCategories();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

      $data = [
        'account_id' => trim($_POST['account_id']),
        'category_id' => trim($_POST['category_id']),
        'amount' => trim($_POST['amount']),
        'type' => trim($_POST['type']),
        'description' => trim($_POST['description']),
        'transaction_date' => trim($_POST['transaction_date']),
        'accounts' => $accounts,
        'categories' => $categories,
        'account_err' => '',
        'category_err' => '',
        'amount_err' => '',
        'type_err' => '',
        'date_err' => ''
      ];

      // Validate
      if(empty($data['account_id'])){ $data['account_err'] = 'Selecciona una cuenta'; }
      if(empty($data['category_id'])){ $data['category_err'] = 'Selecciona una categoría'; }
      if(empty($data['amount'])){ $data['amount_err'] = 'Ingresa el monto'; }
      if(empty($data['type'])){ $data['type_err'] = 'Selecciona el tipo'; }
      if(empty($data['transaction_date'])){ $data['date_err'] = 'Ingresa la fecha'; }

      if(empty($data['account_err']) && empty($data['category_err']) && empty($data['amount_err']) && empty($data['type_err']) && empty($data['date_err'])){
        if($this->transactionModel->addTransaction($data)){
          flash('transaction_message', 'Transacción Registrada');
          redirect('transactions');
        } else {
          die('Something went wrong');
        }
      } else {
        $this->view('transactions/add', $data);
      }
    } else {
      $data = [
        'account_id' => '',
        'category_id' => '',
        'amount' => '',
        'type' => '',
        'description' => '',
        'transaction_date' => date('Y-m-d'),
        'accounts' => $accounts,
        'categories' => $categories,
        'account_err' => '',
        'category_err' => '',
        'amount_err' => '',
        'type_err' => '',
        'date_err' => ''
      ];

      $this->view('transactions/add', $data);
    }
  }

  public function edit($id){
    $accounts = $this->accountModel->getAccounts();
    $categories = $this->categoryModel->getCategories();

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

      $data = [
        'id' => $id,
        'account_id' => trim($_POST['account_id']),
        'category_id' => trim($_POST['category_id']),
        'amount' => trim($_POST['amount']),
        'type' => trim($_POST['type']),
        'description' => trim($_POST['description']),
        'transaction_date' => trim($_POST['transaction_date']),
        'accounts' => $accounts,
        'categories' => $categories,
        'account_err' => '',
        'category_err' => '',
        'amount_err' => '',
        'type_err' => '',
        'date_err' => ''
      ];

      if(empty($data['account_id'])){ $data['account_err'] = 'Selecciona una cuenta'; }
      if(empty($data['category_id'])){ $data['category_err'] = 'Selecciona una categoría'; }
      if(empty($data['amount'])){ $data['amount_err'] = 'Ingresa el monto'; }
      if(empty($data['type'])){ $data['type_err'] = 'Selecciona el tipo'; }
      if(empty($data['transaction_date'])){ $data['date_err'] = 'Ingresa la fecha'; }

      if(empty($data['account_err']) && empty($data['category_err']) && empty($data['amount_err']) && empty($data['type_err']) && empty($data['date_err'])){
        if($this->transactionModel->updateTransaction($data)){
          flash('transaction_message', 'Transacción Actualizada');
          redirect('transactions');
        } else {
          die('Something went wrong');
        }
      } else {
        $this->view('transactions/edit', $data);
      }
    } else {
      $transaction = $this->transactionModel->getTransactionById($id);

      if($transaction->user_id != $_SESSION['user_id']){
        redirect('transactions');
      }

      $data = [
        'id' => $id,
        'account_id' => $transaction->account_id,
        'category_id' => $transaction->category_id,
        'amount' => $transaction->amount,
        'type' => $transaction->type,
        'description' => $transaction->description,
        'transaction_date' => $transaction->transaction_date,
        'accounts' => $accounts,
        'categories' => $categories,
        'account_err' => '',
        'category_err' => '',
        'amount_err' => '',
        'type_err' => '',
        'date_err' => ''
      ];

      $this->view('transactions/edit', $data);
    }
  }

  public function delete($id){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $transaction = $this->transactionModel->getTransactionById($id);
      
      if($transaction->user_id != $_SESSION['user_id']){
        redirect('transactions');
      }

      if($this->transactionModel->deleteTransaction($id)){
        flash('transaction_message', 'Transacción Eliminada');
        redirect('transactions');
      } else {
        die('Something went wrong');
      }
    } else {
      redirect('transactions');
    }
  }
}
