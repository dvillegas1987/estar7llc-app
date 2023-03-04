<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($messagesList)) { ?>
    <?php foreach ($messagesList as $message) {
        $shop_name = '';
        if ($message['shop_id'] > 0) {
            $shop_name = ' - ' . $message['shop_name'];
            $userImgUpdatedOn = Shop::getAttributesById($message['shop_id'], 'shop_updated_on');
            $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
            $toImage = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'shopLogo', array($message['shop_id'], $siteLangId, 'thumb')) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
        } else {
            if ($message['orrmsg_from_admin_id']) {
                $toImage = UrlHelper::generateUrl('Image', 'siteLogo', array($siteLangId, 'THUMB'));
            } else {

                $userImgUpdatedOn = User::getAttributesById($message['orrmsg_from_user_id'], 'user_updated_on');
                $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
                $toImage = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'user', array($message['orrmsg_from_user_id'], 'thumb', true)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
            }
        }

    ?>
        <li class="is-read">
            <div class="msg_db">
                <div class="avtar">
                    <?php if ($message['orrmsg_from_admin_id']) { ?>
                        <img src="<?php echo $toImage; ?>" title="<?php echo $message['admin_name']; ?>" alt="<?php echo $message['admin_name']; ?>">
                    <?php } else { ?>
                        <img src="<?php echo $toImage; ?>" title="<?php echo $message['msg_user_name']; ?>" alt="<?php echo $message['msg_user_name']; ?>">
                    <?php } ?>
                </div>
            </div>
            <div class="msg__desc">
                <span class="msg__title"><?php echo ($message['orrmsg_from_admin_id']) ? $message['admin_name'] : $message['msg_user_name'] . $shop_name; ?></span>
                <span class="msg__date"><?php echo FatDate::format($message['orrmsg_date'], true); ?></span>
                <p class="msg__detail"><?php echo nl2br($message['orrmsg_msg']); ?></p>

            </div>
        </li>
    <?php } ?>
<?php
    $postedData['page'] = $page;
    echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmOrderReturnRequestMsgsSrchPaging'));
} else {
    //echo Labels::getLabel('MSG_No_Record_Found', $siteLangId);
} ?>