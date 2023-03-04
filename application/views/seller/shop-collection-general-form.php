<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Shop_Collections', $siteLangId); ?></h5>
        <div class="">
            <a href="javascript:void(0)" onClick="shopCollections(this)" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Back_to_Collections', $siteLangId);?></a>
        </div>
    </div>
    <div class="card-body">
        <div class="row ">
            <div class="col-md-12">
                <div class="">
                    <div class="tabs tabs-sm tabs--scroll clearfix">
                        <ul>
                            <?php $inactive = ($scollection_id == 0) ? 'fat-inactive' : ''; ?>
                            <li class="is-active"><a onclick="getShopCollectionGeneralForm(<?php echo $scollection_id; ?>);" href="javascript:void(0)"><?php echo Labels::getLabel('TXT_Basic', $siteLangId);?></a></li>
                            <li class="<?php echo (0 == $scollection_id) ? 'fat-inactive' : ''; ?>">
                                <a href="javascript:void(0);" <?php echo (0 < $scollection_id) ? "onclick='editShopCollectionLangForm(" . $scollection_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                    <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                                </a>
                            </li>
                            <li class="<?php echo $inactive; ?>"><a
                            <?php if ($scollection_id > 0) {?>
                                onclick="sellerCollectionProducts(<?php echo $scollection_id; ?>);"
                            <?php } ?> href="javascript:void(0);"><?php echo Labels::getLabel('TXT_LINK', $siteLangId);?></a></li>
                            <li class="<?php echo $inactive; ?>"><a
                            <?php if ($scollection_id > 0) {?>
                                onclick="collectionMediaForm(this, <?php echo $scollection_id; ?>);"
                            <?php } ?> href="javascript:void(0);"><?php echo Labels::getLabel('TXT_Media', $siteLangId);?></a></li>
                        </ul>
                    </div>
                </div>
                <div class="row form__subcontent">
                    <div class="col-lg-12 col-md-12">
                        <?php
                        $colectionForm->setFormTagAttribute('class', 'form form--horizontal');
                        $colectionForm->setFormTagAttribute('onsubmit', 'setupShopCollection(this); return(false);');
                        $colectionForm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
                        $colectionForm->developerTags['fld_default_col'] = 4;
                        $urlFld = $colectionForm->getField('urlrewrite_custom');
                        $urlFld->setFieldTagAttribute('id', "urlrewrite_custom");
                        $urlFld->setFieldTagAttribute('onkeyup', "getSlugUrl(this,this.value,'".$baseUrl."','post')");
                        $collectionUrl = "";
                        if (0 < $scollection_id) {
                            $collectionUrl = UrlHelper::generateFullUrl('Shops', 'Collection', array($shop_id, $scollection_id));
                        }
                        $urlFld->htmlAfterField = "<small class='form-text text-muted'>" . $collectionUrl .'</small>';
                        $IDFld = $colectionForm->getField('scollection_id');
                        $IDFld->setFieldTagAttribute('id', "scollection_id");
                        $identiFierFld = $colectionForm->getField('scollection_identifier');
                        $identiFierFld->setFieldTagAttribute('onkeyup', "Slugify(this.value,'urlrewrite_custom','scollection_id')");

                        $submitFld = $colectionForm->getField('btn_submit');
                        $submitFld->setFieldTagAttribute('class', "btn btn-brand btn-wide");
                        echo $colectionForm->getFormHtml();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
