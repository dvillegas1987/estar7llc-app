<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (count($arr_listing) == 0) {
    $this->includeTemplate('_partial/no-record-found.php', array('adminLangId' => $adminLangId));
} else {
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'product_identifier' => Labels::getLabel('LBL_Product', $adminLangId),
    'tags' => Labels::getLabel('LBL_Tags', $adminLangId)
);

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table--orders'));
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
$sr_no = ($page > 1) ? $recordCount - (($page - 1) * $pageSize) : $recordCount;
foreach ($arr_listing as $sn => $row) {
    $productsArr[] = $row['product_id'];
    $tr = $tbl->appendElement('tr', array('class' => ''));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no, true);
                break;
            case 'product_identifier':
                $td->appendElement(
                    'a',
                    array('href' => 'javascript:void(0)', 'class' => '', 'title' => 'Links', "onclick" => 'redirectfunc("' . UrlHelper::generateUrl('Products') . '", ' . $row['product_id'] . ')'),
                    $row['product_name'],
                    true
                );
                break;
            case 'tags':
                $productTags = Product::getProductTags($row['product_id']);
                $tagData = array();
                foreach ($productTags as $key => $data) {
                    $tagData[$key]['id'] = $data['tag_id'];
                    $tagData[$key]['value'] = $data['tag_identifier'];
                }
                $encodedData = htmlspecialchars(json_encode($tagData), ENT_QUOTES, 'UTF-8');
                $td->appendElement('plaintext', array(), "<div class='product-tag' id='product" . $row['product_id'] . "'><input class='tag_name' type='text' name='tag_name" . $row['product_id'] . "' value='" . $encodedData . "' data-product_id='" . $row['product_id'] . "'></div>", true);
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
    $sr_no--;
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}


$frm = new Form('frmTagsListing', array('id' => 'frmTagsListing'));
$frm->setFormTagAttribute('class', 'web_form last_td_nowrap');
$frm->setFormTagAttribute('onsubmit', 'formAction(this, reloadList ); return(false);');
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('Tags', 'deleteSelected'));
$frm->addHiddenField('', 'status');

echo $frm->getFormTag();
echo $frm->getFieldHtml('status');
echo $tbl->getHtml(); ?>
</form>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array(
    'name' => 'frmTagSearchPaging'
));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'adminLangId' => $adminLangId);
$this->includeTemplate('_partial/pagination.php', $pagingArr, false); ?>

<?php if (count($arr_listing) > 0) { ?>
    <script>
        var productsArr = [ <?php echo '"' . implode('","', $productsArr) . '"' ?> ];      
        $("document").ready(function() {
            getTagsAutoComplete = function(e) {
                var keyword = e.detail.value;
                var list = [];
                fcom.ajax(fcom.makeUrl('Tags', 'autoComplete'), {keyword:keyword}, function(t) {
                    var ans = $.parseJSON(t);
                    for (i = 0; i < ans.length; i++) {
                        list.push({
                            "id": ans[i].id,
                            "value": ans[i].tag_identifier,
                        });
                    }
                    e.detail.tagify.settings.whitelist = list;
                });
                
            }            
            $.each(productsArr, function(index, value) {
                tagify = new Tagify(document.querySelector('input[name=tag_name' + value + ']'), {
                    whitelist: [],
                    delimiters: "#",
                    editTags: false,
                }).on('add', addTagData).on('remove', removeTagData).on('input', getTagsAutoComplete);
            });

        });
    </script>
<?php }
}
