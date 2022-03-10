<?php

namespace Model;

use PDO, \Manager\PDOManager;

class Model{
    private $db;
    public function __construct(){
        $this->db = PDOManager::getInstance()->getPdo();
        // $this->db  <== mon objet PDO
    }
    public function getDb(){
        return $this->db;
    }
    public function getEntity(){
        // Model\ProduitModel
        // Produit
        return str_replace(array('Model\\','Model'),'',get_called_class());
    }
    public function getTableName(){
        // Produit => produit
        return strtolower($this->getEntity());
    }
    public function getIdColumnName(){
        $requete = 'DESC '.$this->getTableName();
        $resultat = $this->getDb()->query($requete);
        $donnees = $resultat->fetch();
        return $donnees->Field;
    }
    public function findAll(){
        $requete = "SELECT * FROM " . $this->getTableName();
        $resultat = $this->getDb()->query($requete);
        $donnees = $resultat->fetchAll(PDO::FETCH_CLASS,'Entity\\' . $this->getEntity());
        return $donnees;
    }
    public function find($id){
        $requete = "SELECT * FROM " . $this->getTableName() . " WHERE " . $this->getIdColumnName() . "=:id";
        $resultat = $this->getDb()->prepare($requete);
        $resultat->bindValue('id',$id,PDO::PARAM_INT);
        $resultat->execute();
        $resultat->setFetchMode(PDO::FETCH_CLASS,'Entity\\' . $this->getEntity() );
        $donnees = $resultat->fetch();
        return $donnees;
    }
    public function delete($id){
        $requete = "DELETE FROM " . $this->getTableName() . " WHERE " . $this->getIdColumnName() . "=:id";
        $resultat = $this->getDb()->prepare($requete);
        $resultat->bindValue('id',$id,PDO::PARAM_INT);
        return $resultat->execute();
    }

    public function register($infos){
        $requete = "INSERT INTO " . $this->getTableName() . " (".implode(',',array_keys($infos)).") VALUES (:".implode(',:',array_keys($infos)).")";        
        $resultat = $this->getDb()->prepare($requete);
        if($resultat->execute($infos)){
            return $this->getDb()->lastInsertId();
        }
        else{
            return false;
        }
    }

    public function update($id,$infos){
        $newValues = array();
        foreach(array_keys($infos) as $key){
            $newValues[] = "$key = :$key";
        }
        $requete = "UPDATE " . $this->getTableName() . " SET " . implode(",",$newValues) . " WHERE ".$this->getIdColumnName()."=:id";
        $infos['id'] = $id;
        $resultat = $this->getDb()->prepare($requete);
        return $resultat->execute($infos);       
    }

}
