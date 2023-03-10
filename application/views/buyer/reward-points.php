<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmSrch->setFormTagAttribute('onSubmit', 'searchRewardPoints(this); return false;');
$frmSrch->setFormTagAttribute('class', 'form');
$frmSrch->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frmSrch->developerTags['fld_default_col'] = 12;
?> <?php $this->includeTemplate('_partial/dashboardNavigation.php'); ?> <main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Reward_Points', $siteLangId);?></h2> <?php echo $frmSrch->getFormHtml();?> </div>
        </div>
        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <?php echo Labels::getLabel('LBL_Current_Reward_Points', $siteLangId);?> (<?php echo $totalRewardPoints;?>) -
                        <?php echo CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($totalRewardPoints));?>
                    </h5>
                </div>
                <div class="card-body ">
                    <!-- <h2><?php echo Labels::getLabel("LBL_Reward_Point_History", $siteLangId); ?></h2> -->
                    <div id="rewardPointsListing"><?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?></div>
                </div>
            </div>
        </div>
    </div>
</main>
