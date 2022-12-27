<?php

    declare(strict_types = 1);

    namespace Core;

    class BaseView {

        public $model;
        public $controller;

        public function __construct($model, $controller) {
            $this->model = $model;
            $this->controller = $controller;
        }

        public function getData(string $rout): void {
            $productList = [];
            $config = new Config();
            $companyName = $config->companyName;
            if ($rout == 'avito' || $rout == 'ya_market' || $rout == 'ya_webmaster' || $rout == 'facebook'|| $rout ==
                '2gis') {
                $productList = $this->model->getProductList('PR');
            } else {
                $productList = $this->model->getProductList('all');
            }
            $categoriesList = $this->model->getCategoryList('all');

            if ($rout === 'avito'){
                require ('templates/avito.php');
            } elseif ($rout === 'google' || $rout == 'facebook') {
                require ('templates/google.php');
            } elseif ($rout === 'ya_market' || $rout == 'ya_business') {
                require ('templates/ya_market.php');
            } elseif ($rout === 'ya_webmaster') {
                require ('templates/ya_webmaster.php');
            } elseif ($rout === '2gis') {
                require ('templates/2gis.php');
            }
            else {
                header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
            }
        }
    }