<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="js-scrollable table-wrap">
	<?php $arr_flds = array(
		'name'	=>	Labels::getLabel('LBL_Product', $siteLangId),
		'op_selprod_sku'	=>	Labels::getLabel('LBL_SKU', $siteLangId),
		'wishlist_user_counts'	=>	Labels::getLabel('LBL_WishList_User_Counts', $siteLangId)
	);

	if ($topPerformed) {
		$arr_flds['totSoldQty'] = Labels::getLabel('LBL_Sold_Quantity', $siteLangId);
	} else {
		$arr_flds['totRefundQty'] = Labels::getLabel('LBL_Refund_Quantity', $siteLangId);
	}

	$tbl = new HtmlElement('table', array('class' => 'table'));
	$th = $tbl->appendElement('thead')->appendElement('tr', array('class' => ''));
	foreach ($arr_flds as $val) {
		$e = $th->appendElement('th', array(), $val);
	}

	$sr_no = 0;
	foreach ($arrListing as $sn => $listing) {
		$sr_no++;
		$tr = $tbl->appendElement('tr', array('class' => ''));

		foreach ($arr_flds as $key => $val) {
			$td = $tr->appendElement('td');
			switch ($key) {
				case 'name':
					$txt = '<div class="item__description">';
					$txt .= '<div class="item__title">' . $listing['op_product_name'] . '</div>';
					if ($listing['op_selprod_title'] != '') {
						$txt .= '<div class="item__sub_title"><strong>' . Labels::getLabel('LBL_Custom_Title', $siteLangId) . ": </strong>" . $listing['op_selprod_title'] . '</div>';
					}

					if ($listing['op_selprod_options'] != '') {
						$txt .= '<div class="item__specification">' . Labels::getLabel('LBL_Options', $siteLangId) . ": </strong>" . $listing['op_selprod_options'] . '</div>';
					}

					if ($listing['op_brand_name'] != '') {
						$txt .= '<div class="item__brand"><strong>' . Labels::getLabel('LBL_Brand', $siteLangId) . ": </strong>" . $listing['op_brand_name'] . '</div>';
					}
					$txt .= '</div>';
					$td->appendElement('plaintext', array(), $txt, true);
					break;

				case 'totSoldQty':
					$td->appendElement('plaintext', array(), $listing['totSoldQty'], true);
					break;

				case 'totRefundQty':
					$td->appendElement('plaintext', array(), $listing['totRefundQty'], true);
					break;

				case 'wishlist_user_counts':
					$td->appendElement('plaintext', array(), $listing['wishlist_user_counts'], true);
					break;
				default:
					$td->appendElement('plaintext', array(), $listing[$key], true);
					break;
			}
		}
	}

	$noteLbl = Labels::getLabel("LBL_Note:_Performance_Report_on_the_basis_of_Sold_Quantity", $siteLangId);
	echo $tbl->getHtml();
	if (count($arrListing) == 0) {
		$message = Labels::getLabel('LBL_No_Records_Found', $siteLangId);
		$this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId, 'message' => $message));
	} ?>
</div>
<?php $postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSrchProdPerformancePaging'));
$pagingArr = array('pageCount' => $pageCount, 'page' => $page, 'recordCount' => $recordCount, 'callBackJsFunc' => 'goToTopPerformingProductsSearchPage');
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
?>