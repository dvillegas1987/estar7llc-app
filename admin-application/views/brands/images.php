<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if (!empty($images)) { ?>
    <ul class="grids--onethird" id="<?php if ($canEdit) { ?>sortable<?php } ?>">
        <?php
        $count = 1;
        foreach ($images as $afile_id => $row) { 
            $uploadedTime = AttachedFile::setTimeParam($row['afile_updated_at']);
            ?>
            <li id="<?php echo $row['afile_id']; ?>">
                <div class="logoWrap">
                    <div class="logothumb">
                        <img src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateUrl('Image', $imageFunction, array($row['afile_record_id'], $row['afile_lang_id'], "THUMB", $row['afile_id'], $row['afile_screen']), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" title="<?php echo $row['afile_name']; ?>" alt="<?php echo $row['afile_name']; ?>">
                        <?php if ($canEdit) { ?>
                            <a class="deleteLink white" href="javascript:void(0);" title="Delete <?php echo $row['afile_name']; ?>" onclick="deleteMedia(<?php echo $row['afile_record_id']; ?>, '<?php echo $file_type; ?>', <?php echo $row['afile_id']; ?>);" class="delete">
                                <i class="ion-close-round"></i>
                            </a>
                        <?php } ?>
                    </div>
                    <?php if (isset($imgTypesArr) && !empty($imgTypesArr[$row['afile_record_subid']])) {
                        echo '<small class=""><strong>' . Labels::getLabel('LBL_Type', $adminLangId) . ': </strong> ' . $imgTypesArr[$row['afile_record_subid']] . '</small><br/>';
                    }

                    $lang_name = Labels::getLabel('LBL_All', $adminLangId);
                    if ($row['afile_lang_id'] > 0) {
                        $lang_name = $languages[$row['afile_lang_id']]; ?>
                    <?php } ?>
                    <small class="">
                        <strong><?php echo Labels::getLabel('LBL_Language', $adminLangId); ?>:</strong> <?php echo $lang_name; ?>
                    </small>
                </div>
            </li>
        <?php $count++;
        } ?>
    </ul>
<?php } ?>