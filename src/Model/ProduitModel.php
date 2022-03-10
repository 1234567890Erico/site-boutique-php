<?php

namespace Model;
use PDO;

class ProduitModel extends Model{

    public function getAllCategories(){
        $requete = "SELECT DISTINCT categorie FROM ".$this->getTableName()." ORDER BY categorie";
        $resultat = $this->getDb()->query($requete);
        $donnees = $resultat->fetchAll();
        return $donnees;
    }

    public function getProduitsByCategorie($cat){
        $requete = "SELECT * FROM ".$this->getTableName()." WHERE categorie=:cat";
        $resultat = $this->getDb()->prepare($requete);
        $resultat->bindValue('cat',$cat,PDO::PARAM_STR);
        $resultat->execute();
        $donnees = $resultat->fetchAll(PDO::FETCH_CLASS,'Entity\\'.$this->getEntity());
        return $donnees;
    }
    public function recherche($critere){
        
        $requete = 'SELECT * FROM ' . $this->getTableName() . " WHERE LOWER(titre) LIKE CONCAT('%',:critere,'%') OR
        LOWER(description) LIKE  CONCAT('%',:critere,'%') OR
        LOWER(categorie) LIKE  CONCAT('%',:critere,'%') OR
        LOWER(couleur) LIKE  CONCAT('%',:critere,'%')";
        $resultat = $this->getDb()->prepare($requete);
        $resultat->bindValue('critere',$critere,PDO::PARAM_STR);
        $resultat->execute();
        $donnees = $resultat->fetchAll(PDO::FETCH_CLASS, 'Entity\\'.$this->getEntity());
        if($donnees) return $donnees;
        else return false;
    }
}