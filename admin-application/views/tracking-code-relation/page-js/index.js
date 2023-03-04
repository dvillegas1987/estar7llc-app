$(document).ready(function(){
	searchCourier();
});
(function() {
	searchCourier = function(){
		var dv = $('#listing');
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('TrackingCodeRelation', 'search'), '', function(res){
			$(dv).html(res);
		});
	};
    
    setUpCourierRelation = function(ele) {
		var trackingApiCode = $(ele).val();
        var shipApiCode = $(ele).attr('id');
        if(trackingApiCode == '' || shipApiCode == ''){
            return false;
        }
        
        var data = 'trackingApiCode='+trackingApiCode+'&shipApiCode='+shipApiCode;
		fcom.updateWithAjax(fcom.makeUrl('TrackingCodeRelation', 'setUpCourierRelation'), data, function(t) {
			searchCourier();
		});
	};
	



})();
