<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'frmImportExportSettings');
$frm->setFormTagAttribute('class', 'form form--horizontal');
$frm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
$frm->developerTags['fld_default_col'] = 6;
$fld = $frm->getField('csvfile');
$fld->htmlBeforeField = '<div class="btn-group">';
$fld->htmlAfterField = '</div>';
$fld->developerTags['noCaptionTag'] = true;
$fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm h-auto');
$fld->htmlAfterField = ' <a class = "btn btn-outline-brand btn-sm" href="'.UrlHelper::generateUrl('ImportExport', 'exportInventory').'">'.Labels::getLabel('LBL_Export_CSV_File', $siteLangId).'</a>';
$variables = array('siteLangId'=>$siteLangId,'action'=>$action, 'canEditImportExport'=>$canEditImportExport, 'canUploadBulkImages'=>$canUploadBulkImages);
$this->includeTemplate('import-export/_partial/top-navigation.php', $variables, false); ?>
<div class="card">
    <div class="card-body">
        <div class="cms">
            <div id="productInventory"><?php echo $frm->getFormHtml(); ?></div>
            <div class="mt-4">
            <?php if (!empty($pageData['epage_content'])) { ?>
                <h3 class="mb-4"><?php echo $pageData['epage_label']; ?></h3>
                <?php echo FatUtility::decodeHtmlEntities($pageData['epage_content']);
            } ?>
            </div>
        </div>
    </div>
</div>
