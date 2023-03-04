<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form ');

$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
$optionFld = $frm->getField('export_option');
$optionFld->developerTags['noCaptionTag'] = true;

$radFld = $frm->getField('export_option');
$radFld->setOptionListTagAttribute('class', 'list-vertical'); 
$radFld->developerTags['rdLabelAttributes'] = array('class'=>'radio');
$radFld->developerTags['rdHtmlAfterRadio'] = '<i class="input-helper"></i>';

$variables = array('siteLangId'=>$siteLangId,'action'=>$action, 'canEditImportExport'=>$canEditImportExport, 'canUploadBulkImages'=>$canUploadBulkImages);
$this->includeTemplate('import-export/_partial/top-navigation.php', $variables, false); ?>
<div class="card">
    <div class="card-body">
        <div class="tabs__content">
            <div class="row">
                <div class="col-md-12" id="importFormBlock">
                    <?php echo $frm->getFormHtml();  ?>
                </div>
            </div>
        </div>
    </div>
</div>
