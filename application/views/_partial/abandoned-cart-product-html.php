<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$prodImage = UrlHelper::generateFullUrl('image', 'product', array($data['selprod_product_id'], "THUMB", $data['selprod_id'], 0, $langId),CONF_WEBROOT_FRONTEND);
$str = '<tr><td style="padding-right: 25px;"><img style="border: solid 1px #ececec; padding: 10px; border-radius: $radius4;" src="'.$prodImage.'"></td><td style="text-align:left;"><span style="font-size: 20px; font-weight:normal; color:#999999;">'.$data['selprod_title'].'</span><span style="font-size: 14px; font-weight: bold; color:#000000; display: block; padding: 20px 0;">'.CommonHelper::displayMoneyFormat($data['selprod_price']).'</span></td></tr>'; 
echo  $str;
            