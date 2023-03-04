<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/dashboardNavigation.php');
$col = (true === $canSendSms) ? '4' : '6'; ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title">
                <?php echo Labels::getLabel('LBL_UPDATE_CREDENTIALS', $siteLangId);?>
                </h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row">
                <div class="col-lg-<?php echo $col; ?> col-md-<?php echo $col; ?> mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title "><?php echo Labels::getLabel('Lbl_UPDATE_EMAIL', $siteLangId);?></h5>
                        </div>
                        <div class="card-body ">
                            <div id="changeEmailFrmBlock"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-<?php echo $col; ?> col-md-<?php echo $col; ?> mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title "><?php echo Labels::getLabel('LBL_UPDATE_PASSWORD', $siteLangId);?></h5>
                        </div>
                        <div class="card-body ">
                            <div id="changePassFrmBlock"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                        </div>
                    </div>
                </div>
                <?php if (true === $canSendSms) { ?>
                    <div class="col-lg-4 col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title "><?php echo Labels::getLabel('Lbl_UPDATE_PHONE_NUMBER', $siteLangId);?></h5>
                            </div>
                            <div class="card-body">
                                <div id="changePhoneNumberFrmBlock"> <?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?> </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</main>