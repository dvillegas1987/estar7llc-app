<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class='page'>
    <div class='container container-fluid'>
        <div class="row">
            <div class="col-lg-12 col-md-12 space">
                <div class="page__title">
                    <div class="row">
                        <div class="col--first col-lg-6">
                            <span class="page__icon"><i class="ion-android-star"></i></span>
                            <h5><?php echo Labels::getLabel('LBL_Buy_Together_Products', $adminLangId); ?> </h5>
                            <?php $this->includeTemplate('_partial/header/header-breadcrumb.php'); ?>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="sectionbody space">
                        <?php $relProdFrm->setFormTagAttribute('onsubmit', 'setUpSellerProductLinks(this); return(false);');
                        $relProdFrm->setFormTagAttribute('class', 'web_form form form--horizontal');
                        $prodFld = $relProdFrm->getField('product_name');
                        $prodFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Select_Product', $adminLangId));

                        $relProdFld = $relProdFrm->getField('products_upsell');
                        $relProdFld->setFieldTagAttribute('placeholder', Labels::getLabel('LBL_Add_Buy_Together_Products', $adminLangId));

                        $submitBtnFld = $relProdFrm->getField('btn_submit');
                        $submitBtnFld->setFieldTagAttribute('class', 'btn-block btn btn-brand'); ?>
                        <?php echo $relProdFrm->getFormTag(); ?>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="field-set">
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <?php echo $relProdFrm->getFieldHTML('product_name');?>                                    
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="field-set">
                                    <div class="field-wraper">
                                        <div class="field_cover custom-tagify">
                                            <?php echo $relProdFrm->getFieldHTML('products_upsell');?>
                                            <ul class="list-tags" id="upsell-products"></ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="field-set">
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                            <?php echo $relProdFrm->getFieldHTML('btn_submit');?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php echo $relProdFrm->getFieldHTML('selprod_id'); ?>
                        </form>
                        <?php echo $relProdFrm->getExternalJS();?>
                    </div>
                </section>
                <!--<div class="col-sm-12">-->
                <section class="section">
                    <div class="sectionhead">
                        <h4><?php echo Labels::getLabel('LBL_Buy_Together_Products_List', $adminLangId); ?> </h4>
                    </div>
                    <div class="sectionbody">
                        <div class="tablewrap" >
                            <div id="listing"> <?php echo Labels::getLabel('LBL_Processing...', $adminLangId); ?></div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
