<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmBrandReq->setFormTagAttribute('class', 'form form--horizontal');
$frmBrandReq->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frmBrandReq->developerTags['fld_default_col'] = 12;
$frmBrandReq->setFormTagAttribute('onsubmit', 'setupBrandReq(this); return(false);');
$identifierFld = $frmBrandReq->getField(Brand::DB_TBL_PREFIX.'id');
$identifierFld->setFieldTagAttribute('id', Brand::DB_TBL_PREFIX.'id');
$submitFld = $frmBrandReq->getField('btn_submit');
$submitFld->setFieldTagAttribute('class', 'btn btn-brand');
?>

<div class="box__head">
<h4><?php echo (FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) ? Labels::getLabel('LBL_Request_New_Brand', $siteLangId) : Labels::getLabel('LBL_New_Brand', $siteLangId) ?></h4>
</div>
<div class="box__body">
    <div class="tabs tabs--small tabs--scroll">
        <ul>
            <li class="is-active"><a href="javascript:void(0)" onclick="addBrandReqForm(<?php echo $brandReqId; ?>);"><?php echo Labels::getLabel('LBL_Basic', $siteLangId);?></a></li>
            <li class="<?php echo (0 == $brandReqId) ? 'fat-inactive' : ''; ?>">
                <a href="javascript:void(0);" <?php echo (0 < $brandReqId) ? "onclick='addBrandReqLangForm(" . $brandReqId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                    <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                </a>
            </li>
            <?php $inactive=($brandReqId==0)?'fat-inactive':''; ?>
            <li class="<?php echo $inactive;?>"><a href="javascript:void(0)"
                <?php if ($brandReqId > 0) { ?>
                    onclick="brandMediaForm(<?php echo $brandReqId ?>);"
                <?php } ?>><?php echo Labels::getLabel('LBL_Media', $siteLangId); ?></a>
            </li>
        </ul>
    </div>
    <?php
        echo $frmBrandReq->getFormHtml();
    ?>
</div>
