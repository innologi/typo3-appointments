/**
 * Appointments Javascript Library
 * -----
 * jQuery dependent.
 * Yes, I know of $.
 * @TODO zie main.js van assumburg
 * Yes, I know of noConflict().
 * -----
 * @author Frenck Lutke <http://frencklutke.nl/>
 */

jQuery(document).ready(function() {
	
	//general vars
	var scriptStartTime = new Date().getTime() / 1000;
	// @TODO var formELem = $('.tx-appointments form'); ?
	
	
	//*********************
	// delete confirmation
	//*********************
	
	var confirmDeleteMessage = "###DELETE_CONFIRM###";
	
	//click function performs a confirm, if TRUE/OK continues button functionality
	jQuery('.tx-appointments .button_delete').click(function() {
		return confirm(confirmDeleteMessage);
	});
	
	
	//****************
	// unload warning
	//****************
	
	var warnUnloadEnabled = "###WARN_ON_LEAVE###";
	var warnUnloadText = "###WARN_UNLOAD###";
	//respect REFRESH vars
	var secondsUntilRefresh = null;
	var respectMethod = null;
	
	//gets (int)seconds from a REFRESH string
	function getRefreshSeconds(refreshString) {
		return parseInt(refreshString.substring(0, refreshString.indexOf(';',0)),10); 
	}
	
	//checks if the amount of REFRESH seconds have passed, to see if warnUnload needs to be disabled
	function haveRefreshSecondsPassed() {
		var currentTime = new Date().getTime() / 1000;
		var secondsSinceStart = Math.round(currentTime - scriptStartTime);
		if (secondsSinceStart >= secondsUntilRefresh) {
			warnUnloadText = ''; //disable message
		}
	}
	
	//run through the warnUnload script
	if (warnUnloadEnabled == '1') { //if warnUnload-enabled in TypoScript
		var warnUnloadElem = jQuery('.tx-appointments form.warnUnload');
		if (warnUnloadElem[0]) { //if a warnUnload element is available 
			if (warnUnloadText.length) { //if warnUnloadText is empty, there's no sense in continuing
				//set exception-button text in warnUnloadText
				warnUnloadText = warnUnloadText.replace('$1',"###WARN_UNLOAD_S1###");
				//check respect REFRESH settings and read seconds
				respectMethod = "###WARN_ON_LEAVE_RESPECT_REFRESH###";
				if (respectMethod == 'meta-tag') { //read from meta tag
					var metaElem = jQuery('meta[http-equiv=REFRESH]'); //e.g. <meta http-equiv="REFRESH" content="1800; URL={url}" />
					if (metaElem[0]) { //if there is a meta tag
						secondsUntilRefresh = getRefreshSeconds(metaElem.attr('content'));
					}
				} else if (respectMethod == 'header') { //read from header
					//prepare a new HEAD request in order to be able to read the response REFRESH header
					var jqxhr = jQuery.ajax({type:'HEAD'}).done(function() {
						var refreshHeader = jqxhr.getResponseHeader('REFRESH'); //e.g. REFRESH: 1800;URL={url}
						if (refreshHeader != null && refreshHeader.length) {
							secondsUntilRefresh = getRefreshSeconds(refreshHeader);
						}
					});
				} 
				
				// @TODO warning: TypeError: anonymous function does not always return a value
				//set the actual onbeforeunload event
				window.onbeforeunload = function() {
					//before calling the message, check if respect REFRESH needs to disable it
					if (secondsUntilRefresh != null) {
						haveRefreshSecondsPassed(); //disables warnUnload if REFRESH seconds passed
					}
					
					//calls message if not disabled
					if (warnUnloadText.length) {
						return warnUnloadText;
					}
				};
				
				//exceptions to warnUnload event
				jQuery('.tx-appointments .allowUnload').on('submit', function() {
						warnUnloadText = ''; //disable message 
				});
			}
		}
	}
	
	
	//**********
	// tooltips
	//**********
	
	//hovering over the csh element will replace the normal tooltip with ours
	jQuery('.tx-appointments .csh').hover(
		function() {
			appear = true;
			if (jQuery(this).next().is('.tx-appointments span.tooltip') == false) {
				tooltip = jQuery(this).attr('title');
				if (tooltip) {
					jQuery(this).data('title',tooltip);
					//we rely on animate() to fade in, so we have to set display and opacity in js
					jQuery(this).after(jQuery('<span class="tooltip" style="display:none;opacity:0;"/>').text(tooltip));
				} else {
					appear = false;
				}
				
			}
			
			if (appear) {
				//removes the actual tooltip
				jQuery(this).prop('title', ''); //need to use prop() because removeAttr() doesn't work well in IE (7,8,9)
				//fadeToggle() and fadeIn() touch the display style property, hence animate()
				jQuery(this).next().css('display','inline-block').animate({'opacity':0.85},150);
			}
		},
		function() {
			elem = jQuery(this).next();
			if (elem.is('.tx-appointments span.tooltip')) {
				//fadeOut() doesn't change the final opacity style edited by animate(), so again animate()
				jQuery(elem).animate({'opacity':0},150, function() {
					jQuery(this).css('display','none');
				});
				jQuery(this).prop('title',jQuery(this).data('title')); //returns the title attribute to its original value
			}
		}
	);
	
	
	//***************
	// UI Datepicker
	//***************
	
	//enable jQuery UI datepicker
	jQuery('.tx-appointments form .datepicker').datepicker({
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
	jQuery('.tx-appointments form .datepicker.max-today').datepicker('option','maxDate','0');
	
	
	//*****************************
	// Reservation Timer Countdown
	//*****************************
	
	var timerElem = jQuery('.tx-appointments span.reservation-timer');
	var timerSeconds = 0;
	var timerRemaining = 0;
	var flashStart = 60;
	var flashSet = false;
	var counter = null;
	
	//countdown timer-html-setter function
	function reservationTimer() {
		//timerSeconds--; //this isn't representative if an alert or warnunload box comes along
		var currentTime = new Date().getTime() / 1000;
		var secondsSinceStart = Math.round(currentTime - scriptStartTime);
		timerRemaining = timerSeconds - secondsSinceStart;
		
		if (!flashSet && timerRemaining <= flashStart) {
			timerElem.addClass('flash'); //starts flashing (or marking) the timer according to class css
			flashSet = true;
		} else if (timerRemaining < 1) {
			clearInterval(counter); //stop counter
			replaceTimerMessage();
			replaceTimeSlotButton();
			return;
		}
		
		var displayMin = Math.floor(timerRemaining / 60);
		var displaySec = '0' + (timerRemaining % 60); //remainder of seconds by modulus
		//set new inner html
		timerElem.html(displayMin + ':' + displaySec.slice(-2)); //only show the last 2 numbers of seconds
	}
	
	//replace timer message
	function replaceTimerMessage() {
		var body = timerElem.parent('.tx-appointments .typo3-message .message-body');
		if (body[0]) {
			var head = body.prev('.tx-appointments .typo3-message .message-header');
			var container = body.parent('.tx-appointments .typo3-message');
			if (head[0] && container[0]) {
				//replace texts
				body.html("###TIMER_ZERO###");
				head.html("###TIMER_ZERO_HEAD###");
				//replace box class
				container.addClass('message-warning');
				container.removeClass('message-information');
			}
		}
	}
	
	//replace timeslot button
	function replaceTimeSlotButton() {
		var freeTimeButton = jQuery('.tx-appointments form #appointments-submit-time');
		if (freeTimeButton[0]) {
			freeTimeButton.val("###RENEW_TIME###");
			freeTimeButton.addClass('attention');
		}
	}
	
	//start timer countdown if a timer exists
	if (timerElem[0]) {
		//calculate timer variables
		var timerVal = timerElem.html();
		var splitAt = timerVal.indexOf(':',0);
		var minutes = parseInt(timerVal.substring(0, splitAt),10);
		timerSeconds = (minutes * 60) + parseInt(timerVal.substring(splitAt+1),10);
		if (timerSeconds <= flashStart) {
			timerElem.addClass('flash');
			flashSet = true;
		}
		//run every second (in milliseconds)
		counter = setInterval(reservationTimer, 1000);
	}

	
	//***********************
	// Change submit buttons
	//***********************
	
	jQuery('.tx-appointments form #appointments-select-type').change(function() {
		jQuery('.tx-appointments form #appointments-submit-type').addClass('attention');
	});
	
	jQuery('.tx-appointments form #appointments-select-date').change(function() {
		jQuery('.tx-appointments form #appointments-submit-date').addClass('attention');
	});
	
	jQuery('.tx-appointments form #appointments-select-time').change(function() {
		jQuery('.tx-appointments form #appointments-submit-time').addClass('attention');
	});
	
	
	//*********************
	// The "Disabled" Form
	//*********************

	jQuery('.tx-appointments #disabledForm :input').prop('disabled', true);
	jQuery('.tx-appointments #disabledForm').addClass('visible');


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
	function storeValueInSession(e) {
		sessionStorage.setItem(
			e.id,
			e.value
		);
	}
	
	//populates the form fields from session values
	function getFormStorage() {
		var storage = window['sessionStorage'];
		//retrieve all ids of session-marked form elements
		var fields = jQuery('.tx-appointments form .session').map(function(index) {
			return this.id;
		}).get();
		
		for (var i in fields) {
			var id = fields[i];
			if (storage.getItem(id)) { //checks if there is a session value for the id
				var elemObj = jQuery('.tx-appointments form #' + id);
				var val = storage.getItem(id);
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
		getFormStorage();

		//add storage events
		jQuery('.tx-appointments form textarea.session').keyup(function() {
			storeValueInSession(this);
		});
		jQuery('.tx-appointments form .textinput.session').keyup(function() {
			storeValueInSession(this);
		});
		//usage of datepicker can happen without keyup events
		jQuery('.tx-appointments form .datepicker.session').change(function() {
			storeValueInSession(this);
		});
		jQuery('.tx-appointments form .select.session').change(function() {
			storeValueInSession(this);
		});
		// we look at checked instead of value @ radio / checkboxes
		jQuery('.tx-appointments form .radio.session').change(function() {
			sessionStorage.setItem(
				this.id,
				this.checked
			);
			// radio only triggers onchange if enabled, but enabling one disables others
			jQuery(this).siblings('.radio.session[name="' + jQuery(this).attr('name') + '"]').each(function(i, radio) {
				sessionStorage.setItem(
					radio.id,
					radio.checked
				);
			});
		});
		jQuery('.tx-appointments form .checkbox.session').change(function() {
			sessionStorage.setItem(
				this.id,
				this.checked
			);
		});
		
		//clicking the new and edit back links should all clear the session (also works on tab/enter).
		jQuery('.tx-appointments .button_new').click(function() {
			sessionStorage.clear();
		});
		jQuery('.tx-appointments .button_new_datefirst').click(function() {
			sessionStorage.clear();
		});
		jQuery('.tx-appointments .button_edit').click(function() {
			sessionStorage.clear();
		});
		//doing it on form submit can cause us to lose values if a validation error won't save anything,
		//or even worse, a user can stop halfway, and start a new appointment, without checking if everything
		//is in order because the form was filled with previous values. doing it on the back button can cause
		//us to lose the session even when someone decides to stay on the page.
	}

	
	//**********************************************
	// Enable Field detection & add change triggers
	//**********************************************

	//set events on enablers as configured by fields that are to be enabled
	var fieldEnablers = {};
	jQuery('.tx-appointments form .enablefield').each(function(i, elem) {
		var classes = jQuery(elem).attr('class');
		var evalPos = classes.indexOf('eval-f') + 6;
		var evalParts = classes.slice(evalPos).split('-');
		var uid = evalParts[0];
		// the split here ensures correct value regardless if more classes follow
		var desiredValue = evalParts[1].split(' ')[0].toLowerCase();
		
		var enablerObj = null;
		// check if the enabler was already processed
		if (fieldEnablers[uid] !== undefined) {
			enablerObj = fieldEnablers[uid];
		} else {
			// new enabler
			var enablerObj = jQuery('.tx-appointments form .formfield-id-' + uid);
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
			var fields = jQuery(enablerObj[0]).data('enable-fields');
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
		if (jQuery(elem).attr('required') === 'required') {
			required.push(elem);
		} else {
			/*
			 * if the current element doesn't have any set, then perhaps it is a wrapper
			 * CONTAINING elements that possibly have a required attribute set
			 */
			jQuery(elem).find('[required="required"]').each(function(i, reqElem) {
				required.push(reqElem);
			});
		}
		return required;
	}
	
	// checks if a field{element,enableValue,required} should be shown or hidden
	function checkEnableField(field, currentValue, active) {
		var elemObj = jQuery(field.element);
		if (active && currentValue === field.enableValue) {
			jQuery(field.required).attr('required','required');
			elemObj.show();
		} else {
			elemObj.hide();
			jQuery(field.required).removeAttr('required');
		}
	}
	
	function runChecks(elem, active) {
		var newValue = jQuery(elem).val().toLowerCase();
		// get fields to check from data store
		var fields = jQuery(elem).data('enable-fields');
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
	
});