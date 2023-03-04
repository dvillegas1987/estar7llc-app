<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
    $frmSearchCustomCatalogProducts->setFormTagAttribute('onSubmit', 'searchCustomCatalogProducts(this); return(false);');

    $frmSearchCustomCatalogProducts->setFormTagAttribute('class', 'form');
    $frmSearchCustomCatalogProducts->developerTags['colClassPrefix'] = 'col-md-';
    $frmSearchCustomCatalogProducts->developerTags['fld_default_col'] = 12;

    $keyFld = $frmSearchCustomCatalogProducts->getField('keyword');
    $keyFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Keyword', $siteLangId));
    $keyFld->setWrapperAttribute('class', 'col-lg-6');
    $keyFld->developerTags['col'] = 6;
    $keyFld->developerTags['noCaptionTag'] = true;

    $submitBtnFld = $frmSearchCustomCatalogProducts->getField('btn_submit');
    $submitBtnFld->setFieldTagAttribute('class', 'btn-block');
    $submitBtnFld->setWrapperAttribute('class', 'col-lg-3');
    $submitBtnFld->developerTags['col'] = 3;
    $submitBtnFld->developerTags['noCaptionTag'] = true;

    $cancelBtnFld = $frmSearchCustomCatalogProducts->getField('btn_clear');
    $cancelBtnFld->setFieldTagAttribute('class', 'btn-block');
    $cancelBtnFld->setWrapperAttribute('class', 'col-lg-3');
    $cancelBtnFld->developerTags['col'] = 3;
    $cancelBtnFld->developerTags['noCaptionTag'] = true;
?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row justify-content-between mb-3">
            <?php //$this->includeTemplate('_partial/dashboardTop.php'); ?>
            <div class="col-md-auto">
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Products_Request', $siteLangId); ?><i class="fa fa-question-circle" onClick="productInstructions(<?php echo Extrapage::PRODUCT_REQUEST_INSTRUCTIONS; ?>)"></i></h2>                
            </div>
            <?php $this->includeTemplate('_partial/productPagesTabs.php', array('siteLangId'=>$siteLangId, 'controllerName'=>$controllerName, 'action'=>$action, 'canEdit'=>$canEdit), false); ?>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="replaced">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <?php
                                        $submitFld = $frmSearchCustomCatalogProducts->getField('btn_submit');
                                        $submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block ');

                                        $fldClear= $frmSearchCustomCatalogProducts->getField('btn_clear');
                                        $fldClear->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');

                                        echo $frmSearchCustomCatalogProducts->getFormHtml();
                                        ?>
                                        <?php echo $frmSearchCustomCatalogProducts->getExternalJS(); ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div id="listing">
                                <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    jQuery(document).ready(function($) {
        $(".initTooltip").click(function(){
            $.facebox({ div: '#requestedProductsToolTip' }, 'catalog-bg');
        });
    });
</script>
