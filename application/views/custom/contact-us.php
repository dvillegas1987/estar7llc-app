<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$contactFrm->setFormTagAttribute('class', 'form form--normal');
$contactFrm->setFormTagAttribute('action', UrlHelper::generateUrl('Custom', 'contactSubmit'));
$contactFrm->developerTags['colClassPrefix'] = 'col-md-';
$contactFrm->developerTags['fld_default_col'] = 6;
$fld = $contactFrm->getField('phone');
$fld->developerTags['col'] = 12;
$fld = $contactFrm->getField('message');
$fld->developerTags['col'] = 12;

$fld = $contactFrm->getField('htmlNote');
if (null != $fld) {
    $fld->developerTags['col'] = 12;
}

$fld = $contactFrm->getField('btn_submit');
$fld->addFieldTagAttribute('class', 'btn btn-brand');
$fld->developerTags['col'] = 12;
?>
<script>
    events.contactUs();
</script>
<div id="body" class="body"   >
    <div class="bg-second pt-3 pb-3">
        <div class="container container--fixed">
            <div class="row align-items-center justify-content-center">
                <div class="col-md-8 col-sm-8">
                    <div class="section-head section--white--head justify-content-center mb-0">
                        <div class="section__heading text-center">
                            <h1><?php echo Labels::getLabel('LBL_Get_in_Touch', $siteLangId);?>
                            </h1>
                            <p><?php echo Labels::getLabel('LBL_Get_in_Touch_Txt', $siteLangId);?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-auto col-sm-auto"></div>
            </div>
        </div>
    </div> 
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-9">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="bg-gray rounded p-4">
                                <?php echo $contactFrm->getFormHtml(); ?>
                            </div>

                        </div>
                        <div class="col-md-5">
                            <div class="border rounded p-4 h-100">
                                <h6><?php echo Labels::getLabel('LBL_General_Inquiry', $siteLangId);?>
                                </h6>
                                <p class="">
                                    <?php 
                                        $dialCode = FatApp::getConfig('CONF_SITE_PHONE_DCODE', FatUtility::VAR_STRING, '');
                                        echo ValidateElement::formatDialCode($dialCode) . FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_INT, '');
                                    ?>
                                    <br><?php echo Labels::getLabel('LBL_24_a_day_7_days_week', $siteLangId);?>
                                </p>

                                <div class="divider"></div>

                                <h6><?php echo Labels::getLabel('LBL_Fax', $siteLangId);?>
                                </h6>
                                <p class="">
                                    <?php 
                                    $dialCode = FatApp::getConfig('CONF_SITE_FAX_DCODE', FatUtility::VAR_STRING, '');
                                    echo ValidateElement::formatDialCode($dialCode) . FatApp::getConfig('CONF_SITE_FAX', FatUtility::VAR_STRING, '');?>
                                    <br><?php echo Labels::getLabel('LBL_24_a_day_7_days_week', $siteLangId);?>
                                </p>

                                <div class="divider"></div>

                                <h6><?php echo Labels::getLabel('LBL_Address', $siteLangId);?>
                                </h6>
                                <p class=""><?php echo nl2br(FatApp::getConfig('CONF_ADDRESS_' . $siteLangId, FatUtility::VAR_STRING, ''));?>
                                </p>

                                <?php $this->includeTemplate('_partial/footerSocialMedia.php'); ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php  if (FatApp::getConfig('CONF_MAP_IFRAME_CODE', FatUtility::VAR_STRING, '') != '') { ?>
    <section class="g-map">        
    <?php echo FatApp::getConfig('CONF_MAP_IFRAME_CODE', FatUtility::VAR_STRING); ?>
    </section>
    <?php } ?>
</div>
<?php 
$siteKey = FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '');
$secretKey = FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '');
if (!empty($siteKey) && !empty($secretKey)) {?>
    <script src='https://www.google.com/recaptcha/api.js?onload=googleCaptcha&render=<?php echo $siteKey; ?>'></script>
<?php } ?>