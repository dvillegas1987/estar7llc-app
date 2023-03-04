<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
$location = $frm->getField('location');
$location->developerTags['noCaptionTag'] = true;
$location->addFieldTagAttribute("id", "ga-autoComplete");
$location->addFieldTagAttribute("autocomplete", "off");
$location->addFieldTagAttribute("class", "form-control");
$location->addFieldTagAttribute("title", Labels::getLabel('LBL_ENTER_YOUR_LOCATION', $siteLangId));
$location->addFieldTagAttribute("placeholder", Labels::getLabel('LBL_TYPE_YOUR_ADDRESS', $siteLangId));
?>

<div class="location-permission">
    <div class="location-permission_head">
        <h5>
            <?php echo Labels::getLabel('LBL_ALLOW_"LOCATIONACCESS"_TO_ACCESS_YOUR_LOCATION_WHILE_YOU_ARE_USING_THE_WEBSITE?', $siteLangId); ?>
        </h5>
        <p><?php echo Labels::getLabel('MSG_ALLOW_LOCATION_ACCESS_DESCRIPTION', $siteLangId); ?></p>
    </div>

    <div class="location-permission_body">
        <a class="default" href="javascript:void(0)" onclick="loadGeoLocation()">
            <i class="icn"><img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/location.svg" alt=""></i>
            <span class="location-name"><?php echo Labels::getLabel("LBL_DELIVER_IN", $siteLangId); ?><strong>
                    <?php echo isset($_COOKIE["_ykGeoAddress"]) ? $_COOKIE["_ykGeoAddress"] : Labels::getLabel("LBL_CURRENT_LOCATION", $siteLangId); ?> </strong></span>
        </a>

        <div class="or"><span><?php echo Labels::getLabel('LBL_OR', $siteLangId); ?></span></div>
        <div class="location-search">
            <?php echo $frm->getFormHtml(); ?>
        </div>
    </div>
</div>