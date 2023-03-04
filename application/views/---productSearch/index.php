<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div id="body" class="body">
    <?php $this->includeTemplate('productSearch/listing-page.php', $data, false); ?>
</div>
<script>
function removePaginationFromLink(){
	if(typeof searchArr['page'] == 'undefined') {return;}
	delete searchArr['page'];
	var frm = document.frmProductSearchPaging;
	$(frm.page).val(1);
}

function getSearchQueryUrl(includeBaseUrl){
	url = '';
	itemSeperator = '&';
	valueSeperator = '-';

	if(typeof includeBaseUrl != 'undefined' || includeBaseUrl != null){
		url  = "http://v8-yokart.local.4livedemo.com/productSearch/search";
	}
	var keyword = $("input[id=keyword]").val();
	if(keyword !=''){
		delete searchArr['keyword'];
		url = url +setQueryParamSeperator(url)+'keyword'+valueSeperator+keyword.replace(/_/g,'-');
	}

	var category = parseInt($("input[id=searched_category]").val());
	if(category > 0){
		delete searchArr['category'];
		url = url +setQueryParamSeperator(url)+'category'+valueSeperator+category;
	}

	for (var key in searchArr) {
		url = url +setQueryParamSeperator(url)+ key.replace(/_/g,'-') + valueSeperator+ searchArr[key];
	}

	/* var currency = parseInt($("input[name=currency_id]").val());
	if(currency > 0){
		delete searchArr['currency'];
		url = url +setQueryParamSeperator(url)+'currency'+valueSeperator+currency;
	} */

	var featured = parseInt($("input[name=featured]").val());
	if(featured > 0){
		url = url +setQueryParamSeperator(url)+'featured'+valueSeperator+featured;
	}

	var collection_id = parseInt($("input[name=collection_id]").val());
	if(collection_id > 0){
		url = url +setQueryParamSeperator(url)+'collection'+valueSeperator+collection_id;
	}

	var shop_id = parseInt($("input[name=shop_id]").val());
	if(shop_id > 0){
		url = url +setQueryParamSeperator(url)+'shop'+valueSeperator+shop_id;
	}

	/* var page = parseInt($("input[name=page]").val());
	if(page > 1){
		url = url +setQueryParamSeperator(url)+'page-'+page;
	} */

	var e = document.getElementById("sortBy");
	if($(e).is("select")) {
		var sortBy = e.options[e.selectedIndex].value;
	}else{
		var sortBy = e.value;
	}

	if(sortBy){
		url = url +setQueryParamSeperator(url)+'sort'+valueSeperator+sortBy.replace(/_/g,'-');
	}

	var e = document.getElementById("pageSize");
	var pageSize = parseInt(e.options[e.selectedIndex].value);
	if(pageSize > 0){
		url = url +setQueryParamSeperator(url)+'pagesize'+valueSeperator+pageSize;
	}

	return encodeURI(url);
}

function submitSiteSearch(frm, page) {
    events.search();
	var keyword = $.trim($(frm).find('input[name="keyword"]').val());
	keyword = keyword.replace('&', '++');

	if (3 > keyword.length || '' === keyword) {
		$.mbsmessage(langLbl.searchString, true, 'alert--danger');
		return;
	}

	//var data = fcom.frmData(frm);
	var qryParam = ($(frm).serialize_without_blank());

	var urlString = '';
	if (qryParam.indexOf("keyword") > -1) {		
		var protomatch = /^(https?|ftp):\/\//; 
		urlString = urlString + setQueryParamSeperator(urlString)+'keyword-' + encodeURIComponent(keyword.replace(protomatch, '').replace(/\//g, '-'))+'&pagesize='+page;			
	}
	
	if (qryParam.indexOf("category") > -1 && $(frm).find('input[name="category"]').val() > 0) {				
		urlString = urlString + setQueryParamSeperator(urlString)+'category-' + $(frm).find('input[name="category"]').val();
	}

	/* url_arr = []; */

	if (themeActive == true) {
		url = fcom.makeUrl('Products', 'search', []) + urlString +'&theme-preview';
		document.location.href = url;
		return;
	}
	url = fcom.makeUrl('ProductSearch', 'search', [])+ urlString;
	document.location.href = url;
}

(function() {
	goToProductListingSearchPage = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}

		removePaginationFromLink(page);
		var frm = document.frmProductSearchPaging;
		$(frm.page).val(page);
		$("form[name='frmProductSearchPaging']").remove();
		getSetSelectedOptionsUrl(frm);
		var url = getSearchQueryUrl(true);
		window.location.href = url+setQueryParamSeperator(url)+'page-'+page;
		//searchProducts(frm,0,0,1,1);
		/* $('html, body').animate({ scrollTop: 0 }, 'slow'); */
	};
	getSetSelectedOptionsUrl = function(frm){
		var data = fcom.frmData(frm);

		/* Category filter value pickup[ */
		var category=[];
		$("input:checkbox[name=category]:checked").each(function(){
			var id = $(this).parent().parent().find('label').attr('id');
			addToSearchQueryString (id,this);
			addFilter (id,this);
			category.push($(this).val());
		});
		if ( category.length ){
			data=data+"&category="+[category];
		}
		/* ] */

		/* brands filter value pickup[ */
		var brands= getSelectedBrands();		
		if ( brands.length ){
			data=data+"&brand="+[brands];
		}
		/* ] */

		/* Option filter value pickup[ */
		var optionvalues=[];
		$("input:checkbox[name=optionvalues]:checked").each(function(){
			var id = $(this).parent().parent().find('label').attr('id');
			addToSearchQueryString (id,this);
			addFilter (id,this);
			optionvalues.push($(this).val());
		});
		if ( optionvalues.length ){
			data=data+"&optionvalue="+[optionvalues];
		}
		/* ] */

		/* condition filters value pickup[ */
		var conditions=[];
		$("input:checkbox[name=conditions]:checked").each(function(){
			var id = $(this).parent().parent().find('label').attr('id');
			addToSearchQueryString (id,this);
			addFilter (id,this);
			conditions.push($(this).val());
		});
		if ( conditions.length ){
			data=data+"&condition="+[conditions];
		}
		/* ] */

		/* Free Shipping Filter value pickup[ */

		/* ] */

		/* Out Of Stock Filter value pickup[ */
		$("input:checkbox[name=out_of_stock]:checked").each(function(){
			var id = $(this).parent().parent().find('label').attr('id');
			addToSearchQueryString (id,this);
			addFilter (id,this);
			data=data+"&out_of_stock=1";
		});
		/* ] */

		/* price filter value pickup[ */
		if(typeof $("input[name=priceFilterMinValue]").val() != "undefined"){
			data = data+"&min_price_range="+$("input[name=priceFilterMinValue]").val();
		}

		if(typeof $("input[name=priceFilterMaxValue]").val() != "undefined"){
			data = data+"&max_price_range="+$("input[name=priceFilterMaxValue]").val();
		}

		if ( ($("input[name=filterDefaultMinValue]").val() !=  $("input[name=priceFilterMinValue]").val()) || ($("input[name=filterDefaultMaxValue]").val() !=  $("input[name=priceFilterMaxValue]").val())){
			addPricefilter(false);
		}

		return data;
	};
	loadProductListingfilters = function(frm) {
		
		$('.productFilters-js').html(fcom.getLoader());
		var url = window.location.href;
		if($currentPageUrl == removeLastSpace(url)+'/index'){
			url = fcom.makeUrl('ProductSearch','filters');
		}else{
			url = url.replace($currentPageUrl, fcom.makeUrl('ProductSearch','filters'));
		}

		if (url.indexOf("ProductSearch/filters") == -1) {
			url = fcom.makeUrl('ProductSearch','filters');
	    }
		
		//url = fcom.makeUrl('Products','filters');
		var data = fcom.frmData(frm);
		fcom.ajax(url, data, function(res){
			$('.productFilters-js').html(res);
			getSetSelectedOptionsUrl(frm);
		});
	};
})();
</script>