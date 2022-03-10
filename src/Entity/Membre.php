<?php

namespace Entity;

class Membre{

    private $id_membre;
    private $pseudo;
    private $mdp;
    private $nom;
    private $prenom;
    private $email;
    private $civilite;
    private $ville;
    private $code_postal;
    private $adresse;    
    private $statut;

    // ex $membre->getField('pseudo');
    public function getField($propriete){
        return $this->$propriete;
        // return $this->pseudo;
    }

    // $membre->setField('ville','Lyon');
    public function setField($propriete,$valeur){
        $this->$propriete = $valeur;
    }

    public function isAdmin(){
        return ( $this->getField('statut') == 1 ); // booleen
    }
    public function getAllFields(){
        return get_object_vars($this);
    }

}