<?php

namespace Entity;

class Commande{

    private $id_commande;
    private $id_membre;
    private $id_montant;
    private $date_enregistrement;
    private $etat;

    public function getField($propriete){
        return $this->$propriete;
    }
    public function getAllFields(){
        return get_object_vars($this);
    }

}