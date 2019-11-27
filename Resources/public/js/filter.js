$(document).ready(function(){

	$('select.cocept-filter-match').on('change', function(e){
		var self = $(this);
		var selectedValue = self.val();
		var columnName = self.data('columnName');
		if(selectedValue != "")
			window.location.search = jQuery.query.set("filter_" + columnName, selectedValue);
		else
			window.location.search = jQuery.query.remove("filter_" + columnName);
	});

	$('button.cocept-filter-operator').on('click', function(e) {
		var self = $(this);
		var columnName = self.data('columnName');
		var operator = jQuery.query.get('operator_' + columnName);
		if (!operator || operator == 'eq') {
			window.location.search = jQuery.query.set('operator_' + columnName, 'neq');
		} else {
			window.location.search = jQuery.query.set('operator_' + columnName, 'eq');
		}
	});

	$('input.cocept-filter-search').on('keydown', function(e) {
		if (e.keyCode == 13) {
			var self = $(this);
			var value = self.val();
			var columnName = self.data('columnName');

			if (!value) {
				window.location.search = jQuery.query.remove('filter_' + columnName).remove('operator_' + columnName);
			} else {
				window.location.search = jQuery.query.set("filter_" + columnName, value).set("operator_" + columnName, 'ilike');
			}
		}
	});
});
