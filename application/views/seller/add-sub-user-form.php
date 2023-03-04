<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="row justify-content-between align-items-center">
    <div class="col-auto"><?php echo Labels::getLabel('LBL_New_Sub_User', $siteLangId); ?></div>
    <div class="col-auto">
        <div class="btn-group">
            <a class="btn btn-outline-brand btn-sm" title="<?php echo Labels::getLabel('LBL_Back', $siteLangId); ?>" onclick="searchUsers()" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
        </div>
    </div>
</div>
 
        <?php
        $frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
        $frm->setFormTagAttribute('class', 'form form--horizontal');
        $frm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-4 col-sm-';
        $frm->developerTags['fld_default_col'] = 4;
        $phoneFld = $frm->getField('user_phone');
        $phoneFld->setFieldTagAttribute('class', 'phone-js ltr-right');
        $phoneFld->setFieldTagAttribute('placeholder', ValidateElement::PHONE_NO_FORMAT);
        $phoneFld->setFieldTagAttribute('maxlength', ValidateElement::PHONE_NO_LENGTH);
        $countryFld = $frm->getField('user_country_id');
        $countryFld->setFieldTagAttribute('id', 'user_country_id');
        $countryFld->setFieldTagAttribute('onChange', 'getCountryStates(this.value,'.$stateId.',\'#user_state_id\')');
        $stateFld = $frm->getField('user_state_id');
        $stateFld->setFieldTagAttribute('id', 'user_state_id');
		if ($userId > 0) {
			$usernameFld = $frm->getField('user_username');
            $usernameFld->setFieldTagAttribute('disabled', 'disabled');
        }
        $submitFld = $frm->getField('btn_submit');
        $submitFld->setFieldTagAttribute('class', "btn btn-brand");
        echo $frm->getFormHtml();
        ?>
 
<script language="javascript">
    $(document).ready(function() {
        getCountryStates($("#user_country_id").val(), <?php echo $stateId ;?>, '#user_state_id');
    });
</script>
