<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$sharingFrm->addFormTagAttribute('class', 'form');
$sharingFrm->addFormTagAttribute('onsubmit', 'sendMailShareEarn(this);return false;');
$sharingFrm->developerTags['colClassPrefix'] = 'col-xs-12 col-md-';
$sharingFrm->developerTags['fld_default_col'] = 12;
$submitFld = $sharingFrm->getField('btn_submit');
$submitFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');
$submitFld->developerTags['col'] = 2;

$this->includeTemplate('_partial/dashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Share_and_Earn', $siteLangId);?></h2>
            </div>
        </div>
        <div class="content-body">
            <div class="share-earn mb-4">
                <div class="share-earn-block">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><?php echo Labels::getLabel('LBL_Share_with_your_friends_and_you_both_earn_reward_points', $siteLangId)?></h5>
                        </div>
                        <div class="card-body ">
                            <div class="stats">
                                <a href="javascript:void(0)" class="btn btn-outline-brand btn-sm" title="<?php echo $referralTrackingUrl; ?>" onclick="copy($(this))"><?php echo Labels::getLabel('LBL_Click_to_copy', $siteLangId)?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (!empty(FatApp::getConfig("CONF_FACEBOOK_APP_ID")) && !empty(FatApp::getConfig("CONF_FACEBOOK_APP_SECRET"))) { ?>
                <div class="share-earn-block">
                    <a id="facebook_btn" href="javascript:void(0);" class="box--share box--share-fb">
					<i class="icon fab fa-facebook-f"></i>
					<div class="detail">
                        <h5><?php echo Labels::getLabel('L_Share_on', $siteLangId)?></h5>
                        <h2><?php echo Labels::getLabel('L_Facebook', $siteLangId)?></h2>
                        <p> <?php echo sprintf(Labels::getLabel('L_Post_your_wall_facebook', $siteLangId), '<strong>'.Labels::getLabel('L_Facebook', $siteLangId).'</strong>')?> </p>
                        <span class="ajax_message thanks-msg" id="fb_ajax"></span>
						</div>
                    </a>
                </div>
                <?php } ?>
                <?php if (false !== $twitterUrl) { ?>
                <div class="share-earn-block">
                    <a class="box--share box--share-tw" id="twitter_btn" href="javascript:void(0);">
					<i class="icon fa fa-twitter"></i>
					<div class="detail">	
					
                        <h5><?php echo Labels::getLabel('L_Share_on', $siteLangId)?></h5>
                        <h2><?php echo Labels::getLabel('L_Twitter', $siteLangId)?></h2>
                        <p> <?php echo sprintf(Labels::getLabel('L_Send_a_tweet_followers', $siteLangId), '<strong>'.Labels::getLabel('L_Tweet', $siteLangId).'</strong>')?> </p>
                        <span class="ajax_message thanks-msg" id="twitter_ajax"></span>
						</div>
                    </a>
                </div>
                <?php } ?>
                <div class="share-earn-block">
                    <a class="showbutton box--share box--share-mail" href="javascript:void(0);"> 
					<i class="icon fa fa-envelope"></i><div class="detail">
					
                        <h5><?php echo Labels::getLabel('L_Share_on', $siteLangId)?></h5>
                        <h2><?php echo Labels::getLabel('L_Email', $siteLangId)?></h2>
                        <p> <?php echo Labels::getLabel('L_Email', $siteLangId)?> <?php echo Labels::getLabel('L_Your_friend_tell_them_about_yourself', $siteLangId)?> </p>
                        <span class="ajax_message thanks-msg"></span>
						</div>
                    </a>
                </div>
            </div>
            <div class="row mb-3 borderwrap showwrap" style="display:none;">
                <div class="col-lg-12 col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><?php echo Labels::getLabel('L_Invite_friends_through_email', $siteLangId)?></h4>
                        </div>
						<div class="card-body"> <?php echo $sharingFrm->getFormHtml(); ?> <span class="ajax_message" id="custom_ajax"></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script type="text/javascript">
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=<?php echo FatApp::getConfig("CONF_FACEBOOK_APP_ID", FatUtility::VAR_STRING, ''); ?>";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function facebook_redirect(response_token) {
        FB.ui({
            method: 'share_open_graph',
            action_type: 'og.likes',
            action_properties: JSON.stringify({
                object: {
                    'og:url': "<?php echo $referralTrackingUrl?>",
                    'og:title': "<?php echo sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_FACEBOOK_POST_TITLE_$siteLangId", FatUtility::VAR_STRING, ''), FatApp::getConfig("CONF_WEBSITE_NAME_$siteLangId"))?>",
                    'og:description': "<?php echo sprintf(FatApp::getConfig("CONF_SOCIAL_FEED_FACEBOOK_POST_CAPTION_$siteLangId", FatUtility::VAR_STRING, ''), FatApp::getConfig("CONF_WEBSITE_NAME_$siteLangId"))?>",
                    'og:image': "<?php echo UrlHelper::generateFullUrl('image', 'socialFeed', array($siteLangId ,''), "/")?>",
                }
            })
        }, function(response) {
            if (response !== null && typeof response.post_id !== 'undefined') {
                $.mbsmessage(langLbl.thanksForSharing, true, 'alert--success');
                /* $("#fb_ajax").html(langLbl.thanksForSharing); */
            }
        });
    }

    function twitter_shared(name) {
        $.mbsmessage(langLbl.thanksForSharing, true, 'alert--success');
        /* $("#twitter_ajax").html(langLbl.thanksForSharing); */
    }
</script>
<script type="text/javascript">
    var newwindow;
    var intId;

    function twitter_login() {
        var screenX = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft,
            screenY = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop,
            outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth,
            outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22),
            width = 800,
            height = 600,
            left = parseInt(screenX + ((outerWidth - width) / 2), 10),
            top = parseInt(screenY + ((outerHeight - height) / 2.5), 10),
            features = ('width=' + width + ',height=' + height + ',left=' + left + ',top=' + top);
        newwindow = window.open('<?php echo $twitterUrl; ?>', 'Login_by_twitter', features);
        if (window.focus) {
            newwindow.focus()
        }
        return false;
    }
</script>
