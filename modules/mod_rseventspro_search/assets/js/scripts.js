/** *	Ajax search */	function rsepro_search(root,itemid,opener) {		if (jQuery('#rsepro_ajax').val().length == 0) {		jQuery('#rsepro_ajax_list').slideUp();		return;	}		rse_root = typeof rsepro_root != 'undefined' ? rsepro_root : '';	jQuery('#rsepro_ajax_loader').css('display','');		jQuery.ajax({		url: rse_root + 'index.php?option=com_rseventspro',		type: 'post',		dataType: 'html',		data: 'task=ajax&search=' + jQuery('#rsepro_ajax').val() + '&iid=' + itemid + '&opener=' + opener + '&randomTime='+Math.random()	}).done(function( response ) {		var start = response.indexOf('RS_DELIMITER0') + 13;		var end = response.indexOf('RS_DELIMITER1');		response = response.substring(start, end);				if (response != '') {			jQuery('#rsepro_ajax_list').html(response);			jQuery('#rsepro_ajax_list').slideDown();		} else {			jQuery('#rsepro_ajax_list').slideUp();		}				jQuery('#rsepro_ajax_loader').css('display','none');	});}/** *	Close ajax search window */	function rsepro_ajax_close() {	jQuery('#rsepro_ajax_list').slideUp();}/** *	Check dates for the search module */	function rs_check_dates() {	if (jQuery('#enablestart').length) {		if (jQuery('#enablestart').prop('checked')) {			jQuery('#rsstart').prop('disabled',false);			jQuery('#rsstart_datetimepicker button').css('display','');		} else {			jQuery('#rsstart').prop('disabled',true);			jQuery('#rsstart_datetimepicker button').css('display','none');		}	}			if (jQuery('#enableend').length) {		if (jQuery('#enableend').prop('checked')) {			jQuery('#rsend').prop('disabled',false);			jQuery('#rsend_datetimepicker button').css('display','');		} else {			jQuery('#rsend').prop('disabled',true);			jQuery('#rsend_datetimepicker button').css('display','none');		}	}}/** *	Check search module */	function rsepro_search_form_verification() {	if (jQuery('#rskeyword').val() == '')		return false;		return true;}