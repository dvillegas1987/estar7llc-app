<?php  defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="row justify-content-between align-items-center mb-4">
    <?php $this->includeTemplate('account/wishListItemsActions.php', array('isWishList' => false, 'siteLangId' => $siteLangId, 'wishListRow' => $wishListRow)); ?>
</div>
<form method="post" name="favtlistForm" id="favtlistForm">
    <div id="favListItems"></div>
</form>

<div id="loadMoreBtnDiv"></div>

<script type="text/javascript">
    $("document").ready(function() {
        searchFavouriteListItems();
    });
</script>