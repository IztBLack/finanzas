<?php
class Subscriptions extends Controller {
  public function __construct(){
    if(!isset($_SESSION['user_id'])){
      redirect('users/login');
    }
    $this->subscriptionModel = $this->model('Subscription');
    $this->accountModel = $this->model('Account');
  }

  public function index(){
    $subscriptions = $this->subscriptionModel->getSubscriptions();
    $monthlyTotal = $this->subscriptionModel->getMonthlySubscriptionTotal();

    $data = [
      'subscriptions' => $subscriptions,
      'monthly_total' => $monthlyTotal
    ];

    $this->view('subscriptions/index', $data);
  }

  public function add(){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

      $data = [
        'name' => trim($_POST['name'] ?? ''),
        'amount' => trim($_POST['amount'] ?? ''),
        'account_id' => trim($_POST['account_id'] ?? ''),
        'billing_cycle' => trim($_POST['billing_cycle'] ?? 'monthly'),
        'billing_day' => trim($_POST['billing_day'] ?? ''),
        'status' => 'active',
        'name_err' => '',
        'amount_err' => '',
        'account_err' => '',
        'day_err' => '',
        'accounts' => $this->accountModel->getAccounts()
      ];

      // Validating
      if(empty($data['name'])){
        $data['name_err'] = 'Ingresa el nombre del servicio (Netflix, Spotify, etc.)';
      }
      if(empty($data['amount']) || !is_numeric($data['amount'])){
        $data['amount_err'] = 'Ingresa un costo válido';
      }
      if(empty($data['account_id'])){
        $data['account_err'] = 'Selecciona una cuenta para el pago';
      }
      if(empty($data['billing_day']) || $data['billing_day'] < 1 || $data['billing_day'] > 31){
        $data['day_err'] = 'Ingresa un día de cobro válido (1 al 31)';
      }

      if(empty($data['name_err']) && empty($data['amount_err']) && empty($data['account_err']) && empty($data['day_err'])){
        if($this->subscriptionModel->addSubscription($data)){
          flash('subscription_message', 'Suscripción agregada correctamente');
          redirect('subscriptions');
        } else {
          die('Algo salió mal al guardar en la BD');
        }
      } else {
        $this->view('subscriptions/add', $data);
      }
    } else {
      $accounts = $this->accountModel->getAccounts();

      $data = [
        'name' => '',
        'amount' => '',
        'account_id' => '',
        'billing_cycle' => 'monthly',
        'billing_day' => '',
        'name_err' => '',
        'amount_err' => '',
        'account_err' => '',
        'day_err' => '',
        'accounts' => $accounts
      ];

      $this->view('subscriptions/add', $data);
    }
  }

  public function edit($id){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

      $data = [
        'id' => $id,
        'name' => trim($_POST['name'] ?? ''),
        'amount' => trim($_POST['amount'] ?? ''),
        'account_id' => trim($_POST['account_id'] ?? ''),
        'billing_cycle' => trim($_POST['billing_cycle'] ?? 'monthly'),
        'billing_day' => trim($_POST['billing_day'] ?? ''),
        'status' => trim($_POST['status'] ?? 'active'),
        'name_err' => '',
        'amount_err' => '',
        'account_err' => '',
        'day_err' => '',
        'accounts' => $this->accountModel->getAccounts()
      ];

      if(empty($data['name'])) $data['name_err'] = 'Ingresa el nombre del servicio';
      if(empty($data['amount']) || !is_numeric($data['amount'])) $data['amount_err'] = 'Ingresa un costo válido';
      if(empty($data['account_id'])) $data['account_err'] = 'Selecciona una cuenta';
      if(empty($data['billing_day']) || $data['billing_day'] < 1 || $data['billing_day'] > 31) $data['day_err'] = 'Ingresa un día de cobro válido (1 al 31)';

      if(empty($data['name_err']) && empty($data['amount_err']) && empty($data['account_err']) && empty($data['day_err'])){
        if($this->subscriptionModel->updateSubscription($data)){
          flash('subscription_message', 'Suscripción actualizada correctamente');
          redirect('subscriptions');
        } else {
          die('Error interno');
        }
      } else {
        $this->view('subscriptions/edit', $data);
      }
    } else {
      $subscription = $this->subscriptionModel->getSubscriptionById($id);

      if($subscription->user_id != $_SESSION['user_id']){
        redirect('subscriptions');
      }

      $data = [
        'id' => $id,
        'name' => $subscription->name,
        'amount' => $subscription->amount,
        'account_id' => $subscription->account_id,
        'billing_cycle' => $subscription->billing_cycle,
        'billing_day' => $subscription->billing_day,
        'status' => $subscription->status,
        'name_err' => '',
        'amount_err' => '',
        'account_err' => '',
        'day_err' => '',
        'accounts' => $this->accountModel->getAccounts()
      ];

      $this->view('subscriptions/edit', $data);
    }
  }

  public function delete($id){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
      $subscription = $this->subscriptionModel->getSubscriptionById($id);
      
      if($subscription->user_id != $_SESSION['user_id']){
        redirect('subscriptions');
      }

      if($this->subscriptionModel->deleteSubscription($id)){
        flash('subscription_message', 'Suscripción eliminada');
        redirect('subscriptions');
      } else {
        die('Error interno al eliminar');
      }
    } else {
      redirect('subscriptions');
    }
  }
}
