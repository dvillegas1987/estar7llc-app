<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/dashboardNavigation.php');
$merchantId = isset($userData[$keyName . '_merchantId']) ? $userData[$keyName . '_merchantId'] : '';
$serviceAccInfo = isset($userData['service_account']) ? $userData['service_account'] : '';
?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col">
                <h2 class="content-header-title"><?php echo $pluginName;?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <h6 class="m-0">
                                <?php echo Labels::getLabel('Lbl_MERCHANT_ID', $siteLangId);?> : 
                                <?php echo $merchantId;
                                if (empty($merchantId) && $userPrivilege->canEditAdvertisementFeed(UserAuthentication::getLoggedUserId(), true)) { ?>
                                    <a class="btn btn-outline-brand btn-sm" href="<?php echo UrlHelper::generateUrl($keyName, 'getAccessToken')?>" title="<?php echo Labels::getLabel('Lbl_SETUP_MERCHANT_ACCOUNT', $siteLangId); ?>"><?php echo Labels::getLabel('Lbl_SETUP_MERCHANT_ACCOUNT', $siteLangId); ?></a>
                                <?php }
                                
                                if (empty($merchantId) && !$userPrivilege->canEditAdvertisementFeed(UserAuthentication::getLoggedUserId(), true)) {
                                    echo Labels::getLabel('LBL_YOU_ARE_NOT_ALLOWED_TO_SETUP_ACCOUNT', $siteLangId);
                                }
                                ?>
                            </h6>
                            <?php if (!empty($merchantId) && $userPrivilege->canEditAdvertisementFeed(UserAuthentication::getLoggedUserId(), true)) { ?>
                                <a class="btn btn-brand btn-sm" href="javascript:void(0)" onClick="serviceAccountForm();" id="userAccInfoBtn"><?php echo Labels::getLabel('Lbl_SERVICE_ACCOUNT_INFO', $siteLangId); ?></a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!empty($merchantId) && !empty($serviceAccInfo)) { ?>
                <div class="row mb-4">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <?php echo Labels::getLabel('LBL_BATCH_SETUP', $siteLangId); ?>
                                </h5>
                                <div id="batchSetup"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="listing"></div>
                            </div>
                        </div>
                    </div>    
                </div>
            <?php } ?>
        </div>
    </div>
</main>

<?php if (!empty($merchantId) && empty($serviceAccInfo) && $userPrivilege->canEditAdvertisementFeed(UserAuthentication::getLoggedUserId(), true)) { ?>
    <script>
        serviceAccountForm();
    </script>
<?php }
