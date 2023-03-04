<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('onsubmit', 'updateSettings(this); return(false);');
$frm->setFormTagAttribute('class', 'form');

$frm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
$frm->developerTags['fld_default_col'] = 6;

$fldSubmit = $frm->getField('btn_submit');
$fldSubmit->setFieldTagAttribute('class', "btn btn-brand btn-wide");

$variables = array('siteLangId'=>$siteLangId,'action'=>$action, 'canEditImportExport'=>$canEditImportExport, 'canUploadBulkImages'=>$canUploadBulkImages);
$this->includeTemplate('import-export/_partial/top-navigation.php', $variables, false); ?>
<div class="card">
    <div class="card-body">
        <div class="tabs__content">
            <div class="row">
                <div class="col-md-12" id="settingFormBlock">
                    <?php echo $frm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
