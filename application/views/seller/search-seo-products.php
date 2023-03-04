<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="card-body">
	<?php $arr_flds = array(
		'listserial' => '#',
		'product_name' => Labels::getLabel('LBL_Product', $siteLangId),
	);
	if (1 > count($arrListing)) {
		unset($arr_flds['select_all']);
	}
	$tbl = new HtmlElement('table', array('width'=>'100%', 'class'=>'table volDiscountList-js'));
	$thead = $tbl->appendElement('thead');
	$th = $thead->appendElement('tr', array('class' => ''));

	foreach ($arr_flds as $key => $val) {
		if ('select_all' == $key && $canEditMetaTag) {
			$th->appendElement('th')->appendElement('plaintext', array(), '<label class="checkbox"><input title="'.$val.'" type="checkbox" onclick="selectAll($(this))" class="selectAll-js"><i class="input-helper"></i></label>', true);
		} else {
			$th->appendElement('th', array(), $val);
		}
	}
	if ($page ==1) {
		$sr_no = 0;
	} else {
		$sr_no = ($page-1) * $pageSize;
	}
	foreach ($arrListing as $sn => $row) {
		$sr_no++;
		$tr = $tbl->appendElement('tr', array());
		$selProdId = $row['selprod_id'];
		foreach ($arr_flds as $key => $val) {
			$tr->setAttribute('id', 'row-'.$selProdId);
			$td = $tr->appendElement('td');
			switch ($key) {
				case 'select_all':
					$td->appendElement('plaintext', array(), '<label class="checkbox"><input class="selectItem--js" type="checkbox" name="selprod_ids['.$selProdId.']" value='.$selProdId.'><i class="input-helper"></i></label>', true);
					break;
				case 'listserial':
					$td->appendElement('plaintext', array(), $sr_no, true);
					break;
				case 'product_name':
					// last Param of getProductDisplayTitle function used to get title in html form.
					$productName = SellerProduct::getProductDisplayTitle($selProdId, $siteLangId, false);
					$editMetaTags = ($canEditMetaTag) ? "editProductMetaTagLangForm(".$selProdId.", ".$siteLangId.")" : "";
					$td->appendElement(
						'a',
						array('href'=>'javascript:void(0)', 'class'=>'',
						'title'=>'Links',"onclick"=>$editMetaTags),
						$productName,
						true
					);
					break;
				default:
					$td->appendElement('plaintext', array(), $row[$key], true);
					break;
			}
		}
	}

	if (count($arrListing) == 0) {
		$message = Labels::getLabel('LBL_You_need_to_create_products_in_order_to_add_meta_tags', $siteLangId);
		$this->includeTemplate('_partial/no-record-found-with-info.php', array('siteLangId'=>$siteLangId,'message'=>$message));
	} else {
		echo $tbl->getHtml();
	}

	$frm = new Form('frmSeoListing', array('id'=>'frmSeoListing'));
	$frm->setFormTagAttribute('class', 'form');

	echo $frm->getFormTag(); ?>
	</form>
</div>
<div class="card-footer">
	<?php
	$postedData['page'] = $page;
	echo FatUtility::createHiddenFormFromData($postedData, array('name' => 'frmSearchSeoProductsPaging'));

	$pagingArr=array('pageCount'=>$pageCount,'page'=>$page,'recordCount'=>$recordCount,'callBackJsFunc' => 'goToSearchPage','adminLangId'=>$siteLangId);
	$this->includeTemplate('_partial/pagination.php', $pagingArr, false); ?>
</div>


