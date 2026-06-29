<?php
  class Pages extends Controller{
    public function __construct(){
    
    }

    // Load Homepage
    public function index(){
      // If logged in, load dashboard metrics instead of static welcome
      if(isset($_SESSION['user_id'])){
        $accountModel = $this->model('Account');
        $transactionModel = $this->model('Transaction');
        $loanModel = $this->model('Loan');
        $subscriptionModel = $this->model('Subscription');
        
        $accounts = $accountModel->getAccounts();
        $transactions = $transactionModel->getTransactions();
        $loans = $loanModel->getLoans();
        $loan_payments = $loanModel->getAllLoanPayments();
        $monthly_subscription_total = $subscriptionModel->getMonthlySubscriptionTotal();

        $total_balance = 0;
        foreach($accounts as $acc) {
            $total_balance += $acc->initial_balance;
        }

        $income_month = 0;
        $expense_month = 0;
        $current_month = date('m');
        $current_year = date('Y');

        // Aggregar todos los movimientos
        $all_movements = [];

        foreach($transactions as $t){
            $all_movements[] = (object)[
                'transaction_date' => $t->transaction_date,
                'description' => $t->description,
                'category_name' => $t->category_name,
                'account_name' => $t->account_name,
                'type' => $t->type,
                'amount' => $t->amount
            ];
        }

        foreach($loans as $loan){
            $all_movements[] = (object)[
                'transaction_date' => $loan->loan_date,
                'description' => 'Préstamo (' . $loan->debtor_name . ')',
                'category_name' => 'Préstamo Emitido',
                'account_name' => $loan->account_name,
                'type' => 'expense',
                'amount' => $loan->amount
            ];
        }

        foreach($loan_payments as $payment){
            $all_movements[] = (object)[
                'transaction_date' => $payment->payment_date,
                'description' => 'Abono a Préstamo',
                'category_name' => 'Pago Recibido',
                'account_name' => $payment->account_name,
                'type' => 'income',
                'amount' => $payment->amount
            ];
        }

        usort($all_movements, function($a, $b) {
            return strtotime($b->transaction_date) - strtotime($a->transaction_date);
        });

        $recent_transactions = array_slice($all_movements, 0, 10); // top 10 recientes

        // Procesar Transacciones
        foreach($transactions as $t){
            if($t->type == 'income') {
                $total_balance += $t->amount;
            } else {
                $total_balance -= $t->amount;
            }

            $t_month = date('m', strtotime($t->transaction_date));
            $t_year = date('Y', strtotime($t->transaction_date));
            
            if($t_month == $current_month && $t_year == $current_year){
                if($t->type == 'income') {
                    $income_month += $t->amount;
                } else {
                    $expense_month += $t->amount;
                }
            }
        }

        // Procesar Préstamos (Gastos / Salidas de dinero)
        foreach($loans as $loan){
            $total_balance -= $loan->amount;

            $l_month = date('m', strtotime($loan->loan_date));
            $l_year = date('Y', strtotime($loan->loan_date));
            
            if($l_month == $current_month && $l_year == $current_year){
                $expense_month += $loan->amount;
            }
        }

        // Procesar Pagos de Préstamos (Ingresos / Entradas de dinero)
        foreach($loan_payments as $payment){
            $total_balance += $payment->amount;

            $p_month = date('m', strtotime($payment->payment_date));
            $p_year = date('Y', strtotime($payment->payment_date));
            
            if($p_month == $current_month && $p_year == $current_year){
                $income_month += $payment->amount;
            }
        }

        // Suscripciones Mensuales Activas
        // Solo restamos el gasto del mes (afecta el gasto de este mes, no un histórico por falta de datos)
        $expense_month += $monthly_subscription_total;
        $total_balance -= $monthly_subscription_total;

        $data = [
            'total_balance' => $total_balance,
            'income_month' => $income_month,
            'expense_month' => $expense_month,
            'recent_transactions' => $recent_transactions
        ];
        
        $this->view('pages/dashboard', $data);
        return; // Salir para no ejecutar lo de abajo
      }

    $data = [
      'title' => 'Control de Finanzas',
      'description' => 'Tu sistema personal para administrar ingresos, gastos, y préstamos de manera efectiva.'
    ];

      // Load homepage/index view
      $this->view('pages/index', $data);
    }

    // public function about(){
    //   //Set Data
    //   $data = [
    //     'version' => '1.0.0'
    //   ];

    //   // Load about view
    //   $this->view('pages/about', $data);
    // }
  }