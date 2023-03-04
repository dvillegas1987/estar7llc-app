<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if ($page == 'faq') {
    $faqMainCat = FatApp::getConfig("CONF_FAQ_PAGE_MAIN_CATEGORY", null, '');
} else {
    $faqMainCat = FatApp::getConfig("CONF_SELLER_PAGE_MAIN_CATEGORY", null, '');
}

if (count($listCategories)) {
    $faqMainCat = empty($faqMainCat) ? current($listCategories)['faqcat_id'] : $faqMainCat;
}
if (isset($listCategories) && is_array($listCategories)) {
    foreach ($listCategories as $faqCat) { ?>
        <a href="javascript:void(0);" onClick="searchFaqs('<?php echo $page; ?>', <?php echo $faqCat['faqcat_id']; ?>);" id="<?php echo $faqCat['faqcat_id']; ?>" class="<?php echo ($faqCat['faqcat_id'] == $faqMainCat ? 'is--active' : '') ?>"><?php echo $faqCat['faqcat_name']; ?></a>
<?php
    }
}
