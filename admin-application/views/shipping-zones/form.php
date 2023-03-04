<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$zoneIds = [];
$countryStatesArr = [];
$zoneCountries = [];

if (!empty($zoneLocations)) {
    $zoneIds = array_column($zoneLocations, 'shiploc_zone_id');
    $zoneIds = array_unique(array_map('intval', $zoneIds));
    foreach ($zoneLocations as $location) {
        $selectedCountryId = $location['shiploc_country_id'];
        $selectedStateId = $location['shiploc_state_id'];
        $selectedZoneId = $location['shiploc_zone_id'];
        $zoneCountries[$selectedZoneId][] = $selectedCountryId;
        $countryStatesArr[$selectedCountryId][] = $selectedStateId;
    }
}
$excludeCountryStates = [];
$exZoneIds = [];

if (!empty($excludeLocations)) {
    $exZoneIds = array_column($excludeLocations, 'shiploc_zone_id');
    $exZoneIds = array_unique(array_map('intval', $exZoneIds));
    foreach ($excludeLocations as $exLocation) {
        $disableCountryId = $exLocation['shiploc_country_id'];
        $disableStateId = $exLocation['shiploc_state_id'];
        $excludeCountryStates[$disableCountryId][] = $disableStateId;
    }
}
?>
<div class="portlet">
    <form onsubmit="setupZone(this); return(false);" method="post" class="web_form form_horizontal" id="shippingZoneFrm">
        <div class="portlet__head">
            <div class="portlet__head-label">
                <h3 class="portlet__head-title"><?php echo Labels::getLabel('LBL_Zone_Setup', $adminLangId); ?>
                </h3>
            </div>
        </div>
        <div class="portlet__body">
            <input type="hidden" name="shipprozone_id" value="<?php echo (!empty($zone_data)) ? $zone_data['shipprozone_id'] : 0; ?>">
            <input type="hidden" name="shipzone_id" value="<?php echo $zone_id; ?>">
            <input type="hidden" name="shipzone_profile_id" value="<?php echo $profile_id; ?>">
            <!--<input type="hidden" name="selected_ship_zone"> -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group mb-20 zone-main-field--js">
                        <input type="text" placeholder="<?php echo Labels::getLabel("LBL_Zone_Name", $adminLangId); ?>" name="shipzone_name" class="form-control shipzone_name" value="<?php echo (!empty($zone_data)) ? $zone_data['shipzone_name'] : ''; ?>" required>
                        <span class="form-text text-muted"><?php echo Labels::getLabel("LBL_Customers_will_not_see_this.", $adminLangId); ?></span>
                    </div>
                </div>
            </div>
            <div class="row simplebar-resize-wrapper mb-20">
                <div class="col-sm-12">
                    <div class="field-wraper mb-4">
                        <div class="field_cover">
                            <label>
                                <span class="checkbox" data-zoneid="-1"><input type="checkbox" name="rest_of_the_world" value="-1" class="checkbox_zone_-1" <?php echo (in_array(-1, $zoneIds)) ? 'checked' : ''; ?> <?php echo (in_array(-1, $exZoneIds)) ? 'disabled' : ''; ?>><i class="input-helper"></i></span><?php echo Labels::getLabel("LBL_REST_OF_THE_WORLD", $adminLangId); ?>
                            </label>
                        </div>
                    </div>
                    <div class="checkbox_container--js">
                        <?php
                        if (!empty($zones)) {
                            foreach ($zones as $zone) {
                                $countCounties = 0;
                                if (!empty($zoneCountries)) {
                                    $cZoneCountries = (isset($zoneCountries[$zone['zone_id']])) ? $zoneCountries[$zone['zone_id']] : array();
                                    $countCounties = count(array_unique($cZoneCountries));
                                }
                                $countries = (isset($zone['countries'])) ? $zone['countries'] : array();
                                $totalCountries = count($countries); ?>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <label>
                                            <span class="checkbox zone--js" data-zoneid="<?php echo $zone['zone_id']; ?>">
												<input type="checkbox" name="shiploc_zone_ids[]" value="<?php echo $zone['zone_id']; ?>" class="countries-js checkbox_zone_<?php echo $zone['zone_id']; ?>" <?php echo ($countCounties == $totalCountries && $countCounties != 0) ? 'checked' : ''; ?>>
												<i class="input-helper"></i>
											</span><?php echo $zone['zone_name']; ?>
                                        </label>
                                    </div>
                                </div>
                                <?php
                                if (!empty($countries)) { ?>
                                    <ul class="child-checkbox-ul zone_<?php echo $zone['zone_id']; ?>">
                                        <?php foreach ($countries as $country) {                                          
                                            $statesCount = count($country['states']);
                                            $countryId = $country['country_id'];
                                            $disabled = '';
                                            $checked = '';
                                            $countryStates = [];
                                            //$exCountryStates = [];
                                            if (!empty($countryStatesArr) && isset($countryStatesArr[$countryId])) {
                                                $countryStates = $countryStatesArr[$countryId];
                                            }
                                            if (!empty($countryStates) && in_array('-1', $countryStates)) {
                                                $checked = 'checked';
                                            }
                                            if (!empty($excludeCountryStates) && isset($excludeCountryStates[$countryId])) {                                          
                                                $disabled = 'disabled';
                                            }
                                           ?>
                                            <li>
                                                <div class="row no-gutters">
                                                    <div class="col">
                                                        <div class="field-wraper">
                                                            <div class="field_cover">
                                                                <label>
                                                                    <span class="checkbox country--js " data-countryid="<?php echo $countryId; ?>" data-statecount="<?php echo $statesCount; ?>">
                                                                        <input type="checkbox" name="c_id[]" value="<?php echo $zone['zone_id']; ?>-<?php echo $countryId; ?>" class="checkbox_country_<?php echo $countryId; ?>" <?php echo $checked; ?>><i class="input-helper"></i>
                                                                    </span>
                                                                    <?php echo $country['country_name']; ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-auto mr-3">
                                                        <?php if ($statesCount > 0) { ?>
                                                            <a class="link font-bolder link_<?php echo $countryId; ?> containChild-js" data-toggle="collapse" href="#state_list_<?php echo $countryId; ?>" aria-expanded="false" aria-controls="state_list_<?php echo $countryId; ?>" data-countryid="<?php echo $countryId; ?>" data-loadedstates="1" >
                                                                <span class="statecount--js selectedStateCount--js_<?php echo $countryId; ?> " data-totalcount="<?php echo $statesCount; ?>">0</span>
                                                                <?php echo Labels::getLabel("LBL_of", $adminLangId); ?>
                                                                <span class="totalStates "><?php echo $statesCount; ?></span>
                                                                <span class="ion-ios-arrow-down icon"></span>
                                                            </a>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <div class="collapse" id="state_list_<?php echo $countryId; ?>">                                                    
                                                    <?php if (!empty($country['states'])) { ?>
                                                        <ul class="child-checkbox-ul country_<?php echo $countryId; ?>">
                                                            <?php
                                                            foreach ($country['states'] as $state) {
                                                                $stateChecked = '';                                                           
                                                                $countryStates = [];
                                                                $exCountryStates = [];

                                                                if (!empty($countryStatesArr) && isset($countryStatesArr[$countryId])) {
                                                                    $countryStates = $countryStatesArr[$countryId];
                                                                }
                                                                if ((!empty($countryStates) && (in_array('-1', $countryStates) || in_array($state['state_id'], $countryStates)))) {
                                                                    $stateChecked = 'checked';
                                                                }
                                                                $stateDisabled ='';                                                                
                                                                if (isset($excludeCountryStates[$countryId]) && in_array($state['state_id'],$excludeCountryStates[$countryId])) {                                          
                                                                    $stateDisabled = ' disabled';
                                                                }
                                                                ?>	
                                                                <li>
                                                                    <div class="field-wraper">
                                                                        <div class="field_cover">
                                                                            <label><span class="checkbox " data-stateid="<?php echo $state['state_id']; ?>"><input type="checkbox" name="s_id[]" value="<?php echo $zone['zone_id']; ?>-<?php echo $countryId; ?>-<?php echo $state['state_id']; ?>" class="state--js" <?php echo $stateChecked; ?> <?php echo $stateDisabled; ?>><i class="input-helper"></i></span><?php echo $state['state_name']; ?></label>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                        <?php } ?>
                                                        </ul>
                                                    <?php } ?>
                                                </div>
                                             </li>
                                        <?php
                                        } ?>
                                    </ul>
                                <?php } ?>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="portlet__foot">
            <div class="row">
                <div class="col-md-12">
                    <?php 
                        $lbl = (0 < $zone_id ? Labels::getLabel("LBL_UPDATE_ZONE", $adminLangId) : Labels::getLabel("LBL_ADD_ZONE", $adminLangId));
                    ?>
                    <input type="submit" name="btn_submit" value="<?php echo $lbl; ?>">
                    <!--<input type="button" name="cancel" onClick="searchProductsSection(<?php echo $profile_id; ?>);" value="<?php echo Labels::getLabel("LBL_Cancel", $adminLangId); ?>">-->
                </div>
            </div>
        </div>
    </form>
</div>
<?php if (0 < $zone_id) { ?>
    <script>
            setTimeout(function(){                
                $('.country--js input[type="checkbox"]').each(function(){
                    var countryId = $(this).closest('.country--js').data('countryid');               
                    var stateCount = $('.country_'+countryId+' .state--js:checked').length;                
                    if(!$(this).prop("checked")){
                        if(0 < stateCount){
                            $('.link_'+countryId).click();
                        }
                    }                
                    $('.selectedStateCount--js_'+countryId).text(stateCount);
                    
                });   
                $('.zone--js').each(function(){
                    var zoneId = $(this).data('zoneid');
                    var stateCount  = $('.zone_'+zoneId+' .state--js:checked').length;
                    if(0 < stateCount && !$(this).prop("checked")){
                       $('.containCountries-js-'+zoneId).click();
                    }
                })                
                
            }, 150);
      
    </script>
<?php } ?>
