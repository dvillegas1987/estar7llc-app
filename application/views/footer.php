<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if ('' !=  FatApp::getConfig("CONF_FACEBOOK_PIXEL_ID", FatUtility::VAR_STRING, '')) {  ?>
    <img alt="Facebook Pixel" height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo $pixelId; ?>&ev=PageView&noscript=1" />
<?php }
if (CommonHelper::demoUrl()) { ?>
    <div class="feedback-btn">
        <a href="https://www.yo-kart.com/yokart-marketing-website-feedback.html<?php /* echo UrlHelper::generateUrl('Custom','feedback'); */ ?>" class="crcle-btn" data-toggle="tooltip" data-placement="left" title="Give Feedback">
            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="comments-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                <path d="M416 224V64c0-35.3-28.7-64-64-64H64C28.7 0 0 28.7 0 64v160c0 35.3 28.7 64 64 64v54.2c0 8 9.1 12.6 15.5 7.8l82.8-62.1H352c35.3.1 64-28.6 64-63.9zm96-64h-64v64c0 52.9-43.1 96-96 96H192v64c0 35.3 28.7 64 64 64h125.7l82.8 62.1c6.4 4.8 15.5.2 15.5-7.8V448h32c35.3 0 64-28.7 64-64V224c0-35.3-28.7-64-64-64z" class=""></path>
            </svg>
        </a>

    </div>
<?php } ?>
<?php if (!$isUserDashboard) { ?>
    <footer class="footer section pb-0 no-print" id="footer">



        <?php if ($controllerName == 'home' && $action == 'index') {
            $this->includeTemplate('_partial/footerTrustBanners.php');
        } ?>
        <div class="container">
            <div class="back-to-top">
                <a href="#top">
                    <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#up-arrow" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#up-arrow"></use>
                    </svg>
                    <span>Top</span>
                </a>
            </div>
            <div class="up-footer ">
                <div class="row">
                    <?php $this->includeTemplate('_partial/footerNavigation.php'); ?>
                    <?php if (FatApp::getConfig("CONF_ACTIVATE_SEPARATE_SIGNUP_FORM", FatUtility::VAR_INT, 1)) { ?>
                        <div class="col-lg-2 col-md-4  mb-3 mb-md-0">
                            <div class="toggle-group">
                                <h5 class="toggle__trigger toggle__trigger-js"><?php echo Labels::getLabel('LBL_Sell_With', $siteLangId) . " " . FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, FatUtility::VAR_STRING, ''); ?></h5>
                                <div class="toggle__target toggle__target-js">
                                    <div class="store-button">
                                        <a href="<?php echo UrlHelper::generateUrl('supplier'); ?>" class="btn btn-brand storeBtn-js"><i class="icn icn-1"><img src="<?php echo CONF_WEBROOT_URL; ?>images/store-icn.png" alt=""></i>
                                            <?php echo Labels::getLabel('LBL_Open_a_store', $siteLangId); ?> </a>
                                    </div>
                                    <?php /* <div class="gap"></div>
                            <div class="f-heading"><?php echo Labels::getLabel('LBL_DOWNLOAD_THE_APP',$siteLangId); ?> [Pending]
                        </div>
                        <div class="g-play"><a href="javascript:void(0)"><img src="<?php echo CONF_WEBROOT_URL; ?>images/g-play.png" alt="<?php echo Labels::getLabel('LBL_Download_APP', $siteLangId); ?>"></a></div> */ ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="col-lg-4 col-md-8  mb-3 mb-md-0">
                        <div class="toggle-group">
                            <h5 class="toggle__trigger toggle__trigger-js"><?php echo (FatApp::getConfig('CONF_ENABLE_NEWSLETTER_SUBSCRIPTION', FatUtility::VAR_INT, 0)) ? Labels::getLabel('LBL_Sign_Up_To_Our_Newsletter', $siteLangId) : Labels::getLabel('LBL_Contact_us', $siteLangId); ?></h5>
                            <div class="toggle__target toggle__target-js">
                                <?php if (FatApp::getConfig('CONF_ENABLE_NEWSLETTER_SUBSCRIPTION', FatUtility::VAR_INT, 0)) { ?>
                                    <p><?php echo Labels::getLabel('LBL_Be_the_first_to_here_about_the_latest_trends,_new_arrivals_&_exclusive_offers', $siteLangId); ?></p>
                                <?php $this->includeTemplate('_partial/footerNewsLetterForm.php');
                                } ?>
                                <ul class="contact-info">
                                    <?php
                                    $dialCode = FatApp::getConfig('CONF_SITE_PHONE_DCODE', FatUtility::VAR_STRING, '');
                                    $site_conatct = FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_INT, '');
                                    if ($site_conatct) { ?>
                                        <li><i class="icn"><img src="<?php echo CONF_WEBROOT_URL; ?>images/icn-mobile.png" alt="<?php echo Labels::getLabel('LBL_Phone', $siteLangId); ?>"></i><?php echo ValidateElement::formatDialCode($dialCode) . $site_conatct; ?></li>
                                    <?php } ?>
                                    <?php $email_id = FatApp::getConfig('CONF_CONTACT_EMAIL', FatUtility::VAR_STRING, '');
                                    if ($email_id) { ?>
                                        <li><i class="icn"><img src="<?php echo CONF_WEBROOT_URL; ?>images/icn-email.png" alt="<?php echo Labels::getLabel('LBL_Email', $siteLangId); ?>"></i> <a href="mailto:<?php echo $email_id; ?>"><?php echo $email_id; ?></a> </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php /*<div class="middle-footer">
        <div class="accordion-footer">
            <?php $this->includeTemplate('_partial/footerTopBrands.php'); ?>
            <?php $this->includeTemplate('_partial/footerTopCategories.php'); ?>
        </div>
    </div>*/ ?>
            <div class="bottom-footer">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <?php $this->includeTemplate('_partial/footerSocialMedia.php'); ?>
                    </div>
                    <div class="col-md-4">
                        <div class="copyright">
                            <?php
                            $replacements = array(
                                '{YEAR}' => '&copy; ' . date("Y"),
                                '{PRODUCT}' => '<a target="_blank" href="https://yo-kart.com" rel="noopener">Yo!Kart</a>',
                                '{OWNER}' => '<a target="_blank" href="https://www.fatbit.com" rel="noopener">FATbit Technologies</a>',
                            );
                            echo CommonHelper::replaceStringData(Labels::getLabel('LBL_COPYRIGHT_TEXT', $siteLangId), $replacements);
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="payment">
                            <img src="<?php echo CONF_WEBROOT_URL; ?>images/payment.png" alt="<?php echo Labels::getLabel('LBL_PAYMENT_OPTIONS', $siteLangId); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
<?php } ?>
<?php if (FatApp::getConfig('CONF_ENABLE_COOKIES', FatUtility::VAR_INT, 1) && !CommonHelper::getUserCookiesEnabled()) { ?>
    <div class="cc-window cc-banner cc-type-info cc-theme-block cc-bottom cookie-alert no-print">
        <?php if (FatApp::getConfig('CONF_COOKIES_TEXT_' . $siteLangId, FatUtility::VAR_STRING, '')) { ?>
            <div class="box-cookies">
                <span id="cookieconsent:desc" class="cc-message">
                    <?php echo FatUtility::decodeHtmlEntities(mb_substr(FatApp::getConfig('CONF_COOKIES_TEXT_' . $siteLangId, FatUtility::VAR_STRING, ''), 0, 500)); ?>
                    <a href="<?php echo UrlHelper::generateUrl('cms', 'view', array(FatApp::getConfig('CONF_COOKIES_BUTTON_LINK', FatUtility::VAR_INT))); ?>"><?php echo Labels::getLabel('LBL_Read_More', $siteLangId); ?></a></span>
                <span class="cc-close cc-cookie-accept-js"><?php echo Labels::getLabel('LBL_Accept_Cookies', $siteLangId); ?></span>
            </div>
        <?php } ?>
    </div>
<?php } ?>
<?php if (!isset($_SESSION['geo_location']) && FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, '') != '') { ?>
    <script src='https://maps.google.com/maps/api/js?key=<?php echo FatApp::getConfig('CONF_GOOGLEMAP_API_KEY', FatUtility::VAR_STRING, ''); ?>&libraries=places'></script>

<?php } ?>
<?php if (FatApp::getConfig('CONF_ENABLE_LIVECHAT', FatUtility::VAR_STRING, '')) {
    echo FatApp::getConfig('CONF_LIVE_CHAT_CODE', FatUtility::VAR_STRING, '');
} ?>
<?php if (FatApp::getConfig('CONF_SITE_TRACKER_CODE', FatUtility::VAR_STRING, '')) {
    echo FatApp::getConfig('CONF_SITE_TRACKER_CODE', FatUtility::VAR_STRING, '');
} ?>

<?php /*?><script src="<?php
$fl = 'js/variables.js';
echo FatUtility::generateUrl('JsCss', 'js', array(), '', false). '&f=' . rawurlencode($fl);
?>"></script> <?php */ ?>
<div class="no-print">
    <?php if (CommonHelper::demoUrl()) { ?>
        <!--Start of Tawk.to Script-->
        <script>
            var Tawk_API = Tawk_API || {},
                Tawk_LoadStart = new Date();
            (function() {
                var s1 = document.createElement("script"),
                    s0 = document.getElementsByTagName("script")[0];
                s1.async = true;
                s1.src = 'https://embed.tawk.to/5898f87bf1b57c0a05d78696/default';
                s1.charset = 'UTF-8';
                s1.setAttribute('crossorigin', '*');
                s0.parentNode.insertBefore(s1, s0);
            })();

            window.onbeforeprint = function() {
                Tawk_API.hideWidget();
            };
            window.onafterprint = function() {
                Tawk_API.showWidget();
            };
        </script>
        <!--End of Tawk.to Script-->
    <?php
        if (FatApp::getConfig('CONF_AUTO_RESTORE_ON', FatUtility::VAR_INT, 1) && CommonHelper::demoUrl()) {
            $this->includeTemplate('restore-system/page-content.php');
        }
    } ?>
    <?php if (FatApp::getConfig('CONF_PWA_SERVICE_WORKER', FatUtility::VAR_INT, 1)) { ?>
        <script>
            $(document).ready(function() {
                if ('serviceWorker' in navigator) {
                    window.addEventListener('load', function() {
                        navigator.serviceWorker.register('<?php echo CONF_WEBROOT_URL; ?>sw.js?t=<?php echo filemtime(CONF_INSTALLATION_PATH . 'public/sw.js'); ?>&f').then(function(registration) {});
                    });
                }
            });
        </script>
    <?php } ?>
</div>
</div>
</body>

</html>
<?php
//$content  = ob_get_clean();
//echo CommonHelper::minifyHtml($content);
?>