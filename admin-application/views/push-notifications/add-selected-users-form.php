<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('id', 'setupNotificationToUserfrm');
$frm->setFormTagAttribute('onsubmit', 'bindUser(this); return(false);');

$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;

$usersFld = $frm->getField('users');
$usersFld->setWrapperAttribute('class', 'ui-front');
$usersFld->addFieldTagAttribute('data-buyers', $notifyTo['pnotification_for_buyer']);
$usersFld->addFieldTagAttribute('data-sellers', $notifyTo['pnotification_for_seller']);
?>
<section class="section">
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
                    <a href="javascript:void(0);"
                        onClick="getMediaForm(<?php echo $pNotificationId; ?>)">
                        <?php echo Labels::getLabel('LBL_MEDIA', $adminLangId); ?>
                    </a>
                </li>
                <li class="<?php echo 1 > $pNotificationId ? 'fat-inactive' : ''; ?>">
                    <a class="active" href="javascript:void(0)"><?php echo Labels::getLabel('LBL_SELECTED_USERS', $adminLangId); ?></a>
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
<?php
$htm = '';
if (isset($data) && !empty($data)) {
    foreach ($data as $val) {
        $name = $val['user_name'] . '(' . $val['credential_username'] . ')';
        $htm .= '<li id="selectedUser-js-' . $val['pntu_user_id'] . '"><i class=" icon ion-close-round"></i> ' . $name . '<input type="hidden" name="pntu_user_id[]" class="userId" value="' . $val['pntu_user_id'] . '" /></li>';
    }
}?>
<script type="text/javascript">
    $("ul#selectedUsersList-js").append('<?php echo $htm; ?>');
</script>