<?php
// noyau_backend/models/Vehicule.php

class Vehicule
{
    private $conn;
    private $table_name = "vehicules";

    public $id;
    public $utilisateur_id;
    public $immatriculation;
    public $modele;
    public $couleur;
    public $est_electrique;
    public $photos;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET utilisateur_id=:utilisateur_id, immatriculation=:immatriculation, modele=:modele, couleur=:couleur, est_electrique=:est_electrique, photos=:photos";
        $stmt = $this->conn->prepare($query);

        $this->immatriculation = htmlspecialchars(strip_tags($this->immatriculation));
        $this->modele = htmlspecialchars(strip_tags($this->modele));
        $this->couleur = htmlspecialchars(strip_tags($this->couleur));
        $this->est_electrique = $this->est_electrique ? 1 : 0;

        $stmt->bindParam(":utilisateur_id", $this->utilisateur_id);
        $stmt->bindParam(":immatriculation", $this->immatriculation);
        $stmt->bindParam(":modele", $this->modele);
        $stmt->bindParam(":couleur", $this->couleur);
        $stmt->bindParam(":est_electrique", $this->est_electrique);
        $stmt->bindParam(":photos", $this->photos);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getByUser($utilisateur_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE utilisateur_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $utilisateur_id);
        $stmt->execute();
        return $stmt;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET immatriculation=:immatriculation, modele=:modele, couleur=:couleur, est_electrique=:est_electrique, photos=:photos 
                  WHERE id = :id AND utilisateur_id = :utilisateur_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":immatriculation", $this->immatriculation);
        $stmt->bindParam(":modele", $this->modele);
        $stmt->bindParam(":couleur", $this->couleur);
        $stmt->bindParam(":est_electrique", $this->est_electrique);
        $stmt->bindParam(":photos", $this->photos);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":utilisateur_id", $this->utilisateur_id);
        return $stmt->execute();
    }

    public function delete($id, $utilisateur_id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND utilisateur_id = :utilisateur_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":utilisateur_id", $utilisateur_id);
        return $stmt->execute();
    }
}
