<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $frm->setFormTagAttribute('onsubmit', 'uploadCollectionImage(this); return(false);');
    $frm->setFormTagAttribute('class', 'form');
    $frm->developerTags['colClassPrefix'] = 'col-md-';
    $frm->developerTags['fld_default_col'] = 12;

    $fld = $frm->getField('collection_image');
    $fld->addFieldTagAttribute('class', '');
    $fld->addFieldTagAttribute('onChange', 'collectionPopupImage(this)');
?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Shop_Collections', $siteLangId); ?></h5>
        <div class="">
            <a href="javascript:void(0)" onClick="shopCollections(this)" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Back_to_Collections', $siteLangId);?></a>
        </div>
    </div>
    <div class="card-body">
        <div class="col-md-6">
            <div class="">
                <div class="tabs tabs-sm tabs--scroll clearfix">
                    <ul>
                        <li ><a onclick="getShopCollectionGeneralForm(<?php echo $scollection_id; ?>);" href="javascript:void(0)"><?php echo Labels::getLabel('TXT_Basic', $siteLangId);?></a></li>
                        <li class="<?php echo (0 == $scollection_id) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo (0 < $scollection_id) ? "onclick='editShopCollectionLangForm(" . $scollection_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                            </a>
                        </li>
                        <li>
                            <a onclick="sellerCollectionProducts(<?php echo $scollection_id ?>)" href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_LINK', $siteLangId);?> </a>
                        </li>
                        <li class="is-active"><a
                        <?php if ($scollection_id > 0) {?>
                            onclick="collectionMediaForm(this, <?php echo $scollection_id; ?>);"
                        <?php } ?> href="javascript:void(0);"><?php echo Labels::getLabel('TXT_Media', $siteLangId);?></a></li>
                    </ul>
                </div>
            </div>
            <div class="form__subcontent">
                <div class="preview" id="shopFormBlock">
                    <small class="form-text text-muted"><?php echo sprintf(Labels::getLabel('MSG_Upload_shop_collection_image_text', $siteLangId), '610*343')?></small>
                    <?php echo $frm->getFormHtml();?>
                       <div id="imageListing" class="row" ></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var collectionMediaWidth = '610';
    var collectionMediaHeight = '343';
</script>
