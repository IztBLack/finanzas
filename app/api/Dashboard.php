<?php
require_once APPROOT . '/libraries/ApiController.php';

class DashboardApi extends ApiController {

  public function __construct() {
    parent::__construct();
    $this->requireAuth();
  }

  /**
   * GET /api/dashboard
   * Devuelve resumen financiero del usuario: balance total, ingresos/gastos del mes,
   * y los 10 movimientos más recientes.
   */
  public function index() {
    $accountModel      = $this->model('Account');
    $transactionModel  = $this->model('Transaction');
    $loanModel         = $this->model('Loan');
    $subscriptionModel = $this->model('Subscription');

    $accounts     = $accountModel->getAccounts();
    $transactions = $transactionModel->getTransactions();
    $loans        = $loanModel->getLoans();
    $loanPayments = $loanModel->getAllLoanPayments();
    $monthlySubscriptionTotal = $subscriptionModel->getMonthlySubscriptionTotal();

    $totalBalance  = 0;
    $incomeMonth   = 0;
    $expenseMonth  = 0;
    $currentMonth  = date('m');
    $currentYear   = date('Y');
    $allMovements  = [];

    // Balance base de cuentas
    foreach ($accounts as $acc) {
      $totalBalance += $acc->initial_balance;
    }

    // Transacciones
    foreach ($transactions as $t) {
      $allMovements[] = [
        'date'         => $t->transaction_date,
        'description'  => $t->description,
        'category'     => $t->category_name,
        'account'      => $t->account_name,
        'type'         => $t->type,
        'amount'       => (float)$t->amount,
      ];

      if ($t->type === 'income') {
        $totalBalance += $t->amount;
      } else {
        $totalBalance -= $t->amount;
      }

      $tMonth = date('m', strtotime($t->transaction_date));
      $tYear  = date('Y', strtotime($t->transaction_date));
      if ($tMonth === $currentMonth && $tYear === $currentYear) {
        if ($t->type === 'income') $incomeMonth += $t->amount;
        else $expenseMonth += $t->amount;
      }
    }

    // Préstamos
    foreach ($loans as $loan) {
      $allMovements[] = [
        'date'        => $loan->loan_date,
        'description' => 'Préstamo (' . $loan->debtor_name . ')',
        'category'    => 'Préstamo Emitido',
        'account'     => $loan->account_name,
        'type'        => 'expense',
        'amount'      => (float)$loan->amount,
      ];
      $totalBalance -= $loan->amount;
      $lMonth = date('m', strtotime($loan->loan_date));
      $lYear  = date('Y', strtotime($loan->loan_date));
      if ($lMonth === $currentMonth && $lYear === $currentYear) {
        $expenseMonth += $loan->amount;
      }
    }

    // Pagos de préstamos
    foreach ($loanPayments as $p) {
      $allMovements[] = [
        'date'        => $p->payment_date,
        'description' => 'Abono a Préstamo',
        'category'    => 'Pago Recibido',
        'account'     => $p->account_name,
        'type'        => 'income',
        'amount'      => (float)$p->amount,
      ];
      $totalBalance += $p->amount;
      $pMonth = date('m', strtotime($p->payment_date));
      $pYear  = date('Y', strtotime($p->payment_date));
      if ($pMonth === $currentMonth && $pYear === $currentYear) {
        $incomeMonth += $p->amount;
      }
    }

    // Suscripciones activas del mes
    $expenseMonth += $monthlySubscriptionTotal;
    $totalBalance -= $monthlySubscriptionTotal;

    // Ordenar movimientos por fecha desc
    usort($allMovements, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

    $this->success([
      'total_balance'              => round((float)$totalBalance, 2),
      'income_month'               => round((float)$incomeMonth, 2),
      'expense_month'              => round((float)$expenseMonth, 2),
      'monthly_subscriptions'      => round((float)$monthlySubscriptionTotal, 2),
      'recent_transactions'        => array_slice($allMovements, 0, 10),
    ]);
  }
}
