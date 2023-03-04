<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$shopFrm->setFormTagAttribute('onsubmit', 'setupShop(this); return(false);');
$shopFrm->setFormTagAttribute('class', 'form form--horizontal');

$shopFrm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
$shopFrm->developerTags['fld_default_col'] = 4;

$countryFld = $shopFrm->getField('shop_country_code');
$countryFld->setFieldTagAttribute('id', 'shop_country_code');
$countryFld->setFieldTagAttribute('onChange', 'getStatesByCountryCode(this.value,' . $stateId . ',\'#shop_state\', \'state_code\')');
$countryFld->setFieldTagAttribute('class', 'addressSelection-js');
$stateFld = $shopFrm->getField('shop_state');
$stateFld->setFieldTagAttribute('id', 'shop_state');
$stateFld->setFieldTagAttribute('class', 'addressSelection-js');
$urlFld = $shopFrm->getField('urlrewrite_custom');
$urlFld->setFieldTagAttribute('id', "urlrewrite_custom");
$urlFld->setFieldTagAttribute('onkeyup', "getUniqueSlugUrl(this,this.value,$shop_id)");
$urlFld->htmlAfterField = "<p class='note' id='shopurl'>" . UrlHelper::generateFullUrl('Shops', 'View', array($shop_id), '/') . '</p>';
$IDFld = $shopFrm->getField('shop_id');
$IDFld->setFieldTagAttribute('id', "shop_id");
$identiFierFld = $shopFrm->getField('shop_identifier');
$identiFierFld->setFieldTagAttribute('onkeyup', "Slugify(this.value,'urlrewrite_custom','shop_id','shopurl')");
$variables = array('language' => $language, 'siteLangId' => $siteLangId, 'shop_id' => $shop_id, 'action' => $action);
$postalCode = $shopFrm->getField('shop_postalcode');
$postalCode->setFieldTagAttribute('id', "postal_code");

$latFld = $shopFrm->getField('shop_lat');
$latFld->setFieldTagAttribute('id', "lat");
$lngFld = $shopFrm->getField('shop_lng');
$lngFld->setFieldTagAttribute('id', "lng");

$btnSubmit = $shopFrm->getField('btn_submit');
/* $btnSubmit->developerTags['noCaptionTag'] = true; */
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand btn-wide");

$variables = array('language' => $language, 'siteLangId' => $siteLangId, 'shop_id' => $shop_id, 'action' => $action);
$this->includeTemplate('seller/_partial/shop-navigation.php', $variables, false); ?>
<div class="tabs__content tabs__content-js">
    <div class="card">
        <div class="card-body ">
            <div class="row">
                <div class="col-lg-12 col-md-12" id="shopFormBlock"> <?php echo $shopFrm->getFormHtml(); ?>
                    <?php if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) { ?>
                        <div class="col-lg-12 col-md-12" id="map" style="width:1500px; height:500px"></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script language="javascript">
    /* $(document).ready(function() {
        getCountryStates($("#shop_country_id")
            .val(), <?php //echo $stateId; ?> , '#shop_state');
    }); */
</script>
<?php if (FatApp::getConfig('CONF_ENABLE_GEO_LOCATION', FatUtility::VAR_INT, 0)) { ?>
    <script>
        var lat = (!$('#lat').val()) ? 0 : $('#lat').val();
        var lng = (!$('#lng').val()) ? 0 : $('#lng').val();
        initMap(lat, lng);
    </script>
<?php } ?>