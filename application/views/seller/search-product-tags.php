<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="js-scrollable table-wrap">
<?php $arr_flds = array(
    'listserial'=>'#',
    'product_identifier' => Labels::getLabel('LBL_Product', $siteLangId),
    'tags' => Labels::getLabel('LBL_Tags', $siteLangId)
);

$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table'));
$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
foreach ($arr_flds as $key => $val) {
    if ($key == 'listserial') {
        $e = $th->appendElement('th', array('width' => '5%'), $val);
    } elseif ($key == 'product_identifier') {
        $e = $th->appendElement('th', array('width' => '30%'), $val);
    } else {
        $e = $th->appendElement('th', array('width' => '65%'), $val);
    }
}
$productsArr = array();
$sr_no = ($page == 1) ? 0 : ($pageSize*($page-1));
foreach ($arr_listing as $sn => $row) {
    $productsArr[] = $row['product_id'];
    $sr_no++;
    $tr = $tbl->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no, true);
                break;
            case 'product_identifier':
                $td->appendElement('plaintext', array(), $row['product_name'], true);
                break;
            case 'tags':
                $productTags = Product::getProductTags($row['product_id']);
                $tagData = array();
                foreach ($productTags as $key => $data) {
                    $tagData[$key]['id'] = $data['tag_id'];
                    $tagData[$key]['value'] = $data['tag_identifier'];
                }
                $readOnly = (!$canEdit) ? 'readonly' : '';
                $encodedData = htmlspecialchars(json_encode($tagData), ENT_QUOTES, 'UTF-8');
                $td->appendElement('plaintext', array(), "<div class='product-tag scroll-y' id='product".$row['product_id']."' data-simplebar><input ".$readOnly." class='tag_name' type='text' name='tag_name".$row['product_id']."' value='".$encodedData."' data-product_id='".$row['product_id']."'></div>", true);
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
}

if (count($arr_listing) == 0) {
    $message = Labels::getLabel('LBL_You_need_to_create_private_products_in_order_to_add_tags', $siteLangId);
    $this->includeTemplate('_partial/no-record-found-with-info.php', array('siteLangId'=>$siteLangId,'message'=>$message));
} else {
    echo $tbl->getHtml();
} ?>
</div>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmCatalogProductSearchPaging'));
$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'callBackJsFunc' => 'goToCatalogProductSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>
<?php if (count($arr_listing) > 0) { ?>
<script>
var productsArr = [<?php echo '"'.implode('","', $productsArr).'"' ?>];
$("document").ready(function() {
    getTagsAutoComplete = function(e){  
        var keyword = e.detail.value;
        var list = [];   
        fcom.ajax(fcom.makeUrl('Seller', 'tagsAutoComplete'), {keyword:keyword}, function(t) {
            var ans = $.parseJSON(t);
            for (i = 0; i < ans.length; i++) {
                list.push({
                    "id" : ans[i].id,
                    "value" : ans[i].tag_identifier,
                });
            }
            e.detail.tagify.settings.whitelist = list;        
        });       
    }
   
    $.each(productsArr, function( index, value ) {
        tagify = new Tagify(document.querySelector('input[name=tag_name'+value+']'), {
               whitelist : [],
               delimiters : "#",
               editTags : false,
               backspace : false
            }).on('add', addTagData).on('remove', removeTagData).on('input', getTagsAutoComplete);
    });

});
</script>
<?php }?>
