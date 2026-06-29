<?php
class Account {
  private $db;
  private $userId;

  public function __construct($userId = null){
    $this->db = new Database;
    $this->userId = $userId ?? ($_SESSION['user_id'] ?? null);
  }

  // Obtener todas las cuentas del usuario logueado
  public function getAccounts(){
    $this->db->query("SELECT a.*,
                             (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE account_id = a.id AND type = 'income' AND deleted_at IS NULL) +
                             (SELECT COALESCE(SUM(amount), 0) FROM loan_payments WHERE account_id = a.id AND deleted_at IS NULL) as total_income,

                             (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE account_id = a.id AND type = 'expense' AND deleted_at IS NULL) +
                             (SELECT COALESCE(SUM(amount), 0) FROM loans WHERE account_id = a.id AND deleted_at IS NULL) as total_expense
                      FROM accounts a
                      WHERE a.user_id = :user_id AND a.deleted_at IS NULL
                      ORDER BY a.created_at DESC");
    $this->db->bind(':user_id', $this->userId);
    return $this->db->resultSet();
  }

  // Obtener una sola cuenta
  public function getAccountById($id){
    $this->db->query("SELECT a.*,
                             (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE account_id = a.id AND type = 'income' AND deleted_at IS NULL) +
                             (SELECT COALESCE(SUM(amount), 0) FROM loan_payments WHERE account_id = a.id AND deleted_at IS NULL) as total_income,

                             (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE account_id = a.id AND type = 'expense' AND deleted_at IS NULL) +
                             (SELECT COALESCE(SUM(amount), 0) FROM loans WHERE account_id = a.id AND deleted_at IS NULL) as total_expense
                      FROM accounts a
                      WHERE a.id = :id AND a.user_id = :user_id AND a.deleted_at IS NULL");
    $this->db->bind(':id', $id);
    $this->db->bind(':user_id', $this->userId);
    return $this->db->single();
  }

  // Crear cuenta
  public function addAccount($data){
    $this->db->query('INSERT INTO accounts (user_id, name, initial_balance, type, credit_limit, cutoff_date, payment_date) VALUES(:user_id, :name, :initial_balance, :type, :credit_limit, :cutoff_date, :payment_date)');
    $this->db->bind(':user_id', $this->userId);
    $this->db->bind(':name', $data['name']);
    $this->db->bind(':initial_balance', $data['initial_balance']);
    $this->db->bind(':type', $data['type']);
    $this->db->bind(':credit_limit', $data['credit_limit'] ?: null);
    $this->db->bind(':cutoff_date', $data['cutoff_date'] ?: null);
    $this->db->bind(':payment_date', $data['payment_date'] ?: null);

    return $this->db->execute();
  }

  // Actualizar cuenta
  public function updateAccount($data){
    $this->db->query('UPDATE accounts SET name = :name, initial_balance = :initial_balance, type = :type, credit_limit = :credit_limit, cutoff_date = :cutoff_date, payment_date = :payment_date WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $data['id']);
    $this->db->bind(':user_id', $this->userId);
    $this->db->bind(':name', $data['name']);
    $this->db->bind(':initial_balance', $data['initial_balance']);
    $this->db->bind(':type', $data['type']);
    $this->db->bind(':credit_limit', $data['credit_limit'] ?: null);
    $this->db->bind(':cutoff_date', $data['cutoff_date'] ?: null);
    $this->db->bind(':payment_date', $data['payment_date'] ?: null);

    return $this->db->execute();
  }

  // Eliminar cuenta (soft delete)
  public function deleteAccount($id){
    $this->db->query('UPDATE accounts SET deleted_at = NOW() WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $id);
    $this->db->bind(':user_id', $this->userId);

    return $this->db->execute() && $this->db->rowCount() > 0;
  }
}
