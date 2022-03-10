<?php

namespace Model;
use PDO;

class MembreModel extends Model{

    public function existsPseudo($pseudo){

        $requete = "SELECT * FROM ".$this->getTableName()." WHERE pseudo=:pseudo";
        $resultat = $this->getDb()->prepare($requete);
        $resultat->bindValue('pseudo',$pseudo,PDO::PARAM_STR);
        $resultat->execute();
        $resultat->setFetchMode(PDO::FETCH_CLASS,'Entity\\'.$this->getEntity());
        $donnees = $resultat->fetch();
        return $donnees;
    }


}