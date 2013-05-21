<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Facilitates email functionality.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Appointments_Service_EmailService implements t3lib_Singleton {

	#@SHOULD rebuild service and separate content / implementation somewhat more
	/**
	 * Extension name
	 *
	 * @var string
	 */
	protected $extensionName;

	/**
	 * Controller context
	 *
	 * @var Tx_Extbase_MVC_Controller_ControllerContext
	 */
	protected $controllerContext;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * Sender in either array or string format
	 *
	 * @var mixed
	 */
	protected $sender;

	/**
	 * Text that is the same for both HTML and plain emails.
	 *
	 * @var string
	 */
	protected $text;

	/**
	 * Set extensionname (REQUIRED)
	 *
	 * Used to access locallang files.
	 *
	 * @param string $extensionName
	 * @return void
	 */
	public function setExtensionName($extensionName) {
		$this->extensionName = strtolower($extensionName);
	}

	/**
	 * Set controllerContext (REQUIRED)
	 *
	 * Used to access UriBuilder and retrieve the flashMessageContainer.
	 *
	 * @param Tx_Extbase_MVC_Controller_ControllerContext $controllerContext
	 */
	public function setControllerContext(Tx_Extbase_MVC_Controller_ControllerContext $controllerContext) {
		$this->controllerContext = $controllerContext;
	}

	/**
	 * injectConfigurationManager
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * Initializes a mail instance
	 *
	 * @return t3lib_mail_Message
	 */
	protected function initializeMail() {
		$this->text = NULL;
		return new t3lib_mail_Message(NULL, NULL, NULL, 'utf-8');
	}

	/**
	 * Performs the appropriate send-actions. This is a container function to provide
	 * a single try/catch construction on all the email-processes.
	 *
	 * @param string $action The email action [create / update / delete]
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment
	 * @return boolean
	 */
	public function sendAction($action, Tx_Appointments_Domain_Model_Appointment $appointment) {
		try {
			$this->sendEmailAction($action,$appointment);
			$this->sendCalendarAction($action,$appointment);
			return TRUE;
		} catch (Tx_Appointments_MVC_Exception_PropertyDeleted $e) {
			#@TODO __syslog it
			return FALSE;
		} catch (Exception $e) {
			return FALSE; #@TODO separate more exceptions OR syslog simply the thrown error message
		}
	}

	/**
	 * Sends an email action.
	 *
	 * @param string $action The email action [create / update / delete]
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment to confirm
	 * @return boolean TRUE on success, FALSE on failure
	 */
	protected function sendEmailAction($action, Tx_Appointments_Domain_Model_Appointment $appointment) {
		$recipientArray = $this->collectAllowedRecipients($action,$appointment);
		if (!empty($recipientArray)) {
			$mail = $this->initializeMail();

			$mail->setSubject(
					$this->getActionSubject($action,'email')
			)->setFrom(
					$this->getSender()
			)->setBody(
					$this->getText($appointment,$action,'email',1),
					'text/html'
			);
			$mail->addPart(
					$this->getText($appointment,$action,'email'),
					'text/plain'
			);

			//both recipient-classes have a getEmail() method
			$toArray = $this->getRecipientEmailArray($recipientArray);

			//send to each recipient separately
			foreach ($toArray as $to) {
				$mail->setTo($to)->send();
			}
		}
	}

	/**
	 * Returns the email confirmation subject.
	 *
	 * @param string $action The email action [create / update / delete]
	 * @param string $type Action type [calendar / email]
	 * @return string Subject
	 */
	protected function getActionSubject($action, $type = 'email') {
		switch ($action) {
			case 'create':
				$subject = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.'.$type.'_create_subject', $this->extensionName);
				break;
			case 'update':
				$subject = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.'.$type.'_update_subject', $this->extensionName);
				break;
			case 'delete':
				$subject = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.'.$type.'_delete_subject', $this->extensionName);
		}
		return $subject;
	}

	/**
	 * Gets the email sender.
	 *
	 * @return mixed Array or NULL on failure
	 */
	protected function getSender() {
		if ($this->sender === NULL) {
			global $TYPO3_CONF_VARS;
			$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$this->extensionName]);

			#@TODO add support to set email address/name from agenda
			if (isset($extConf['email_from'][0])) { /*&& t3lib_div::validEmail($extConf['email_from'])*/ #@TODO leave it out and catch it by exception?
				$from = $extConf['email_from'];
				if (isset($extConf['email_name'][0])) { #@TODO document this in manual
					$from = array($from => $extConf['email_name']);
				}
			} else {
				$from = $TYPO3_CONF_VARS['MAIL']['defaultMailFromAddress'];
				if (isset($TYPO3_CONF_VARS['MAIL']['defaultMailFromName'][0])) {
					$from = array($from => $TYPO3_CONF_VARS['MAIL']['defaultMailFromName']);
				}
			}

			$this->sender = $from;
		}
		return $this->sender;
	}

	/**
	 * Processes and returns email/calendar text.
	 *
	 * Replaces variables with appropriate values.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment Subject appointment of the text
	 * @param string $action The email action [create / update / delete]
	 * @param string $bodyType Sets what text property to get (email|calendar)
	 * @param boolean $isHTML On TRUE, returns a HTML body. On FALSE, returns plain text
	 * @return string Processed email/calendar text
	 * @throws Tx_Appointments_MVC_Exception_PropertyDeleted
	 */
	protected function getText(Tx_Appointments_Domain_Model_Appointment $appointment, $action, $bodyType = 'email', $isHTML = FALSE) {
		$agenda = $appointment->getAgenda();
		switch ($bodyType) {
			case 'calendar':
				$body = $agenda->getCalendarInviteText();
				break;
			default:
				$body = $agenda->getEmailText();
		}

		if (!isset($this->text)) { //put everything not-$isHTML-related in text var
			$feUser = $appointment->getFeUser();
			$address = $appointment->getAddress();
			$type = $appointment->getType();
			if (!is_object($type) || !is_object($feUser)
					|| ( !is_object($address) && !$type->getAddressDisable() )
			) {
				throw new Tx_Appointments_MVC_Exception_PropertyDeleted('One or more object-properties are not available.', 407501337);
			}

			//replaces variables
			$body = str_replace('###USER###',$feUser->getName(),$body);
			$body = str_replace('###AGENDA###',$appointment->getAgenda()->getName(),$body);
			$body = str_replace('###TYPE###',$appointment->getType()->getName(),$body);
			$body = str_replace('###DATE###',$appointment->getBeginTime()->format('d-m-Y'),$body);
			$body = str_replace('###START_TIME###',$appointment->getBeginTime()->format('H:i'),$body);
			$body = str_replace('###END_TIME###',$appointment->getEndTime()->format('H:i'),$body);
			$body = str_replace('###NOTES###',$appointment->getNotes(),$body);
			$body = str_replace('###NOTES_SU###',$appointment->getNotesSu(),$body);
			$body = str_replace('###SECURITY###',
					( $type->getAddressDisable() ? '' : $address->getSocialSecurityNumber() ),
					$body
			);
			$this->text = $body; #@FIXME wordt niet onthouden? wat gebeurt er? plain text versie heeft namelijk geen vervangen tags
		}
			//build address supports a variable separator, so we'll let $isHTML decide
		if (strpos($body,'###ADDRESS###') !== FALSE) {
			$body = str_replace('###ADDRESS###',
					( $appointment->getType()->getAddressDisable() ? '' :
							$this->buildAddress($appointment->getAddress(),
									( $isHTML?'<br />':"\n" )
							)
					),
					$body
			);
		}
			//only build a link if action is not delete
		if (strpos($body,'###LINK###') !== FALSE) {
			$body = str_replace('###LINK###',
					( $action === 'delete' ? '' : $this->buildLink($appointment,$isHTML) ),
					$body
			);
		}

		if ($isHTML) { //html
			//wrap with HTML tags and convert newlines
			$body = '<html><body>'.nl2br($body).'</body></html>';
		} else { //text
			//before stripping all HTML, replace anything that represents (a) newline(s)
			$body = preg_replace('/<p>/i','',$body);
			$body = preg_replace('`</p>`i',"\n\n",$body);
			$body = preg_replace('`<br[\s]?[/]?>`i',"\n",$body);
			$body = strip_tags($body);
		}

		return $body;
	}

	/**
	 * Builds a nicely formatted name & address.
	 *
	 * @param Tx_Appointments_Domain_Model_Address $address The address object
	 * @param string $separator Separator between Name/Address and Address/Zip
	 * @return string
	 */
	protected function buildAddress(Tx_Appointments_Domain_Model_Address $address, $separator = "\n") {
		return $address->getName() . $separator .
			$address->getAddress() . $separator .
			$address->getZip() . ' ' . $address->getCity();
	}

	/**
	 * Returns email recipients array.
	 *
	 * @param array $emailAddresses Contains objects supporting getEmail() method
	 * @return array Consists of email addresses
	 */
	protected function getRecipientEmailArray($emailAddresses) {
		$emailArray = array();

		foreach ($emailAddresses as $address) {
			$email = $address->getEmail();
			if (isset($email[0]) && t3lib_div::validEmail($email)) {
				$emailArray[] = $email;
			}
		}

		return $emailArray;
	}





	/**
	 * Sends a calendar action.
	 *
	 * @param string $action The calendar action [create / update / delete]
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment that is the subject of the calendar action
	 * @return boolean TRUE on success, FALSE on failure
	 */
	protected function sendCalendarAction($action, Tx_Appointments_Domain_Model_Appointment $appointment) {
		$agenda = $appointment->getAgenda();
		if ($this->isActionAllowed($action, $agenda->getCalendarInviteTypes())) {
			$mail = $this->initializeMail();

			$mail->setSubject(
					$this->getActionSubject($action,'calendar')
			)->setFrom(
					$this->getSender()
			)->setBody(
					$this->getCalendarActionBody($action,$appointment),
					'text/calendar'
			);

			$toArray = $this->getRecipientEmailArray(
					$agenda->getCalendarInviteAddress()->toArray()
			);

			foreach ($toArray as $to) {
				$mail->setTo($to)->send();
			}
		}
	}

	/**
	 * Gets Calendar Action body.
	 *
	 * @param string $action The calendar action [create / update / delete]
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment that is the subject of the calendar action
	 * @return String The calendar action body
	 */
	protected function getCalendarActionBody($action, Tx_Appointments_Domain_Model_Appointment $appointment) {
		//the configuration manager gets us access to the template paths, so we use that to retrieve the body template file
		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		if (isset($extbaseFrameworkConfiguration['view']['templateRootPath'])
			&& isset($extbaseFrameworkConfiguration['view']['templateRootPath'][0])
		) {
			$template = $extbaseFrameworkConfiguration['view']['templateRootPath'] . 'Calendar.html';
		}

		$body = $this->fileResource($template); #@TODO __run through the template and see if anything is in need of a change (the most obvious being timezone)

		$start = $appointment->getBeginTime()->getTimestamp();
		$end = $appointment->getEndTime()->getTimestamp();
		$sender = $this->getSender();
		$sequence = 0; #@TODO __Gmail has never connected updates, Thunderbird doesn't any longer, and Outlook? Check and remedy this!

		//id unique to this appointment and domain
		$id = 'typo3-'.$this->extensionName.'-a'.$appointment->getAgenda()->getUid().'-t'.$appointment->getType()->getUid().'-a'.$appointment->getUid() . '@' . t3lib_div::getIndpEnv('TYPO3_SITE_URL');

		//escape certain chars and newlines for them to work as intended in a description
		$description = str_replace("\r",'',
				str_replace("\n","\\n",
						str_replace(',','\,',
								str_replace(';','\;',
										str_replace('\\','\\\\',
												$this->getText($appointment,$action,'calendar')
										)
								)
						)
				)
		); #@TODO do more Outlook testing on description here

		$markerArray = array(
				'###START###' => strftime('%Y%m%dT%H%M%S', $start),
				'###END###' => strftime('%Y%m%dT%H%M%S', $end),
				'###TSTAMP###' => strftime('%Y%m%dT%H%M%S'),
				'###FROM###' => is_array($sender) ? key($sender) : $sender,
				'###FROMCN###' => $appointment->getFeUser()->getName(),
				'###UID###' => $id,
				'###OWNERAPPTID###' => $id,
				'###DESCRIPTION###' => $description,
				'###LOCATION###' => '',
				'###SUBJECT###' => $appointment->getType()->getName()
		);

		//action-dependant variables
		switch ($action) {
			case 'update':
				$sequence = time(); //makes sure it is always a higher sequence number than previous
			case 'create':
				$markerArray2  = array(
						'###METHOD###' => 'REQUEST',
						'###PRIORITY###' => 5,
						'###STATUS###' => 'CONFIRMED',
						'###OUTLOOK_STATUS###' => 'BUSY',
						'###OUTLOOK_IMPORTANCE###' => 1,
						'###SEQUENCE###' => $sequence
				);
				break;
			case 'delete':
				$markerArray2 = array(
					'###METHOD###' => 'CANCEL',
					'###PRIORITY###' => 1,
					'###STATUS###' => 'CANCELLED',
					'###OUTLOOK_STATUS###' => 'FREE',
					'###OUTLOOK_IMPORTANCE###' => 2,
					'###SEQUENCE###' => time()
				);
		}

		$markerArray = array_merge($markerArray,$markerArray2);
		foreach ($markerArray as $marker => $value) {
			$body = str_replace($marker, $value, $body);
		}

		return $body;
	}





	/**
	 * Retrieves file content from resourcepath.
	 *
	 * @param	string		$resourcePath	Path to resource. If not EXT:, assumes the resourcePath starts in serverroot!
	 * @return	string		Content of resource
	 */
	protected function fileResource($resourcePath) {
		//replace EXT: if it's in, and create a full path out of the resourcePath
		$resourcePath = t3lib_div::getFileAbsFileName($resourcePath);
		$resourceContent = is_file($resourcePath) && file_exists($resourcePath) ? @file_get_contents($resourcePath) : '';

		return $resourceContent;
	}

	/**
	 * Checks if an action is allowed according to the given bitValue.
	 *
	 * @param string $action The action [create / update / delete]
	 * @param integer $bitValue
	 * @return boolean TRUE if allowed, FALSE if not allowed
	 */
	protected function isActionAllowed($action, $bitValue) {
		$bit = 0;
		switch ($action) {
			case 'create':
				$bit = 1;
				break;
			case 'update':
				$bit = 2;
				break;
			case 'delete':
				$bit = 4;
		}

		return $bitValue & $bit == $bit;
	}

	/**
	 * Collects recipient-objects from the appointment instance, but only those that are allowed.
	 *
	 * Typically, these are objects that contain getEmail() functions.
	 * Permissions are dictated by the relevant agenda record fields.
	 *
	 * @param string $action The email action [create / update / delete]
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment The appointment subject of this action
	 * @return array Contains recipient-objects
	 */
	protected function collectAllowedRecipients($action, Tx_Appointments_Domain_Model_Appointment $appointment) {
		$recipientArray = array();

		$agenda = $appointment->getAgenda();

		if ($this->isActionAllowed($action, $agenda->getEmailOwnerTypes())) {
			$recipientArray[] = $appointment->getFeUser();
		}

		if ($this->isActionAllowed($action, $agenda->getEmailTypes())) {
			$recipientArray = array_merge(
					$recipientArray, //array(FeUser)
					$agenda->getEmailAddress()->toArray() //array(Address)
			);
		}

		return $recipientArray;
	}

	/**
	 * Builds a link to the appointment (showAction) for use in email.
	 *
	 * @param Tx_Appointments_Domain_Model_Appointment $appointment
	 * @param boolean $isHTML Returns a HTML link on TRUE, only the URL on FALSE
	 * @return string The link
	 */
	protected function buildLink(Tx_Appointments_Domain_Model_Appointment $appointment, $isHTML = FALSE) {
		$uriBuilder = $this->controllerContext->getUriBuilder();

		$arguments = array(
				'appointment' => $appointment
		);

		//after a quick look @ Tx_Fluid_ViewHelpers_Link_ActionViewHelper..
		$uri = $uriBuilder
			->setUseCacheHash(TRUE)
			->setCreateAbsoluteUri(TRUE)
			->uriFor('show', $arguments, 'Appointment', 'appointments', 'list');

		$text = Tx_Extbase_Utility_Localization::translate('tx_appointments_list.email_link_label', $this->extensionName);

		$link = $isHTML ? '<a href="' . $uri . '">' . $text . '</a>' : $text . ': ' . $uri;

		return $link;
	}
}
?>