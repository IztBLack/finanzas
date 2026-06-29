<?php
class Category {
  private $db;
  private $userId;

  public function __construct($userId = null){
    $this->db = new Database;
    $this->userId = $userId ?? ($_SESSION['user_id'] ?? null);
  }

  // Obtener categorías del usuario
  public function getCategories(){
    $this->db->query('SELECT * FROM categories WHERE user_id = :user_id AND deleted_at IS NULL ORDER BY type ASC, name ASC');
    $this->db->bind(':user_id', $this->userId);
    return $this->db->resultSet();
  }

  // Obtener una sola categoría
  public function getCategoryById($id){
    $this->db->query('SELECT * FROM categories WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $id);
    $this->db->bind(':user_id', $this->userId);
    return $this->db->single();
  }

  // Crear categoría
  public function addCategory($data){
    $this->db->query('INSERT INTO categories (user_id, name, type) VALUES(:user_id, :name, :type)');
    $this->db->bind(':user_id', $this->userId);
    $this->db->bind(':name', $data['name']);
    $this->db->bind(':type', $data['type']);

    if($this->db->execute()){
      return true;
    } else {
      return false;
    }
  }

  // Actualizar categoría
  public function updateCategory($data){
    $this->db->query('UPDATE categories SET name = :name, type = :type WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $data['id']);
    $this->db->bind(':user_id', $this->userId);
    $this->db->bind(':name', $data['name']);
    $this->db->bind(':type', $data['type']);

    if($this->db->execute()){
      return true;
    } else {
      return false;
    }
  }

  // Eliminar categoría (soft delete)
  public function deleteCategory($id){
    $this->db->query('UPDATE categories SET deleted_at = NOW() WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL');
    $this->db->bind(':id', $id);
    $this->db->bind(':user_id', $this->userId);

    return $this->db->execute() && $this->db->rowCount() > 0;
  }
}
