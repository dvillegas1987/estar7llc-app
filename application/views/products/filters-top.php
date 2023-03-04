<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$currencySymbolLeft = isset($currencySymbolLeft) ? $currencySymbolLeft : CommonHelper::getCurrencySymbolLeft();
$currencySymbolRight = isset($currencySymbolRight) ? $currencySymbolRight : CommonHelper::getCurrencySymbolRight();

$catCodeArr = array();

if (isset($prodcat_code)) {
    $currentCategoryCode = substr($prodcat_code, 0, -1);
    $catCodeArr = explode("_", $currentCategoryCode);
    array_walk($catCodeArr, function (&$n) {
        $n = FatUtility::int($n);
    });
}
?>
<?php if ($shopCatFilters) {
    $searchFrm->setFormTagAttribute('onSubmit', 'searchProducts(this); return(false);');
    $keywordFld = $searchFrm->getField('keyword');
    $keywordFld->addFieldTagAttribute('placeholder', Labels::getLabel('LBL_Shop_Search', $siteLangId));
    $keywordFld->htmlAfterField = '<input name="btnSrchSubmit" value="" type="submit" class="input-submit">';
    /*$keywordFld = $frmProductSearch->getField('keyword');
    $keywordFld->overrideFldType("hidden");*/ ?>
<div class="product-search">
    <!--<form>
        <input placeholder="Search" class="input-field nofocus" value="" type="text">
        <input name="btnSrchSubmit" value="" class="input-submit" type="submit">
    </form>-->
    <?php
    $searchFrm->addFormTagAttribute('class', 'form');
    echo $searchFrm->getFormTag();
    $fld = $searchFrm->getField('keyword');
    $fld->addFieldTagAttribute("class", "input-field nofocus");
    echo $searchFrm->getFieldHTML('keyword');
    echo $searchFrm->getFieldHTML('shop_id');
    echo $searchFrm->getFieldHTML('join_price');
    echo '</form>';
    echo $searchFrm->getExternalJS(); ?>
</div>
<?php
} ?>
<!--Filters[ -->
 
<!-- ] -->

<!--Categories Filters[ resetAll-->
<div class="filters_body"> 
<?php if (isset($categoriesArr) && $categoriesArr) { ?>
    <div class="sidebar-widget dropdown">
        <div class="sidebar-widget__head " data-toggle="dropdown">
            <?php echo Labels::getLabel('LBL_Categories', $siteLangId); ?> 
        </div>
        <?php if (!$shopCatFilters) { ?>
            <div class="sidebar-widget__body dropdown-menu dropdown-menu-anim" >
                <div id="accordian" class="cat-accordion toggle-target scrollbar-filters scroll" data-simplebar="init" data-simplebar-auto-hide="false" >
                <ul >
                    <?php foreach ($categoriesArr as $cat) {
                    $catUrl = UrlHelper::generateUrl('category', 'view', array($cat['prodcat_id'])); ?>
                    <li>
                        <?php if (count($cat['children']) > 0) {
                        echo '<span class="acc-trigger" ripple="ripple" ripple-color="#000"></span>';
                    } ?>
                        <a class="filter_categories" data-id="<?php echo $cat['prodcat_id']; ?>"
                            href="<?php echo $catUrl; ?>"><?php echo $cat['prodcat_name']; ?></a>
                        <?php if (count($cat['children']) > 0) {
                        echo '<ul>';
                        foreach ($cat['children'] as $children) {
                            ?>
                    <li>
                        <?php if (isset($children['children']) && count($children['children']) > 0) {
                                echo '<span class="acc-trigger" ripple="ripple" ripple-color="#000"></span>';
                            } ?>
                        <a class="filter_categories" data-id="<?php echo $children['prodcat_id']; ?>"
                            href="<?php echo UrlHelper::generateUrl('category', 'view', array($children['prodcat_id'])); ?>"><?php echo $children['prodcat_name']; ?></a>
                        <?php if (isset($children['children']) && count($children['children']) > 0) {
                                echo '<ul>';
                                foreach ($children['children'] as $subChildren) {
                                    ?>
                    <li>
                        <?php if (isset($subChildren['children']) && count($subChildren['children']) > 0) {
                                        echo '<span class="acc-trigger" ripple="ripple" ripple-color="#000"></span>';
                                    } ?>
                        <a class="filter_categories" data-id="<?php echo $subChildren['prodcat_id']; ?>"
                            href="<?php echo UrlHelper::generateUrl('category', 'view', array($subChildren['prodcat_id'])); ?>"><?php echo $subChildren['prodcat_name']; ?></a>

                        <?php if (isset($subChildren['children']) && count($subChildren['children']) > 0) {
                                        echo '<ul>';
                                        foreach ($subChildren['children'] as $subSubChildren) {
                                            ?>

                    <li>
                        <?php if (isset($subSubChildren['children']) && count($subSubChildren['children']) > 0) {
                                                echo '<span class="acc-trigger" ripple="ripple" ripple-color="#000"></span>';
                                            } ?>
                        <a class="filter_categories" data-id="<?php echo $subSubChildren['prodcat_id']; ?>"
                            href="<?php echo UrlHelper::generateUrl('category', 'view', array($subSubChildren['prodcat_id'])); ?>"><?php echo $subSubChildren['prodcat_name']; ?></a>
                    </li>
                    <?php
                                        }
                                        echo '</ul>';
                                    } ?>
                    </li>
                    <?php
                                }
                                echo '</ul>';
                            } ?>
                    </li>
                    <?php
                        }
                        echo '</ul>';
                    } ?>

                    </li>
                    <?php
                } ?>
                </ul>
                </div>
            </div>
        <?php } else {?>  
            <div class="sidebar-widget__body dropdown-menu dropdown-menu-anim" >  
                <div class="scrollbar-filters" id="scrollbar-filters">
                    <ul class="list-vertical">
                        <?php
                        $seprator = '&raquo;&raquo;&nbsp;&nbsp;';
                            foreach ($categoriesArr as $cat) {
                                $catName = $cat['prodcat_name'];
                                $productCatCode = explode("_", $cat['prodcat_code']);
                                $productCatName = '';
                                $seprator = '';
                                foreach ($productCatCode as $code) {
                                    $code = FatUtility::int($code);
                                    if ($code) {
                                        if (isset($categoriesArr[$code]['prodcat_name'])) {
                                            $productCatName .= $seprator . $categoriesArr[$code]['prodcat_name'];
                                            $seprator = '&raquo;&raquo;&nbsp;&nbsp;';
                                        }
                                    }
                                } ?>
                        <li>
                            <label class="checkbox brand" id="prodcat_<?php echo $cat['prodcat_id']; ?>"><input name="category"
                                    value="<?php echo $cat['prodcat_id']; ?>" type="checkbox" data-title="<?php echo $catName; ?>" <?php if (in_array($cat['prodcat_id'], $prodcatArr)) {
                                    echo "checked";
                                } ?>><i class="input-helper"></i><?php echo $productCatName; ?></label></a>
                        </li>

                        <?php
                            } ?>
                    </ul>
                    <!--<a onClick="alert('Pending')" class="btn btn--link ripplelink"><?php echo Labels::getLabel('LBL_View_More', $siteLangId); ?> </a> -->
                </div>
            </div>
        <?php }?>   
    </div>    
<?php }?>    
<!-- ] -->

<!--Price Filters[ -->
<?php if (isset($priceArr) && $priceArr) { ?>

    <div class="sidebar-widget dropdown">
            <div class="sidebar-widget__head filter-head-js" data-toggle="dropdown">
                <?php echo Labels::getLabel('LBL_Price', $siteLangId) . ' (' . (CommonHelper::getCurrencySymbolRight() ? CommonHelper::getCurrencySymbolRight() : CommonHelper::getCurrencySymbolLeft()) . ')'; ?>
            </div>
            <div class="sidebar-widget__body dropdown-menu dropdown-menu-anim">
                <div class="filter-content toggle-target">
                    <div class="prices" id="perform_price">
                        <div id="rangeSlider"></div>
                    </div>
                    <div class="clear"></div>
                    <div class="slide__fields form">
                        <?php $symbol = CommonHelper::getCurrencySymbolRight() ? CommonHelper::getCurrencySymbolRight() : CommonHelper::getCurrencySymbolLeft(); ?>
                        <div class="price-input">
                            <div class="price-text-box input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><?php echo $symbol; ?></span></div>                                
                                <input class="input-filter form-control" value="<?php echo floor($priceArr['minPrice']); ?>"
                                    data-defaultvalue="<?php echo $filterDefaultMinValue; ?>" name="priceFilterMinValue"
                                    type="text" id="priceFilterMinValue">
                                
                            </div>
                        </div>
                        <span class="dash"> - </span>
                        <div class="price-input">
                            <div class="price-text-box input-group">
                            <div class="input-group-prepend"><span class="input-group-text"><?php echo $symbol; ?></span></div>
                                <input class="input-filter form-control" value="<?php echo ceil($priceArr['maxPrice']); ?>"
                                    data-defaultvalue="<?php echo $filterDefaultMaxValue; ?>" name="priceFilterMaxValue"
                                    type="text" id="priceFilterMaxValue">
                                 
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
    </div>
<?php
  } ?>
<!-- ] -->

<!--Brand Filters[ -->
<?php if (isset($brandsArr) && count($brandsArr) > 1) {
      $brandsCheckedArr = (isset($brandsCheckedArr) && !empty($brandsCheckedArr)) ? $brandsCheckedArr : array(); ?>

    <div class="sidebar-widget dropdown">
        <div class="sidebar-widget__head" data-toggle="dropdown">
            <?php echo Labels::getLabel('LBL_Brand', $siteLangId); ?></div>
        <div class="sidebar-widget__body dropdown-menu dropdown-menu-anim">
            <div class="scrollbar-filters" id="scrollbar-filters">
                <ul class="list-vertical brandFilter-js">
                    <?php foreach ($brandsArr as $brand) {
            if ($brand['brand_id'] == null) {
                continue;
            } ?>
                    <li><label class="checkbox brand" id="brand_<?php echo $brand['brand_id']; ?>"><input name="brands"
                                data-id="brand_<?php echo $brand['brand_id']; ?>" value="<?php echo $brand['brand_id']; ?>"
                                data-title="<?php echo $brand['brand_name']; ?>" type="checkbox" <?php if (in_array($brand['brand_id'], $brandsCheckedArr)) {
                echo "checked='true'";
            } ?>><i class="input-helper"></i><span class="lb-txt"><?php echo $brand['brand_name']; ?></span> </label>
                    </li>
                    <?php
        } ?>
                </ul>
        </div>
        

        <?php if (count($brandsArr) >= 10) {?>
        <div class="pt-3">
            <a href="javascript:void(0)" onClick="brandFilters()"
                class="link"><?php echo Labels::getLabel('LBL_View_More', $siteLangId); ?> </a>
        </div>
    <?php } ?>
    </div>
</div>
<?php
  }?>
<!-- ] -->

<!-- Option Filters[ -->
<?php
        $optionIds = array();
        $optionValueCheckedArr = (isset($optionValueCheckedArr) && !empty($optionValueCheckedArr)) ? $optionValueCheckedArr : array();

        if (isset($options) && $options) { ?>
<?php function sortByOrder($a, $b)
        {
            return $a['option_id'] - $b['option_id'];
        }

            usort($options, 'sortByOrder');
            $optionName = '';
            $liData = '';

            foreach ($options as $optionRow) {
                if ($optionName != $optionRow['option_name']) {
                    if ($optionName != '') {
                        echo "</ul></div> </div>";
                    }
                    $optionName = ($optionRow['option_name']) ? $optionRow['option_name'] : $optionRow['option_identifier']; ?>

<div class="sidebar-widget dropdown">
    <div class="sidebar-widget__head" data-toggle="dropdown">
        <?php echo ($optionRow['option_name']) ? $optionRow['option_name'] : $optionRow['option_identifier']; ?></div>
    <div class="sidebar-widget__body dropdown-menu dropdown-menu-anim">
        <ul class="list-vertical"><?php
                }
                $optionValueId = $optionRow['option_id'] . '_' . $optionRow['optionvalue_id'];
                //$liData.= "<li>".$optionRow['optionvalue_name']."</li>";?>
            <li><label class="checkbox optionvalue" id="optionvalue_<?php echo $optionRow['optionvalue_id']; ?>"><input
                        name="optionvalues" value="<?php echo $optionValueId; ?>" type="checkbox" <?php if (in_array($optionRow['optionvalue_id'], $optionValueCheckedArr)) {
                    echo "checked='true'";
                } ?>><i
                        class="input-helper"></i><?php echo ($optionRow['optionvalue_name']) ? $optionRow['optionvalue_name'] : $optionRow['optionvalue_identifier']; ?>
                </label></li>

            <?php
            }
            echo "</ul></div> </div>";
        }?>
            <!-- ]-->

            <!-- Condition Filters[ -->

            <?php if (isset($conditionsArr) && count($conditionsArr) > 1) {
            $conditionsCheckedArr = (isset($conditionsCheckedArr) && !empty($conditionsCheckedArr)) ? $conditionsCheckedArr : array(); ?>
            
            <div class="sidebar-widget dropdown">
                <div class="sidebar-widget__head" data-toggle="dropdown" >
                    <?php echo Labels::getLabel('LBL_Condition', $siteLangId); ?></div>
                <div class="sidebar-widget__body dropdown-menu dropdown-menu-anim">
                    <ul class="list-vertical">
                        <?php foreach ($conditionsArr as $condition) {
                if (empty($condition) || $condition['selprod_condition'] == 0) {
                    continue;
                } ?>
                        <li><label class="checkbox condition"
                                id="condition_<?php echo $condition['selprod_condition']; ?>"><input
                                    value="<?php echo $condition['selprod_condition']; ?>" name="conditions"
                                    type="checkbox" <?php if (in_array($condition['selprod_condition'], $conditionsCheckedArr)) {
                    echo "checked='true'";
                } ?>><i class="input-helper"></i><?php echo Product::getConditionArr($siteLangId)[$condition['selprod_condition']]; ?>
                            </label></li>
                        <?php
            } ?>
                    </ul>
                </div>
            </div>

            <?php
        } ?>
            <!-- ] -->

            <!--Availability Filters[ -->
            <?php
        if (isset($availabilityArr) && count($availabilityArr) > 1) {
            $availability = isset($availability) ? $availability : 0; ?>
            
            <div class="sidebar-widget dropdown">
                <div class="sidebar-widget__head   collapsed" data-toggle="dropdown">
                    <?php echo Labels::getLabel('LBL_Availability', $siteLangId); ?>
                </div>
                <div class="sidebar-widget__body dropdown-menu dropdown-menu-anim">                  
                        <ul class="listing--vertical listing--vertical-chcek">
                            <li><label class="checkbox availability" id="availability_1"><input name="out_of_stock"
                                        value="1" type="checkbox" <?php if ($availability == 1) {
                echo "checked='true'";
            } ?>><i class="input-helper"></i><?php echo Labels::getLabel('LBL_Exclude_out_of_stock', $siteLangId); ?>
                                </label></li>
                        </ul>
                 
                </div>
            </div>
            <?php
        }?>

    </div>
            <!-- ] -->
			<div class="selected-filters" id="filters">
            <?php /*?><span class="chip more">+10</span><?php */?>
				<a href="javascript:void(0)" data-yk='link' class="resetAll link" id="resetAll" onClick="resetListingFilter()"
					style="display:none;">
					<?php echo Labels::getLabel('LBL_Clear_All', $siteLangId);?>
				</a>
			</div>
            <script language="javascript">
            var catCodeArr = <?php echo json_encode($catCodeArr); ?> ;
            $.each(catCodeArr, function(key, value) {
                if ($("ul li a[data-id='" + value + "']").parent().find('span')) {
                    $("ul li a[data-id='" + value + "']").parent().find('span:first').addClass('is--active');
                    $("ul li a[data-id='" + value + "']").parent().find('ul:first').css('display', 'block');
                }

            });

            $("document").ready(function() {
                var min = 0;
                var max = 0; 
                <?php
                if (isset($priceArr) && $priceArr) {
                    ?>
                    var $from = $('input[name="priceFilterMinValue"]');
                    var $to = $('input[name="priceFilterMaxValue"]');
                    var range,
                        min = Math.floor( <?php echo $filterDefaultMinValue; ?> ),
                        max = Math.ceil( <?php echo $filterDefaultMaxValue; ?> ),
                        from,
                        to;

                    const len = 4;
                    var step = (max - min) / (len - 1);
                    var steps = Array(len).fill().map((_, idx) => min + (idx * step));
                    var rangeSlider = document.getElementById('rangeSlider');
                    noUiSlider.create(rangeSlider, {
                        start: [$from.val(), $to.val()],
                        step: Math.floor(step / len),
                        range: {
                            'min': [min],
                            'max': [max]
                        },
                        connect: true,
                        tooltips: true,
                        direction: '<?php echo $layoutDirection; ?>',
                        pips: {
                            mode: 'values',
                            values: steps,
                            density: 4
                        }
                    });

                    rangeSlider.noUiSlider.on('change', function(values, handle) {
                        var value = values[handle];
                        /* handle return 0,1(min hanle and max handle) in RTL it return opposite */
                        if (handle) {
                            to = value;
                        } else {
                            from = value;
                        }
                        updateValues();
                        addPricefilter(true);
                    });

                    var updateRange = function() {
                        rangeSlider.noUiSlider.set([from, to]);
                        updateValues();
                        addPricefilter();
                    };

                    $from.on("change", function() {
                        from = $(this).prop("value");
                        if (!$.isNumeric(from)) {
                            from = 0;
                        }
                        if (from < min) {
                            from = min;
                        }
                        if (from >= max) {
                            from = (max - 1);
                        }
                        updateRange();
                    });

                    $to.on("change", function() {
                        to = $(this).prop("value");
                        if (!$.isNumeric(to)) {
                            to = 0;
                        }
                        if (to > max) {
                            to = max;
                        }
                        if (to < min) {
                            to = min;
                        }
                        updateRange();
                    });

                    var updateValues = function() {
                        $from.prop("value", from);
                        $to.prop("value", to);
                    }; <?php
                } ?>

                /* left side filters scroll bar[ */

                <?php
                if (true === $shopCatFilters) {
                    ?>
                    // new SimpleBar(document.getElementById('accordian'));
                    <?php
                } ?>
                var x = document.getElementsByClassName("scrollbar-filters");
                var i;
                for (i = 0; i < x.length; i++) {
                    new SimpleBar(x[i]);
                }
                /* ] */

                /* left side filters expand-collapse functionality [ */
                $('.span--expand').bind('click', function() {
                    $(this).parent('li.level').toggleClass('is-active');
                    $(this).toggleClass('is--active');
                    $(this).next('ul').toggle("");
                });
                $('.span--expand').click();
                /* ] */

                updatePriceFilter( <?php echo floor($priceArr['minPrice']); ?> , <?php echo ceil($priceArr[
                    'maxPrice']); ?> );

                if ('rtl' == langLbl.layoutDirection && 0 < $("[data-simplebar]").length) {
                    $("[data-simplebar]").attr('data-simplebar-direction', 'rtl');
                }
            });

            $("#accordian li span.acc-trigger").click(function() {
                var link = $(this);
                var closest_ul = link.siblings("ul");

                if (link.hasClass("is--active")) {
                    closest_ul.slideUp();
                    link.removeClass("is--active");
                } else {
                    closest_ul.slideDown();
                    link.addClass("is--active");
                }
            });
            $('.dropdown-menu').on('click', function(e) {
                e.stopPropagation();
            });
            </script>