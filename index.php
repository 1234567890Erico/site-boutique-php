<?php
/*
http://localhost/boutique/produit/fiche/85
entité : produit
controller : ProduitController
modele : ProduitModel

Dans le controller
public function fiche($id_produit){
    // rendu de la vue correspondant à la fiche produit du produit 85 'fiche_produit.html'
}
http://localhost/boutique/produit/liste
*/

// fuseau horaire
date_default_timezone_set('Europe/Paris');

// chargement du fichier d'autoload
require_once __DIR__ . '/vendor/autoload.php';

session_name('FASHIONSESSION');
session_start();

// instanciation de l'application et lancement
$app = new Manager\Application;
$app->run();