<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$url = UrlHelper::generateFullUrl('GuestUser', 'redirectAbandonedCartUser', array($userId,0, true), CONF_WEBROOT_FRONTEND);
$str = '<tr><td style="padding-right: 25px;"></td><td style="text-align:left;"><a href="'.$url.'" style="background: #ff3a59;border:none; border-radius: $radius4; color: #fff; cursor: pointer;margin: 0;   width: auto; font-weight: normal; padding: 10px 20px;">Complete Checkout </a></td></tr>';
echo  $str;
            