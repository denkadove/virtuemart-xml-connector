<?php

    declare(strict_types = 1);

    namespace Core;

    use PDO;
    use PDOException;


    class BaseModel {

        public function __construct() {

        }

        public function getDBconnect(): array
        {
            $config = (array) new Config;
            $db['user'] = $config["user"];
            $db['pass'] = $config["pass"];
            $db['host'] = $config["host"];
            $db['dbname'] = $config["dbname"];
            $db['table_prefix']  = $config["table_prefix"];
            return $db;
        }

        public function getPRcategoryList(): string
        {
            $config = (array) new Config;
            return $config['PRcategory'];
        }

        public function getPRproductList(): string
        {
            $config = (array) new Config;
            return $config['PRproducts'];
        }

        public function get_lang_overrides(){
            //$origin_lang_ini_path = 'https://gazkit.ru/language/overrides/ru-RU.override.ini';
            $origin_lang_ini_path = SITE . '/language/overrides/ru-RU.override.ini';
            $local_lang_ini_path = $_SERVER["DOCUMENT_ROOT"].'/language/ru-RU.override.ini';
            copy($origin_lang_ini_path,$local_lang_ini_path);
            return $local_lang_ini_path;
        }

        public function getProductList(string $type): array
        {
            $lang = 'ru_ru'; //TODO lang check functiom, move to config
            if ($type ==='PR') {
                $excludeCat = self::getPRcategoryList();
                $excludeProduct = self::getPRproductList();
                $selectSign = ' IN ';
            } else {
                $excludeCat = '0';
                $excludeProduct = '0';
                $selectSign = ' NOT IN ';
            }
            $db = self::getDBconnect();
            $query = 'SELECT DISTINCT a.virtuemart_product_id, a.product_sku, a.product_canon_category_id, b.slug, b.product_name, b.product_desc, d.product_price, d.product_override_price, d.override, g.virtuemart_category_id, d.product_currency, e.mf_name, a.product_parent_id, a.virtuemart_vendor_id, a.product_in_stock, d.product_tax_id, d.product_discount_id,  e.virtuemart_manufacturer_id FROM ('.$db['table_prefix'].'virtuemart_product_categories g LEFT JOIN ('.$db['table_prefix'].'virtuemart_product_prices d RIGHT JOIN (('.$db['table_prefix'].'virtuemart_product_manufacturers f RIGHT JOIN '.$db['table_prefix'].'virtuemart_products a ON f.virtuemart_product_id = a.virtuemart_product_id) LEFT JOIN '.$db['table_prefix'].'virtuemart_manufacturers_'.$lang.' e ON f.virtuemart_manufacturer_id = e.virtuemart_manufacturer_id LEFT JOIN '.$db['table_prefix'].'virtuemart_products_'.$lang.' b ON b.virtuemart_product_id = a.virtuemart_product_id) ON d.virtuemart_product_id = a.virtuemart_product_id) ON g.virtuemart_product_id = a.virtuemart_product_id) WHERE a.published = 1 AND d.product_price > 0 AND b.product_name <> \'\' AND g.virtuemart_category_id'.$selectSign.'('.$excludeCat.') AND a.virtuemart_product_id '.$selectSign.'('.$excludeProduct.') GROUP BY a.virtuemart_product_id';
            $productList = $this->getDBinfo($query);
            $local_lang_ini_path = self::get_lang_overrides();
            $language_items = parse_ini_file($local_lang_ini_path);
            foreach ($productList as $key => $product) {
                $productID = $product['virtuemart_product_id'];
                $productList[$key]['medias'] = self::getImages((string)$productID);
                $productList[$key]['customfields'] = self::getCustomFields((string)$productID, $language_items);
                if ($product['product_canon_category_id'] != NULL ){
                    $productList[$key]['canonical_url'] = self::getProductCanonicalUrl((string)$product['product_canon_category_id'], $product['slug']);
                } else {
                    $productList[$key]['canonical_url'] = '0';
                }
                $productList[$key]['avito_category'] = self::getAvitoCategory($product['product_canon_category_id']);

            }
            return $productList;
        }

        public function getImages(string $product_id): array{
            $db = self::getDBconnect();
            $query = 'SELECT a.file_url FROM '.$db['table_prefix'].'virtuemart_medias a JOIN '.$db['table_prefix'] .'virtuemart_product_medias b ON b.virtuemart_media_id = a.virtuemart_media_id WHERE a.published = 1 AND b.virtuemart_product_id = '.$product_id.' ORDER BY b.ordering, b.id LIMIT 10';
            return $this->getDBinfo($query);
        }

        public function outputDataType(string $file, string $filename, string $live_site, string $xml): string{
            if ($file) { //TODO Check it
                $xml_file = fopen($filename, 'w+');
                if (!$xml_file) {
                    $message = 'Ошибка открытия файла';
                } else {
                    ftruncate($xml_file, 0);
                    fwrite($xml_file, $xml);
                    $message = $xml;//'Файл создан, url - <a href="'. $live_site. 'market/' .$filename. '">' .$live_site. 'market/' .$filename. '</a>';
                }
                fclose($xml_file);
                return $message;
            } else {
                return $xml;
            }
        }

        public function getProductCanonicalUrl(string $product_canonical_id, string $product_slug): string {
            $db = self::getDBconnect();
            $query = 'SELECT `path` FROM `'.$db['table_prefix'].'menu` WHERE `link` like "%option=com_virtuemart&view=category&virtuemart_category_id='.$product_canonical_id.'%"';
            $paths = self::getDBinfo($query);
            if (isset($paths[0])){
                $url = $paths[0]["path"];
            } else {
                $url = 'warning';
            }
            $canonical_url = SITE . DS. $url . DS . $product_slug . '.html';;
            return $canonical_url; //TODO finish function

        }

        public function getCategoryList($type): array {
            $lang = 'ru_ru'; //TODO lang check functiom, move to config
            if ($type ==='PR') {
                $excludeCat = self::getPRcategoryList();
                $excludeProduct = self::getPRproductList();
                $selectSign = ' IN ';
            } else {
                $excludeCat = '0';
                $excludeProduct = '0';
                $selectSign = ' NOT IN ';
            }
            $db = self::getDBconnect();
            $query = 'SELECT a.category_parent_id, a.category_child_id, b.category_name FROM '.$db['table_prefix'].'virtuemart_category_categories a RIGHT JOIN '.$db['table_prefix'].'virtuemart_categories_'.$lang.' b ON b.virtuemart_category_id = a.category_child_id WHERE a.category_child_id '.$selectSign.' ('.$excludeCat.') ORDER BY a.category_child_id';
            $categoriesList = self::getDBinfo($query);
            return $categoriesList;
        }

        public function getDBinfo(string $query): array
        {
            $db = self::getDBconnect();
            try {
                $dbh = new \PDO('mysql:host='. $db['host'] .';dbname=' . $db['dbname'], $db['user'], $db['pass']);
                $queryList = [];
                $listUnit = $dbh->query($query, PDO::FETCH_ASSOC);
                if ($listUnit->rowCount() != '0') {
                    foreach($listUnit as $key => $row) {
                        $queryList[$key] = $row;
                    }
                } 
                $dbh = null;
                return $queryList;
            } catch (PDOException $e) {
                return ["Error!: " => $e->getMessage()];
            }
        }

        public function getCustomFields(string $product_id, array $language_items): string {
            $db = self::getDBconnect();
            $query = 'SELECT a.`virtuemart_customfield_id`, a.`virtuemart_custom_id`, a.`customfield_value`, b.`custom_title` FROM '.$db['table_prefix'].'virtuemart_product_customfields a RIGHT JOIN '.$db['table_prefix'].'virtuemart_customs b ON a.`virtuemart_custom_id` = b.`virtuemart_custom_id` WHERE `virtuemart_product_id` ='.$product_id.' AND a.`virtuemart_custom_id` NOT IN ('. NOT_USED_CUSTOM_FIELDS .');';
            $rows = $this->getDBinfo($query);
            $result = '';
            foreach ($rows as $row) {
                $custom_field_name = $row['custom_title'];
                $custom_field_value = $row['customfield_value'];
                if (array_key_exists($custom_field_name, $language_items)) {
                    $result .= "<br>" . $language_items[$custom_field_name] . ' ' . $custom_field_value . "; </br>";
                } else {
                    $result .= "<br>" . $custom_field_name . ' ' . $custom_field_value . "; </br>";;
                }
            }
            return $result;
        }

        public function getAvitoCategory($product_cat_id): array
        {
            $avitoCategory = '';
            $avitoGoodsType = '';
            $avitoCategoryAccordance = AVITO_CATEGORY;
            if (isset($avitoCategoryAccordance[$product_cat_id]) ){
                $avitoCategory = $avitoCategoryAccordance[$product_cat_id]["category"];
                $avitoGoodsType = $avitoCategoryAccordance[$product_cat_id]["goodsType"];
            } else {
                $avitoCategory = 'warning';
                $avitoGoodsType = 'warning';
            }
            return [$avitoCategory, $avitoGoodsType];
        }

    }