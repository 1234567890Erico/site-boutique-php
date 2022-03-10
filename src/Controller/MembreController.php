<?php

namespace Controller;

class MembreController extends Controller
{

    // http://localhost/boutique/membre/inscription
    public function inscription()
    {

        // si déjà connecté, redirection
        if (isset($_SESSION['membre'])) {
            $this->redirect($this->getUrl() . 'membre/profil');
        }

        $erreurs = array();
        if (!empty($_POST)) {
            // si je rentre ici c'est qu'on a validé le formulaire
            $nbChampsVides = 0;
            foreach ($_POST as $key => $value) {
                $_POST[$key] = htmlspecialchars($value, ENT_QUOTES);
                if (empty(trim($value)))  $nbChampsVides++;
            }
            if ($nbChampsVides > 0) {
                $erreurs[] = 'Il manque ' . $nbChampsVides . " information(s)";
            }
            if (!empty($_POST['pseudo']) && (iconv_strlen($_POST['pseudo']) < 3 || iconv_strlen($_POST['pseudo']) > 20)) {
                $erreurs[] = 'Le pseudo doit comporter entre 3 et 20 caractères';
            }

            if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $erreurs[] = "Le format de l'adresse mail est incorrect";
            }

            if (!empty($_POST['code_postal']) && !preg_match('#^[0-9]{5}$#', $_POST['code_postal'])) {
                $erreurs[] = "Le format du code postal est incorrect";
            }

            // controler l'unicité du pseudo
            if ($this->getModel()->existsPseudo($_POST['pseudo'])) {
                $erreurs[] = "Le pseudo est indisponible. Merci d'en choisir un autre";
            }

            if (empty($erreurs)) {
                //  je n'ai aucune erreur, on peut procéder à l'inscription
                // crypter le mdp
                $_POST['mdp'] = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
                // statut par défaut pour un membre
                $_POST['statut'] = 0;
                // Insertion en BDD et obtention du numéro de membre
                $id_user = $this->getModel()->register($_POST);
                // On charge un objet membre à partir de l'id obtenu
                $membre = $this->getModel()->find($id_user);
                // En faisant exister membre dans $_SESSION, je le mets en mode connecté
                $_SESSION['membre'] = $membre;
                // Je le redirige sur sa page profil
                $this->redirect($this->getUrl() . 'membre/profil');
            }
        }

        $params['title'] = 'Inscription';
        // en cas d'erreur j'alimente la variable $erreur sur la vue
        $params['erreur'] = (!empty($erreurs)) ? implode('<br>', $erreurs) : '';
        return $this->render('layout.html', 'inscription.html', $params);
    }

    public function connexion()
    {

        // si déjà connecté, redirection
        if (isset($_SESSION['membre'])) {
            $this->redirect($this->getUrl() . 'membre/profil');
        }

        $erreurs = array();
        if (!empty($_POST)) {
            // traiter la connexion
            if (empty(trim($_POST['pseudo'])) || empty(trim($_POST['mdp']))) {
                $erreurs[] = 'Merci de remplir tous les champs';
            }
            if (empty($erreurs)) {
                $membre = $this->getModel()->existsPseudo($_POST['pseudo']);
                if ($membre) {

                    if (password_verify($_POST['mdp'], $membre->getField('mdp'))) {
                        // pseudo et mdp sont OK
                        $_SESSION['membre'] = $membre; // on le met en mode connecté
                        $this->redirect($this->getUrl() . 'membre/profil');
                    } else {
                        $erreurs[] = 'Erreur sur les identifiants';
                    }
                } else {
                    $erreurs[] = 'Erreur sur les identifiants';
                }
            }
        }

        $params['title'] = 'Connexion';
        $params['erreur'] = (!empty($erreurs)) ? implode('<br>', $erreurs) : '';
        return $this->render('layout.html', 'connexion.html', $params);
    }

    public function deconnexion()
    {
        unset($_SESSION['membre']); // si membre n'existe plus je ne suis plus en mode connecté
        $this->redirect($this->getUrl()); // redirection page d'accueil
    }

    public function profil()
    {

        // si pas connecté, redirigé vers le formulaire de connexion
        if (!isset($_SESSION['membre'])) {
            $this->redirect($this->getUrl() . 'membre/connexion');
        }

        $params['title'] = 'Profil';
        return $this->render('layout.html', 'profil.html', $params);
    }


    public function adminMembres()
    {

        if (isset($_SESSION['membre']) && $_SESSION['membre']->isAdmin()) {
            $params['title'] = 'Gestion des membres';
            $params['membres'] = $this->getModel()->findAll();
            return $this->render('layout.html', 'adminmembres.html', $params);
        } else {
            $this->redirect($this->getUrl() . 'membre/connexion');
        }
    }

    public function editStatut($id)
    {
        if (isset($_SESSION['membre']) && $_SESSION['membre']->isAdmin()) {
            if ($id != $_SESSION['membre']->getField('id_membre')) {
                $newstatut = ($this->getModel()->find($id)->getField('statut') == 0) ? 1 : 0;
                $this->getModel()->update($id, array('statut' => $newstatut));
                $this->redirect($this->url . 'membre/adminMembres');
            } else {
                $this->redirect($this->url . 'membre/adminMembres');
            }
        } else {
            $this->redirect($this->url . 'membre/connexion');
        }
    }



    public function modifProfil($arg)
    {
        if (isset($_SESSION['membre'])) {

            $id_membre = $_SESSION['membre']->getField('id_membre');

            if (isset($_POST['validmodif'])) {
                // validation du formulaire des coordonnées
                $champsvides = 0;
                foreach ($_POST as $value) {
                    if (empty(trim($value))) $champsvides++;
                }
                if ($champsvides == 0) {
                    unset($_POST['validmodif']); // j'en ai pas besoin pour l'update
                    $this->getModel()->update($id_membre, $_POST);
                    $membre = $this->getModel()->find($id_membre);
                    $_SESSION['membre'] = $membre;
                    $_SESSION['pending_message'] = 'Les coordonnées ont été modifiées avec succès';
                    $_SESSION['type_message'] = 'success';

                    $this->redirect($this->url . 'membre/profil');
                }
            }

            if (isset($_POST['validmdp'])) {


                // validation du changement de mdp
                if (!empty($_POST['mdp']) && !empty($_POST['oldmdp']) && !empty($_POST['confirmmdp'])) {

                    $erreurs = array();
                    if ($_POST['mdp'] != $_POST['confirmmdp']) {
                        $erreurs[] = 'Le mot de passe et la confirmation ne concordent pas';
                    }
                    if (!password_verify($_POST['oldmdp'], $this->getModel()->find($_SESSION['membre']->getField('id_membre'))->getField('mdp'))) {
                        $erreurs[] = 'Le mot de passe actuel est incorrect';
                    }

                    if (empty($erreurs)) {
                        $newmdp = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
                        $this->getModel()->update($id_membre, array('mdp' => $newmdp));
                        $_SESSION['pending_message'] = 'Le mot de passe a été modifié avec succès';
                        $_SESSION['type_message'] = 'success';
                    } else {
                        $_SESSION['pending_message'] = implode('<br>', $erreurs);
                        $_SESSION['type_message'] = 'danger';
                    }
                } else {
                    $_SESSION['pending_message'] = 'Merci de remplir tous les champs';
                    $_SESSION['type_message'] = 'danger';
                }
                $this->redirect($this->url . 'membre/profil');
            }

            $params['title'] = 'Profil';
            if ($arg == 'coord') $params['action'] = 'editcoord';
            if ($arg == 'mdp') $params['action'] = 'editmdp';

            return $this->render('layout.html', 'profil.html', $params);
        }
    }

}
