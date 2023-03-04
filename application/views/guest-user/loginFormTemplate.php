<?php
    $showSignUpLink = isset($showSignUpLink) ? $showSignUpLink : true;
    $onSubmitFunctionName = isset($onSubmitFunctionName) ? $onSubmitFunctionName : 'defaultSetUpLogin';

    $loginFrm->setFormTagAttribute('class', 'form');
    $loginFrm->setFormTagAttribute('name', 'formLoginPage');
    $loginFrm->setFormTagAttribute('id', 'formLoginPage');
    $loginFrm->setValidatorJsObjectName('loginFormObj');

    $loginFrm->setFormTagAttribute('onsubmit', 'return ' . $onSubmitFunctionName . '(this, loginFormObj);');
    $loginFrm->developerTags['fld_default_col'] = 12;
    $loginFrm->developerTags['colClassPrefix'] = 'col-md-';
    $remembermeField = $loginFrm->getField('remember_me');
    $remembermeField->setWrapperAttribute("class", "rememberme-text");
    $remembermeField->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
    $remembermeField->developerTags['col'] = 6;
    $remembermeField->developerTags['cbHtmlAfterCheckbox'] = '<i class="input-helper"></i>';
    $fldforgot = $loginFrm->getField('forgot');
    $fldforgot->value = '<a href="' . UrlHelper::generateUrl('GuestUser', 'forgotPasswordForm') . '"
    class="link">' . Labels::getLabel('LBL_Forgot_Password?', $siteLangId) . '</a>';
    $fldforgot->developerTags['col'] = 6;
    $fldSubmit = $loginFrm->getField('btn_submit');
?>
<div id="sign-in">
    <div class="login-wrapper">
        <div class="form-side">
            <div class="section-head  section--head--center">
                <div class="section__heading otp-heading">
                    <h2>
                        <?php echo Labels::getLabel('LBL_Login', $siteLangId);?>
                    </h2>
                    <?php if (isset($smsPluginStatus) && true === $smsPluginStatus) { ?>
                        <a class="otp-link" href="javaScript:void(0)" data-form="formLoginPage" onClick="signInWithPhone(this, true)">
                            <?php echo Labels::getLabel('LBL_USE_PHONE_NUMBER_INSTEAD', $siteLangId); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
            <?php echo $loginFrm->getFormTag(); ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="field-set">
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $loginFrm->getFieldHtml('username'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="field-set">
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $loginFrm->getFieldHtml('password'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row align-items-center">
                <div class="col-md-6 col-6">
                    <div class="field-set">
                        <div class="field-wraper">
                            <div class="field_cover">
                                <?php echo $loginFrm->getFieldHtml('remember_me'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-6">
                    <div class="field-set">
                        <div class="forgot"><?php echo $loginFrm->getFieldHtml('forgot'); ?></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="field-set">
                        <div class="field-wraper">
                            <div class="field_cover"><?php echo $loginFrm->getFieldHtml('btn_submit'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            </form>
            <?php echo $loginFrm->getExternalJS();?>

            <?php if ($showSignUpLink) { ?>
                <div class="row justify-content-center">
                    <div class="col-auto text-center">
                        <a class="link" href="<?php echo UrlHelper::generateUrl('GuestUser', 'loginForm', array(applicationConstants::YES)); ?>">
                            <?php echo sprintf(Labels::getLabel('LBL_Not_Registered_Yet?', $siteLangId), FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId));?>
                        </a>
                    </div>
                    <?php if (isset($includeGuestLogin) && 'true' == $includeGuestLogin) {?>
                    <div class="col-auto text-center">
                        <a class="link" href="javascript:void(0)" onclick="guestUserFrm()"><?php echo sprintf(Labels::getLabel('LBL_Guest_Checkout?', $siteLangId), FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId));?></a>
                    </div>
                    <?php }?>
                </div>
            <?php } ?>
            <?php if (!empty($socialLoginApis) && 0 < count($socialLoginApis)) { ?>
                <div class="other-option">
                    <div class="section-head section--head--center">
                        <div class="section__heading">
                            <h6 class="mb-0"><?php echo Labels::getLabel('LBL_Or_Login_With', $siteLangId); ?></h6>
                        </div>
                    </div>
                    <div class="buttons-list">
                        <ul>
                            <?php foreach ($socialLoginApis as $plugin) { ?>
                                <li>
                                    <a href="<?php echo UrlHelper::generateUrl($plugin['plugin_code']); ?>" class="btn btn--social btn--<?php echo $plugin['plugin_code'];?>">
                                        <i class="icn">
                                            <img src="<?php echo CONF_WEBROOT_URL; ?>images/retina/social-icons/<?php echo $plugin['plugin_code']; ?>.svg">
                                        </i>
                                    </a>
                                </li>
                            <?php } ?>  
                        </ul>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>