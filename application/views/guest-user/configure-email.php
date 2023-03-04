<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php /* $this->includeTemplate('_partial/dashboardNavigation.php'); */ ?>
<div id="body" class="body"   >
    <section class="bg-second pt-3 pb-3">
        <div class="container">
            <div class="section-head section--white--head section--head--center mb-0">
                <div class="section__heading">
                    <h2 class="mb-0 pageTitle-js"><?php echo Labels::getLabel('LBL_CONFIGURE_YOUR_DETAILS', $siteLangId); ?></h2>
                </div>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9">
                    <div class="row justify-content-center">
                        <?php if (true === $verificationPending) { ?>
                            <div class="col-md-6">
                                <div class="info">
                                    <span>
                                        <svg class="svg">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#info">
                                            </use>
                                        </svg>
                                        <?php echo str_replace("{clickhere}", '<a href="javascript:void(0)" class="link" onclick="resendVerificationLink(' . "'" . $userInfo['credential_email'] . "'" . ')">' . Labels::getLabel('LBL_VERIFY?', $siteLangId) . '</a>', Labels::getLabel('MSG_YOUR_EMAIL_VERIFICATION_IS_PENDING._{clickhere}', $siteLangId)); ?>
                                    </span>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="col-md-5">
                                <div class="border rounded p-4 h-100">
                                    <h6><?php echo Labels::getLabel('LBL_UPDATE_EMAIL', $siteLangId); ?></h6>
                                    <div id="changeEmailFrmBlock"><?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?></div>
                                </div>
                            </div>
                            <?php if (true === $canSendSms) { ?>
                                <div class="col">
                                    <div class="or-wrap">
                                        <div class="or or-vertical"><?php echo Labels::getLabel('LBL__OR_', $siteLangId); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="border rounded p-4 h-100">
                                        <h6><?php echo Labels::getLabel('LBL_UPDATE_PHONE_NUMBER', $siteLangId); ?></h6>
                                        <div id="changePhoneFrmBlock"><?php echo Labels::getLabel('LBL_Loading..', $siteLangId); ?></div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>