<?php 
if (isset($collection['testimonials']) && count($collection['testimonials']) > 0) { ?>
    <section class="section" style="background-color:var(--secondary-color);color:var(--secondary-color-inverse);">
        <div class="container">
            <div class="section-head section--white--head section--head--center">
                <div class="section__heading">
                    <h2>
                        <?php echo $collection['collection_name']; ?>
                    </h2>
                </div>
            </div>
            <!-- Slider -->
            <div class="<?php echo (3 < count($collection['testimonials'])) ? 'js-slider-testimonials' : '';?> slider-testimonials">
                <?php foreach ($collection['testimonials'] as $testimonial) { ?>
                    <div class="slide-item">
                        <div class="slide-item__text">
                            <p>
                                <?php echo CommonHelper::truncateCharacters($testimonial['testimonial_text'], 250, '', '', true); ?>
                                <?php if (strlen($testimonial['testimonial_text']) > 150) {
                                    echo '...';
                                } ?>
                                <?php /* <span class="lessText">
                            <?php echo CommonHelper::truncateCharacters($testimonial['testimonial_text'], 150, '', '', true); ?>
                        </span> 
                        <?php if (strlen($testimonial['testimonial_text']) > 150) { ?> 
                            <span class="moreText hidden">
                                <?php echo FatUtility::decodeHtmlEntities($testimonial['testimonial_text']); ?>
                            </span> 
                            <br>
                            <a class="readMore link--arrow btn-link" href="javascript:void(0);">
                                <?php echo Labels::getLabel('Lbl_SHOW_MORE', $siteLangId) ; ?>
                            </a>
                        <?php }  */ ?>
                            </p>
                        </div>
                        <div class="slide-item__from">
                            <img class="user-pic" alt="<?php echo $testimonial['testimonial_user_name']; ?>" src="<?php echo UrlHelper::generateUrl('Image', 'testimonial', array($testimonial['testimonial_id'], $siteLangId, 'THUMB')) . '?t=' . time(); ?>">
                            <div class="user-detail">
                                <p><span class="name"><?php echo $testimonial['testimonial_user_name']; ?></span>
                                    <!--span class="designation">Senior UI/UX Designer</span-->
                                </p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <!-- /Slider -->
            <div class="section-foot text-center">
                <a class="btn btn-outline-white btn-wide" href="<?php echo UrlHelper::generateUrl('Testimonials'); ?>"><?php echo Labels::getLabel('LBL_View_all', $siteLangId); ?></a>
            </div>
        </div>
    </section>
    <?php if (3 < count($collection['testimonials'])) { ?>
        <script>
            $(".js-slider-testimonials").slick({
                centerMode: true,
                centerPadding: '0',
                slidesToShow: 3,
                variableWidth: false,
                dots: true,
                arrows: true,
                swipe: true,
                //  infinite: true,
                swipeToSlide: true,
                //adaptiveHeight: true,

                responsive: [{
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1,
                            dots: true,
                            arrows: false,
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            dots: true,
                            arrows: false,
                        }
                    }

                ]
            });
        </script>
    <?php } ?>
<?php } ?>