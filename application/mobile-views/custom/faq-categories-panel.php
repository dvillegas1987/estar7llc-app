<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$faqMainCat = FatApp::getConfig("CONF_FAQ_PAGE_MAIN_CATEGORY", null, '');
if (count($listCategories)) {
    $faqMainCat = empty($faqMainCat) ? current($listCategories)['faqcat_id'] : $faqMainCat;
}

$data = [
    'faqMainCat' => $faqMainCat,
    'listCategories' => $listCategories
];

if (empty($listCategories)) {
    $status = applicationConstants::OFF;
}
