<?php
class Transaction {
  private $db;
  private $userId;

  public function __construct($userId = null){
    $this->db = new Database;
    $this->userId = $userId ?? ($_SESSION['user_id'] ?? null);
  }

  // Obtener transacciones con detalles de cuenta y categoría
  public function getTransactions(){
    $this->db->query('SELECT t.id, t.amount, t.type, t.description, t.transaction_date,
                             a.name as account_name, c.name as category_name
                      FROM transactions t
                      INNER JOIN accounts a ON t.account_id = a.id
                      INNER JOIN categories c ON t.category_id = c.id
                      WHERE t.user_id = :user_id AND t.deleted_at IS NULL
                      ORDER BY t.transaction_date DESC, t.created_at DESC');
    $this->db->bind(':user_id', $this->userId);
    return $this->db->resultSet();
  }

  public function getTransactionById($id){
    $this->db->query('SELECT * FROM transactions WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $id);
    $this->db->bind(':user_id', $this->userId);
    return $this->db->single();
  }

  public function addTransaction($data){
    $this->db->query('INSERT INTO transactions (user_id, account_id, category_id, amount, type, description, transaction_date)
                      VALUES(:user_id, :account_id, :category_id, :amount, :type, :description, :transaction_date)');
    $this->db->bind(':user_id', $this->userId);
    $this->db->bind(':account_id', $data['account_id']);
    $this->db->bind(':category_id', $data['category_id']);
    $this->db->bind(':amount', $data['amount']);
    $this->db->bind(':type', $data['type']);
    $this->db->bind(':description', $data['description']);
    $this->db->bind(':transaction_date', $data['transaction_date']);

    if($this->db->execute()){
      return true;
    } else {
      return false;
    }
  }

  public function updateTransaction($data){
    $this->db->query('UPDATE transactions
                      SET account_id = :account_id, category_id = :category_id, amount = :amount,
                          type = :type, description = :description, transaction_date = :transaction_date
                      WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $data['id']);
    $this->db->bind(':user_id', $this->userId);
    $this->db->bind(':account_id', $data['account_id']);
    $this->db->bind(':category_id', $data['category_id']);
    $this->db->bind(':amount', $data['amount']);
    $this->db->bind(':type', $data['type']);
    $this->db->bind(':description', $data['description']);
    $this->db->bind(':transaction_date', $data['transaction_date']);

    if($this->db->execute()){
      return true;
    } else {
      return false;
    }
  }

  public function deleteTransaction($id){
    $this->db->query('UPDATE transactions SET deleted_at = NOW() WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $id);
    $this->db->bind(':user_id', $this->userId);

    return $this->db->execute() && $this->db->rowCount() > 0;
  }
}
