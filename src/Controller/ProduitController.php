<?php

namespace Controller;

class ProduitController extends Controller
{

    public function afficheAll($categorie = '')
    {
        $params['categories'] = $this->getModel()->getAllCategories();

        if(empty($categorie)){
            $params['produits'] = $this->getModel()->findAll();
        }
        else{
            $params['produits'] = $this->getModel()->getProduitsByCategorie($categorie);
        }

        $params['title'] = 'Fashion - Nos articles';
        return $this->render('layout.html', 'boutique.html', $params);
    }

    public function adminProduits()
    {
        if (isset($_SESSION['membre']) && $_SESSION['membre']->isAdmin()) {

            $params['title'] = 'Gestion des produits';
            $params['produits'] = $this->getModel()->findAll();
            return $this->render('layout.html', 'adminproduits.html', $params);
        } else {
            $this->redirect($this->getUrl() . 'membre/connexion');
        }
    }

    public function ajoutProduit()
    {
        if (isset($_SESSION['membre']) && $_SESSION['membre']->isAdmin()) {
            if (!empty($_POST)) { // j'ai cliquer sur "enregistrer"

                list($erreurs,$params) = $this->verifFormulaire();

                // enregistrer le produit en BDD
                if (empty($erreurs)) {
                    $this->getModel()->register($_POST);
                    $this->redirect($this->getUrl() . 'produit/adminProduits');
                }
            }

            $params['title'] = "Ajout d'un produit";
            $params['erreur'] = (!empty($erreurs)) ? implode('<br>', $erreurs) : '';
            return $this->render('layout.html', 'formproduit.html', $params);
        } else {
            $this->redirect($this->getUrl() . 'membre/connexion');
        }
    }
    public function editProduit($id)
    {
        if (isset($_SESSION['membre']) && $_SESSION['membre']->isAdmin()) {

            $params['currentProduct'] = $this->getModel()->find($id)->getAllFields();

            // traitement du formulaire
            if (!empty($_POST)) { // j'ai cliquer sur "enregistrer"

                list($erreurs,$params) = $this->verifFormulaire();
               
                // enregistrer le produit en BDD
                if (empty($erreurs)) {
                    $this->getModel()->update($id,$_POST);
                    $this->redirect($this->getUrl() . 'produit/adminProduits');
                }
            }
            //-----------------------------------

            $params['title'] = "Edition d'un produit";
            $params['erreur'] = (!empty($erreurs)) ? implode('<br>',$erreurs) : '';
            return $this->render('layout.html','formproduit.html',$params);

        } else {
            $this->redirect($this->getUrl() . 'membre/connexion');
        }
    }
    public function deleteProduit($id)
    {
        if (isset($_SESSION['membre']) && $_SESSION['membre']->isAdmin()) {

            $photos = $this->getModel()->find($id)->getField('photo');
            if( $this->getModel()->delete($id) ){
                $liste = explode('*',$photos);
                $dirPhoto = __DIR__ . '/../../web/photo/';
                foreach($liste as $photo){
                    if(file_exists($dirPhoto . $photo)) unlink($dirPhoto . $photo);
                }
                $this->redirect($this->getUrl() . 'produit/adminProduits');
            }

        } else {
            $this->redirect($this->getUrl() . 'membre/connexion');
        }
    }

    public function verifFormulaire(){

        $params['currentProduct'] = $_POST;
        if( !empty($_POST['current_photos']) ){
            $_POST['photo'] = $_POST['current_photos'];
            $params['currentProduct']['photo'] = $_POST['photo'];
            unset($_POST['current_photos']);
        }

        $erreurs = array();
        $champsvides = 0;
        foreach ($_POST as $key => $value) {
            $_POST[$key] = htmlspecialchars($value, ENT_QUOTES);
            if (trim($_POST[$key]) == '') $champsvides++;
        }
        if (empty($_FILES['photo']['name'][0]) && empty($_POST['photo'])) {
            $champsvides++;
        }
        if ($champsvides > 0) {
            $erreurs[] = 'Il manque ' . $champsvides . ' information(s)';
        }

        if (!empty($_FILES['photo']['name'][0])) {
            $listeFichiers = array();
            // copier les images dans PHOTO
            $ext = array('image/jpeg', 'image/png', 'image/gif');
            foreach ($_FILES['photo']['name'] as $key => $value) {
                $listeFichiers[] = $_POST['reference'] . '_' . $key . '_' . $value;
                if (!in_array($_FILES['photo']['type'][$key], $ext)) {
                    $erreursimg[] = 'Format requis : JPEG, PNG, GIF : ' . $_FILES['photo']['name'][$key];
                }
                if (empty($erreursimg)) {
                    if ($_FILES['photo']['size'][$key] == 0 || $_FILES['photo']['size'][$key] > 2.048e6) {
                        $erreursimg[] = 'Taille maxi : 2Mo : ' . $_FILES['photo']['name'][$key];
                    }
                    if (empty($erreursimg)) {
                        $chemin = __DIR__  . '/../../web/photo/';
                        move_uploaded_file($_FILES['photo']['tmp_name'][$key], $chemin . $listeFichiers[$key]);
                    }
                }else{
                    $erreurs = array_merge($erreurs,$erreursimg);
                }
            }
            $_POST['photo'] = implode('*', $listeFichiers);
            $params['currentProduct']['photo'] =  $_POST['photo'];
        }
        return array($erreurs,$params);
    }

    public function ficheProduit($id){

        if(!empty($_POST)){
            //var_dump($_POST);
            $this->ajoutProduiPanier($id,$_POST['quantite']);
            $_SESSION['pending_message'] = 'Le produit a été ajouté au panier';
            $_SESSION['type_message'] = 'success';
            $this->redirect($this->getUrl() . 'produit/ficheProduit/'. $id);
        }


        $produit = $this->getModel()->find($id);
        if($produit){

            $params['produit'] = $produit;
            $params['title'] = 'Fiche produit : ' . $produit->getField('titre');
            return $this->render('layout.html','ficheproduit.html',$params);
        }
        else{
            $this->redirect($this->getUrl());
        }

    }

    //  ----------------------- Moteur de recherche --------------------------

    public function recherche()
    {

        if (!empty($_POST['critere'])) {

            $params['produits'] = $this->getModel()->recherche(strtolower($_POST['critere']));
            $params['title'] = 'Recherche de ' . $_POST['critere'];
            return $this->render('layout.html', 'recherche.html', $params);
        } else {
            $this->redirect($this->getUrl());
        }
    }

    // -------------------------------------  FONCTIONS LIEES AU PANIER --------------------------------------
    public function creationPanier(){
        if(!isset($_SESSION['panier'])){
            $_SESSION['panier']['id_produit'] = array(); // (10,12,47,52)
            $_SESSION['panier']['quantite'] = array(); // (1,2,1,3)
            $_SESSION['panier']['prix'] = array(); // (49,50,18,29)
        }
    }

    public function ajoutProduiPanier($id,$qte){
        $this->creationPanier();
        $position_produit = array_search($id,$_SESSION['panier']['id_produit']);
        if($position_produit !== false){
            // je l'ai trouvé, je mets à jour la quantité
            $this->modifQuantite($position_produit,$qte);
        }
        else{
            // nouveau produit à ajouter au panier
            $_SESSION['panier']['id_produit'][] = (int) $id;
            $_SESSION['panier']['quantite'][] = (int) $qte;
            $_SESSION['panier']['prix'][] = (float) $this->getModel()->find($id)->getField('prix');
        }
    }
    public function modifQuantite($pos,$qte){
        $_SESSION['panier']['quantite'][$pos] += $qte;
    }
    public function deleteProduitPanier($id){
        if(isset($_SESSION['panier'])){
            $position_produit = array_search($id,$_SESSION['panier']['id_produit']);
            if($position_produit !== false){
                array_splice($_SESSION['panier']['id_produit'],$position_produit,1);
                array_splice($_SESSION['panier']['quantite'],$position_produit,1);
                array_splice($_SESSION['panier']['prix'],$position_produit,1);
                if(empty($_SESSION['panier']['id_produit'])) $this->viderPanier();
            }
        }
    }
    public function viderPanier(){
        unset($_SESSION['panier']);
    }
    public function viderPanierR(){
        $this->viderPanier();
        $this->redirect($this->getUrl().'produit/panier');
    }

    public function panier(){


        // Controles des quantités et des prix
        $warnings = array();
        if( !empty($_SESSION['panier']['id_produit'])){
            $params['details'] = array();
            foreach($_SESSION['panier']['id_produit'] as $key => $value){
                $params['details'][$key] = $this->getModel()->find($value);
                $flag= false;
                // controle de la quantité max autorisée
                if( $_SESSION['panier']['quantite'][$key] > 5){
                    $_SESSION['panier']['quantite'][$key] = 5;
                    $flag = true;
                    $warnings[] = 'Compte tenu de la quantité en stock et maximum autorisée par article, le panier a été mis à jour pour ce produit : '.$params['details'][$key]->getField('titre');
                }
                // controle du stock
                if( $_SESSION['panier']['quantite'][$key] > $params['details'][$key]->getField('stock') ){
                    $_SESSION['panier']['quantite'][$key] =  $params['details'][$key]->getField('stock');
                    if(!$flag){
                        $warnings[] = 'Compte tenu de la quantité en stock et maximum autorisée par article, le panier a été mis à jour pour ce produit : '.$params['details'][$key]->getField('titre');
                    }
                }
                // controle du prix
                if($_SESSION['panier']['prix'][$key] != $params['details'][$key]->getField('prix')){
                    $_SESSION['panier']['prix'][$key] = $params['details'][$key]->getField('prix');
                    $warnings[] = 'Le prix ayant évolué, le panier a été mis à jour pour ce produit : '.$params['details'][$key]->getField('titre');
                }
            }
        }

        $params['title'] = 'Panier';
        $params['warning'] = (!empty($warnings)) ? implode('<br>',$warnings) : '';
        return $this->render('layout.html','panier.html',$params);
    }

//-----------------------------------------------------------------------------------------

// fonction de mise à jour de stock suite à une commande
    public function majStock($token){
        if( isset($_SESSION['membre']) && isset($_SESSION['panier'])){
            
            $token = str_replace('_SLASH_','/',$token);
            if(password_verify('secure!2020$',$token)){

                foreach($_SESSION['panier']['id_produit'] as $key => $value){
                   $produit = $this->getModel()->find($value) ;
                   $stock_actuel = $produit->getField('stock');
                   $new_stock = $stock_actuel - $_SESSION['panier']['quantite'][$key];
                   $this->getModel()->update($value,array(
                       'stock' => $new_stock
                   ));
                }
                // destruction du panier
                $this->viderPanier();
                $_SESSION['pending_message'] = "Votre commande a été validée. Nous attendons votre paiement pour procéder à son expédition";
                $_SESSION['type_message'] = 'success';
                $this->redirect($this->getUrl() . 'commande/afficheMesCommandes');
            } 
        }
    }
}
