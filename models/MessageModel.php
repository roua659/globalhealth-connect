<?php
require_once 'config/database.php';

class MessageModel {
    private $conn;
    private $table_name = "messages";

    public $id;
    public $id_user;
    public $contenu;
    public $date_envoi;
    public $statut;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET id_user = :id_user,
                      contenu = :contenu,
                      date_envoi = NOW(),
                      statut = 'non lu'";
        
        $stmt = $this->conn->prepare($query);
        
        $this->id_user = htmlspecialchars(strip_tags($this->id_user));
        $this->contenu = htmlspecialchars(strip_tags($this->contenu));
        
        $stmt->bindParam(':id_user', $this->id_user);
        $stmt->bindParam(':contenu', $this->contenu);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
