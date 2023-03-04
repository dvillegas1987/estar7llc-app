<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$countryStatesArr = [];
if (!empty($zoneLocations)) {
	foreach ($zoneLocations as $location) {
		$selectedCountryId = $location['shiploc_country_id'];
		$selectedStateId = $location['shiploc_state_id'];
		$countryStatesArr[$selectedCountryId][] = $selectedStateId;
	}
}

$excludeCountryStatesArr = [];
if (!empty($excludeLocations)) {
	foreach ($excludeLocations as $exLocation) {
		$disableCountryId = $exLocation['shiploc_country_id'];
		$disableStateId = $exLocation['shiploc_state_id'];
		$excludeCountryStatesArr[$disableCountryId][] = $disableStateId;
	}
}
?>
<?php if (!empty($states)) { ?>
	<ul class="child-checkbox-ul country_<?php echo $countryId;?>">
	<?php foreach ($states as $key => $state) { 
	$checked = '';
	$disabled = '';
	$countryStates = [];
	$exCountryStates = [];
	
	if (!empty($countryStatesArr) && isset($countryStatesArr[$countryId])) {
		$countryStates = $countryStatesArr[$countryId];
	}
	if ((!empty($countryStates) && (in_array('-1', $countryStates) || in_array($key, $countryStates))) || ($selected > 0)) {
		$checked = 'checked';
	}
	
	if (!empty($excludeCountryStatesArr) && isset($excludeCountryStatesArr[$countryId])) {
		$exCountryStates = $excludeCountryStatesArr[$countryId];
	}
	if (!empty($exCountryStates) && (in_array('-1', $exCountryStates) || in_array($key, $exCountryStates))) {
		$disabled = 'disabled';
	}
	?>	
		<li>
			<div class="field-wraper">
			<div class="field_cover">
				<label><span class="checkbox <?php echo $disabled;?>" data-stateid="<?php echo $key;?>"><input type="checkbox" name="shiploc_state_ids[]" value="<?php echo $zoneId;?>-<?php echo $countryId;?>-<?php echo $key;?>" class="state--js" <?php echo $checked; ?> <?php echo $disabled;?>><i class="input-helper"></i></span><?php echo $state;?></label>
			</div>
			</div>
		</li>
	<?php } ?>
	</ul>
<?php } ?>