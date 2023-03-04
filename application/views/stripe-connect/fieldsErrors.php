<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!empty($errors)) { ?>
    <ul class="errorlist erlist_business_profile_support_phone_">
        <?php foreach ($errors as $errorData) { ?>
            <li>
                <a href="javascript:void(0);"><?php echo $errorData['reason']; ?></a>
            </li>
        <?php } ?>
    </ul>
<?php }
