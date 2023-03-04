<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
/* $this->includeTemplate('_partial/blogTopFeaturedCategories.php'); */
$frm->setFormTagAttribute('class', 'form');
/* $frm->setFormTagAttribute('onsubmit','setupContribution(this);return false;'); */
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('Blog', 'setupContribution'));
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
$fileFld = $frm->getField('file');
$fileFld->htmlBeforeField='<div class="filefield"><span class="filename"></span>';
$preferredDimensionsStr = '</div><span class="form-text text-muted">'.Labels::getLabel('MSG_Allowed_Extensions', $siteLangId).'</span>';
$fileFld->htmlAfterField = $preferredDimensionsStr;

$btnSubmitFld = $frm->getField('btn_submit');
$btnSubmitFld->setFieldTagAttribute('class', 'btn btn-brand btn-wide');
$isUserLogged = UserAuthentication::isUserLogged();
if ($isUserLogged) {
    $nameFld = $frm->getField(BlogContribution::DB_TBL_PREFIX.'author_first_name');
    $nameFld->setFieldTagAttribute('readonly', 'readonly');
}
?>
<div id="body" class="body">
   <div class="bg-second pt-3 pb-3">
      <div class="container">
        <div class="row align-items-center justify-content-between">
          <div class="col">
               <div class="section-head section--white--head mb-0">
						<div class="section__heading">
                		<h1 class="mb-0"><?php echo Labels::getLabel('Lbl_Blog_Contribution', $siteLangId); ?></h1>
                        </div>
                     </div>
          </div>
          <div class="col-auto"><a href="<?php echo UrlHelper::generateUrl('Blog'); ?>" class="btn btn-brand btn-sm"><?php echo Labels::getLabel('Lbl_Back_to_home', $siteLangId); ?></a></div>
        </div>
      </div>
    </div>
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="bg-gray rounded p-5">
                    <?php echo $frm->getFormHtml(); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php 
$siteKey = FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '');
$secretKey = FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '');
if (!empty($siteKey) && !empty($secretKey)) {?>
    <script src='https://www.google.com/recaptcha/api.js?onload=googleCaptcha&render=<?php echo $siteKey; ?>'></script>
<?php } ?>
