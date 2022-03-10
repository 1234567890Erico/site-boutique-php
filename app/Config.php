<?php

class Config{

    private $parameters;
    public function __construct(){
        require __DIR__ . '/Config/parameters.php';
        $this->parameters = $parameters;
    }
    public function getParametersConnect(){
        return $this->parameters['connect'];
    }
    public function getParametersUri(){
        return $this->parameters['uri'];
    }
    public function getDefaultEntity(){
        return $this->parameters['defaultentity'];
    }
    public function getDefaultMethod(){
        return $this->parameters['defaultmethod'];
    }
}