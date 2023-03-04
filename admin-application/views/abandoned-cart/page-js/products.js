$(document).ready(function(){
	searchAbandonedCartProducts(1);
});


(function() {
	var currentPage = 1;
	
	searchAbandonedCartProducts = function(page){
		if (!page) {
			page = currentPage;
		}
		currentPage = page;	
        var data = 'page='+page;
		var dv = $('#abandonedCartProducts');		
		dv.html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('AbandonedCart','getProducts'),data,function(res){
			dv.html(res);
		});
	};
        
    goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page = 1;
		}		
		searchAbandonedCartProducts(page);
	}    
    
})();