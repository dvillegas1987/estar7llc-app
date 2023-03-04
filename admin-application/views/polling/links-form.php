<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$linksFrm->setFormTagAttribute('class', 'web_form form_horizontal');
if(!empty($polling_type)){
	if($polling_type == Polling::POLLING_TYPE_PRODUCTS){
		$polling_type_text = 'Products';
	} else if($polling_type == Polling::POLLING_TYPE_CATEGORY){
		$polling_type_text = 'Categories';
	}
}
else{
	die(Labels::getLabel('LBL_Required_variables_not_passed.',$adminLangId));
}
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Link',$adminLangId); ?> <?php echo $polling_type_text; ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0)" onclick="pollingForm(<?php echo $polling_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
            <li class="<?php echo ($polling_id == 0) ? 'fat-inactive' : ''; ?>">
                <a href="javascript:void(0);" <?php echo ($polling_id) ? "onclick='pollingLangForm(" . $polling_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
			<li><a class="active" href="javascript:void(0)" onclick="linksForm(<?php echo $polling_id ?>);"><?php echo Labels::getLabel('LBL_Link',$adminLangId); ?> <?php echo $polling_type_text; ?></a></li>
		</ul>
		<div class="tabs_panel_wrap" style="min-height:300px">
			<div class="tabs_panel">
				<?php echo $linksFrm->getFormHtml(); ?>
				<div id="linked_entities_list" class="col-xs-10" ></div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$("document").ready(function(){
	<?php if( $polling_type == Polling::POLLING_TYPE_PRODUCTS ){?>
	reloadLinkedProducts(<?php echo $polling_id; ?>);
	$('input[name=\'product\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: '<?php echo UrlHelper::generateUrl('Products','autoComplete'); ?>',
				data: {keyword: request['term'],fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'], value: item['name'], id: item['id'] };
					}));
				},
			});
		},
		'select': function(event, ui) {
			updateLinkedProducts(<?php echo $polling_id; ?>, ui.item.id );
		}
	});
	<?php } elseif( $polling_type == Polling::POLLING_TYPE_CATEGORY ){ ?>
	reloadLinkedCategories(<?php echo $polling_id; ?>);
	$('input[name=\'category\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('ProductCategories', 'autoComplete'),
				data: {keyword: request['term'],fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'], value: item['name'], id: item['id'] };
					}));
				},
			});
		},
		select: function(event, ui) {
			updateLinkedCategories(<?php echo $polling_id; ?>, ui.item.id );
		}
	});
	<?php } ?>
});
</script>