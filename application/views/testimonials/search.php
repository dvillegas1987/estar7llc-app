<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if( !empty($list) ){  ?>
<div class="row">
	<?php foreach( $list as $listItem ){ 
	//CommonHelper::printArray($listItem);
	?>
	 <!-- ***** Testimonials Item Start ***** -->
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="testimonials-item">
            <div class="user">
            <?php $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_TESTIMONIAL_IMAGE, $listItem['testimonial_id']);
                $uploadedTime = AttachedFile::setTimeParam($fileData['afile_updated_at']); ?>
              <img alt="<?php echo $listItem['testimonial_user_name'];?>" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'testimonial', array($listItem['testimonial_id'],0,'THUMB'), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg'); ?>" >
            </div>
            <div class="testimonials-content">
              <h3 class="user-name"><?php echo $listItem['testimonial_user_name']; ?></h3>
              
              <div class="txt">
                <p class=""  data-simplebar> <?php echo $listItem['testimonial_text']; ?></p>
              </div>
            </div>
          </div>
        </div>
<?php } ?>
</div>
<?php } else {
	$this->includeTemplate('_partial/no-record-found.php' , array('siteLangId'=>$siteLangId),false);
}