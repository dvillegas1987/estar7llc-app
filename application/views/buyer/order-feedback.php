<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form form--horizontal');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('Buyer', 'setupOrderFeedback'));
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 8;
$btnSubmit = $frm->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");
?>
<?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Order_Feedback', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <?php echo Labels::getLabel('LBL_Product', $siteLangId),' : ',(!empty($opDetail['op_selprod_title']) ? $opDetail['op_selprod_title'] : $opDetail['op_product_name']) ,' | ', Labels::getLabel('LBL_Shop', $siteLangId),' : ', $opDetail['op_shop_name'] ; ?>
                    </h5>
                </div>
                <div class="card-body ">
                    <?php echo $frm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</main>
<script type="text/javascript">
    $(document).ready(function() {
        $('.star-rating').barrating({
            showSelectedRating: false
        });
    });
</script>
