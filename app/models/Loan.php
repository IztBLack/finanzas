<?php
class Loan {
  private $db;
  private $userId;

  public function __construct($userId = null){
    $this->db = new Database;
    $this->userId = $userId ?? ($_SESSION['user_id'] ?? null);
  }

  // Obtener préstamos con el nombre de la cuenta origen
  public function getLoans(){
    $this->db->query('SELECT l.*, a.name as account_name
                      FROM loans l
                      INNER JOIN accounts a ON l.account_id = a.id
                      WHERE l.user_id = :user_id AND l.deleted_at IS NULL
                      ORDER BY l.status ASC, l.loan_date DESC');
    $this->db->bind(':user_id', $this->userId);
    return $this->db->resultSet();
  }

  public function getLoanById($id){
    $this->db->query('SELECT l.*, a.name as account_name
                      FROM loans l
                      INNER JOIN accounts a ON l.account_id = a.id
                      WHERE l.id = :id AND l.user_id = :user_id AND l.deleted_at IS NULL');
    $this->db->bind(':id', $id);
    $this->db->bind(':user_id', $this->userId);
    return $this->db->single();
  }

  public function addLoan($data){
    $this->db->query('INSERT INTO loans (user_id, account_id, debtor_name, amount, loan_date, due_date, description)
                      VALUES(:user_id, :account_id, :debtor_name, :amount, :loan_date, :due_date, :description)');
    $this->db->bind(':user_id', $this->userId);
    $this->db->bind(':account_id', $data['account_id']);
    $this->db->bind(':debtor_name', $data['debtor_name']);
    $this->db->bind(':amount', $data['amount']);
    $this->db->bind(':loan_date', $data['loan_date']);
    $this->db->bind(':due_date', !empty($data['due_date']) ? $data['due_date'] : null);
    $this->db->bind(':description', $data['description']);

    if($this->db->execute()){
      return true;
    } else {
      return false;
    }
  }

  public function updateLoan($data){
    $this->db->query('UPDATE loans SET account_id = :account_id, debtor_name = :debtor_name,
                      amount = :amount, loan_date = :loan_date, due_date = :due_date, description = :description
                      WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $data['id']);
    $this->db->bind(':user_id', $this->userId);
    $this->db->bind(':account_id', $data['account_id']);
    $this->db->bind(':debtor_name', $data['debtor_name']);
    $this->db->bind(':amount', $data['amount']);
    $this->db->bind(':loan_date', $data['loan_date']);
    $this->db->bind(':due_date', !empty($data['due_date']) ? $data['due_date'] : null);
    $this->db->bind(':description', $data['description']);

    if($this->db->execute()){
      return true;
    } else {
      return false;
    }
  }

  public function deleteLoan($id){
    $this->db->query('UPDATE loans SET deleted_at = NOW() WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $id);
    $this->db->bind(':user_id', $this->userId);

    return $this->db->execute() && $this->db->rowCount() > 0;
  }

  // ---- PAYMENTS (PAGOS) ----

  public function getPaymentsByLoan($loan_id){
    // Filtra por user_id vía JOIN para que no se puedan leer pagos de préstamos ajenos
    $this->db->query('SELECT p.*, a.name as account_name
                      FROM loan_payments p
                      INNER JOIN loans l ON p.loan_id = l.id
                      LEFT JOIN accounts a ON p.account_id = a.id
                      WHERE p.loan_id = :loan_id AND l.user_id = :user_id AND p.deleted_at IS NULL
                      ORDER BY p.payment_date DESC');
    $this->db->bind(':loan_id', $loan_id);
    $this->db->bind(':user_id', $this->userId);
    return $this->db->resultSet();
  }

  public function addPayment($data){
    // Confirma que el préstamo pertenezca al usuario antes de registrar el pago
    if (!$this->getLoanById($data['loan_id'])) {
      return false;
    }

    $this->db->query('INSERT INTO loan_payments (loan_id, account_id, amount, payment_date)
                      VALUES(:loan_id, :account_id, :amount, :payment_date)');
    $this->db->bind(':loan_id', $data['loan_id']);
    $this->db->bind(':account_id', $data['account_id']);
    $this->db->bind(':amount', $data['amount']);
    $this->db->bind(':payment_date', $data['payment_date']);

    if($this->db->execute()){
      // Actualizar el monto pagado en el préstamo principal y cambiar estado si ya se pagó todo
      $this->updateLoanPaidAmount($data['loan_id']);
      return true;
    } else {
      return false;
    }
  }

  // Borra un pago SOLO si su préstamo pertenece al usuario. Deriva el loan_id del propio
  // pago (no de un parámetro controlable por el cliente) para evitar IDOR.
  public function deletePayment($paymentId){
    $this->db->query('SELECT p.loan_id
                      FROM loan_payments p
                      INNER JOIN loans l ON p.loan_id = l.id
                      WHERE p.id = :id AND l.user_id = :user_id AND p.deleted_at IS NULL');
    $this->db->bind(':id', $paymentId);
    $this->db->bind(':user_id', $this->userId);
    $payment = $this->db->single();

    if (!$payment) {
      return false; // no existe o no pertenece al usuario
    }

    $this->db->query('UPDATE loan_payments SET deleted_at = NOW() WHERE id = :id');
    $this->db->bind(':id', $paymentId);
    if($this->db->execute()){
      $this->updateLoanPaidAmount($payment->loan_id);
      return true;
    } else {
      return false;
    }
  }

  private function updateLoanPaidAmount($loan_id){
    // Calcular suma de pagos vigentes (no borrados)
    $this->db->query('SELECT SUM(amount) as total_paid FROM loan_payments WHERE loan_id = :loan_id AND deleted_at IS NULL');
    $this->db->bind(':loan_id', $loan_id);
    $result = $this->db->single();
    $total_paid = $result->total_paid ? $result->total_paid : 0;

    // Obtener préstamo
    $this->db->query('SELECT amount FROM loans WHERE id = :loan_id');
    $this->db->bind(':loan_id', $loan_id);
    $loan = $this->db->single();

    $status = ($total_paid >= $loan->amount) ? 'paid' : 'pending';

    // Actualizar prestamo
    $this->db->query('UPDATE loans SET paid_amount = :paid_amount, status = :status WHERE id = :loan_id');
    $this->db->bind(':paid_amount', $total_paid);
    $this->db->bind(':status', $status);
    $this->db->bind(':loan_id', $loan_id);
    $this->db->execute();
  }

  public function getAllLoanPayments(){
    $this->db->query('SELECT p.*, a.name as account_name
                      FROM loan_payments p
                      INNER JOIN loans l ON p.loan_id = l.id
                      LEFT JOIN accounts a ON p.account_id = a.id
                      WHERE l.user_id = :user_id AND p.deleted_at IS NULL
                      ORDER BY p.payment_date DESC');
    $this->db->bind(':user_id', $this->userId);
    return $this->db->resultSet();
  }
}
