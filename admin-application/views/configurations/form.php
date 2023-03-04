<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal layout--' . $formLayout);
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = '12';

$tbid = isset($tabId) ? $tabId : 'tabs_' . $frmType;

if ($lang_id > 0) {
    $frm->setFormTagAttribute('onsubmit', 'setupLang(this); return(false);');
    $langFld = $frm->getField('lang_id');
    $langFld->setfieldTagAttribute('onChange', "getLangForm(" . $frmType . ", this.value, '" . $tbid . "');");
} else {
    $frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
}
switch ($frmType) {
    case Configurations::FORM_GENERAL:
        /* if( $lang_id == 0 ){
            $adminLogoFld = $frm->getField('admin_logo');
            $desktopLogoFld = $frm->getField('front_logo');
            $emailLogoFld = $frm->getField('email_logo');
            $faviconFld = $frm->getField('favicon');

            $adminLogoFld->htmlAfterField =  '<span class = "uploadimage--info" >Dimensions 142*45</span>';
            $desktopLogoFld->htmlAfterField = '<span class = "uploadimage--info" >Dimensions 168*37</span>';
            $emailLogoFld->htmlAfterField = '<span class = "uploadimage--info" >Dimensions 168*37</span>';
            if( isset($adminLogo) && !empty($adminLogo) ){
                $adminLogoFld->htmlAfterField .= '<div class="uploaded--image"><img src="'.UrlHelper::generateFullUrl('Image','siteAdminLogo',array('THUMB')).'"> <a  class="remove--img" href="javascript:void(0);" onclick="removeSiteAdminLogo()" ><i class="ion-close-round"></i></a></div>';
            }

            if( isset($desktopLogo) && !empty($desktopLogo) ){
                $desktopLogoFld->htmlAfterField .= '<div class="uploaded--image"><img src="'.UrlHelper::generateFullUrl('Image','siteLogo',array(''), CONF_WEBROOT_FRONT_URL).'"> <a  class="remove--img" href="javascript:void(0);" onclick="removeDesktopLogo()" ><i class="ion-close-round"></i></a></div>';
            }

            if( isset($emailLogo) && !empty($emailLogo) ){
                $emailLogoFld->htmlAfterField .= '<div class="uploaded--image"><img src="'.UrlHelper::generateFullUrl('Image','emailLogo',array(''), CONF_WEBROOT_FRONT_URL).'"><a  class="remove--img" href="javascript:void(0);" onclick="removeEmailLogo()" ><i class="ion-close-round"></i></a></div>';
            }

            if( isset($favicon) && !empty($favicon) ){
                $faviconFld->htmlAfterField = '<div class="uploaded--image"><img src="'.UrlHelper::generateFullUrl('Image','favicon',array(''), CONF_WEBROOT_FRONT_URL).'"> <a  class="remove--img" href="javascript:void(0);" onclick="removeFavicon()" ><i class="ion-close-round"></i></a></div>';
            }
        } */
        break;
    case Configurations::FORM_MEDIA:
        break;
    case Configurations::FORM_PRODUCT:
        $geoFld = $frm->getField('CONF_PRODUCT_GEO_LOCATION');
        $geoFld->setFieldTagAttribute('class', 'geoLocation');

        $lFld = $frm->getField('CONF_LOCATION_LEVEL');
        $lFld->setFieldTagAttribute('class', 'listingFilter');
        if (FatApp::getConfig('CONF_PRODUCT_GEO_LOCATION', FatUtility::VAR_INT, 0) != applicationConstants::BASED_ON_RADIUS) {
            $rFld = $frm->getField('CONF_RADIUS_DISTANCE_IN_MILES');
            $rFld->setFieldTagAttribute('disabled', 'disabled');
        }

        if (FatApp::getConfig('CONF_PRODUCT_GEO_LOCATION', FatUtility::VAR_INT, 0) == applicationConstants::BASED_ON_RADIUS) {
            $lFld->setFieldTagAttribute('disabled', 'disabled');
        }
        break;
    case Configurations::FORM_LOCAL:
        $countryFld = $frm->getField('CONF_COUNTRY');
        if ($countryFld) {
            $countryFld->setFieldTagAttribute('id', 'user_country_id');
            $countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,' . FatApp::getConfig('CONF_STATE', FatUtility::VAR_INT, 1) . ',\'#user_state_id\')');

            $stateFld = $frm->getField('CONF_STATE');
            $stateFld->setFieldTagAttribute('id', 'user_state_id');
        }
        break;

    case Configurations::FORM_DISCOUNT:
        /* $discountValue = $frm->getField('CONF_FIRST_TIME_BUYER_COUPON_DISCOUNT_VALUE');
        $discountValue->requirements()->setRange(0, $record['CONF_FIRST_TIME_BUYER_COUPON_MIN_ORDER_VALUE']); */
        break;
}

?>
<ul class="tabs_nav innerul">
    <?php if ($frmType == Configurations::FORM_IMPORT_EXPORT) {
    ?>
        <li><a href="javascript:void(0);" onclick="generalInstructions(<?php echo $frmType; ?>);"><?php echo Labels::getLabel('LBL_Instructions', $adminLangId); ?></a>
        </li>
    <?php
    } ?>
    <?php if ($frmType != Configurations::FORM_MEDIA && $frmType != Configurations::FORM_SHARING) {
    ?>
        <li><a class="<?php echo ($lang_id == 0) ? 'active' : ''; ?>" href="javascript:void(0)" onClick="getForm(<?php echo $frmType; ?>,'<?php echo $tbid; ?>')"><?php echo Labels::getLabel('LBL_Basic', $adminLangId); ?></a>
        </li>
    <?php
    } ?>
    <?php
    if ($dispLangTab) {
    ?>
        <li>
            <a class="<?php echo (0 < $lang_id ? 'active' : '') ?>" href="javascript:void(0);" onClick="getLangForm(<?php echo $frmType; ?>, <?php echo FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1); ?>, '<?php echo $tbid; ?>')">
                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
            </a>
        </li>
        <?php
        /* foreach( $languages as $langId => $langName ){ ?>
            <li><a href="javascript:void(0);" class="<?php echo ($lang_id == $langId) ? 'active' : '' ; ?>" onClick="getLangForm(<?php echo $frmType;?>,<?php echo $langId;?>,'<?php echo $tbid; ?>')"><?php echo $langName; ?></a></li>
        <?php } */ ?>
    <?php
    } ?>
</ul>
<div class="tabs_panel_wrap">
    <?php echo $frm->getFormHtml(); ?>
</div>
<script language="javascript">
    $(document).ready(function() {
        getCountryStates($("#user_country_id").val(), <?php echo FatApp::getConfig('CONF_STATE', FatUtility::VAR_INT, 1); ?>, '#user_state_id');
    });

    var ratioTypeSquare = <?php echo AttachedFile::RATIO_TYPE_SQUARE; ?>;

    $(document).on('change', '.prefRatio-js', function() {
        var inputElement = $(this).parents('.list-inline').next('input');
        var selectedVal = $(this).val();
        if (selectedVal == ratioTypeSquare) {
            inputElement.attr('data-min_width', 150)
            inputElement.attr('data-min_height', 150)
        } else {
            inputElement.attr('data-min_width', 150)
            inputElement.attr('data-min_height', 85)
        }
    });

    $(document).on('change', '.geoLocation', function() {
        var geolocVal = $(this).val();

        $('.listingFilter').removeAttr('disabled');
        if (geolocVal == <?php echo applicationConstants::BASED_ON_RADIUS; ?>) {
            $('.listingFilter').attr('disabled', 'disabled');
            $('input[name="CONF_RADIUS_DISTANCE_IN_MILES"]').prop('disabled', false); // enable
        } else {
            $('input[name="CONF_RADIUS_DISTANCE_IN_MILES"]').prop('disabled', true); // enable
        }

        if (geolocVal == <?php echo applicationConstants::BASED_ON_DELIVERY_LOCATION; ?>) {
            $('.listingFilter').each(function() {
                if ($(this).val() == <?php echo applicationConstants::LOCATION_ZIP; ?>) {
                    $(this).attr('disabled', 'disabled');
                }
            });
        }
    });
</script>