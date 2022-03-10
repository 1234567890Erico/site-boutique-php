<?php

class Autoload{

    public static function inclusionAuto($className){
        // Model\MembreModel
       $tab = explode('\\',$className) ;
       // $tab[0] <= Model
       // $tab[1] <= MembreModel
       if( $tab[0] == 'Manager' ||
           ( $tab[0] == 'Model' && $tab[1] == 'Model') ||
           ( $tab[0] == 'Controller' && $tab[1] == 'Controller')
        ){
            // on reste dans le rép courant vendor
            $chemin = __DIR__ . '/' . implode('/',$tab) . '.php';
        }else{
            // on doit aller dans src
            $chemin = __DIR__ . '/../src/' .  implode('/',$tab) . '.php';
        }
        require($chemin);
    }
}
spl_autoload_register(array('Autoload','inclusionAuto'));

/*
Les cas où je reste dans vendor
new Manager\*
new Model\Model ou encore extends Model
new Controller\Controller ou encore extends Controller
*/