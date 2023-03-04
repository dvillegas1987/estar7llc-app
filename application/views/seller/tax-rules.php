<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<?php $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo $taxCategory; ?></h2>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <a href="<?php echo UrlHelper::generateUrl('seller', 'taxCategories');?>"
                        class="btn btn-outline-brand btn-sm">
                        <?php echo Labels::getLabel('LBL_Back_To_Tax_Categories', $siteLangId)?>
                    </a>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="card">
                <div class="card-body">
                <?php if (!empty($rulesData)) { ?>
                    <div class="tax-rules">
                        <ul>
                            <?php
                            foreach($rulesData as $rule) {
                                $combinedData = [];
                                if (!empty($combinedRulesDetails) && isset($combinedRulesDetails[$rule['taxrule_id']])) {
                                    $combinedData = $combinedRulesDetails[$rule['taxrule_id']];
                                }
                                $ruleId = $rule['taxrule_id'];
                                $locations = (!empty($ruleLocations) && isset($ruleLocations[$ruleId]))?$ruleLocations[$ruleId]:array();
                                $countryIds = [];
                                $stateIds = [];
                                $typeIds = [];
                                if (!empty($locations)) {
                                    $countryNames = array_column($locations, 'country_name');
                                    $countryNames = array_unique($countryNames);
                                    $stateNames = array_column($locations, 'state_name');
                                    $stateNames = array_unique($stateNames);
                                    $typeIds = array_column($locations, 'taxruleloc_type');
                                    $typeIds = array_unique($typeIds);
                                }
                            ?>
                            <li>
                                <h5 class="title"><?php echo Labels::getLabel('LBL_Rule', $siteLangId); ?>:
                                    <?php echo $rule['taxrule_name'];?>
                                    <span class=""><?php echo Labels::getLabel('LBL_Tax_Rate(%)', $siteLangId); ?>: <?php echo $rule['taxrule_rate'];?>
                                                </span></h5>
                                <ul class="tax-rules__states">
                                    <li>
                                        <div class="stats">
                                            <p></p>
                                        </div>
                                    </li>
                                    <?php if (!empty($combinedData) && $rule['taxstr_is_combined'] > 0) { ?>
                                    <li>
                                        <div class="stats">
                                           <h6 class="title-sub"> <?php echo Labels::getLabel('LBL_Combined_Taxes(%)', $siteLangId); ?></h6>
                                        </div>
                                    </li>
                                    <?php foreach ($combinedData as $comData) { ?>
                                    <li>
                                        <div class="stats">
                                            <p><span class="lable"><?php echo $comData['taxstr_name'];?>:
                                                </span><?php echo $comData['taxruledet_rate'];?></p>
                                            <?php } ?>
                                        </div>
                                    </li>
                                    <?php } else { ?>
                                        <li>
                                            <div class="stats">
                                                <p><span class="lable"><?php echo Labels::getLabel('LBL_Tax_Name', $siteLangId); ?>:
                                                    </span><?php echo $rule['taxstr_name'];?></p>
                                            </div>
                                        </li>
                                    <?php } ?>
                                    <li>
                                        <div class="stats">
                                            <p><span class="lable"><?php echo Labels::getLabel('LBL_Country', $siteLangId); ?>:
                                                </span><?php echo (!empty($countryNames[0])) ? implode(', ', $countryNames) : Labels::getLabel('LBL_Rest_of_the_world', $siteLangId); ?>
                                            </p>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="stats">
                                            <p><span class="lable"><?php echo Labels::getLabel('LBL_States', $siteLangId); ?>:
                                                </span><?php echo TaxRule::getTypeOptions($siteLangId)[$typeIds[0]]; ?>
                                                <?php echo (!empty($stateNames[0])) ? ': '.implode(', ', $stateNames) : ''; ?>
                                            </p>
                                        </div>
                                    </li>
                                </ul>

                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php } else { 
                        $message = Labels::getLabel('LBL_No_Record_found', $siteLangId);
                        $this->includeTemplate('_partial/no-record-found.php', array('siteLangId'=>$siteLangId,'message'=>$message));
                    } ?>
                </div>
            </div>
        </div>
    </div>
</main>