<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <?php //$this->includeTemplate('_partial/dashboardTop.php'); 
            ?>
            <div class="col-md-auto">
                <h2 class="content-header-title">
                    <?php
                    if ($type == 1) {
                        echo Labels::getLabel('LBL_Seller_Products', $siteLangId);
                    } else {
                        echo Labels::getLabel('LBL_Marketplace_Products', $siteLangId);
                    }
                    ?>
                    <i class="fa fa-question-circle" onClick="productInstructions(<?php echo Extrapage::MARKETPLACE_PRODUCT_INSTRUCTIONS; ?>)"></i>
                </h2>
            </div>
            <?php $this->includeTemplate('_partial/productPagesTabs.php', array('siteLangId' => $siteLangId, 'controllerName' => $controllerName, 'action' => $action, 'canEdit' => $canEdit, 'type' => $type), false); ?>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <?php
                                $frmSearchCatalogProduct->setFormTagAttribute('id', 'frmSearchCatalogProduct');
                                $frmSearchCatalogProduct->setFormTagAttribute('class', 'form');
                                $frmSearchCatalogProduct->setFormTagAttribute('onsubmit', 'searchCatalogProducts(this); return(false);');
                                $frmSearchCatalogProduct->getField('keyword')->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Search_by_keyword/EAN/ISBN/UPC_code', $siteLangId));
                                $frmSearchCatalogProduct->developerTags['colClassPrefix'] = 'col-md-';
                                $frmSearchCatalogProduct->developerTags['fld_default_col'] = 12;

                                $keywordFld = $frmSearchCatalogProduct->getField('keyword');
                                $keywordFld->setFieldTagAttribute('id', 'tour-step-3');
                                $keywordFld->setWrapperAttribute('class', 'col-lg-6');
                                $keywordFld->developerTags['col'] = 6;

                                // if (FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT')) {
                                // $dateFromFld = $frmSearchCatalogProduct->getField('type');
                                // $dateFromFld->setFieldTagAttribute('class', '');
                                // $dateFromFld->setWrapperAttribute('class', 'col-lg-2');
                                // $dateFromFld->developerTags['col'] = 2;
                                // }
                                $typeFld = $frmSearchCatalogProduct->getField('product_type');
                                $typeFld->setWrapperAttribute('class', 'col-lg-2');
                                $typeFld->developerTags['col'] = 2;

                                $submitFld = $frmSearchCatalogProduct->getField('btn_submit');
                                $submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');
                                $submitFld->setWrapperAttribute('class', 'col-lg-2');
                                $submitFld->developerTags['col'] = 2;

                                $fldClear = $frmSearchCatalogProduct->getField('btn_clear');
                                $fldClear->setFieldTagAttribute('onclick', 'clearSearch()');
                                $fldClear->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
                                $fldClear->setWrapperAttribute('class', 'col-lg-2');
                                $fldClear->developerTags['col'] = 2;
                                /* if( User::canAddCustomProductAvailableToAllSellers() ){
                                      $submitFld = $frmSearchCatalogProduct->getField('btn_submit');
                                      $submitFld->setFieldTagAttribute('class','btn-block');
                                      $submitFld->developerTags['col'] = 4;
                                    } */
                                echo $frmSearchCatalogProduct->getFormHtml();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="listing"> </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    $(document).ready(function() {
        <?php //if (!$displayDefaultListing) { 
        ?>
        searchCatalogProducts(document.frmSearchCatalogProduct);
        <?php //} 
        ?>
    });

    $(".btn-inline-js").click(function() {
        $(".box-slide-js").slideToggle();
    });
</script>