<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$nextPage = $page + 1;
if( $nextPage <= $pageCount ){ ?>
	<a id="loadMoreBtn" href="javascript:void(0)" onClick="goToLoadMore(<?php echo $nextPage; ?>);" class="btn btn-outline-brand loadmore"><?php echo Labels::getLabel('LBL_Load_More', $siteLangId); ?></a>
<?php
}
?>
