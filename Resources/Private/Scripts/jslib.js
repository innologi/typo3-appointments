/**
 * Appointments Javascript Library
 * -----
 * jQuery dependent.

 * @author Frenck Lutke <typo3@innologi.nl>
 */
(function($) {

	//general vars
	var scriptStartTime = new Date().getTime() / 1000,
		$container = $('.tx-appointments'),
		$forms = $('form', $container);


	//*********************
	// delete confirmation
	//*********************

	var confirmDeleteMessage = "###DELETE_CONFIRM###";

	//click function performs a confirm, if TRUE/OK continues button functionality
	$('.button_delete', $container).click(function(event) {
		if(confirm(confirmDeleteMessage)) {
			return true;
		} else {
			event.stopImmediatePropagation();
			return false;
		};
	});


	//****************
	// unload warning
	//****************

	var warnUnloadEnabled = "###WARN_ON_LEAVE###",
		warnUnloadText = "###WARN_UNLOAD###",
		//respect REFRESH vars
		secondsUntilRefresh = null,
		respectMethod = null;

	//gets (int)seconds from a REFRESH string
	function getRefreshSeconds(refreshString) {
		return parseInt(refreshString.substring(0, refreshString.indexOf(';',0)),10);
	}

	//checks if the amount of REFRESH seconds have passed, to see if warnUnload needs to be disabled
	function haveRefreshSecondsPassed() {
		var currentTime = new Date().getTime() / 1000,
			secondsSinceStart = Math.round(currentTime - scriptStartTime);
		if (secondsSinceStart >= secondsUntilRefresh) {
			warnUnloadText = ''; //disable message
		}
	}

	//run through the warnUnload script
	if (warnUnloadEnabled === '1') { //if warnUnload-enabled in TypoScript
		var warnUnloadElem = $('form.warnUnload', $container);
		if (warnUnloadElem[0]) { //if a warnUnload element is available
			if (warnUnloadText.length) { //if warnUnloadText is empty, there's no sense in continuing
				//set exception-button text in warnUnloadText
				warnUnloadText = warnUnloadText.replace('$1',"###WARN_UNLOAD_S1###");
				//check respect REFRESH settings and read seconds
				respectMethod = "###WARN_ON_LEAVE_RESPECT_REFRESH###";
				if (respectMethod == 'meta-tag') { //read from meta tag
					var metaElem = $('meta[http-equiv=REFRESH]'); //e.g. <meta http-equiv="REFRESH" content="1800; URL={url}" />
					if (metaElem[0]) { //if there is a meta tag
						secondsUntilRefresh = getRefreshSeconds(metaElem.attr('content'));
					}
				} else if (respectMethod == 'header') { //read from header
					//prepare a new HEAD request in order to be able to read the response REFRESH header
					var jqxhr = $.ajax({type:'HEAD'}).done(function() {
						var refreshHeader = jqxhr.getResponseHeader('REFRESH'); //e.g. REFRESH: 1800;URL={url}
						if (refreshHeader != null && refreshHeader.length) {
							secondsUntilRefresh = getRefreshSeconds(refreshHeader);
						}
					});
				}

				//set the actual onbeforeunload event
				window.addEventListener('beforeunload', function (e) {
					//before calling the message, check if respect REFRESH needs to disable it
					if (secondsUntilRefresh != null) {
						haveRefreshSecondsPassed(); //disables warnUnload if REFRESH seconds passed
					}

					//calls message if not disabled
					if (warnUnloadText.length) {
						e.returnValue = warnUnloadText; // Gecko, Trident, Chrome 34+
						return warnUnloadText; // Gecko, WebKit, Chrome <34
					}
				});

				//exceptions to warnUnload event
				$('.allowUnload', $container).on('submit', function() {
						warnUnloadText = ''; //disable message
				});
			}
		}
	}


	//**********
	// tooltips
	//**********

	//hovering over the csh element will replace the normal tooltip with ours
	$('.csh', $container).hover(
		function() {
			appear = true;
			if ($(this).next().is('.tx-appointments span.tooltip') == false) {
				tooltip = $(this).attr('title');
				if (tooltip) {
					$(this).data('title',tooltip);
					//we rely on animate() to fade in, so we have to set display and opacity in js
					$(this).after($('<span class="tooltip" style="display:none;opacity:0;"/>').text(tooltip));
				} else {
					appear = false;
				}

			}

			if (appear) {
				//removes the actual tooltip
				$(this).prop('title', ''); //need to use prop() because removeAttr() doesn't work well in IE (7,8,9)
				//fadeToggle() and fadeIn() touch the display style property, hence animate()
				$(this).next().css('display','inline-block').animate({'opacity':0.85},150);
			}
		},
		function() {
			elem = $(this).next();
			if (elem.is('.tx-appointments span.tooltip')) {
				//fadeOut() doesn't change the final opacity style edited by animate(), so again animate()
				$(elem).animate({'opacity':0},150, function() {
					$(this).css('display','none');
				});
				$(this).prop('title',$(this).data('title')); //returns the title attribute to its original value
			}
		}
	);


	//***************
	// UI Datepicker
	//***************

	//enable jQuery UI datepicker
	$('.datepicker', $forms).datepicker({
		showOn: 'focus', //focus|button|both
		dateFormat: 'dd-mm-yy',
		changeMonth: true, //select box month
		changeYear: true, //select box year
		constrainInput: true, //only allows characters of dateFormat
		firstDay: 1, //start with monday
		hideIfNoPrevNext: true, //hide arrows if not available
		minDate: '-120y',
		yearRange: '-120y:+10',
		showOtherMonths: true, //shows days of adjacent months to fill out the table
		selectOtherMonths: true, //makes above days selectable
		showWeek: true,
		dayNamesMin: ["###DAY7###","###DAY1###","###DAY2###","###DAY3###","###DAY4###","###DAY5###","###DAY6###"],
		monthNamesShort: ["###MON1###","###MON2###","###MON3###","###MON4###","###MON5###","###MON6###","###MON7###","###MON8###","###MON9###","###MON10###","###MON11###","###MON12###"]
	});

	//sets max of datepicker to today if the field has class 'max-today'
	$('.datepicker.max-today', $forms).datepicker('option','maxDate','0');


	//*****************************
	// Reservation Timer Countdown
	//*****************************

	//countdown timer-html-setter function
	function reservationTimer(timer) {
		//timerSeconds--; //this isn't representative if an alert or warnunload box comes along
		var currentTime = new Date().getTime() / 1000,
			secondsSinceStart = Math.round(currentTime - scriptStartTime);
		timer.remaining = timer.seconds - secondsSinceStart;

		if (!timer.flashSet && timer.remaining <= timer.flashStart) {
			$(timer.element).addClass('flash'); //starts flashing (or marking) the timer according to class css
			timer.flashSet = true;
		} else if (timer.remaining < 1) {
			clearInterval(timer.counter); //stop counter
			if (replaceTimerMessage(timer)) {
				replaceTimeSlotButton();
				return;
			} else {
				// @TODO finish this
			}
		}

		var displayMin = Math.floor(timer.remaining / 60),
			displaySec = '0' + (timer.remaining % 60); //remainder of seconds by modulus
		//set new inner html
		$(timer.element).html(displayMin + ':' + displaySec.slice(-2)); //only show the last 2 numbers of seconds
	}

	//replace timer message
	function replaceTimerMessage(timer) {
		var body = $(timer.element).parent('.tx-appointments .typo3-messages .alert.alert-info .alert-message');
		if (body[0]) {
			var head = body.prev('.tx-appointments .typo3-messages .alert-title');
			var container = body.parent('.tx-appointments .typo3-messages .alert');
			if (head[0] && container[0]) {
				//replace texts
				body.html("###TIMER_ZERO###");
				head.html("###TIMER_ZERO_HEAD###");
				//replace box class
				container.addClass('alert-warning');
				container.removeClass('alert-info');
				return true;
			}
		}
		return false;
	}

	//replace timeslot button
	function replaceTimeSlotButton() {
		var freeTimeButton = $('#appointments-submit-time', $forms);
		if (freeTimeButton[0]) {
			freeTimeButton.val("###RENEW_TIME###");
			freeTimeButton.addClass('attention');
		}
	}

	// start timer countdown for each timer
	$('span.reservation-timer', $container).each(function() {
		var timer = {
			seconds: 0,
			remaining: 0,
			flashStart: 60,
			flashSet: false,
			counter: null,
			element: this
		},
			//calculate timer variables
			timerVal = $(this).html(),
			splitAt = timerVal.indexOf(':',0),
			minutes = parseInt(timerVal.substring(0, splitAt),10);

		timer.seconds = (minutes * 60) + parseInt(timerVal.substring(splitAt+1),10);
		if (timer.seconds <= timer.flashStart) {
			$(this).addClass('flash');
			timer.flashSet = true;
		}
		//run every second (in milliseconds)
		timer.counter = setInterval(reservationTimer, 1000, timer);
	});


	//***********************
	// Change submit buttons
	//***********************

	$('#appointments-select-type', $forms).change(function() {
		$('#appointments-submit-type', $(this).parents('.tx-appointments form')).addClass('attention');
	});

	$('#appointments-select-date', $forms).change(function() {
		$('#appointments-submit-date', $(this).parents('.tx-appointments form')).addClass('attention');
	});

	$('#appointments-select-time', $forms).change(function() {
		$('#appointments-submit-time', $(this).parents('.tx-appointments form')).addClass('attention');
	});


	//*********************
	// The "Disabled" Form
	//*********************

	var $disabledForms = $('.appointment-form.disabled', $container);
	$disabledForms.addClass('visible');
	$(':input', $disabledForms).prop('disabled', true);


	//**********************
	// Form session storage
	//**********************

	//check support for session storage in user-agent
	function isStorageSupported() {
		try {
			return 'sessionStorage' in window && window['sessionStorage'] !== null;
		} catch(e){
			return false;
		}
	}

	//default storage function
	function storeValueInSession(e, fId) {
		sessionStorage.setItem(
			fId + '_' + e.id,
			e.value
		);
	}

	//populates the form fields from session values
	function getFormStorage(form) {
		//retrieve all ids of session-marked form elements
		var fields = $('.session', form).map(function(index) {
			return this.id;
		}).get();

		for (var i in fields) {
			var id = fields[i],
				sId = form.id + '_' + id;
			if (sessionStorage.getItem(sId)) { //checks if there is a session value for the id
				var elemObj = $('#' + id, form),
					val = sessionStorage.getItem(sId);
				//checkboxes/radio work differently from all other fields
				if (elemObj.hasClass('checkbox') || elemObj.hasClass('radio')) {
					//note that val retrieved from sessionStorage is of type string, NOT boolean!
					if (val == 'true') {
						elemObj.prop('checked', true);
					} else {
						elemObj.prop('checked', false);
					}
				} else {
					elemObj.val(val);
				}
			}
		}
	}

	//run appropriate functions if supported
	if (isStorageSupported()) {
		//var storage = window['sessionStorage'];
		$('form.session', $container).each(function() {
			var formId = this.id;
			getFormStorage(this);

			//add storage events
			$('textarea.session', this).keyup(function() {
				storeValueInSession(this, formId);
			});
			$('.textinput.session', this).keyup(function() {
				storeValueInSession(this, formId);
			});
			//usage of datepicker can happen without keyup events
			$('.datepicker.session', this).change(function() {
				storeValueInSession(this, formId);
			});
			$('.select.session', this).change(function() {
				storeValueInSession(this, formId);
			});
			// we look at checked instead of value @ radio / checkboxes
			$('.radio.session', this).change(function() {
				sessionStorage.setItem(
					formId + '_' + this.id,
					this.checked
				);
				// radio only triggers onchange if enabled, but enabling one disables others
				$(this).siblings('.radio.session[name="' + $(this).attr('name') + '"]').each(function(i, radio) {
					sessionStorage.setItem(
						formId + '_' + radio.id,
						radio.checked
					);
				});
			});
			$('.checkbox.session', this).change(function() {
				sessionStorage.setItem(
					formId + '_' + this.id,
					this.checked
				);
			});

			// DISABLED BECAUSE OF CHANGES FOR RESUMING APPOINTMENT-CREATION
			//clicking the new and edit links should all clear the session (also works on tab/enter).
			/*$('.tx-appointments .button_new').click(function() {
				sessionStorage.clear();
			});
			$('.tx-appointments .button_new_datefirst').click(function() {
				sessionStorage.clear();
			});
			$('.tx-appointments .button_edit').click(function() {
				sessionStorage.clear();
			});*/
			//doing it on form submit can cause us to lose values if a validation error won't save anything,
			//or even worse, a user can stop halfway, and start a new appointment, without checking if everything
			//is in order because the form was filled with previous values. doing it on the back button can cause
			//us to lose the session even when someone decides to stay on the page.
		});
	}


	//**********************************************
	// Enable Field detection & add change triggers
	//**********************************************

	//set events on enablers as configured by fields that are to be enabled
	var fieldEnablers = {};
	$('.enablefield', $forms).each(function(i, elem) {
		var classes = $(elem).attr('class'),
			evalPos = classes.indexOf('eval-f') + 6,
			evalParts = classes.slice(evalPos).split('-'),
			uid = evalParts[0],
			// the split here ensures correct value regardless if more classes follow
			desiredValue = evalParts[1].split(' ')[0].toLowerCase(),
			enablerObj = null;

		// check if the enabler was already processed
		if (fieldEnablers[uid] !== undefined) {
			enablerObj = fieldEnablers[uid];
		} else {
			// new enabler
			enablerObj = $('.formfield-id-' + uid, $(elem).parents('.tx-appointments form'));
			if (enablerObj[0]) {
				// sets as processed, so it doesn't again for multiple fields
				fieldEnablers[uid] = enablerObj;
				// data store which will contain all fields it "enables"
				enablerObj.data('enable-fields',{});
				// add event
				if (enablerObj.hasClass('radio')) {
					// @TODO _____this limits to select, radio, and.. ? note that checkbox should only influence its own field. Perhaps if we can get that to work, we can apply the same logic to radio!
					enablerObj.change(function() {
						runChecks(this, this.checked);
					});
				} else {
					enablerObj.change(function() {
						runChecks(this, true);
					});
				}
			}
		};

		// if the enabler exists, add this field to its data store
		if (enablerObj[0]) {
			// get data from the first object
			var fields = $(enablerObj[0]).data('enable-fields');
			fields[i] = {
				element: elem,
				enableValue: desiredValue,
				required: getRequiredElements(elem)
			};
			enablerObj.data('enable-fields', fields);
		}
	});

	// retrieve elements set with 'required' attribute
	function getRequiredElements(elem) {
		var required = [];
		if ($(elem).attr('required') === 'required') {
			required.push(elem);
		} else {
			/*
			 * if the current element doesn't have any set, then perhaps it is a wrapper
			 * CONTAINING elements that possibly have a required attribute set
			 */
			$(elem).find('[required="required"]').each(function(i, reqElem) {
				required.push(reqElem);
			});
		}
		return required;
	}

	// checks if a field{element,enableValue,required} should be shown or hidden
	function checkEnableField(field, currentValue, active) {
		var elemObj = $(field.element);
		if (active && currentValue === field.enableValue) {
			$(field.required).attr('required','required');
			elemObj.show();
		} else {
			elemObj.hide();
			$(field.required).removeAttr('required');
		}
	}

	function runChecks(elem, active) {
		var newValue = $(elem).val().toLowerCase(),
		// get fields to check from data store
			fields = $(elem).data('enable-fields');
		for (var m in fields) {
			checkEnableField(fields[m], newValue, active);
		}
	}

	// initial check, should be AFTER sessionStorage calls!
	for (var i in fieldEnablers) {
		/*
		 * unfortunately, radio needs a special init because normally
		 * ALL buttons would get called and pass their checked-state
		 * to ALL fields. This way, only the last would influence the
		 * outcome.
		 */
		if (fieldEnablers[i].hasClass('radio')) {
			var checked = false;
			fieldEnablers[i].each(function(l, enabler) {
				if (this.checked) {
					runChecks(enabler, true);
					checked = true;
					// break loop
					return false;
				}
			});
			if (!checked) {
				runChecks(fieldEnablers[i][0], false);
			}
		} else {
			fieldEnablers[i].change();
		}
	}

})(jQuery);