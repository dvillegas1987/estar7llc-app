var frm = document.frmProductSearch;
function resetListingFilter() {
	searchArr = [];
	document.frmProductSearch.reset();
	document.frmProductSearchPaging.reset();

	$('#filters a').each(function(){
		id = $(this).attr('data-yk');
		clearFilters(id,this);
	});
	updatePriceFilter();
	reloadProductListing(frm);
	showSelectedFilters();
	//searchProducts(frm,0,0,1,1);
}
