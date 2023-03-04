<?php defined('SYSTEM_INIT') or die('Invalid Usage.');?>
<div class="row justify-content-between align-items-center">
    <div class="col-auto"><?php echo Labels::getLabel('LBL_Change_Password', $siteLangId); ?></div>
    <div class="col-auto">
        <div class="btn-group">
            <a class="btn btn-outline-brand btn-sm" title="<?php echo Labels::getLabel('LBL_Back', $siteLangId); ?>" onclick="searchUsers()" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_Back', $siteLangId); ?></a>
        </div>
    </div>
</div>
<div class="card-body">
    <div class="form__subcontent">
        <?php
        $frm->setFormTagAttribute('onsubmit', 'updateUserPassword(this); return(false);');
        $frm->setFormTagAttribute('class', 'form form--horizontal');
        $frm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-4 col-sm-';
        $frm->developerTags['fld_default_col'] = 4;
        $submitFld = $frm->getField('btn_submit');
        $submitFld->setFieldTagAttribute('class', "btn btn-brand");
        $newPwd = $frm->getField('new_password');
        $newPwd->htmlAfterField = '<span class="text--small">' . sprintf(Labels::getLabel('LBL_Example_password', $siteLangId), 'User@123') . '</span>';
        echo $frm->getFormHtml();
        ?>
    </div>
</div>
