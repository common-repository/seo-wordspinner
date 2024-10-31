jQuery(document).ready(function($) {

	$('#obstart').bind('click change', function() {
		seo_spin_toggle_table_rows($(this));
	});	
	
	seo_spin_toggle_table_rows($('#obstart'));
	
	function seo_spin_toggle_table_rows($el) {
		var index = $el.parents('tr').index();
		
		if($el.is(":checked")) {
			$('table.form-table tr:gt('+index+')').hide();
		} else {
			$('table.form-table tr:gt('+index+')').show();
		}
	}
	
});