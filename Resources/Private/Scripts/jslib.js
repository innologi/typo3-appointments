/**
 * Appointments Javascript Library
 * -----
 * jQuery dependent.
 * Yes, I know of $.
 * Yes, I know of noConflict().
 * -----
 * @author Frenck Lutke <http://frencklutke.nl/>
 */

jQuery(document).ready(function() {
	
	//********************
	// confirmation boxes
	//********************
	
	//:first is necessary because there could be multiple delete buttons, yet it's all the same
	var confirmDeleteMessage = jQuery('.tx-appointments .delete_confirm_text:first').text();
	
	//click function performs a confirm, if TRUE/OK continues button functionality
	jQuery('.tx-appointments .button_delete').click(function() {
		return confirm(confirmDeleteMessage);
	});
	
	
	//****************
	// unload warning
	//****************
	
	var warnUnload = null;
	var warnUnloadElem = jQuery('.tx-appointments form#appointment span.warnUnload');
	//'respect REFRESH header!' variables
	var sessionStart = new Date().getTime() / 1000;
	var header = null;
	
	//set onbeforeunload if a warnUnload message exists
	if (warnUnloadElem[0]) {
		warnUnload = warnUnloadElem.text();
		if (warnUnload != null && warnUnload.length) {
			//set function
			window.onbeforeunload = function() {
				//perform a 'respect REFRESH header!' check
				if (header != null) {
					var currentTime = new Date().getTime() / 1000;
					var seconds = Math.round(currentTime - sessionStart);
					if (seconds >= header) {
						warnUnload = ''; //unset the message
					}
				}
				
				if (warnUnload.length) {
					return warnUnload;
				}
			};
			
			//prepare 'respect REFRESH header!'
			var req = new XMLHttpRequest();
			req.open('GET', document.location, false); //note that this produces a second GET request, so it's rather inefficient.. can we make it optional?
			req.send(null);
			header = req.getResponseHeader('REFRESH');
			header = (header != null && header.length) ? parseInt(header.substring(0, header.indexOf(';',0)),10) : null;
		}
	}
	
	
	//exceptions
	jQuery('.tx-appointments .allowUnload').on('submit', function() {
			warnUnload = ''; //unset the message
	});
	
	
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
	jQuery('.tx-appointments .datepicker').datepicker({
		showOn: 'focus', //focus|button|both
		dateFormat: 'dd-mm-yy',
		changeMonth: true, //select box month
		changeYear: true, //select box year
		constrainInput: true, //only allows characters of dateFormat
		firstDay: 1, //start with monday
		hideIfNoPrevNext: true, //hide arrows if not available
		minDate: '-120y',
		showOtherMonths: true, //shows days of adjacent months to fill out the table
		selectOtherMonths: true, //makes above days selectable
		showWeek: true,
		dayNamesMin: ['###DAY7###','###DAY1###','###DAY2###','###DAY3###','###DAY4###','###DAY5###','###DAY6###'],
		monthNamesShort: ['###MON1###','###MON2###','###MON3###','###MON4###','###MON5###','###MON6###','###MON7###','###MON8###','###MON9###','###MON10###','###MON11###','###MON12###']
	});
	
	//sets max of datepicker to today if the field has class 'max-today'
	$('.tx-appointments .datepicker.max-today').datepicker('option','maxDate','0');
	
	
	//*****************************
	// Reservation Timer Countdown
	//*****************************
	
	var timerElem = jQuery('.tx-appointments span.reservation-timer');
	var seconds = 0;
	var flashStart = 59;
	var counter = null;
	
	//start timer countdown if a timer exists
	if (timerElem[0]) {
		//calculate timer variables
		var timerVal = timerElem.html();
		var splitAt = timerVal.indexOf(':',0);
		var minutes = parseInt(timerVal.substring(0, splitAt),10);
		seconds = (minutes * 60) + parseInt(timerVal.substring(splitAt+1),10);
		if (seconds < flashStart) {
			flashStart = seconds - 1;
		}
		//run every second (in milliseconds)
		counter = setInterval(reservationTimer, 1000);
		//because this starts after load, there is a few seconds delay, but it doesn't seem to be an issue.
	}
	
	//countdown timer-html-setter function
	function reservationTimer() {
		seconds--;
		
		var displayMin = Math.floor(seconds / 60);
		var displaySec = '0' + (seconds % 60); //remainder of seconds by modulus
		//set new inner html
		timerElem.html(displayMin + ':' + displaySec.slice(-2)); //only show the last 2 numbers of seconds
		
		if (seconds == flashStart) {
			timerElem.addClass('flash'); //starts flashing (or marking) the timer according to class css
		} else if (seconds <= 0) {
			clearInterval(counter); //stop counter
			return;
		}
	};

});