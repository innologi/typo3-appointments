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
	var message = jQuery('.tx-appointments .delete_confirm_text:first').text();
	
	//click function performs a confirm, if TRUE/OK continues button functionality
	jQuery('.tx-appointments .button_delete').click(function() {
		return confirm(message);
	});
	
});