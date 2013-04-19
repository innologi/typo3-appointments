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
	var obj = jQuery('.tx-appointments form#appointment span.warnUnload');
	//'respect REFRESH header!' variables
	var sessionStart = new Date().getTime() / 1000;
	var header = null;
	
	//set onbeforeunload if a warnUnload message exists
	if (obj[0]) {
		warnUnload = obj.text();
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
			header = (header != null && header.length) ? parseInt(header.substring(0, header.indexOf(';',0))) : null;
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
	
});