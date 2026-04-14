<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Model.php';

class Role extends Model {
    protected $table = 'role';
    protected $fillable = ['id', 'nom', 'description'];

    public function getNom() {
        return $this->get('nom');
    }

    public function setNom($nom) {
        return $this->set('nom', $nom);
    }

    public function getDescription() {
        return $this->get('description');
    }

    public function setDescription($description) {
        return $this->set('description', $description);
    }
}
