<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!empty($arr_listing) && is_array($arr_listing)) { ?>
    <div class="messages-list">
        <ul>
            <?php
            foreach ($arr_listing as $sn => $row) {

                $liClass = 'is-read';

                if (!in_array($row['message_to'], $parentAndTheirChildIds)) {
                    $toUserId = $row['message_to_user_id'];
                    $toName = $row['message_to_name'];
                    if ($row['message_to_shop_name'] != '') {
                        $toName = $row['message_to_shop_name'] . ' (' . $row['message_to_name'] . ')';
                    }

                    if ($row['message_to_shop_name'] != '' && $row['message_to_shop_id'] > 0) {
                        $userImgUpdatedOn = Shop::getAttributesById($row['message_to_shop_id'], 'shop_updated_on');
                        $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
                        $toImage = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'shopLogo', array($row['message_to_shop_id'], $siteLangId, 'thumb')) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                    } else {
                        $userImgUpdatedOn = User::getAttributesById($toUserId, 'user_updated_on');
                        $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
                        $toImage = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'user', array($toUserId, 'thumb', true)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                    }
                } else {
                    if (in_array($row['message_from_user_id'], $parentAndTheirChildIds)) {
                        $toUserId = $row['thread_started_by'];
                        $toName = $row['thread_started_by_name'];
                        $userImgUpdatedOn = User::getAttributesById($toUserId, 'user_updated_on');
                        $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
                        $toImage = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'user', array($toUserId, 'thumb', true)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                    } else {
                        $toUserId = $row['message_from_user_id'];
                        $toName = $row['message_from_name'];
                        if ($row['message_from_shop_name'] != '') {
                            $toName = $row['message_from_shop_name'] . ' (' . $row['message_from_name'] . ')';
                        }
                        if ($row['message_from_shop_name'] != '' && $row['message_from_shop_id'] > 0) {
                            $userImgUpdatedOn = Shop::getAttributesById($row['message_from_shop_id'], 'shop_updated_on');
                            $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
                            $toImage = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'shopLogo', array($row['message_from_shop_id'], $siteLangId, 'thumb')) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                        } else {
                            $userImgUpdatedOn = User::getAttributesById($toUserId, 'user_updated_on');
                            $uploadedTime = AttachedFile::setTimeParam($userImgUpdatedOn);
                            $toImage = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'user', array($toUserId, 'thumb', true)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
                        }
                    }
                }

                if ($row['message_to'] == $loggedUserId) {
                    if ($row['message_is_unread'] == Thread::MESSAGE_IS_UNREAD) {
                        $liClass = '';
                    }
                }

            ?>
                <li class="<?php echo $liClass; ?>">
                    <div class="msg_db"><img src="<?php echo $toImage; ?>" alt="<?php echo $toName; ?>"></div>
                    <div class="msg__desc">
                        <span class="msg__title"><?php echo htmlentities($toName); ?></span>
                        <span class="msg__date"><?php echo FatDate::format($row['message_date'], true); ?></span>
                        <p class="msg__detail"><?php echo CommonHelper::truncateCharacters(trim(preg_replace('/\s\s+/', ' ', $row['message_text'])), 120, '', '', true); ?></p>

                    </div>
                    <ul class="actions">
                        <li><a href="<?php echo UrlHelper::generateUrl('Account', 'viewMessages', array($row['thread_id'], $row['message_id'])); ?>"><i class="fa fa-eye"></i></a></li>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } else {
    $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false);
}

$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmMessageSrchPaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToMessageSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
