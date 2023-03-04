<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section pt-0">
	<div class="">
		<div class="js-hero-slider hero-slider" dir="<?php echo CommonHelper::getLayoutDirection();?>">
		<?php foreach($slides as $slide){
			$desktop_url = '';
			$tablet_url = '';
			$mobile_url = '';
			$haveUrl = ( $slide['slide_url'] != '' ) ? true : false;
			$defaultUrl = '';
			$slideArr = AttachedFile::getMultipleAttachments( AttachedFile::FILETYPE_HOME_PAGE_BANNER, $slide['slide_id'], 0, $siteLangId );
			if( !$slideArr ){
				continue;
			}else{
				foreach($slideArr as $slideScreen){
					$uploadedTime = AttachedFile::setTimeParam($slideScreen['afile_updated_at']);
					switch($slideScreen['afile_screen']){
						case applicationConstants::SCREEN_MOBILE:
							$mobile_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image','slide',array($slide['slide_id'], applicationConstants::SCREEN_MOBILE, $siteLangId, 'MOBILE')).$uploadedTime,CONF_IMG_CACHE_TIME, '.jpg').",";
							break;
						case applicationConstants::SCREEN_IPAD:
							$tablet_url = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image','slide',array($slide['slide_id'], applicationConstants::SCREEN_IPAD, $siteLangId, 'TABLET')).$uploadedTime,
							CONF_IMG_CACHE_TIME, '.jpg').",";
							break;
						case applicationConstants::SCREEN_DESKTOP:
							$defaultUrl =  UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image','slide',array($slide['slide_id'], applicationConstants::SCREEN_DESKTOP, $siteLangId, 'DESKTOP')).$uploadedTime,CONF_IMG_CACHE_TIME, '.jpg');
							$desktop_url = $defaultUrl.",";
							break;
					}
				}
			}

			if($defaultUrl == ''){
				$defaultUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image','slide',array($slide['slide_id'], applicationConstants::SCREEN_DESKTOP, $siteLangId, 'DESKTOP')),CONF_IMG_CACHE_TIME, '.jpg');
			}

			$out = '<div class="hero-item">';
			if($haveUrl){
				if($slide['promotion_id']>0){
					$slideUrl =  UrlHelper::generateUrl('slides','track',array($slide['slide_id']));
				}else{
					$slideUrl = CommonHelper::processUrlString($slide['slide_url']);
				}
			}
			if( $haveUrl ){ $out .= '<a target="'.$slide['slide_target'].'" href="'.$slideUrl.'">'; }
			$out .= '<div class="hero-media">
				<picture>
					<source data-aspect-ratio="4:3" srcset="'. rtrim($mobile_url, ',') .'" media="(max-width: 767px)">
					<source data-aspect-ratio="4:3" srcset="'. rtrim($tablet_url, ',') .'" media="(max-width: 1024px)">
					<source data-aspect-ratio="10:3" srcset="'. rtrim($desktop_url, ',') .'">
					<img data-aspect-ratio="10:3" src="'. rtrim($desktop_url, ',') .'" alt="">
				</picture>
			</div>';
			if( $haveUrl ){ $out .= '</a>'; }
			$out .= '</div>';
			echo $out;
			if(isset($slide['promotion_id']) && $slide['promotion_id']>0){
				Promotion::updateImpressionData($slide['promotion_id']);
			}
		} ?>
	</div>
	</div>
</section>
