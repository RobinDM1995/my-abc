// JavaScript Document

jQuery.expr[':'].contains = function(a, i, m) {
  return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

var ccmFormidableFormLayoutCheckSelectors = function(s) {
	if (s != undefined && s.length > 0 && typeof(s) !== 'object') { return false; }

	if ($('select[name="appearance"]').val() == 'step') {
		$('div.label-holder').slideDown().find(':input').attr('disabled', false); 
		$('div.columns-holder').slideUp();
		$('div.button-holder').slideDown();
	} else if ($('select[name="appearance"]').val() == 'fieldset') {
		$('div.label-holder').slideDown().find(':input').attr('disabled', false);
		$('div.button-holder').slideUp();
	} else {
		$('div.label-holder').slideUp().find(':input').attr('disabled', true).val(''); 
		$('div.columns-holder').slideDown();
		$('div.button-holder').slideUp();
	}	
	
	var div = $('div[id="custom_buttons_div"]');
	if ($('input[name="custom_buttons"]').is(':checked')) div.slideDown();
	else div.slideUp();

	var element = $('input[name="btn_prev_css_value"]');
	if ($('input[name="btn_prev_css"]').is(':checked')) {
		element.attr('disabled', false); $('#btn_prev_css_content_note').slideDown();
		if (s && s.attr('name') == 'btn_prev_css') element.focus();
	} else {
		element.attr('disabled', true).val(''); $('#btn_prev_css_content_note').slideUp();
	}

	var element = $('input[name="btn_next_css_value"]');
	if ($('input[name="btn_next_css"]').is(':checked')) {
		element.attr('disabled', false); $('#btn_next_css_content_note').slideDown();
		if (s && s.attr('name') == 'btn_next_css') element.focus();
	} else {
		element.attr('disabled', true).val(''); $('#btn_next_css_content_note').slideUp();
	}

	var element = $('input[name="css_value"]');
	if ($('input[name="css"]').is(':checked')) {
		element.attr('disabled', false); $('#css_content_note').slideDown();
		if (s && s.attr('name') == 'css') element.focus();
	} else {
		element.attr('disabled', true).val(''); $('#css_content_note').slideUp();
	}
};

var ccmFormidableMoveLayout = function() {
	$('.f-row').addClass('moving');

	$(".f-row.moving").parent().sortable({
		items: "div.moving",
		handle: "div.overlay",
		sort: function(event, ui) {},
		stop: function(event, ui) {			
			var list = 'action=sort&formID='+formID+'&ccm_token='+formidable_security_token_layout;
			$("div.f-row").each(function(i, row) {
				list += '&rows[]='+$(row).attr('data-id');
			});
			$.ajax({
	            url: layout_tools_url+'order',
	            type: 'post',
	            dataType: 'json',
	            data: list,
	            success: function (ret) {
	            	ccmFormidableSetMessage(ret.type, ret.message);
	            	$('.f-row').removeClass('moving');
					//ccmFormidableInitializeSortables();
					ccmFormidableLoadElements();		
	            }
	        });	
		}
	});
};

var ccmFormidableMoveColumns = function(rowID) {
	$('#row_'+rowID+' .f-col').addClass('moving');
	$(".f-col.moving").parent().sortable({
		items: "div.moving",
		handle: "div.overlay",
		stop: function() {			
			var list = 'action=sort&rowID='+rowID+'&formID='+formID+'&ccm_token='+formidable_security_token_layout;
			$("div.f-col.moving").each(function(i, row) {
				list += '&cols[]='+$(row).attr('data-id');
			});
			$.ajax({
	            url: layout_tools_url+'order',
	            type: 'post',
	            dataType: 'json',
	            data: list,
	            success: function (ret) {
	            	ccmFormidableSetMessage(ret.type, ret.message);
	            	$('#row_'+rowID+' .f-col').removeClass('moving');
					ccmFormidableLoadElements();
					//ccmFormidableInitializeSortables();	
	            }
	        });	
		}
	});
};

var ccmFormidableInitializeSortables = function () {
	$("#ccm-element-list").sortable({
		items: "div.element_row_wrapper",
		handle: ".mover",
		sort: function(event, ui) {
			$(this).removeClass( "ui-state-default" );			
			ui.item.parents('.f-col').each(function() {
				var elnum = $('.element_row_wrapper:not(.element-empty)',this).length;
				if(elnum == 1) $('.element-empty', this).removeClass('hide').fadeIn();
				else $('.element-empty', this).hide();
			});			
			$('.ui-sortable-placeholder').parents('.f-col').each(function() {
				var elnum = $('.element_row_wrapper:not(.element-empty)',this).length;
				if(elnum == 0) $('.element-empty', this).removeClass('hide').fadeIn();
				else $('.element-empty', this).hide();
			});
		},
		stop: function() {
			// Show or hide empty-elements
			$('.f-col').each(function() {
				var elnum = $('.element_row_wrapper:not(.element-empty)',this).length;
				if(elnum == 0) $('.element-empty', this).removeClass('hide').fadeIn();
				else $('.element-empty', this).hide();
			});			
			var list = 'formID='+formID+'&ccm_token='+formidable_security_token_element;
			$("#ccm-element-list").find('.element_row_wrapper[data-element_id]').each(function(i, row) {
				list += '&elements[]='+$(row).attr('data-element_id')+'&layout[]='+$(row).parent().parent().attr('data-id');
			});
			$.ajax({
	            url: tools_url+'order',
	            type: 'post',
	            dataType: 'json',
	            data: list,
	            success: function (ret) {
	               ccmFormidableSetMessage(ret.type, ret.message);
	               	$('.f-row').removeClass('moving');
					ccmFormidableLoadElements();
					//ccmFormidableInitializeSortables();
	            }
	        });	        
		}
	});
};

var ccmFormidableOpenNewElementDialog = function (layoutID) {
	jQuery.fn.dialog.closeTop();
	var query_string = "formID="+formID+"&layoutID="+layoutID+'&ccm_token='+formidable_security_token_element;
	jQuery.fn.dialog.open({
		width: 970,
		height: '75%', 
		modal: true, 
		href: layout_dialog_url+"select/?"+query_string, 
		title: element_message_add
	});

};

var ccmFormidableOpenLayoutDialog = function(layoutID, rowID) {
	jQuery.fn.dialog.closeTop();
	var query_string = "formID="+formID+"&rowID="+rowID+"&layoutID="+layoutID+'&ccm_token='+formidable_security_token_layout;
	jQuery.fn.dialog.open({
		width: 970,
		height: 400, 
		modal: true, 
		href: layout_dialog_url+"?"+query_string, 
		title: (rowID < 0 ? layout_message_add : layout_message_edit)		
	});
};

var ccmFormidableCheckFormLayoutSubmit = function() {
	var data = $('#layoutForm').serialize();
	data += '&formID='+formID;
	$.ajax({ 
		type: "POST",
		url: layout_tools_url+'save?ccm_token='+formidable_security_token_layout,
		data: data,
		dataType: 'json',
		beforeSend: function () {
			ccmFormidableLoadLoader(true);
		},
		success: function(ret) {
			ccmFormidableSetMessage(ret.type, ret.message);
			ccmFormidableLoadElements();
			jQuery.fn.dialog.closeTop();
		}
	});	
};

var ccmFormidableOpenDeleteLayoutDialog = function(layoutID, rowID) {
	var query_string = '?layoutID='+layoutID+'&rowID='+rowID+'&formID='+formID+'&ccm_token='+formidable_security_token_layout;
	jQuery.fn.dialog.open({ 
		width: 520,
		height: 160,
		modal: true,
		href: layout_dialog_url+'delete'+query_string,
		title: layout_message_delete
	});
};

var ccmFormidableDeleteLayout = function(layoutID, rowID) {
	data = 'layoutID='+layoutID+'&rowID='+rowID+'&formID='+formID+'&all='+($('#all').is(':checked')?1:0)+'&ccm_token='+formidable_security_token_layout;
	$.ajax({ 
		type: "POST",
		url: layout_tools_url+'delete',
		data: data,
		dataType: 'json',
		beforeSend: function () {
			ccmFormidableLoadLoader(true);
		},
		success: function(ret) {
			ccmFormidableSetMessage(ret.type, ret.message);
			ccmFormidableLoadElements();
		}
	});	
};

var ccmFormidableOpenDuplicateLayoutDialog = function(layoutID, rowID) {
	var query_string = '?layoutID='+layoutID+'&rowID='+rowID+'&formID='+formID+'&ccm_token='+formidable_security_token_layout;
	jQuery.fn.dialog.open({ 
		width: 520,
		height: 160,
		modal: true,
		href: layout_dialog_url+'duplicate'+query_string,
		title: layout_message_duplicate
	});
};

var ccmFormidableDuplicateLayout = function(layoutID, rowID) {
	data = 'layoutID='+layoutID+'&rowID='+rowID+'&formID='+formID+'&ccm_token='+formidable_security_token_layout;
	$.ajax({ 
		type: "POST",
		url: layout_tools_url+'duplicate',
		data: data,
		dataType: 'json',
		beforeSend: function () {
			ccmFormidableLoadLoader(true);
		},
		success: function(ret) {
			ccmFormidableSetMessage(ret.type, ret.message);
			ccmFormidableLoadElements();
		}
	});	
};