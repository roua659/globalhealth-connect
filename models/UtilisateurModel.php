<?php
require_once __DIR__ . '/../config/database.php';

class UtilisateurModel {
    private $conn;
    private $table_name = "utilisateur";
    
    public $id_user;
    public $nom;
    public $prenom;
    public $age;
    public $sexe;
    public $poids;
    public $taille;
    public $email;
    public $mot_de_passe;
    public $cas_social;
    public $date_naissance;
    public $adresse;
    public $id_role;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function readAll() {
        try {
            $query = "SELECT u.*, r.type_role FROM " . $this->table_name . " u
                      LEFT JOIN role r ON u.id_role = r.id_role
                      ORDER BY u.nom ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur readAll utilisateur: " . $e->getMessage());
            return [];
        }
    }
    
    public function getMedecins() {
        try {
            $query = "SELECT u.*, m.id_medecin, m.specialite 
                      FROM utilisateur u
                      INNER JOIN medecin m ON u.id_user = m.id_user
                      INNER JOIN role r ON u.id_role = r.id_role
                      WHERE r.type_role = 'medecin'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getMedecins: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPatients() {
        try {
            $query = "SELECT u.*, p.id_patient
                      FROM utilisateur u
                      INNER JOIN patient p ON u.id_user = p.id_user
                      INNER JOIN role r ON u.id_role = r.id_role
                      WHERE r.type_role = 'patient'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getPatients: " . $e->getMessage());
            return [];
        }
    }
    
    public function getOne($id) {
        try {
            $query = "SELECT u.*, r.type_role 
                      FROM " . $this->table_name . " u
                      LEFT JOIN role r ON u.id_role = r.id_role
                      WHERE u.id_user = :id_user";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_user", $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getOne: " . $e->getMessage());
            return null;
        }
    }
    
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      SET nom=:nom, prenom=:prenom, age=:age, sexe=:sexe,
                          email=:email, mot_de_passe=:mot_de_passe, id_role=:id_role,
                          date_naissance=:date_naissance, adresse=:adresse";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":nom", $data['nom']);
            $stmt->bindParam(":prenom", $data['prenom']);
            $stmt->bindParam(":age", $data['age']);
            $stmt->bindParam(":sexe", $data['sexe']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":mot_de_passe", $data['mot_de_passe']);
            $stmt->bindParam(":id_role", $data['id_role']);
            $stmt->bindParam(":date_naissance", $data['date_naissance']);
            $stmt->bindParam(":adresse", $data['adresse']);
            
            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erreur create utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET nom=:nom, prenom=:prenom, age=:age, sexe=:sexe,
                          email=:email, date_naissance=:date_naissance, adresse=:adresse
                      WHERE id_user = :id_user";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":nom", $data['nom']);
            $stmt->bindParam(":prenom", $data['prenom']);
            $stmt->bindParam(":age", $data['age']);
            $stmt->bindParam(":sexe", $data['sexe']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":date_naissance", $data['date_naissance']);
            $stmt->bindParam(":adresse", $data['adresse']);
            $stmt->bindParam(":id_user", $id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur update utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id_user = :id_user";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_user", $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur delete utilisateur: " . $e->getMessage());
            return false;
        }
    }
    
    public function authenticate($email, $password) {
        try {
            $query = "SELECT u.*, r.type_role 
                      FROM " . $this->table_name . " u
                      LEFT JOIN role r ON u.id_role = r.id_role
                      WHERE u.email = :email AND u.mot_de_passe = MD5(:password)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $password);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur authenticate: " . $e->getMessage());
            return null;
        }
    }
}
?>