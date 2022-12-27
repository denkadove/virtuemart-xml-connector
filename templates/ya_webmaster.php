<?php
    if (!FILE) {
        ob_start('ob_gzhandler', 9);
        header('Content-Type: application/xml; charset=utf-8');
    } else {
        header('Content-Type: text/html; charset=UTF-8');
    }

    $xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
    $xml .= '<!DOCTYPE yml_catalog SYSTEM "ya_market.dtd">'."\n";
    $xml .= '<yml_catalog date="'.date('Y-m-d H:i').'">'."\n";
    $xml .= '<shop>'."\n";
    $xml .= '<name>'.htmlspecialchars(mb_substr(NAME, 0, 20, 'UTF-8')).'</name>'."\n";
    $xml .= '<company>'.htmlspecialchars(DESC).'</company>'."\n";
    $xml .= '<url>'. SITE .'</url>'."\n";
    $xml .= '<currencies>'."\n";
    //<enable_auto_discounts>yes</enable_auto_discounts> TODO ya_market
    $xml .= '<currency id="'.CURRENCY.'" rate="1"/>'."\n";
    $xml .= '</currencies>'."\n";
    $xml .= '<categories>'."\n";

    foreach ($categoriesList as $category) {
          $cat_parent_id = $category["category_parent_id"];
          $cat_child_id = $category["category_child_id"];
          $cat_name = htmlspecialchars(trim(strip_tags($category["category_name"])));
          if ($cat_parent_id == 0 || in_array($cat_parent_id, ['0'])) {
              $xml .= '<category id="'.$cat_child_id.'">'.$cat_name.'</category>'."\n";
          } else {
              $xml .= '<category id="'.$cat_child_id.'" parentId="'.$cat_parent_id.'">'.$cat_name.'</category>'."\n";
          }
   }
    $xml .= '</categories>'."\n";
    $xml .= '<offers>'."\n";

    foreach ($productList as $product) {
        $product_name = htmlspecialchars(trim(strip_tags($product['product_name'])));
        $product_id = $product['virtuemart_product_id'];
        $product_cat_id = $product['virtuemart_category_id'];
        $prices = $product['override'] > 0 ? $product['product_override_price'] : $product['product_price'];
        $type = $product['mf_name'] ? ' type="vendor.model"' : '';
        $available = $product['product_in_stock'] > 0 ? 'true' : 'false';

        if ($available === 'true') {
            $xml .= '<offer'. $type.' id="'.$product_id.'" available="'.$available.'">'."\n";
            $xml .= '<url>'. $product['canonical_url'] .'</url>'."\n";
            $xml .= '<price>'.$prices.'</price>'."\n";
            $xml .= '<currencyId>'.CURRENCY.'</currencyId>'."\n";
            $xml .= '<categoryId>'.$product_cat_id.'</categoryId>'."\n";
            $mediaList = $product['medias'];
            foreach ($mediaList as $key => $image_url) { //TODO get all images
                $xml .= '<picture>' . SITE . DS .  $image_url["file_url"] . '</picture>';
            }
            $xml .= '<delivery>'.DELIVERY.'</delivery>'."\n";

            if ($product['mf_name']) {
                $xml .= '<vendor>'.htmlspecialchars($product['mf_name']).'</vendor>'."\n";
                $xml .= '<model>'.$product_name.'</model>'."\n";
            } else {
                $xml .= '<name>'.$product_name.'</name>'."\n";
            }

           $xml .= '<vendorCode>'.htmlspecialchars($product['product_sku']).'</vendorCode>';

            if ($product['product_desc']) {
                $xml .= '<description>'."\n".htmlspecialchars(mb_substr(strip_tags($product['product_desc']) , 0,
                        4500))."\n". strip_tags($product['customfields']) .'</description>'."\n";
            }
            $xml .= '</offer>'."\n";
        }
    }

    $xml .= '</offers>'."\n";
    $xml .= '</shop>'."\n";
    $xml .= '</yml_catalog>';

    $xml_file = fopen('xml/ya_market.xml', 'w+');
    ftruncate($xml_file, 0);
    fwrite($xml_file, $xml);
    fclose($xml_file);

    echo $this->model->outputDataType(FILE, 'ya_market.xml', SITE, $xml);
