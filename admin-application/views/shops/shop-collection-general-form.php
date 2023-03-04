<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (isset($scollection_id) && $scollection_id > 0) {
    $scollection_id = $scollection_id;
} else {
    $scollection_id = 0;
}
$fatInactive = ($scollection_id == 0) ? 'fat-inactive' : '';
?>
<ul class="tabs_nav tabs_nav--internal">
    <li><a class="active" href="javascript:void(0)" <?php if ($scollection_id > 0) { ?> onclick="getShopCollectionGeneralForm(<?php echo $shop_id; ?>,<?php echo $scollection_id; ?>);" href="javascript:void(0)" <?php } ?>><?php echo Labels::getLabel('TXT_GENERAL', $adminLangId); ?></a></li>

    <li class="<?php echo (0 == $scollection_id) ? 'fat-inactive' : ''; ?>">
        <a href="javascript:void(0);" <?php echo (0 < $scollection_id) ? "onclick='editShopCollectionLangForm(" . $shop_id . ", " . $scollection_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
            <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
        </a>
    </li>

    <li class="<?php echo $fatInactive; ?>"><a <?php if ($scollection_id > 0) { ?> onclick="sellerCollectionProducts(<?php echo $scollection_id; ?>,<?php echo $shop_id; ?>);" <?php } ?> href="javascript:void(0);"><?php echo Labels::getLabel('TXT_LINK', $adminLangId); ?></a></li>
    <li class="<?php echo $fatInactive; ?>">
        <a <?php if ($scollection_id > 0) { ?> onclick="collectionMediaForm(<?php echo $shop_id; ?>,<?php echo $scollection_id ?>)" <?php } ?> href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_MEDIA', $adminLangId); ?> </a>
    </li>
</ul>
<div class="tabs_panel_wrap">
    <div class="form__subcontent">
        <?php
        $colectionForm->setFormTagAttribute('class', 'form form_horizontal web_form');
        $colectionForm->setFormTagAttribute('onsubmit', 'setupShopCollection(this); return(false);');
        $colectionForm->developerTags['colClassPrefix'] = 'col-md-';
        $colectionForm->developerTags['fld_default_col'] = 12;
        $urlFld = $colectionForm->getField('urlrewrite_custom');
        $urlFld->setFieldTagAttribute('id', "urlrewrite_custom");
        $urlFld->setFieldTagAttribute('onkeyup', "getSlugUrl(this,this.value,'" . $baseUrl . "')");
        $urlFld->htmlAfterField = "<br><small class='text--small'>" . UrlHelper::generateFullUrl('Shops', 'Collection', array($shop_id, $scollection_id), CONF_WEBROOT_FRONT_URL) . '</small>';
        $IDFld = $colectionForm->getField('scollection_id');
        $IDFld->setFieldTagAttribute('id', "scollection_id");
        $identiFierFld = $colectionForm->getField('scollection_identifier');
        $identiFierFld->setFieldTagAttribute('onkeyup', "Slugify(this.value,'urlrewrite_custom','scollection_id')");
        echo $colectionForm->getFormHtml();
        ?>
    </div>
</div>