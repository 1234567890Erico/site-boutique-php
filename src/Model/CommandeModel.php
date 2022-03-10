<?php

namespace Model;
use PDO;

class CommandeModel extends Model{

    public function getMesCommandes(){
        $requete = "SELECT * FROM ".$this->getTableName()." WHERE id_membre=:id_membre ORDER BY date_enregistrement DESC";
        $resultat = $this->getDb()->prepare($requete);
        $resultat->bindValue('id_membre', $_SESSION['membre']->getField('id_membre') ,PDO::PARAM_INT);
        $resultat->execute();
        $donnees = $resultat->fetchAll(PDO::FETCH_CLASS,'Entity\\'.$this->getEntity());
        return $donnees;
    }

    public function getDetailsCommandes(){
        $requete = "SELECT d.*, d.id_commande as IDCMD, p.TITRE, p.photo as PHOTO, p.reference as REFERENCE from details_commande d 
        INNER JOIN produit p ON p.id_produit = d.id_produit
        INNER JOIN commande c ON c.id_commande = d.id_commande
        WHERE c.id_membre=:id_membre";
        $resultat = $this->getDb()->prepare($requete);
        $resultat->bindValue('id_membre', $_SESSION['membre']->getField('id_membre') ,PDO::PARAM_INT);
        $resultat->execute();
        $donnees = $resultat->fetchAll();
        return $donnees;
    }

    public function registerDetailsCommande($infos){
        $requete = "INSERT INTO details_commande (".implode(',',array_keys($infos)).") VALUES (:".implode(',:',array_keys($infos)).")";        
        $resultat = $this->getDb()->prepare($requete);
        if($resultat->execute($infos)){
            return $this->getDb()->lastInsertId();
        }
        else{
            return false;
        }
    }

    public function getAllCommandes(){
        $requete = "SELECT * FROM ".$this->getTableName()." c
        INNER JOIN membre m ON c.id_membre = m.id_membre ORDER BY date_enregistrement DESC ";
        $resultat = $this->getDb()->query($requete);
        $donnees = $resultat->fetchAll();
        if($donnees) return $donnees;
        else return false;
    }

    public function getDetailsAllCommandes(){

        $requete = 'SELECT d.*,d.id_commande as IDCMD, p.titre as TITRE, p.photo as PHOTO,p.reference AS REFERENCE
        FROM details_commande d
        INNER JOIN produit p ON p.id_produit = d.id_produit
        INNER JOIN commande c ON d.id_commande = c.id_commande';
        $resultat = $this->getDb()->query($requete);
        $donnees = $resultat->fetchAll();
        return $donnees;
    }


}