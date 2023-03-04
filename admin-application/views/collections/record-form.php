<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 12;
$fld = $frm->getField('collection_records');
$fld->setWrapperAttribute('class', 'ui-front');

$actionName = 'autocomplete';
$hideSelectField = 'hide-addrecord-field--js';
switch ($collection_type) {
    case Collections::COLLECTION_TYPE_PRODUCT:
        $controllerName = 'Collections';
        $actionName = 'autoCompleteSelprods';
        $hideSelectField = '';
        break;
    case Collections::COLLECTION_TYPE_CATEGORY:
        $controllerName = 'ProductCategories';
        $hideSelectField = '';
        break;
    case Collections::COLLECTION_TYPE_SHOP:
        $controllerName = 'Shops';
        $hideSelectField = '';
        break;
    case Collections::COLLECTION_TYPE_BRAND:
        $controllerName = 'Brands';
        $hideSelectField = '';
        break;
    case Collections::COLLECTION_TYPE_BLOG:
        $controllerName = 'BlogPosts';
        $hideSelectField = '';
        break;
    case Collections::COLLECTION_TYPE_FAQ:
        $controllerName = 'Faq';
        $hideSelectField = '';
        break;
    case Collections::COLLECTION_TYPE_TESTIMONIAL:
        $controllerName = 'Testimonials';
        $hideSelectField = '';
        break;
    default:
        $controllerName = '';
        $actionName = '';
        break;
}
?>
<section class="section">
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a class="" href="javascript:void(0)" onclick="collectionForm(<?php echo $collection_type ?>, <?php echo $collection_layout_type ?>, <?php echo $collection_id ?>, 0);">
                                <?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                        </li>
                        <?php if (!in_array($collection_type, Collections::COLLECTION_WITHOUT_RECORDS)) { ?>
                            <li><a class="active" href="javascript:void(0)" onclick="recordForm(<?php echo $collection_id ?>, <?php echo $collection_type ?>);">
                                    <?php echo Labels::getLabel('LBL_Link_Records', $adminLangId); ?></a>
                            </li>
                        <?php } ?>
                        <?php if (!in_array($collection_type, Collections::COLLECTION_WITHOUT_MEDIA)) { ?>
                            <li>
                                <a class="" href="javascript:void(0)" <?php if ($collection_id > 0) { ?> onclick="collectionMediaForm(<?php echo $collection_id ?>);" <?php } ?>>
                                    <?php echo Labels::getLabel('LBL_Media', $adminLangId); ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <?php echo $frm->getFormHtml(); ?>
                            <div id="records_list" class="col-xs-10"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    $("document").ready(function() {
        var controllerName = '<?php echo $controllerName; ?>';
        var actionName = '<?php echo $actionName; ?>';        
        var collectionId = $("input[name='collection_id']").val();
        
        <?php if($collection_type == Collections::COLLECTION_TYPE_PRODUCT){ ?>
            $("select[name='collection_records']").select2({
                closeOnSelect: true,
                dir: layoutDirection,
                allowClear: true,
                placeholder: $("select[name='collection_records']").attr('placeholder'),
                ajax: {
                    url: fcom.makeUrl(controllerName, actionName),
                    dataType: 'json',
                    delay: 250,
                    method: 'post',
                    data: function (params) {
                        return {
                            keyword: params.term, // search term
                            page: params.page,
                            collection_id: collectionId
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.products,
                            pagination: {
                                more: params.page < data.pageCount
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                templateResult: function (result)
                {
                    return result.name;
                },
                templateSelection: function (result)
                {
                    return result.name || result.text;
                }
            }).on('select2:selecting', function (e)
            {   
                var item = e.params.args.data; 
                updateRecord(<?php echo $collection_id; ?>, item.id);                
                setTimeout(function () {
                    $("select[name='collection_records']").val('').trigger('change.select2');
                }, 200);

            });
            
        <?php }else{ ?>    
        
        $('input[name=\'collection_records\']').autocomplete({
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function(request, response) {
                $.ajax({
                    url: fcom.makeUrl(controllerName, actionName),
                    data: {
                        keyword: request['term'],
                        fIsAjax: 1,
                        collection_id: collectionId,
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return {
                                label: item['name'],
                                value: item['name'],
                                id: item['id']
                            };
                        }));
                    },
                });
            },
            select: function(event, ul) {
                updateRecord(<?php echo $collection_id; ?>, ul.item.id);
                $('input[name=\'collection_records\']').val('');
                return false;
            }
        });
        <?php } ?>
    });
</script>