<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$variables = array('siteLangId'=>$siteLangId,'action'=>$action, 'canEditImportExport'=>$canEditImportExport, 'canUploadBulkImages'=>$canUploadBulkImages);
$this->includeTemplate('import-export/_partial/top-navigation.php', $variables, false); ?>
<div class="card">
    <div class="card-body">
        <div class="cms" id="exportFormBlock">
            <?php if (!empty($pageData['epage_content'])) { ?>
                <h3 class="mb-4"><?php echo $pageData['epage_label']; ?></h3>
                <?php echo FatUtility::decodeHtmlEntities($pageData['epage_content']);
            } else {
                echo Labels::getLabel('LBL_Sorry!_No_Instructions', $siteLangId);
            } ?>
        </div>
    </div>
</div>
