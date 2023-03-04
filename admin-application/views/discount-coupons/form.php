<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupCoupon(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$minOrder_fld = $frm->getField('coupon_min_order_value');
$minOrder_fld->setWrapperAttribute('id', 'coupon_minorder_div');

$ctype_fld = $frm->getField('coupon_type');
$ctype_fld->addFieldTagAttribute('onChange', 'callCouponTypePopulate(this.value); ');

$coupon_max_discount_value_fld = $frm->getField('coupon_max_discount_value');
$coupon_max_discount_value_fld->setWrapperAttribute('id', 'coupon_max_discount_value_div');

$discountValueFld = $frm->getField('coupon_discount_value');
$discountValueFld->addFieldTagAttribute('class', 'discountValue-js');

$coupon_discount_in_percent_fld = $frm->getField('coupon_discount_in_percent');
$coupon_discount_in_percent_fld->addFieldTagAttribute('onChange', 'callCouponDiscountIn(this.value, ' . applicationConstants::PERCENTAGE . ', ' . applicationConstants::FLAT . '); ');
$coupon_discount_in_percent_fld->addFieldTagAttribute('class', 'discountType-js');

/* $cvalid_fld = $frm->getField('coupon_valid_for');
$cvalid_fld->setWrapperAttribute('id', 'coupon_validfor_div'); */

/* $reqTrue = new FormFieldRequirement('coupon_min_order_value','value');
$reqTrue->setRequired();
$reqFalse = new FormFieldRequirement('coupon_min_order_value','value');
$reqFalse->setRequired(false);

$cType_fld = $frm->getField('coupon_type');
$cType_fld->requirements()->addOnChangerequirementUpdate(DiscountCoupons::TYPE_DISCOUNT, 'eq', 'coupon_min_order_value', $reqTrue) ;
$cType_fld->requirements()->addOnChangerequirementUpdate(DiscountCoupons::TYPE_SELLER_PACKAGE, 'eq', 'coupon_min_order_value' , $reqFalse) ; */

?>
<section class="section">
    <div class="sectionhead">

        <h4><?php echo Labels::getLabel('LBL_Coupon_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">

            <div class="col-sm-12">
                <h1><?php // echo Labels::getLabel('LBL_Coupon_Setup',$adminLangId);
                    ?></h1>
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a class="active" href="javascript:void(0)" onclick="addCouponForm(<?php echo $coupon_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
                        <li class="<?php echo ($coupon_id == 0) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo ($coupon_id) ? "onclick='addCouponLangForm(" . $coupon_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <li class="<?php echo ($coupon_id == 0) ? 'fat-inactive' : ''; ?>"><a href="javascript:void(0);" <?php if ($coupon_id > 0) { ?> onclick="couponMediaForm(<?php echo $coupon_id ?>);" <?php } ?>><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <?php echo $frm->getFormHtml(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    $(document).ready(function() {
        callCouponTypePopulate(<?php echo $coupon_type; ?>);
        callCouponDiscountIn(<?php echo $couponDiscountIn; ?>, <?php echo applicationConstants::PERCENTAGE; ?>, <?php echo applicationConstants::FLAT; ?>);
    });
    $(document).on('keyup','.discountValue-js',function(){
        if ($('.discountType-js option:selected').val() == "<?php echo applicationConstants::PERCENTAGE; ?>" && $(this).val() > 100) {
            $(this).val(100);
            return false;
        }
    });
</script>