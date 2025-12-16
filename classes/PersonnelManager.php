<?php
require_once 'BaseManager.php';

class PersonnelManager extends BaseManager {
    protected $table = 'personnel';

    public function getAll() {
        $stmt = $this->db->prepare("SELECT id, name, email, address, username FROM personnel ORDER BY name");
        $stmt->execute();
        $personnel = $stmt->fetchAll();

        foreach($personnel as &$person) {
            $person['roles'] = $this->getRoles($person['id']);
        }
        return $personnel;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, name, email, address, username FROM personnel WHERE id = ?");
        $stmt->execute([$id]);
        $person = $stmt->fetch();

        if($person) {
            $person['roles'] = $this->getRoles($id);
        }
        return $person;
    }

    // rollen ophalen
    public function getRoles($personnelId) {
        $stmt = $this->db->prepare("SELECT role FROM personnel_roles WHERE personnel_id = ?");
        $stmt->execute([$personnelId]);
        $rows = $stmt->fetchAll();

        $roles = [];
        foreach($rows as $row) {
            $roles[] = $row['role'];
        }
        return $roles;
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO personnel (name, email, address, username, password)
            VALUES (?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $data['name'],
            $data['email'],
            $data['address'],
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        ]);

        if($result) {
            $personnelId = $this->db->lastInsertId();
            $this->saveRoles($personnelId, $data['roles'] ?? []);
            return $personnelId;
        }
        return false;
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE personnel SET name = ?, email = ?, address = ? WHERE id = ?");
        $result = $stmt->execute([$data['name'], $data['email'], $data['address'], $id]);

        if(isset($data['roles'])) {
            $this->saveRoles($id, $data['roles']);
        }
        return $result;
    }

    // rollen opslaan
    public function saveRoles($personnelId, $roles) {
        $stmt = $this->db->prepare("DELETE FROM personnel_roles WHERE personnel_id = ?");
        $stmt->execute([$personnelId]);

        foreach($roles as $role) {
            $stmt = $this->db->prepare("INSERT INTO personnel_roles (personnel_id, role) VALUES (?, ?)");
            $stmt->execute([$personnelId, $role]);
        }
    }

    public function updatePassword($id, $newPassword) {
        $stmt = $this->db->prepare("UPDATE personnel SET password = ? WHERE id = ?");
        return $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }
}
