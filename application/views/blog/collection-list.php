<?php
if (isset($collections) && count($collections)) {
    /* blog listing design [ */
    foreach ($collections as $collection_id => $row) {
        if (isset($row['blogs']) && count($row['blogs'])) { ?>
            <section class="blog-area">
                <div class="container">
                    <div class="row">
                        <?php foreach ($row['blogs'] as $blog) { ?>
                            <div class="col-md-4">
                                <div class="blog-item">
                                    <div class="article-img">
                                        <a href="#" class="animate-scale">
                                            <picture>
												<?php $fileRow = CommonHelper::getImageAttributes(AttachedFile::FILETYPE_BLOG_POST_IMAGE, $blog['post_id']);?>
                                                <img data-ratio="16:9"
                                                src="<?php echo UrlHelper::generateFullUrl('Image', 'blogPostFront', array($blog['post_id'], $siteLangId, '')); ?>"
                                                alt="<?php echo (!empty($fileRow['afile_attribute_alt'])) ? $fileRow['afile_attribute_alt'] : $blog['post_title'];?>" title="<?php echo (!empty($fileRow['afile_attribute_title'])) ? $fileRow['afile_attribute_title'] : $blog['post_title'];?>">
                                            </picture>
                                        </a>
                                    </div>
                                    <div class="article-inner">
                                        <div class="blog_author">
                                            <span class="article__author"><?php echo $blog['post_author_name']; ?></span>
                                            <span class="article__date"><?php echo $blog['post_updated_on']; ?></span>
                                        </div>
                                        <h3 class="article-title">
                                            <a href="<?php echo UrlHelper::generateUrl('Blog', 'postDetail', array($blog['post_id'])); ?>"><span><?php echo !empty($blog['post_title']) ? $blog['post_title'] : $blog['post_identifier']; ?></span></a>
                                        </h3>
                                        <div class="article-des">
                                            <?php echo FatUtility::decodeHtmlEntities($blog['post_description']); ?>
                                        </div>
                                        <a class="readmore-button btn btn-outline-brand btn-sm" href="<?php echo UrlHelper::generateUrl('Blog', 'postDetail', array($blog['post_id'])); ?>"><?php echo Labels::getLabel('LBL_READ_MORE', $siteLangId); ?></a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </section>
    <?php }
    }
}
