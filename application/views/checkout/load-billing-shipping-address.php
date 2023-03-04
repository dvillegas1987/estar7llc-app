<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<?php  if(isset($defaultAddress) && $defaultAddress) { ?>
	<section class="section-checkout is-completed" id="addressDivFooter">
			<div class="selected-panel">
			<?php if($hasPhysicalProduct){ ?>
			  <div class="selected-panel-type"><?php echo Labels::getLabel('LBL_Billing/Delivery_Address', $siteLangId)?></div>
			<?php }else{
				
			?>
			<div class="selected-panel-type"><?php echo Labels::getLabel('LBL_Billing_Address', $siteLangId)?></div>
			<?php } ?>
			<div class="selected-panel-data"><?php echo $defaultAddress['addr_name']; ?><br>
			<?php echo $defaultAddress['addr_address1'];?>, <?php echo (strlen($defaultAddress['addr_zip'])>0) ? Labels::getLabel('LBL_Zip:', $siteLangId).$defaultAddress['addr_zip'].'<br>':'';?>
			<?php 
			$addrPhone = $defaultAddress['addr_phone'];
			if (!empty($addrPhone) && array_key_exists('addr_phone_dcode', $defaultAddress)) {
				$addrPhone = ValidateElement::formatDialCode($defaultAddress['addr_phone_dcode']) . $addrPhone;
			}
			echo (strlen($addrPhone)>0) ? Labels::getLabel('LBL_Phone:', $siteLangId).$addrPhone.'<br>':'';
			?></div>
			<div class="selected-panel-action"><a href="javascript:void(0)" onClick="showAddressList()" class="btn btn-brand btn-sm ripplelink"><?php echo Labels::getLabel('LBL_Change_Address', $siteLangId); ?></a></div>
		</div>
	</section>
<?php  } ?>