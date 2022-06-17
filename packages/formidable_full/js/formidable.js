var ccmFormidableAddressStates = "";
var ccmFormidableAddressStatesToCountries = "";

var ccmFormidableTranslate = function (s, v) {
	"use strict";
	return I18N_FF && I18N_FF[s] ? (I18N_FF[s].replace('%s', v) || s) : s;
};

// Formidable plugin
(function ($) {
	"use strict";

	$.fn.formidable = function (options) {
		options = $.extend({
			'error_messages_on_top': false,
			'error_messages_on_top_class': 'alert alert-danger',
			'warning_messages_class': 'alert alert-warning',
			'error_messages_beneath_field': true,
			'error_messages_beneath_field_class': 'text-danger error',
			'success_messages_class': 'alert alert-success',
			'remove_form_on_success': true,
			'error_messages_animate_remove': false,
			'step_progress_bar': true,
			'step_progress_bar_selector': 'ul#formidable_steps',
			'animate_step': true,
			'animate_step_easing': 'easeInOutBack',
			'animate_step_duration': 800,
			'hide_steps_after_submission': true,
			'errorCallback': function () {
				console.log('Formidable validation issue, please check your form.');
			},
			'successCallback': function () {
				console.log('Formidable successfully send');
			}
		}, options);

		var called = false;
		var steps = false;
		var animation = '';
		var formObj = this;
		var formID = $('input[id="formID"]', formObj).val();
		var formContainer = $('#formidable_container_' + formID);
		var dependencyRules = [];

		if (!formObj.length) return false;

		var initialize = function () {

			stepify();
			resolution();
			countable();
			setup_options();
			signature();
			honeypot();
			tooltip();

			$('input[type="submit"][id="submit"]', formObj).on('click touchstart', function (e) {
				e.preventDefault();
				submit($(this).attr('id'));
			});

			$('input:not([type="file"]), textarea, select', formObj).on('input, keyup, keydown, change', function () {
				clear_error($(this));
				check_depencencies();
			});

			//$('input[type="checkbox"], input[type="radio"]', formObj).off('click touchstart').on('click touchstart', function () {
			//	clear_error($(this));
			//	if ($(this).is('[type="radio"]')) $('input[name="' + $(this).attr('name') + '"]').not($(this)).trigger('deselect');
			//	check_depencencies();
			//});

			//$('input[type="radio"]', formObj).on('click touchstart', function () {
			//	$('input[name="' + $(this).attr('name') + '"]').not($(this)).trigger('deselect');
			//});

			// Add placeholders if exists
			if ($.fn.addPlaceholder) {
				$('input[placeholder], textarea[placeholder]', formObj).addPlaceholder();
			}

			if (typeof ccmFormidableAddressStatesTextList !== 'undefined') {
				ccmFormidableAddressStates = ccmFormidableAddressStatesTextList.split('|');
			}

			$('select.country_select', formObj).each(function () {
				setup_state_province_selector($(this).data('name'));
			});

			$('input[name="ccmCaptchaCode"]', formObj).attr('id', 'ccmCaptchaCode');

		};

		var submit = function (action) {

			// Check if this method is already called some other way
			if (called) return false;
			called = true;

			// Add this element for the controller
			$('input[id="action"]', formObj).remove();
			formObj.append($('<input>').attr({
				'name': 'action',
				'id': 'action',
				'value': action,
				'type': 'hidden'
			}));

			// Start submission
			var data = formObj.serialize();
			$.ajax({
				type: "POST",
				url: formObj.attr('action'),
				data: data,
				dataType: 'json',
				beforeSend: function () {
					loading();
					message();
					clear_errors();
				},
				success: function (data) {
					// Show message if needed
					if (data.message && data.message.length > 0) {
						message(data.message, 'warning');
					}
					// Errors on submission
					else if (data.errors && data.errors.length > 0) {
						var errors = [];
						$.each(data.errors, function (i, row) {
							// Show error message beneath field.
							error_on_field(row.handle, row.message, false);
							errors.push(row.message);
						});
						message(errors, 'error');
						recaptcha();

						if (!!options.errorCallback && jQuery.isFunction(options.errorCallback)) {
							options.errorCallback.call(formObj, data);
						}
					}
					// On success
					else if (data.success || data.redirect) {
						if (options.hide_steps_after_submission) $(options.step_progress_bar_selector, formContainer).remove();
						if (!!options.successCallback && jQuery.isFunction(options.successCallback)) {
							options.successCallback.call(formObj, data);
						}
					}

					// Redirect on submission
					if (data.redirect) {
						window.location.href = data.redirect;
					}

					// Show success message
					if (data.success) {
						message(data.success, 'success');
						if (options.remove_form_on_success) {
							formObj.remove();
						}
					}

					if (data.clear) {
						if (!options.remove_form_on_success) {
							formObj.get(0).reset();
							initialize();
						}
					}
					loading();
					scroll();

					called = false;
				}
			});
			return false;
		};

		var resolution = function () {
			var resolution = screen.width + 'x' + screen.height;
			$('input[id="resolution"]', formObj).val(resolution);
		};

		var tooltip = function () {
			if ($.fn.tooltip) {
				$('[data-toggle="tooltip"]').tooltip();
			}
		};

		var honeypot = function () {
			var honeypot = $('<input>').attr({
				'type': 'hidden',
				'name': 'emailaddress',
				'id': 'emailaddress',
				'value': ''
			}).css({
				'display': 'none'
			});
			formObj.append(honeypot);
		};

		var countable = function () {
			$('.counter', formObj).closest('.element').each(function () {
				var element = $(this);
				if (element.find('.counter_disabled').length > 0) {
					element.find('.counter').parent().remove();
					return;
				}

				var input = element.find('input[type="checkbox"], select').eq(0);
				if (input.is('input')) element.find('input[type="checkbox"]').on('click', function () {
					counter($(this));
				});
				else if (input.is('select')) {
					input.on('click', function () {
						counter($(this));
					});
				}

				counter(element);
			});
		};

		var counter = function (obj) {

			var element = obj.closest('.element');

			var holder = element.find('.counter');
			var type = holder.attr('type');
			var max = holder.attr('max');
			var span = $('span', holder);

			var input = element.find('input[type="text"], input[type="password"], input[type="number"], input[type="url"], input[type="tel"], textarea').not('[name$="_other"]').eq(0);
			if (input.is('input') || input.is('textarea')) {
				input.simplyCountable({
					counter: span,
					countType: type,
					maxCount: max,
					strictMax: true
				});
				return false;
			}

			var input = element.find('input[type="checkbox"]').eq(0);
			if (input.is('input')) {
				var current = element.find(':checked').length;
				span.text(max - current);
				if (max == current) element.find('input[type="checkbox"]:not(:checked)').attr('disabled', true);
				else element.find('input[type="checkbox"]').attr('disabled', false);
				return false;
			}

			var input = element.find('select').eq(0);
			if (input.is('select')) {
				var current = element.find(':selected').length;
				span.text(max - current);
				if (max == current) element.find('select option:not(:selected)').attr('disabled', true);
				else element.find('select option').attr('disabled', false);
				return false;
			}
		};

		var loading = function (force) {
			if (!force) force = false;

			var button = $('input[type="button"]', formObj);
			var loader = $('.please_wait_loader', formObj);
			if (!button.hasClass('please_wait') || force) {
				button.attr({
					'data-value': ccmFormidableTranslate('Please wait...') == button.val()?button.attr('data-value'):button.val(),
					'disabled': true,
					'value': ccmFormidableTranslate('Please wait...')
				}).addClass('please_wait');
				if (button.hasClass('previous')) button.addClass('hide');
				loader.css({
					display: 'inline-block'
				});
			} else if (button.hasClass('please_wait')) {
				button.val(button.attr('data-value')).attr({
					'disabled': false
				}).removeClass('please_wait');
				if (button.hasClass('previous')) button.removeClass('hide');
				loader.hide();
			}

			$('input[type="submit"]', formObj).each(function () {
				var submit = $(this);
				if (!submit.hasClass('please_wait') || force) {
					submit.attr({
						'data-value': ccmFormidableTranslate('Please wait...') == submit.val()?submit.attr('data-value'):submit.val(),
						'disabled': true,
						value: ccmFormidableTranslate('Please wait...')
					}).addClass('please_wait');
					loader.css({
						display: 'inline-block'
					});
				} else if (submit.hasClass('please_wait')) {
					submit.val(submit.attr('data-value')).attr({
						'disabled': false
					}).removeClass('please_wait');
					loader.hide();
				}
			});
		};

		var setup_options = function () {
			$('select', formObj).find('option[value="option_other"]:selected').each(function () {
				$(this).closest('.element').find('div.option_other').slideDown();
			});
			$('select', formObj).change(function () {
				if ($(this).find('option[value="option_other"]:selected').length > 0) $(this).closest('.element').find('div.option_other').slideDown();
				else $(this).closest('.element').find('div.option_other').slideUp();
			});

			$('input[value="option_other"]:checked', formObj).each(function () {
				$(this).closest('.element').find('div.option_other').slideDown();
			});
			$('input[type=radio]', formObj).click(function () {
				if ($(this).val() == 'option_other') $(this).closest('.element').find('div.option_other').slideDown();
				else $(this).closest('.element').find('div.option_other').slideUp();
			});

			$('input[type="checkbox"]', formObj).click(function () {
				var closed = true;
				$(this).closest('.element').find('input[type="checkbox"]:checked').each(function () {
					if ($(this).val() == 'option_other') closed = false;
				});
				if (!closed) $(this).closest('.element').find('div.option_other').slideDown();
				else $(this).closest('.element').find('div.option_other').slideUp();
			});
		};

		var clear_errors = function () {
			if (!options.error_messages_animate_remove) $('span.error', formObj).remove();
			else {
				$('span.error', formObj).css('opacity', 1).animate({
					'opacity': 0
				}, 200, function () {
					$(this).remove();
				});
			}
			$('.error:not(span), .has-error', formObj).removeClass('has-error error');
		};

		var clear_error = function (element) {
			if (!options.error_messages_animate_remove) element.removeClass('error').closest('.element').removeClass('has-error').find('span.error').remove()
			else {
				element.removeClass('error').closest('.element').removeClass('has-error').find('span.error').css('opacity', 1).animate({
					'opacity': 0
				}, 200, function () {
					$(this).remove();
				});
			}
		};

		var message = function (message, type) {
			var holder = $('div[id="formidable_message_' + formID + '"]');
			if (holder.length <= 0) holder = formObj.before($('<div>').attr('id', 'formidable_message_' + formID));

			if (typeof message != 'undefined' && message.length > 0) {
				if (typeof message == 'object') {
					var temp = $('<div>');
					$.each(message, function (i, row) {
						temp.append($('<div>').html(row));
					});
					message = temp.html();
				}
				if (type == 'success') holder.addClass(options.success_messages_class).removeClass('hide');
				else if (type == 'warning') holder.addClass(options.warning_messages_class).removeClass('hide');
				else if (type == 'error') {
					if (options.error_messages_on_top) {
						holder.addClass(options.error_messages_on_top_class).removeClass('hide');
					}
				}
				holder.html(message);
			} else holder.removeAttr('class').addClass('formidable_message hide').html('');
		};

		var recaptcha = function () {
			var imgObj = $('img.ccm-captcha-image', formObj);
			if (imgObj.length > 0) imgObj.trigger('click');
			$('input[name=ccmCaptchaCode]', formObj).val('');
		};

		var toggle_captcha = function () {
			var holder = $('div.captcha_holder', formObj);
			$('div.captcha_image, div.captcha_input', holder).remove();
			$('div.captcha_done', holder).show();
		};

		var scroll = function () {
			if (formContainer.length > 0 && formContainer.height() > 0) {
				var window_height = $(window).height();
				var scroll_position = $(window).scrollTop();
				var element_position = formContainer.position().top;
				var element_height = formContainer.height();
				if (((element_position < scroll_position) || ((scroll_position + window_height) < element_position + element_height))) {
					$('html, body').animate({
						scrollTop: formContainer.offset().top
					}, 'slow');
				}
			}
		};

		var signature = function() {
			var signatureObj = $('div.signature-holder', formObj);
			if (signatureObj.length > 0) {
				signatureObj.each(function() {

					var wrap = $(this);
					var signature = $('div.signature', wrap);
					var clear = $('[data-signature="clear"]', wrap);
					var textarea = $('textarea', wrap);

					var options = $.extend({
						'width': 'ratio',
						'height': 'ratio',
						'sizeRatio': 2, // only used when height = 'ratio',
						'color': '#000',
						'background-color': '#fff',
						'decor-color': '#eee',
						'lineWidth': 0,
						'minFatFingerCompensation': -10,
						'showUndoButton': false,
						'readOnly': false,
						'data': [],
						'signatureLine': false,
						'afterload': function() {
							// Do something weird when using stepify.
							// We need to change the height of the step after loading the signature.
							step_animate();
						}
					}, JSON.parse('{'+wrap.data('options').replace(/\'/g, '"')+'}'));

					var signature = signature.jSignature(options).on('change', function() {
						textarea.text("data:" + $(this).jSignature("getData", "svgbase64").join(",") );
					});

					// Add image if already there!
					// Disabled for now. It's not nice to edit a signature...
					// if (textarea.text().length > 0)	signature.jSignature("setData", textarea.text());

					clear.on('click', function (event) {
						if (!signatureObj.is(':disabled')) {
							signature.jSignature('reset');
							textarea.text('');
						}
					});
				});
			}
		}

		var address_select_country = function (cls, country) {
			var ss = $('select[id="' + cls + '[province]"]', formObj);
			var si = $('input[id="' + cls + '[province]"]', formObj);

			var foundStateList = false;
			ss.html("");
			if (ccmFormidableAddressStates) {
				for (var j = 0; j < ccmFormidableAddressStates.length; j++) {
					var sa = ccmFormidableAddressStates[j].split(':');
					if ($.trim(sa[0]) == country) {
						if (!foundStateList) {
							foundStateList = true;
							si.attr('name', 'inactive_' + si.attr('ccm-attribute-address-field-name'));
							si.hide();
							ss.append('<option value="">' + ccmFormidableTranslate('Choose State/Province') + '</option>');
						}
						ss.show();
						ss.attr('name', si.attr('ccm-attribute-address-field-name'));
						ss.append('<option value="' + $.trim(sa[1]) + '">' + $.trim(sa[2]) + '</option>');
					}
				}
				if (ss.attr('ccm-passed-value') != '') {
					$(function () {
						ss.find('option[value="' + ss.attr('ccm-passed-value') + '"]').attr('selected', true);
					});
				}
			}
			if (!foundStateList || ss.length <= 0) {
				ss.attr('name', 'inactive_' + si.attr('ccm-attribute-address-field-name'));
				ss.hide();
				si.show();
				si.attr('name', si.attr('ccm-attribute-address-field-name'));
			}
		};

		var setup_state_province_selector = function (cls) {
			var cs = $('select[id="' + cls + '[country]"]', formObj);
			cs.change(function () {
				var v = $(this).val();
				address_select_country(cls, v);
			});
			if (cs.attr('ccm-passed-value') != '') {
				$(function () {
					cs.find('option[value="' + cs.attr('ccm-passed-value') + '"]').attr('selected', true);
					address_select_country(cls, cs.attr('ccm-passed-value'));
					var ss = $('select[id="' + cls + '[province]"]');
					if (ss.attr('ccm-passed-value') != '') {
						ss.find('option[value="' + ss.attr('ccm-passed-value') + '"]').attr('selected', true);
					}
				});
			}
			address_select_country(cls, '');
		};

		var check_uploads = function () {
			if ($('.file_upload[data-progress!="done"]').length > 0) loading(true);
			else loading();
		};

		var add_dependency = function (method) {
			dependencyRules.push(method);
		};

		var check_depencencies = function () {
			for (var i = 0; i < dependencyRules.length; i++) {
				dependencyRules[i](formObj);
			}
		};

		var do_dependency = function (selector, args) {
			var eObj = selector;
			if (!(eObj instanceof jQuery)) {
				eObj = $('[name="' + selector + '"]', formObj);
				if (!eObj.length) eObj = $('[name^="' + selector + '["]', formObj);
				if (!eObj.length) eObj = $('[id="' + selector + '"]', formObj);
				if (eObj.length <= 0) return false;
			}

			var tagName = eObj.get(0).tagName.toLowerCase();
			var typeName = eObj.attr('type');
			var current = eObj.closest('div[data-step]');

			for (var i = 0; i < args.length; i++) {
				switch (args[i][0]) {
					case 'disable':
						eObj.attr('disabled', true);
						break;
					case 'enable':
						eObj.attr('disabled', false);
						break;
					case 'show':
						var obj = eObj.closest('.element');
						if (obj.length <= 0) obj = eObj;
						obj.addClass('ff-show').slideDown();
						break;
					case 'hide':
						var obj = eObj.closest('.element');
						if (obj.length <= 0) obj = eObj;
						obj.removeClass('ff-show').slideUp();
						break;
					case 'value':
						if (tagName == 'input' || tagName == 'textarea' || tagName == 'select') {
							if (typeName == 'checkbox' || typeName == 'radio') {
								var _argument = args[i][1];
								eObj.each(function (j, eObjItem) {
									if ($(eObjItem).val() == _argument) $(eObjItem).attr('checked', false).trigger('click');
								});
							} else eObj.val(args[i][1]).trigger('change');
						}
						break;
					case 'class':
						eObj.removeClass(args[i][1]);
						if (args[i][2] == 'add') eObj.addClass(args[i][1]);
						break;
					case 'placeholder':
						eObj.removeAttr('placeholder');
						if (args[i][2] == 'add') eObj.attr('placeholder', args[i][1]);
						break;
				}
			}
			step_animate();
		};

		var stepify = function () {
			var steps = $('div[data-step]', formObj).length;
			if (steps <= 0) $('input[id="step"]', formObj).remove();
			else {
				steps = true;

				if (!options.step_progress_bar && $(options.step_progress_bar_selector, formContainer).length > 0) {
					$(options.step_progress_bar_selector, formContainer).remove();
				}

				$('div[data-step]').each(function (i, row) {
					$(this).attr('data-step', i);
				});
				$('div[data-step]:first', formObj).show().addClass('step-active').find('input[type="button"].previous').remove();
				$('div[data-step] input[type="button"].previous', formObj).on('click', function () {
					step_back($(this));
				});
				$('div[data-step] input[type="submit"].next', formObj).on('click', function (e) {
					e.preventDefault();
					step_next($(this));
				});
				step_animate();
			}
		};

		var step_back = function back_to(obj) {
			var current = obj.closest('div[data-step]');
			var prev = current.prev('div[data-step]');

			if (!options.animate_step) {
				current.hide().removeClass('step-active');
				prev.show().addClass('step-active');
			} else {
				prev.show().addClass('step-active');
				//hide the current fieldset with style
				current.animate({
					opacity: 0
				}, {
					start: function() {
						step_animate();
					},
					step: function (now, mx) {
						//as the opacity of current reduces to 0 - stored in "now"
						//1. scale prev from 80% to 100%
						var scale = 0.8 + (1 - now) * 0.2;
						//2. take current to the right(50%) - from 0%
						var left = ((1 - now) * 50) + "%";
						//3. increase opacity of prev to 1 as it moves in
						var opacity = 1 - now;
						current.css({
							'left': left
						});
						prev.css({
							'transform': 'scale(' + scale + ')',
							'opacity': opacity
						});
					},
					duration: options.animate_step_duration,
					complete: function () {
						current.hide().css({
							'left': ''
						}).removeClass('step-active');
					},
					//this comes from the custom easing plugin
					easing: options.animate_step_easing
				});
			}
			$('input[id="step"]', formObj).val(prev.data('step'));
			step_progress(prev.data('step'));
		};

		var step_next = function (obj) {
			var current = obj.closest('div[data-step]');
			var next = current.next('div[data-step]');
			step_next_process(obj);
		};

		var step_next_process = function (obj) {

			// Check if this method is already called some other way
			if (called) return false;
			called = true;

			var current = obj.closest('div[data-step]');
			var next = current.next('div[data-step]');

			// Add this element for the controller
			$('input[id="action"]', formObj).remove();
			formObj.append($('<input>').attr({
				'name': 'action',
				'id': 'action',
				'value': 'step',
				'type': 'hidden'
			}));

			// Start submission
			var data = formObj.serialize();
			$.ajax({
				type: "POST",
				url: formObj.attr('action'),
				data: data,
				dataType: 'json',
				beforeSend: function () {
					loading();
					message();
					clear_errors();
				},
				success: function (data) {
					// Show message if needed
					if (data.message && data.message.length > 0) {
						message(data.message, 'warning');
					}
					// Errors on submission
					else if (data.errors && data.errors.length > 0) {
						var errors = [];
						$.each(data.errors, function (i, row) {
							// Show error message beneath field.
							error_on_field(row.handle, row.message, function () {
								step_animate();
							});
							errors.push(row.message);
						});
						message(errors, 'error');

						// If captcha is on this step, toggle it if it's OK
						if (data.captcha) toggle_captcha();
						recaptcha();

						step_animate();

						if (!!options.errorCallback && jQuery.isFunction(options.errorCallback)) {
							options.errorCallback.call(formObj, data);
						}
					}
					// On success
					else if (data.success) {
						// If captcha is on this step, toggle it if it's OK
						if (data.captcha) toggle_captcha();

						// No next step? Do submit for real!
						if (next.length <= 0) {
							called = false;
							submit('submit');
						}
						else {
							if (!options.animate_step) {
								next.show().addClass('step-active');
								current.hide().removeClass('step-active');
							}
							else {
								next.show().addClass('step-active');
								current.animate({
									opacity: 0
								}, {
									start: function() {
										step_animate();
									},
									step: function (now, mx) {
										//as the opacity of current_fs reduces to 0 - stored in "now"
										//1. scale current_fs down to 80%
										var scale = 1 - (1 - now) * 0.2;
										//2. bring next from the right(50%)
										var left = (now * 50) + "%";
										//3. increase opacity of next to 1 as it moves in
										var opacity = 1 - now;

										current.css({
											'transform': 'scale(' + scale + ')',
											'position': 'absolute'
										});
										next.css({
											'left': left,
											'opacity': opacity
										});
									},
									duration: options.animate_step_duration,
									complete: function () {
										current.hide().removeClass('step-active');
										next.css({
											'left': ''
										});
									},
									//this comes from the custom easing plugin
									easing: options.animate_step_easing
								});
							}
							$('input[id="step"]', formObj).val(next.data('step'));
							step_progress(next.data('step'));
						}
					}
					loading();
					scroll();
					called = false;
				}
			});
			return false;
		};

		var step_progress = function (step) {
			if (options.step_progress_bar && $(options.step_progress_bar_selector, formContainer).length > 0) {
				$(options.step_progress_bar_selector, formContainer).children().removeClass('active').filter(':lt(' + (step + 1) + ')').addClass("active");
			}
		};

		var step_animate = function () {
			if ($('div[data-step]').length <= 0) return true;
			if (!options.animate_step) {
				var height = $('div[data-step].step-active', formObj).outerHeight();
				formObj.height(height);
			}
			else {
				clearInterval(animation);
				animation = setInterval(function() {
					$(window).trigger('resize');
					var height = $('div[data-step].step-active', formObj).outerHeight();

					formObj.animate({
						'height': height
					}, {
						duration: options.animate_step_duration,
						queue: false,
						easing: 'easeOutExpo',
						step: function () {
							formObj.css("overflow", "visible");
						}
					});

				}, 400);
			}
			return true;
		};

		var error_on_field = function (handle, message, callback) {
			if (options.error_messages_beneath_field) {
				var eObj = $('[id="' + handle + '"],[name="' + handle + '[]"],[name^="' + handle + '["]:last', formObj).eq(0);
				if (eObj.length > 0) {
					$('[id="' + handle + '"],[id="' + handle + '_confirm"],[name="' + handle + '[]"],[name^="' + handle + '["]', formObj).addClass('error');
					var error = $('<span>').addClass(options.error_messages_beneath_field_class).text(message);
					$(eObj).closest('div.input').append(error.css('opacity', 0).animate({
						'opacity': 1
					}, {
						duration: 300,
						complete: callback
					})).addClass('has-error');
				}
			}
		};

		initialize();

		return {
			'add_dependency': add_dependency,
			'check_depencencies': check_depencencies,
			'do_dependency': do_dependency,
			'check_uploads': check_uploads,
		};
	};
})(jQuery);
