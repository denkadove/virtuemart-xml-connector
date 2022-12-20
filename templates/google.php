<?php
    if (!FILE) {
        ob_start('ob_gzhandler', 9);
        header('Content-Type: application/xml; charset=utf-8');
    } else {
        header('Content-Type: text/html; charset=UTF-8');
    }

    $xml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
    $xml .= '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">'."\n";
    $xml .= '<title>'.htmlspecialchars(mb_substr(NAME, 0, 20, 'UTF-8')).'</title>'."\n";
    $xml .= '<link rel="self" href="'.SITE.'"/>'."\n";

    foreach ($productList as $product) {
        $product_name = htmlspecialchars(trim(strip_tags($product['product_name'])));
        $product_id = $product['virtuemart_product_id'];
        $product_cat_id = $product['virtuemart_category_id'];
        $prices = $product['override'] > 0 ? $product['product_override_price'] : $product['product_price'];
        $available = $product['product_in_stock'] > 0 ? 'in stock' : 'available for order';
        $type = $product['mf_name'] ? ' type="vendor.model"' : '';

        $xml .= '<entry>'."\n";
        $xml .= '<g:id>'.$product_id.'</g:id>'."\n";
        $xml .= '<g:mpn>'.$product_id.'</g:mpn>'."\n";
        $xml .= '<g:title>'.$product_name.'</g:title>'."\n";
        $xml .= '<g:link>'.$product['canonical_url'].'</g:link>'."\n";
        if ($product['mf_name']) {
            $xml .= '<g:brand>'.htmlspecialchars($product['mf_name']).'</g:brand>'."\n";
        }
        $xml .= '<g:description>'."\n".htmlspecialchars(mb_substr(strip_tags($product['product_desc']) , 0, 4500))."\n". $product['customfields'] .'</g:description>'."\n";
        $mediaList = $product['medias'];
        foreach ($mediaList as $key => $image_url) { //TODO get all images
            if ($key == 0) {
                $xml .= '<g:image_link>'. SITE . DS .  $image_url["file_url"] . '</g:image_link>';
            } else {
                $xml .= '<g:additional_image_link>'. SITE . DS .  $image_url["file_url"] . '</g:additional_image_link>';
            }
        }
        $xml .= '<g:condition> new </g:condition>'."\n";
        $xml .= '<g:availability>'.$available.'</g:availability>'."\n";
        $xml .= '<g:price>'.$prices.' RUB'.'</g:price>'."\n";
        $xml .= '</entry>'."\n";
    }
    $xml .= '</feed>'."\n";

    $xml_file = fopen('xml/google.xml', 'w+');
    ftruncate($xml_file, 0);
    fwrite($xml_file, $xml);
    fclose($xml_file);

    echo $this->model->outputDataType(FILE, 'google.xml', SITE, $xml);


