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
				jQuery(this).removeAttr('title'); //removes the actual tooltip
				//fadeToggle() and fadeIn() touch the display style property, hence animate()
				jQuery(this).next().css('display','inline-block').animate({'opacity':0.9},200);
			}
		},
		function() {
			elem = jQuery(this).next();
			if (elem.is('.tx-appointments span.tooltip')) {
				jQuery(elem).fadeOut(200, function() {
					jQuery(this).css('display','none');
				});
				jQuery(this).attr('title',jQuery(this).data('title')); //returns the title attribute to its original value
			}
		}
	);
	
});