<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupCommission(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Commission_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="border-box border-box--space">
            <?php echo $frm->getFormHtml(); ?>
        </div>
    </div>
</section>
<script type="text/javascript">
$("document").ready(function(){
    $('input[name=\'user_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('Commission', 'userAutoComplete'),
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
            $("input[name='commsetting_user_id']").val( ui.item.id );
        }
    });

    $('input[name=\'user_name\']').keyup(function(){
        $('input[name=\'commsetting_user_id\']').val('');
    });

    $('input[name=\'product\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('Commission', 'productAutoComplete'),
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
			$('input[name=\'commsetting_product_id\']').val(ui.item.id);
		}
    });

    $('input[name=\'product\']').keyup(function(){
        $('input[name=\'commsetting_product_id\']').val('');
    });

    $('input[name=\'category_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('productCategories', 'links_autocomplete'),
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
			$('input[name=\'commsetting_prodcat_id\']').val(ui.item.id);
		}
	});

    $('input[name=\'category_name\']').change(function() {
        if ($(this).val() == '') {
            $("input[name='commsetting_prodcat_id']").val(0);
        }
    });
});
</script>
