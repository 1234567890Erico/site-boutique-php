<?php

namespace Manager;

use Config;

final class Application{

    // http://localhost/boutique/membre/inscription
    // controller : Controller\MembreController
    // methode : inscription()
    // params : null
    // http://localhost/boutique/produit/fiche/12
    // controller : Controller\ProduitController
    // methode : fiche()
    // params : 12
    private $controller;
    private $action;
    private $argument = '';
    public function __construct(){
        $tab = explode('/',$_SERVER['REQUEST_URI']);
        require_once __DIR__ . '/../../app/Config.php';
        $cfg = new Config;
        $tabUri = explode('/',$cfg->getParametersUri());
        /*
        http://localhost/boutique/        
        */
        $racine = $tabUri[count($tabUri)-2]; // <== boutique
        $position = array_search($racine,$tab); // position du mot boutique dans le tableau tab
        /*
        position+1 <= entité
        position+2 <= méthode
        position+3 <= argument
        */
       
        // Charger le bon controller, et lancer la méthode avec l'éventuel argument


        // on vérifie l'existence de src/Controller/MembreController.php
        if( !empty($tab[$position+1]) && file_exists(__DIR__ . '/../../src/Controller/' . ucfirst($tab[$position+1]) .'Controller.php' ) ){
            $this->controller = 'Controller\\' . ucfirst($tab[$position+1]).'Controller'; // => Controller\MembreController
        }else{
            $this->controller = 'Controller\\' . ucfirst($cfg->getDefaultEntity()) . 'Controller'; // => Controller\ProduitController dans notre projet
        }

        // On détermine la méthode à exécuter
        if( !empty($tab[$position+2]) ){
            $this->action = $tab[$position+2];
        }
        else{
            $this->controller = 'Controller\\' . ucfirst($cfg->getDefaultEntity()) . 'Controller'; 
            $this->action = $cfg->getDefaultMethod();
        }

        if(!empty($tab[$position+3])){
            $this->argument = urldecode($tab[$position+3]);  
            // urldecode() pour remplacer les %20, ...          
        }
       
        // echo "Mon controller : " . $this->controller; // membre => Membre
        // echo "<br>Ma méthode : " . $this->action;
        // echo "<br>Mes arguments : " . $this->argument ;               
    }

    public function run(){
        if(!is_null($this->controller)){
            $a = new $this->controller;
            // ex : new Controller\MembreController
            if(!is_null($this->action) && method_exists($a,$this->action)){
                $a->{$this->action}($this->argument);
                // ex: http://localhost/boutique/membre/inscription
                // $a->inscription()
                //  ex: http://localhost/boutique/produit/fiche/12
                // $a->fiche(12)
            }else{
                $cfg = new Config;
                $url = $cfg->getParametersUri();
                $title = 'Erreur 404';
                $content = '<h2 class="text-center pt-5 display-2">ERREUR 404</h2>';               
                require __DIR__ . '/../../src/View/layout.html';
               
            }
        }
    }

}
