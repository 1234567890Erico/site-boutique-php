<?php

namespace Entity;

class Produit{

    private $id_produit;
    private $reference;
    private $categorie;
    private $titre;
    private $description;
    private $couleur;
    private $taille;
    private $public;
    private $photo;
    private $prix;
    private $stock;

    // $produit->getField('prix');
    public function getField($propriete){
        return $this->$propriete;
    }
    public function getAllFields(){
        return get_object_vars($this);
    }

}