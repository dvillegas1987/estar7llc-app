<?php if (isset($collection['epageContent']) && count($collection['epageContent']) > 0) {
echo FatUtility::decodeHtmlEntities($collection['epageContent']['epage_content']); 
} ?>
