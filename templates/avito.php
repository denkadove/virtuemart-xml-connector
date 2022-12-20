<?php
    if (!FILE) {
        ob_start('ob_gzhandler', 9);
        header('Content-Type: application/xml; charset=utf-8');
    } else {
        header('Content-Type: text/html; charset=UTF-8');
    }

    $xml = '<Ads formatVersion="3" target="Avito.ru">'."\n";
    foreach ($productList as $product) {
        $product_name = htmlspecialchars(trim(strip_tags($product['product_name'])));
        $product_id = $product['virtuemart_product_id'];
        $product_cat_id = $product['virtuemart_category_id'];
        $prices = $product['override'] > 0 ? $product['product_override_price'] : $product['product_price'];
        $type = $product['mf_name'] ? ' type="vendor.model"' : '';
        $available = $product['product_in_stock'] > 0 ? 'true' : 'false';

        $xml .= '<Ad>'."\n";
        $xml .= '<id>'.$product_id.'</id>'."\n";
        $xml .= '<ListingFee>Package</ListingFee>'."\n";
        $xml .= '<Address>'.ADDRESS.'</Address>'."\n";
        $xml .= '<Category>'.$product['avito_category'][0].'</Category>'."\n";

        if ($product['avito_category'][1] != "без типа"){
            $xml .= '<GoodsType>'. $product['avito_category'][1] . '</GoodsType>'."\n";
        }

        $xml .= '<AdType>Товар от производителя</AdType>'."\n";
        $xml .= '<Title>'.$product_name.'</Title>'."\n";
        $xml .= '<Description><![CDATA['.mb_substr(str_replace('tr>','p>',strip_tags($product['product_desc'],'<p>,//<br>,<strong>,<em>,<ul>,<ol>,<li>,<tr>')), 0, 5000). '<br>Артикул: ' . $product_id . '<br>' . $product['customfields'] . ']]></Description>'."\n";
        $xml .= '<Price>'.$prices.'</Price>'."\n";
        $xml .= '<Condition>Новое</Condition>'."\n";
        $xml .= '<Images>';
        $mediaList = $product['medias'];
        foreach ($mediaList as $image_url) { //TODO get all images
                $xml .= '<Image url="'. SITE . DS .  $image_url["file_url"] . '"/>'."\n";
        }
        $xml .= '</Images>';
        $xml .= '</Ad>'."\n";
    }

    $xml .= '</Ads>';

$xml_file = fopen('xml/avito.xml', 'w+');
ftruncate($xml_file, 0);
fwrite($xml_file, $xml);
fclose($xml_file);

echo $this->model->outputDataType(FILE, 'avito.xml', SITE, $xml);
