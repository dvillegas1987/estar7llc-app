<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if( isset($collection['categories']) && count( $collection['categories'] ) ) { ?>
    <section class="section" style="background-color:#f3f4f5;">
        <div class="container">
            <div class="section-head section--head--center">
                <?php echo ($collection['collection_name'] != '') ? ' <div class="section__heading"><h2>' . $collection['collection_name'] .'</h2></div>' : ''; ?>

                <?php if( $collection['totCategories'] > Collections::LIMIT_CATEGORY_LAYOUT1 ){ ?>
                    <div class="section__action"> <a href="<?php echo UrlHelper::generateUrl('Collections','View',array($collection['collection_id']));?>" class="link"><?php echo Labels::getLabel('LBL_View_More',$siteLangId); ?></a> </div>
                <?php }  ?>
            </div>
            <?php include('category-layout-product-list.php'); ?>
        </div>
    </section>
<?php } ?>
