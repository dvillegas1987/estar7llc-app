<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal layout--' . $formLayout);
$frm->setFormTagAttribute('onsubmit', 'setup(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
?>
<div id="cropperBox-js"></div>
<section class="section"  id="mediaForm-js">
    <div class="sectionhead">
        <h4>
            <?php if (0 == $status) {
                echo Labels::getLabel('LBL_ADD_NEW_CUSTOM_NOTIFICATION', $adminLangId);
            } else {
                echo Labels::getLabel('LBL_NOTIFICATION_DETAIL', $adminLangId);
            } ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class=" tabs_nav_container  flat">
            <ul class="tabs_nav">
                <li>
                    <a href="javascript:void(0)"
                        onClick="addNotificationForm(<?php echo $pNotificationId; ?>)">
                        <?php echo Labels::getLabel('LBL_GENERAL', $adminLangId); ?>
                    </a>
                </li>
                <li class="<?php echo 1 > $pNotificationId ? 'fat-inactive' : ''; ?>">
                    <a class="active" href="javascript:void(0);">
                        <?php echo Labels::getLabel('LBL_MEDIA', $adminLangId); ?>
                    </a>
                </li>
                <li class="<?php echo 1 > $pNotificationId || User::AUTH_TYPE_GUEST == $userAuthType ? 'fat-inactive' : ''; ?>">
                    <a href="javascript:void(0)" <?php echo 0 < $pNotificationId && User::AUTH_TYPE_GUEST != $userAuthType ? 'onclick="addSelectedUsersForm(' . $pNotificationId . ');' : ''; ?>"><?php echo Labels::getLabel('LBL_SELECTED_USERS', $adminLangId); ?></a>
                </li>
            </ul>
            <div class="tabs_panel_wrap">
                <div class="tabs_panel_wrap">
                    <?php echo $frm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
