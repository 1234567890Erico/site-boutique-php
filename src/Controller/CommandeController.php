<?php

namespace Controller;

class CommandeController extends Controller
{
    public function afficheMesCommandes(){
        if(isset($_SESSION['membre'])){

            if(isset($_POST['validerpanier'])){
                $this->validationCommande();
            }

            $params['commandes'] = $this->getModel()->getMesCommandes();
            $details =  $this->getModel()->getDetailsCommandes();
            $retour = array();
            if($details){
                foreach($details as $ligne){
                    $retour[$ligne->IDCMD][] = array(
                        'quantite' => $ligne->quantite,
                        'prix' =>  $ligne->prix,
                        'titre' => $ligne->TITRE,
                        'photo' => $ligne->PHOTO,
                        'reference' => $ligne->REFERENCE
                    );
                }
            }

            $params['details']= $retour ;
            $params['title'] = 'Historique de mes commandes';
            return $this->render('layout.html','affichecommandes.html',$params);

        }
    }

    public function validationCommande(){

        if(isset($_SESSION['membre']) && isset($_SESSION['panier']) && !empty($_SESSION['panier']['id_produit'])){

            $montantTotal = 0;
            $compteurEcarts = 0;
            foreach($_SESSION['panier']['id_produit'] as $key => $value){
                $objPrd = new ProduitController;
                $produit = $objPrd->getModel()->find($value);

                if($_SESSION['panier']['quantite'][$key] > 5){
                    $compteurEcarts++;
                }
                if($_SESSION['panier']['quantite'][$key] > $produit->getField('stock')){
                    $compteurEcarts++;
                }
                if($_SESSION['panier']['prix'][$key] != $produit->getField('prix') ){
                    $compteurEcarts++;
                }
                $montantTotal += $_SESSION['panier']['quantite'][$key] * $_SESSION['panier']['prix'][$key];
            }
            if( $compteurEcarts == 0){
                // tout est ok : on peut insérer la commande  
                // ajout une ligne dans la table commande (entité : commande)
                $numero_commande = $this->getModel()->register(array(
                    'id_membre' => $_SESSION['membre']->getField('id_membre'),
                    'montant' => $montantTotal,
                    'date_enregistrement' => date('Y-m-d H:i:s') ,
                    'etat' => 'en cours de traitement'
                ));
                // ajout les lignes dans la table details_commande (entité : commande)
                foreach($_SESSION['panier']['id_produit'] as $key => $value){
                    $this->getModel()->registerDetailsCommande(array(
                        'id_commande' => $numero_commande,
                        'id_produit' => $_SESSION['panier']['id_produit'][$key],
                        'quantite' => $_SESSION['panier']['quantite'][$key],
                        'prix' => $_SESSION['panier']['prix'][$key]
                    ));
                }

                // Mettre à jour le stock par produit (entité : produit)
                // vidage du panier (entité : produit)
                $token = password_hash('secure!2020$',PASSWORD_DEFAULT);
                $token = str_replace('/','_SLASH_',$token);
                $this->redirect($this->getUrl() . 'produit/majStock/' . $token);

            }
            else{
                $_SESSION['pending_message'] = 'Merci de vérifier à nouveau votre panier et confirmer la validation de votre commande';
                $_SESSION['type_message'] = 'warning';
                $this->redirect($this->getUrl() . 'produit/panier');
            }
        }
    }

    public function adminCommandes()
    {
        if (isset($_SESSION['membre']) && $_SESSION['membre']->isAdmin()) {

            if (!empty($_POST['id_commande']) && is_numeric($_POST['id_commande'])) {

                $this->getModel()->update($_POST['id_commande'], array('etat' => $_POST['newetat']));
            }

            $params['title'] = 'Gestion des commandes';
            $params['commandes'] = $this->getModel()->getAllCommandes();
            $details =  $this->getModel()->getDetailsAllCommandes();
            $retour = array();
            foreach ($details as $ligne) {
                $retour[$ligne->IDCMD][] = array(
                    'quantite' => $ligne->quantite,
                    'prix' => $ligne->prix,
                    'titre' => $ligne->TITRE,
                    'photo' => $ligne->PHOTO,
                    'reference' => $ligne->REFERENCE
                );
            }
            $params['details'] = $retour;

            return $this->render('layout.html', 'admincommandes.html', $params);
        }
        else{
            $this->redirect($this->getUrl().'membre/connexion');
        }
    }

}