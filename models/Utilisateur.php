<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Model.php';

class Utilisateur extends Model {
    protected $table = 'utilisateur';
    protected $fillable = ['id', 'nom', 'prenom', 'email', 'telephone', 'password', 'id_role'];

    public function getNom() {
        return $this->get('nom');
    }

    public function setNom($nom) {
        return $this->set('nom', $nom);
    }

    public function getPrenom() {
        return $this->get('prenom');
    }

    public function setPrenom($prenom) {
        return $this->set('prenom', $prenom);
    }

    public function getEmail() {
        return $this->get('email');
    }

    public function setEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }
        return $this->set('email', $email);
    }

    public function getTelephone() {
        return $this->get('telephone');
    }

    public function setTelephone($telephone) {
        return $this->set('telephone', $telephone);
    }

    public function getPassword() {
        return $this->get('password');
    }

    public function setPassword($password) {
        return $this->set('password', password_hash($password, PASSWORD_BCRYPT));
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->getPassword());
    }

    public function getIdRole() {
        return $this->get('id_role');
    }

    public function setIdRole($roleId) {
        return $this->set('id_role', $roleId);
    }

    public function getFullName() {
        return $this->getNom() . ' ' . $this->getPrenom();
    }

    public function findByEmail($email) {
        try {
            $pdo = config::getConnexion();
            $query = "SELECT * FROM utilisateur WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['email' => $email]);
            $result = $stmt->fetch();
            
            if ($result) {
                $this->fill($result);
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
