<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="box" style="text-align: center;">
<img src="<?php echo UrlHelper::generateUrl('user', 'photo', array($data['user_id'], 100, 100)); ?>">
<form method="post" enctype="multipart/form-data" action="<?php echo UrlHelper::generateUrl('profile', 'updatePhoto') ?>">
	<?php echo Labels::getLabel('LBL_CHANGE_PHOTO', $siteLangId) ?> <input type="file" name="photo" onchange="this.form.submit();">
</form>
<br>
<b><?php echo $data['user_name'] ?></b><br>
<?php echo Labels::getLabel('LBL_DOB', $siteLangId) . ': ' . FatDate::format($data['user_dob']); ?><br>
<?php echo Labels::getLabel('LBL_MEMBER_SINCE', $siteLangId) . ': ' . FatDate::format($data['user_regdate']); ?><br>
<?php echo Labels::getLabel('LBL_PHONE', $siteLangId) . ': ' . ValidateElement::formatDialCode($data['user_phone_dcode']) . $data['user_phone']; ?><br>
<p><?php echo nl2br($data['user_profile_info']); ?>
</p>
<a href="<?php echo UrlHelper::generateUrl('profile','edit-form');?>"><?php echo Labels::getLabel('LBL_EDIT', $siteLangId) ?></a>
</div>
