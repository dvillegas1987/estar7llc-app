<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (isset($collection['categories']) && count($collection['categories'])) { ?>
<section class="section" style="background-color:#f3f4f5;">
    <div class="container">
        <div class="section-head">
            <?php echo ($collection['collection_name'] != '') ? ' <div class="section__heading"><h2>' . $collection['collection_name'] .'</h2></div>' : ''; ?>

            <?php if ($collection['totCategories'] > Collections::LIMIT_CATEGORY_LAYOUT2) { ?>
            <div class="section__action"> <a
                    href="<?php echo UrlHelper::generateUrl('Collections', 'View', array($collection['collection_id']));?>"
                    class="link"><?php echo Labels::getLabel('LBL_View_More', $siteLangId); ?></a>
            </div>
            <?php }  ?>
        </div>
        <div class="top-categories-wrapper">
            <?php foreach ($collection['categories'] as $category) { ?>
                 
                    <div class="top-categories">
                        <?php $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_CATEGORY_BANNER, $category['prodcat_id']);?>
                        <div class="cat-img"><img loading='lazy' data-ratio="4:1"
                                src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Category', 'banner', array($category['prodcat_id'] , $siteLangId, 'MEDIUM', applicationConstants::SCREEN_DESKTOP)), CONF_IMG_CACHE_TIME, '.jpg'); ?>"
                                alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $category['prodcat_name']; ?>"
                                title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $category['prodcat_name']; ?>">
                        </div>
                        <div class="cat-tittle"> <a
                                href="<?php echo UrlHelper::generateUrl('Category', 'View', array($category['prodcat_id'] )); ?>">
                                <?php echo $category['prodcat_name']; ?></a>
                        </div>
                        <div class="cat-list">
                            <ul>
                                <?php $i=1; 
                                foreach ($category['subCategories'] as $subCat) { ?>
                                    <li> <a
                                            href="<?php echo UrlHelper::generateUrl('Category', 'View', array($subCat['prodcat_id'] )); ?>"><?php echo $subCat['prodcat_name']; ?></a>
                                    </li>
                                    <?php $i++; if ($i > 5) {
                                        break;
                                    } 
                                } ?>
                            <?php /* if($i > 5) { ?>
                                                <li class="last-link"> <a href="<?php echo UrlHelper::generateUrl('Category'); ?>" class="link"><?php echo Labels::getLabel('LBL_View_More',$siteLangId); ?></a> </li>
                                                <?php } */ ?>
                        </ul>
                    </div>                
            </div>
            <?php }?>
        </div>
    </div>
</section>
<?php } ?>
