<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<div id="body" class="body"   >
    <section class="enter-page sign-in">
        <div class="container-info">
            <div class="info-item" style="background-image: url(<?php echo CONF_WEBROOT_URL; ?>images/bg-signup.png);">
                <div class="info-item__inner">
                    <div class="icon-wrapper"><i class="icn"> <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#icn-signup" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#icn-signup"></use>
                            </svg></i><?php echo Labels::getLabel('LBL_Sign_up', $siteLangId);?></div>

                    <div class="section-head  section--head--center">
                        <div class="section__heading">
                            <h2><?php echo Labels::getLabel('LBL_Dont_have_an_account_yet?', $siteLangId);?></h2>
                        </div>
                    </div>
                    <a href="javaScript:void(0)" class="btn btn-brand js--register-btn"><?php echo Labels::getLabel('LBL_Register_Now', $siteLangId);?></a>
                </div>
            </div>
            <div class="info-item" style="background-image: url(<?php echo CONF_WEBROOT_URL; ?>images/bg-signin.png);">
                <div class="info-item__inner">
                    <div class="icon-wrapper"><i class="icn"> <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#icn-signin" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#icn-signin"></use>
                            </svg></i><?php echo Labels::getLabel('LBL_Sign_up', $siteLangId);?></div>

                    <div class="section-head  section--head--center">
                        <div class="section__heading">
                            <h2><?php echo Labels::getLabel('LBL_Do_You_Have_An_Account?', $siteLangId);?></h2>
                        </div>

                    </div>

                    <a href="javaScript:void(0)" class="btn btn-brand  js--login-btn"><?php echo Labels::getLabel('LBL_Sign_In_Now', $siteLangId);?></a>
                </div>
            </div>
        </div>
        <div class="container-form <?php echo ($isRegisterForm == 1) ? 'sign-up' : '' ;?>">
            <div id="sign-in" class="form-item sign-in">
                <div class="form-side-inner">
                    <div class="section-head">
                        <div class="section__heading otp-heading">
                            <h2>
                                <?php echo Labels::getLabel('LBL_Sign_In', $siteLangId);?>                                
                            </h2>
                            <?php if (isset($smsPluginStatus) && true === $smsPluginStatus) { ?>                                     
                                        <a class="otp-link" href="javaScript:void(0)"  data-form="frmLogin" onClick="signInWithPhone(this, true)">
                                            <?php echo Labels::getLabel('LBL_USE_PHONE_NUMBER_INSTEAD', $siteLangId); ?>
                                        </a>
                             <?php } ?>
                        </div>
                    </div>
                    <?php $this->includeTemplate('guest-user/loginPageTemplate.php', $loginData, false); ?>
                </div>
            </div>
            <div id="sign-up" class="form-item sign-up <?php echo ($isRegisterForm == 1) ? 'is-opened' : '' ;?>">
                <?php $smsPluginStatus = $smsPluginStatus; ?>
                <?php require_once CONF_VIEW_DIR_PATH . 'guest-user/register-form-detail.php'; ?>
            </div>
        </div>
    </section>
</div>
<script>
    $('.info-item a.btn').click(function() {
        $('.container-form').toggleClass("sign-up");
        $('#sign-up').toggleClass("is-opened");
    });
</script>
