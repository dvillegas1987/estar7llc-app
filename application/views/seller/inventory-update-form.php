<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'frmImportExportSettings');
$frm->developerTags['colClassPrefix'] = 'col-lg-8 col-md-8 col-sm-';
$frm->developerTags['fld_default_col'] = 8;
$fld = $frm->getField('csvfile');
$fld->developerTags['noCaptionTag'] = true;
$fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');


echo $frm->getFormHtml(); ?>

<?php if (!empty($pageData['epage_content'])) { ?>
    <div class="cms mt-4">
        <h3 class="mb-4"><?php echo $pageData['epage_label']; ?></h3>
        <?php echo FatUtility::decodeHtmlEntities($pageData['epage_content']); ?>
    </div>
<?php } ?>