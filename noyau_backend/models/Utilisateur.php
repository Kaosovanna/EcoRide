<?php
// noyau_backend/models/Utilisateur.php

class Utilisateur
{
    private $conn;
    private $table_name = "utilisateurs";

    public $id;
    public $pseudo;
    public $email;
    public $mot_de_passe_hash; 
    public $role;
    public $credits;
    public $photo;
    public $biographie;
    public $pref_fumeur;
    public $pref_animaux;
    public $pref_musique;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET pseudo=:pseudo, email=:email, mot_de_passe_hash=:mot_de_passe_hash, role=:role, credits=:credits";
        $stmt = $this->conn->prepare($query);

        $this->pseudo = htmlspecialchars(strip_tags($this->pseudo));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->mot_de_passe_hash = password_hash($this->mot_de_passe_hash, PASSWORD_BCRYPT);
        $this->role = $this->role ?? 'passager';
        $this->credits = 20; // 20 credits offered on signup

        $stmt->bindParam(":pseudo", $this->pseudo);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":mot_de_passe_hash", $this->mot_de_passe_hash);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":credits", $this->credits);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login($email, $password)
    {
        $query = "SELECT id, pseudo, mot_de_passe_hash, role, credits, photo, biographie, pref_fumeur, pref_animaux, pref_musique 
                  FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Check if role is suspended
        if ($row && $row['role'] === 'suspendu') {
            return false;
        }

        if ($row && password_verify($password, $row['mot_de_passe_hash'])) {
            $this->id = $row['id'];
            $this->pseudo = $row['pseudo'];
            $this->role = $row['role'];
            $this->credits = $row['credits'];
            $this->photo = $row['photo'];
            $this->biographie = htmlspecialchars_decode($row['biographie'] ?? '', ENT_QUOTES);
            $this->pref_fumeur = $row['pref_fumeur'];
            $this->pref_animaux = $row['pref_animaux'];
            $this->pref_musique = $row['pref_musique'];
            return true;
        }
        return false;
    }

    public function getById($id)
    {
        $query = "SELECT id, pseudo, email, role, credits, photo, biographie, pref_fumeur, pref_animaux, pref_musique 
                  FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->id = $row['id'];
            $this->pseudo = $row['pseudo'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            $this->credits = $row['credits'];
            $this->photo = $row['photo'];
            $this->biographie = htmlspecialchars_decode($row['biographie'] ?? '', ENT_QUOTES);
            $this->pref_fumeur = $row['pref_fumeur'];
            $this->pref_animaux = $row['pref_animaux'];
            $this->pref_musique = $row['pref_musique'];
            return true;
        }
        return false;
    }

    public function getAllUsers()
    {
        $query = "SELECT id, pseudo, email, role, credits FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRole($id, $new_role)
    {
        $query = "UPDATE " . $this->table_name . " SET role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $new_role);
        $stmt->bindParam(':id', $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function updateProfile($id, $data)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET biographie = :biographie, photo = :photo, 
                      pref_fumeur = :fumeur, pref_animaux = :animaux, pref_musique = :musique 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $biographie = strip_tags($data->biographie ?? '');
        $photo = $data->photo ?? '';
        $fumeur = (int)($data->pref_fumeur ?? 0);
        $animaux = (int)($data->pref_animaux ?? 0);
        $musique = (int)($data->pref_musique ?? 0);

        $stmt->bindParam(':biographie', $biographie);
        $stmt->bindParam(':photo', $photo);
        $stmt->bindParam(':fumeur', $fumeur);
        $stmt->bindParam(':animaux', $animaux);
        $stmt->bindParam(':musique', $musique);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }
    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

}
