<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Add User / Register
    public function register($data) {
        $this->db->query('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        return $this->db->execute();
    }

    // Find User By Email
    public function findUserByEmail($email) {
        $this->db->query("SELECT * FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        $row = $this->db->single();

        return ($row ? true : false);
    }

    // Login / Authenticate User
    public function login($email, $password) {
        $this->db->query("SELECT * FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        $row = $this->db->single();

        if ($row) {
            if (password_verify($password, $row->password)) {
                return $row;
            }
        }
        return false;
    }

    // Find User By ID
    public function getUserById($id) {
        $this->db->query("SELECT * FROM users WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Delete when password forgot
    public function getUserByEmail($email) {
        $this->db->query("SELECT * FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    // Update password
    public function updatePassword($id, $new_password) {
        $this->db->query('UPDATE users SET password = :password WHERE id = :id');
        $this->db->bind(':password', password_hash($new_password, PASSWORD_DEFAULT));
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Actualizar nombre y correo del perfil
    public function updateProfile($id, $name, $email) {
        $this->db->query('UPDATE users SET name = :name, email = :email WHERE id = :id');
        $this->db->bind(':name', $name);
        $this->db->bind(':email', $email);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // Set require_password_change flag
    public function setRequirePasswordChange($id, $status) {
        $this->db->query('UPDATE users SET require_password_change = :status WHERE id = :id');
        $this->db->bind(':status', $status ? 1 : 0);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>