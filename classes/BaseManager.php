<?php
// BaseManager - parent class
abstract class BaseManager {
    protected $db;
    protected $table;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM " . $this->table . " WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function countAll() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM " . $this->table);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'];
    }

    // elke child moet deze zelf maken
    abstract public function create($data);
    abstract public function update($id, $data);
}
