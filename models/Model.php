<?php

/**
 * Base Model Class - Provides common CRUD functionality
 */
abstract class Model {
    protected $table;
    protected $fillable = [];
    protected $attributes = [];
    protected $pdo;

    public function __construct($data = []) {
        $this->pdo = config::getConnexion();
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    /**
     * Fill model attributes from array
     */
    public function fill($data) {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Generic attribute getter
     */
    protected function getAttribute($key, $default = null) {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Generic attribute setter
     */
    protected function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Public getter for any attribute
     */
    public function get($key, $default = null) {
        return $this->getAttribute($key, $default);
    }

    /**
     * Public setter for any attribute
     */
    public function set($key, $value) {
        return $this->setAttribute($key, $value);
    }

    /**
     * Magic getter
     */
    public function __get($key) {
        return $this->get($key);
    }

    /**
     * Magic setter
     */
    public function __set($key, $value) {
        return $this->set($key, $value);
    }

    /**
     * Get all records with pagination
     */
    public function getAll($limit = 100, $offset = 0) {
        try {
            $limit = (int)$limit;
            $offset = (int)$offset;
            $query = "SELECT * FROM {$this->table} LIMIT {$limit} OFFSET {$offset}";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Find record by ID column
     */
    public function findByIdColumn($idColumn, $id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE {$idColumn} = :{$idColumn}";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(["{$idColumn}" => $id]);
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

    /**
     * Create new record
     */
    public function create() {
        try {
            $columns = array_keys($this->attributes);
            // Only filter out auto-increment primary keys (id, id_publication, id_commentaire, id_user)
            // BUT keep foreign keys like id_publication and id_user for comments and related records
            $columns = array_filter($columns, function($col) {
                // Don't filter out FK columns - only filter if they're auto-increment PKs
                // For comments: keep id_publication and id_user (they're FKs, not auto-increment)
                // Only filter id_commentaire if it's auto-increment (which it is)
                return !in_array($col, ['id_commentaire']);
            });
            
            if (empty($columns)) {
                return ['success' => false, 'error' => 'No data to insert'];
            }
            
            $placeholders = array_map(function($col) {
                return ":{$col}";
            }, $columns);
            
            $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $this->pdo->prepare($sql);
            
            $bindings = [];
            foreach ($columns as $col) {
                $bindings[$col] = $this->attributes[$col];
            }
            
            $stmt->execute($bindings);
            $lastId = $this->pdo->lastInsertId();
            
            // Set ID based on table
            if (isset($this->attributes['id'])) {
                $this->attributes['id'] = $lastId;
            } elseif (isset($this->attributes['id_publication'])) {
                $this->attributes['id_publication'] = $lastId;
            } elseif (isset($this->attributes['id_commentaire'])) {
                $this->attributes['id_commentaire'] = $lastId;
            }
            
            return ['success' => true, 'id' => $lastId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update record
     */
    public function update() {
        try {
            $idColumn = $this->getIdColumn();
            $id = $this->getAttribute($idColumn);
            
            if (!$id) {
                return ['success' => false, 'error' => 'No ID set'];
            }
            
            $columns = array_keys($this->attributes);
            $columns = array_filter($columns, function($col) {
                return !in_array($col, ['id', 'id_publication', 'id_commentaire', 'id_user']);
            });
            
            if (empty($columns)) {
                return ['success' => false, 'error' => 'No data to update'];
            }
            
            $updates = array_map(function($col) {
                return "{$col} = :{$col}";
            }, $columns);
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE {$idColumn} = :{$idColumn}";
            $stmt = $this->pdo->prepare($sql);
            
            $bindings = [];
            foreach ($columns as $col) {
                $bindings[$col] = $this->attributes[$col];
            }
            $bindings[$idColumn] = $id;
            
            $stmt->execute($bindings);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete record
     */
    public function delete() {
        try {
            $idColumn = $this->getIdColumn();
            $id = $this->getAttribute($idColumn);
            
            if (!$id) {
                return ['success' => false, 'error' => 'No ID set'];
            }
            
            $sql = "DELETE FROM {$this->table} WHERE {$idColumn} = :{$idColumn}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(["{$idColumn}" => $id]);
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Convert to array
     */
    public function toArray() {
        return $this->attributes;
    }

    /**
     * Convert to JSON
     */
    public function toJson() {
        return json_encode($this->attributes);
    }

    /**
     * Get the primary key column name
     */
    protected function getIdColumn() {
        if ($this->getAttribute('id')) return 'id';
        if ($this->getAttribute('id_publication')) return 'id_publication';
        if ($this->getAttribute('id_commentaire')) return 'id_commentaire';
        if ($this->getAttribute('id_user')) return 'id_user';
        return 'id';
    }
}
