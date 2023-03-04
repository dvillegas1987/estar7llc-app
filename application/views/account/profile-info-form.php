<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'profileInfoFrm');
$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 6;

$submitFld = $frm->getField('btn_submit');
$submitFld->developerTags['col'] = 12;
$submitFld->developerTags['noCaptionTag'] = true;
$submitFld->setFieldTagAttribute('class', "btn btn-brand");

$frm->setFormTagAttribute('onsubmit', 'updateProfileInfo(this); return(false);');

$usernameFld = $frm->getField('credential_username');
$usernameFld->setFieldTagAttribute('disabled', 'disabled');

if (true == SmsArchive::canSendSms()) {
    $phoneFld = $frm->getField('user_phone');
    $phoneFld->setFieldTagAttribute('disabled', 'disabled');
}

$userDobFld = $frm->getField('user_dob');
if (!empty($data['user_dob']) && $data['user_dob'] != '0000-00-00') {
    $userDobFld->setFieldTagAttribute('disabled', 'disabled');
}

$userDobFld->setFieldTagAttribute('class', 'user_dob_js');

$emailFld = $frm->getField('credential_email');
$emailFld->setFieldTagAttribute('disabled', 'disabled');

$countryFld = $frm->getField('user_country_id');
$countryFld->setFieldTagAttribute('id', 'user_country_id');
$countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,' . $stateId . ',\'#user_state_id\')');

$stateFld = $frm->getField('user_state_id');
$stateFld->setFieldTagAttribute('id', 'user_state_id');



$parent = User::getAttributesById(UserAuthentication::getLoggedUserId(true), 'user_parent');
if (User::isAdvertiser() && $parent == 0) {
    $fld = $frm->getField('user_profile_info');
    $fld->developerTags['col'] = 6;

    $fld = $frm->getField('user_products_services');
    $fld->developerTags['col'] = 6;

    $userCompFld = $frm->getField('user_company');
    $userCompFld->developerTags['col'] = 12;
}
$imgFrm->setFormTagAttribute('action', UrlHelper::generateUrl('Account', 'uploadProfileImage'));
/* $imgFrm->setFormTagAttribute('id', 'imageFrm');
$fld = $imgFrm->getField('user_profile_image');
$fld->addFieldTagAttribute('class','btn btn-brand btn-sm'); */
?>
<div class="row">
    <div class="col-xl-4">
        <div class="row">
            <div class="col-xl-12 col-lg-6 mb-4">
                <div class=" bg-gray rounded p-4 text-center profile-image" id="profileImageFrmBlock">
                    <div class="avtar avtar--large mb-4 ">
                        <?php
                        $userId = UserAuthentication::getLoggedUserId();
                        $userImgUpdatedOn = User::getAttributesById($userId, 'user_updated_on');
                        $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
                        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_USER_PROFILE_IMAGE, $userId);

                        $profileImg = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'user', array($userId, 'thumb', true)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                        ?>
                        <img src="<?php echo $profileImg; ?>" alt="<?php echo Labels::getLabel('LBL_Profile_Image', $siteLangId); ?>">
                        <!--img src="<?php /* echo UrlHelper::generateUrl('Account', 'userProfileImage', array(UserAuthentication::getLoggedUserId(), 'croped', true)).'?t='.time(); ?>"
                                alt="<?php echo Labels::getLabel('LBL_Profile_Image', $siteLangId); */ ?>"-->
                    </div>

                    <div class="btn-group">
                        <?php echo $imgFrm->getFormTag(); ?>
                        <?php if ($mode == 'Edit') { ?>
                            <a class="btn btn-brand btn-sm" href="javascript:void(0)" onClick="popupImage()"><?php echo Labels::getLabel('LBL_Change', $siteLangId); ?></a>
                        <?php } else { ?>
                            <label class="btn btn-brand btn-sm" title="Upload image file">
                                <input type="file" class="sr-only" id="profileInputImage" name="file" accept="image/*" onChange="popupImage(this)">
                                <?php echo Labels::getLabel('LBL_Upload', $siteLangId); ?>
                            </label>
                        <?php } ?>
                        <?php if ($mode == 'Edit' && 0 < $file_row['afile_id']) { ?>
                            <a class="btn btn-outline-brand btn-sm" href="javascript:void(0)" onClick="removeProfileImage()"><?php echo Labels::getLabel('LBL_Remove', $siteLangId); ?></a>
                        <?php } ?>
                        </form>
                        <?php echo $imgFrm->getExternalJS(); ?>
                        <div id="dispMessage"></div>
                    </div>



                </div>
            </div>
            <div class="col-xl-12 col-lg-6 mb-4">
                <?php if (User::isBuyer() && User::isSeller()) { ?>
                    <div class=" bg-gray rounded p-4">
                        <div class="align-items-center">
                            <h5><?php echo Labels::getLabel('LBL_Preferred_Dashboard', $siteLangId); ?> </h5>
                            <div class="switch-group">
                                <ul class="switch setactive-js">
                                    <?php if (User::canViewBuyerTab() && (User::canViewSupplierTab() || User::canViewAdvertiserTab() || User::canViewAffiliateTab())) { ?>
                                        <li <?php echo (User::USER_BUYER_DASHBOARD == $data['user_preferred_dashboard']) ? 'class="is-active"' : '' ?>><a href="javascript:void(0)" onClick="setPreferredDashboad(<?php echo User::USER_BUYER_DASHBOARD; ?>)"><?php echo Labels::getLabel('LBL_Buyer', $siteLangId); ?></a></li>
                                    <?php } ?>
                                    <?php if (User::canViewSupplierTab() && (User::canViewBuyerTab() || User::canViewAdvertiserTab() || User::canViewAffiliateTab())) { ?>
                                        <li <?php echo (User::USER_SELLER_DASHBOARD == $data['user_preferred_dashboard']) ? 'class="is-active"' : '' ?>><a href="javascript:void(0)" onClick="setPreferredDashboad(<?php echo User::USER_SELLER_DASHBOARD; ?>)"><?php echo Labels::getLabel('LBL_Seller', $siteLangId); ?></a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <?php echo $frm->getFormHtml(); ?>
    </div>
</div>
<script language="javascript">
    $(document).ready(function() {
        getCountryStates($("#user_country_id").val(), <?php echo $stateId; ?>, '#user_state_id');
        $('.user_dob_js').datepicker('option', {
            maxDate: new Date()
        });
    });
</script>
<?php
if (isset($countryIso) && !empty($countryIso)) { ?>
    <script>
        langLbl.defaultCountryCode = '<?php echo $countryIso; ?>';
    </script>
<?php } ?>