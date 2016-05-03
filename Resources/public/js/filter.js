$(document).ready(function(){

	$('select.filter').on('change', function(e){
		var self = $(this);
		var selectedValue = self.val();
		var columnName = self.data('columnName');
		if(selectedValue != "")
			window.location.search = jQuery.query.set("filter_" + columnName, selectedValue);
		else
			window.location.search = jQuery.query.remove("filter_" + columnName);
	});
	
});
