<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?> <?php
$showSignUpLink = isset($showSignUpLink) ? $showSignUpLink : true;
$onSubmitFunctionName = isset($onSubmitFunctionName) ? $onSubmitFunctionName : 'defaultSetUpLogin';
//$frm->setRequiredStarPosition(Form::FORM_REQUIRED_STAR_POSITION_NONE);
$loginFrm->setFormTagAttribute('class', 'form');
$loginFrm->setValidatorJsObjectName('loginValObj');
$loginFrm->setFormTagAttribute('action', UrlHelper::generateUrl('GuestUser', 'login'));
$loginFrm->setFormTagAttribute('onsubmit', $onSubmitFunctionName . '(this, loginValObj); return(false);');
$loginFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$loginFrm->developerTags['fld_default_col'] = 12;
$fldforgot = $loginFrm->getField('forgot');
$fldforgot->value = '<a href="' . UrlHelper::generateUrl('GuestUser', 'forgotPasswordForm') . '"
    class="link">' . Labels::getLabel('LBL_Forgot_Password?', $siteLangId) . '</a>';
$fldSubmit = $loginFrm->getField('btn_submit');
$fldSubmit->addFieldTagAttribute('class', 'btn btn-brand btn-wide'); ?> 
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
        <div class="col-md-12">
            <div class="field-set">
                <div class="field-wraper">
                    <div class="field_cover"><?php echo $loginFrm->getFieldHtml('password'); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row align-items-center">
        <div class="col-md-6 col-6">
            <div class="field-set">
                <div class="field-wraper">
                    <div class="field_cover">
                        <label class="checkbox"> <?php
                                    $fld = $loginFrm->getFieldHTML('remember_me');
                                    $fld = str_replace("<label >", "", $fld);
                                    $fld = str_replace("</label>", "", $fld);
                                    echo $fld;
                                ?> <i class="input-helper"></i>
                        </label> <?php if($loginFrm->getField('remember_me')); ?></div>
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
<?php
echo $loginFrm->getExternalJS();

if (!empty($socialLoginApis) && 0 < count($socialLoginApis)) { ?>
    <div class="or-divider">
        <span class="or">
            <?php echo Labels::getLabel('LBL_Or', $siteLangId); ?>
        </span>
    </div>
    <div class="buttons-list">
        <ul> 
            <?php  foreach ($socialLoginApis as $plugin) { ?>
                <li>
                    <a href="<?php echo UrlHelper::generateUrl($plugin['plugin_code']); ?>" class="btn btn--social btn--<?php echo $plugin['plugin_code'];?>">
                        <i class="icn">
                            <img alt="" src="<?php echo CONF_WEBROOT_URL; ?>images/retina/social-icons/<?php echo $plugin['plugin_code']; ?>.svg">
                        </i>
                    </a>
                </li>
            <?php } ?> 
        </ul>
    </div> 
<?php } ?> 