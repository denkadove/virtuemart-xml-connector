<?php
    namespace Core;

    class Config {
        public $companyName = 'companyName';
        public $companyDescription = 'companyDescription';
        public $companyAddress = 'companyAddress';
        public $companyCurrency = 'RUB';
        public $companyDelivery = 'true';
        public $companyStore = 'true';
        public $companyPickup = 'true';
        public $saveToXmlFile = 0;
        public $siteUrl = 'http://domain.com';
        public $user = 'root';
        public $pass = '';
        public $host = 'localhost';
        public $dbname = 'db_name';
        public $table_prefix  = 'g82ax_';
        public $lang = 'ru_ru';
        public $notUsedCuslomFields = '0';
        public $PRcategory = '1,2,3,4';
        public $PRproducts = '4,5,6,50,51';
        public $avitoCategory = [
            81 => ["category" => "category", "goodsType" => "goodsType"],
            82 => ["category" => "category", "goodsType" => "goodsType"],
        ];
    }
