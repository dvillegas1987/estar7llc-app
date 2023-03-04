<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<script>
events.viewContent();
</script>

<div id="body" class="body"   >
    <!--slider[-->
<?php if (isset($slides) && count($slides)) {
    $this->includeTemplate('_partial/homePageSlides.php', array( 'slides' =>$slides, 'siteLangId' => $siteLangId ), false);
} ?>

<?php foreach($collectionTemplates as $collection) {
    echo FatUtility::decodeHtmlEntities($collection['html']);
} ?>
</div>
