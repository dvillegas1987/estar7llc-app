<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
if( !empty($pageData['epage_content']) ){
    ?>
    <h3 class="mb-4"><?php echo $pageData['epage_label'];?></h3>
    <?php
    echo FatUtility::decodeHtmlEntities( $pageData['epage_content'] );
}else{
    echo Labels::getLabel('LBL_Sorry!_No_Instructions', $adminLangId);
}
