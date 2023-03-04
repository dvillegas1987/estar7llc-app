<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<ul class="columlist links--vertical" id="coupon-brand">
<?php if(count($couponBrands)>0){
	$lis = '';
	foreach($couponBrands as $brand){
		$lis .= '<li><span class="left"><a href="javascript:void(0)" title="Remove" onClick="removeCouponBrand('.$coupon_id.','.$brand['brand_id'].');"><i class="icon ion-close-round" data-prod-id="' . $brand['brand_id'] . '"></i></a></span>';
		$lis .= '<span>' . $brand['brand_name'].' ('.$brand['brand_identifier'].')'.'<input type="hidden" value="'.$brand['brand_id'].'"  name="brands[]"></span></li>';
	}
	echo $lis;
} ?>
</ul>