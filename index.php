<?php

    declare(strict_types = 1);

    use Core\Config;

    error_reporting(E_ALL);
    ini_set('display_errors', "1"); 

    require_once ($_SERVER["DOCUMENT_ROOT"].'/config.php');
    require_once ($_SERVER["DOCUMENT_ROOT"].'/core/view.php');
    require_once ($_SERVER["DOCUMENT_ROOT"].'/core/model.php');
    require_once ($_SERVER["DOCUMENT_ROOT"].'/core/controller.php');

    $config = (array) new Config();

    define("NAME", $config['companyName']); //не должно превышать 20 символов
    define("DESC", $config['companyDescription']);
    define("ADDRESS", $config['companyAddress']);
    define("CURRENCY", $config['companyCurrency']);
    define("DELIVERY", $config['companyDelivery']);
    define("STORE", $config['companyStore']);
    define("PICKUP", $config['companyPickup']);
    define("FILE", $config['saveToXmlFile']);
    define("SITE", $config['siteUrl']);
    define('AVITO_CATEGORY', $config['avitoCategory']);
    define('NOT_USED_CUSTOM_FIELDS', $config['notUsedCuslomFields']);

    const DS = DIRECTORY_SEPARATOR;

    $model = new \core\BaseModel;
    $controller = new \core\BaseController($model);
    $view = new \core\BaseView($model, $controller);
    $lang = 'ru_ru';

    $rout = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)))[1];;  

    $filename = 'xml/' . $rout . '.xml';
    if (file_exists($filename)) {
        $file_update_time = new DateTimeImmutable(date ("F d Y H:i:s.", filemtime($filename)));
        $now_date = new DateTimeImmutable(date ("F d Y H:i:s."));
        $interval = $file_update_time->diff($now_date);
        if ($interval->format("%H:i:s") > 4){
            $view->getData($rout);
        } else {
            $xml = simplexml_load_string(file_get_contents($filename));  
            Header('Content-type: text/xml');
            print($xml->asXML());                   
        }        
    } else {
        $view->getData($rout);
    }    