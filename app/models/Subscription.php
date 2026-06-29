<?php
class Subscription {
  private $db;
  private $userId;

  public function __construct($userId = null){
    $this->db = new Database;
    $this->userId = $userId ?? ($_SESSION['user_id'] ?? null);
  }

  // Obtener todas las suscripciones del usuario
  public function getSubscriptions(){
    $this->db->query('SELECT s.*, a.name as account_name FROM subscriptions s LEFT JOIN accounts a ON s.account_id = a.id WHERE s.user_id = :user_id AND s.deleted_at IS NULL ORDER BY s.billing_day ASC');
    $this->db->bind(':user_id', $this->userId);
    return $this->db->resultSet();
  }

  // Obtener suscripción por ID
  public function getSubscriptionById($id){
    $this->db->query('SELECT * FROM subscriptions WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $id);
    $this->db->bind(':user_id', $this->userId);
    return $this->db->single();
  }

  // Agregar suscripción
  public function addSubscription($data){
    $this->db->query('INSERT INTO subscriptions (user_id, name, amount, account_id, billing_cycle, billing_day, status) VALUES(:user_id, :name, :amount, :account_id, :billing_cycle, :billing_day, :status)');
    $this->db->bind(':user_id', $this->userId);
    $this->db->bind(':name', $data['name']);
    $this->db->bind(':amount', $data['amount']);
    $this->db->bind(':account_id', $data['account_id']);
    $this->db->bind(':billing_cycle', $data['billing_cycle']);
    $this->db->bind(':billing_day', $data['billing_day']);
    $this->db->bind(':status', $data['status']);

    return $this->db->execute();
  }

  // Actualizar suscripción
  public function updateSubscription($data){
    $this->db->query('UPDATE subscriptions SET name = :name, amount = :amount, account_id = :account_id, billing_cycle = :billing_cycle, billing_day = :billing_day, status = :status WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $data['id']);
    $this->db->bind(':user_id', $this->userId);
    $this->db->bind(':name', $data['name']);
    $this->db->bind(':amount', $data['amount']);
    $this->db->bind(':account_id', $data['account_id']);
    $this->db->bind(':billing_cycle', $data['billing_cycle']);
    $this->db->bind(':billing_day', $data['billing_day']);
    $this->db->bind(':status', $data['status']);

    return $this->db->execute();
  }

  // Eliminar suscripción (soft delete)
  public function deleteSubscription($id){
    $this->db->query('UPDATE subscriptions SET deleted_at = NOW() WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $id);
    $this->db->bind(':user_id', $this->userId);

    return $this->db->execute() && $this->db->rowCount() > 0;
  }

  // Obtener gasto mensual total en suscripciones activas
  public function getMonthlySubscriptionTotal(){
    $this->db->query("SELECT SUM(CASE WHEN billing_cycle = 'monthly' THEN amount WHEN billing_cycle = 'yearly' THEN amount / 12 ELSE 0 END) as total FROM subscriptions WHERE user_id = :user_id AND status = 'active' AND deleted_at IS NULL");
    $this->db->bind(':user_id', $this->userId);
    $row = $this->db->single();
    return $row->total ?? 0;
  }
}
