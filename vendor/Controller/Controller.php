<?php

namespace Controller;
use Model, Config;
// use namespace Model + classe Config

abstract class Controller{

    protected $model;
    protected $url;
    public function __construct(){
        // Controller\MembreController
        // Model\MembreModel
        $classModel = str_replace('Controller','Model',get_called_class());
        $this->model = new $classModel;
        $config = new Config;
        $this->url = $config->getParametersUri();
    }
    public function getModel(){
        return $this->model;
    }
    public function getUrl(){
        return $this->url;
    }
    public function redirect($adresse){
        // serialisation d'un objet
        header('location:'.$adresse);
        exit();
    }
    public function render($layout,$template,$params){
        $dirView = __DIR__ . '/../../src/View/';
        $dirEntityView = $this->getModel()->getEntity();

        $pathView = $dirView . $dirEntityView . '/' . $template;
        $pathLayout = $dirView . $layout;

        $params['url'] = $this->getUrl();
        extract($params); // => $url entre autres

        ob_start(); // début de mise en mémoire tampon
            require $pathView;
            $content = ob_get_clean(); // je stocke ce qui est en mémoire tampon dans une variable
            ob_start();
            require $pathLayout;
        return ob_end_flush(); // libère la mémoire tampon, la stoppe et affiche le résultat
    }    

}