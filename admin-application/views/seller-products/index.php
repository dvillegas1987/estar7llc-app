<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSearch->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frmSearch->setFormTagAttribute('onsubmit', 'searchProducts(this); return(false);');
$frmSearch->developerTags['colClassPrefix'] = 'col-md-';
$frmSearch->developerTags['fld_default_col'] = 4;
$fld_active = $frmSearch->getField('active');
// $fld_active->addFieldTagAttribute('class', 'small');
$frmSearch->addHiddenField('', 'product_id', $product_id);
?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_MANAGE_SELLER_INVENTORY', $adminLangId); ?>
                            </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section searchform_filter">
                    <div class="sectionhead">
                        <h4> <?php echo Labels::getLabel('LBL_Search...', $adminLangId); ?>
                        </h4>
                    </div>
                    <div class="sectionbody space togglewrap" style="display:none;">
                        <?php echo $frmSearch->getFormHtml(); ?>
                    </div>
                </section>
                <!--<div class="col-sm-12">-->
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_SELLER_INVENTORY_LIST', $adminLangId); ?>
                        </h4>
                        <?php $ul = new HtmlElement("ul", array("class" => "actions actions--centered"));
                        $li = $ul->appendElement("li", array('class' => 'droplink'));
                        $innerDiv = $li->appendElement('div', array('class' => 'dropwrap'));
                        $li->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small green', 'title' => Labels::getLabel('LBL_Edit', $adminLangId)), '<i class="ion-android-more-horizontal icon"></i>', true);
                        $innerUl = $innerDiv->appendElement('ul', array('class' => 'linksvertical'));

                        /*if ($canView && FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT', FatUtility::VAR_INT, 0)) {
                            $innerLiExport=$innerUl->appendElement('li');
                            $innerLiExport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Export', $adminLangId), "onclick"=>"exportForm(".Importexport::TYPE_INVENTORIES.")"), Labels::getLabel('LBL_Export', $adminLangId), true); ?>
                        <!--<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="exportForm(<?php echo Importexport::TYPE_INVENTORIES; ?>)";><?php echo Labels::getLabel('LBL_Export', $adminLangId); ?></a>-->
                        <?php } ?>
                        <?php if ($canEdit && FatApp::getConfig('CONF_ENABLE_IMPORT_EXPORT', FatUtility::VAR_INT, 0)) {
                            $innerLiImport=$innerUl->appendElement('li');
                            $innerLiImport->appendElement('a', array('href'=>'javascript:void(0)','class'=>'button small green','title'=>Labels::getLabel('LBL_Import', $adminLangId),"onclick"=>"addImportForm(". Importexport::TYPE_INVENTORIES.")"), Labels::getLabel('LBL_Import', $adminLangId), true); ?>
                        <!--<a href="javascript:void(0)" class="themebtn btn-default btn-sm" onClick="importForm(<?php echo Importexport::TYPE_INVENTORIES; ?>)";><?php echo Labels::getLabel('LBL_Import', $adminLangId); ?></a>-->
                        <?php } */ ?>
                        <?php if ($product_id) {
                            $innerLiAddCat = $innerUl->appendElement('li');
                            $innerLiAddCat->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'button small 	green', 'title' => Labels::getLabel('LBL_Add_New_Product', $adminLangId), "onclick" => "sellerProductForm(" . $product_id . ")"), Labels::getLabel('LBL_Add_New_Product', $adminLangId), true); ?>
                            <a href="javascript:void(0);" onclick="sellerProductForm(<?php echo $product_id; ?>,0);" class="themebtn btn-default btn-sm"><?php echo Labels::getLabel('LBL_Add_New_Product', $adminLangId); ?></a>
                        <?php
                        }

                        if ($canEdit) {
                            $otherButtons = [
                                [
                                    'attr' => [
                                        'href' => 'javascript:void(0)',
                                        'onclick' => 'addSpecialPrice(0)',
                                        'title' => Labels::getLabel('LBL_Add_Special_Price', $adminLangId),
                                        'class' => 'toolbar-btn-js d-none'
                                    ],
                                    'label' => '<i class="fas fa-dollar-sign"></i>'
                                ],
                                [
                                    'attr' => [
                                        'href' => 'javascript:void(0)',
                                        'onclick' => 'addVolumeDiscount(0)',
                                        'title' => Labels::getLabel('LBL_Add_Volume_Discount', $adminLangId),
                                        'class' => 'toolbar-btn-js d-none'
                                    ],
                                    'label' => '<i class="fas fa-percent"></i>'
                                ],
                            ];
                            $this->includeTemplate('_partial/action-buttons.php', ['otherButtons' => $otherButtons, 'adminLangId' => $adminLangId], false);
                        }
                        ?>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap">
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
<!--</div></div></div>-->