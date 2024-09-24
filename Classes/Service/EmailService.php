<?php

namespace Innologi\Appointments\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use Innologi\Appointments\Domain\Model\{Address, Appointment, SimpleEmailContainer};
use Innologi\Appointments\Mvc\Exception\PropertyDeleted;
use Symfony\Component\Mime\Part\TextPart;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Facilitates email functionality.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EmailService implements SingletonInterface
{
    #@LOW rebuild service and separate content / implementation somewhat more
    /**
     * Extension name
     *
     * @var string
     */
    protected $extensionName;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var mixed
     */
    protected $templatePaths;

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

    public function __construct(
        protected UriBuilder $uriBuilder,
    ) {}

    public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Set extensionname (REQUIRED)
     *
     * Used to access locallang files.
     */
    public function setExtensionName(string $extensionName)
    {
        $this->extensionName = strtolower($extensionName);
    }

    /**
     * Initializes a mail instance
     *
     * @return \TYPO3\CMS\Core\Mail\MailMessage
     */
    protected function initializeMail()
    {
        $this->text = null;
        return new \TYPO3\CMS\Core\Mail\MailMessage();
    }

    /**
     * Performs the appropriate send-actions. This is a container function to provide
     * a single try/catch construction on all the email-processes.
     *
     * @param string $action The email action [create / update / delete]
     * @return boolean
     */
    public function sendAction($action, Appointment $appointment)
    {
        $returnVal = false;
        //$errorMsg = 'Could not send email because of error: ';
        //try {
        $this->sendEmailAction($action, $appointment);
        $this->sendCalendarAction($action, $appointment);
        $returnVal = true;
        // @TODO the original try/catch wasn't adequate, and currently a failure here will fail the appointment creation
        // but at least the exception will be logged in the TYPO3 log until we figure out a better response to exceptions here
        /*} catch (PropertyDeleted $e) { //a property was deleted
            // @TODO do something?
        } catch (\Swift_RfcComplianceException $e) { //one or more email properties does not comply with RFC (e.g. sender email)
            // @TODO TYPO3 v11 introduces an RFC validator, maybe use that instead?
            // @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Mail/Index.html#validators
        } catch (\Exception $e) {
            // @TODO do something?
        }*/
        return $returnVal;
    }

    /**
     * Sends an email action.
     *
     * @param string $action The email action [create / update / delete]
     * @param Appointment $appointment The appointment to confirm
     * @return boolean TRUE on success, FALSE on failure
     */
    protected function sendEmailAction($action, Appointment $appointment)
    {
        $recipientArray = $this->collectAllowedRecipients($action, $appointment);
        if (!empty($recipientArray)) {
            $mail = $this->initializeMail();
            $mail->subject($this->getActionSubject($action, 'email'));
            $mail->html($this->getText($appointment, $action, 'email', true));
            $mail->text($this->getText($appointment, $action, 'email'));

            // uses the old TYPO3 swiftmailer API for [ email => name ]
            $mail->setFrom($this->getSender());

            // both recipient-classes have a getEmail() method
            $toArray = $this->getRecipientEmailArray($recipientArray);
            // send to each recipient separately
            foreach ($toArray as $to) {
                $sendMail = clone $mail;
                $sendMail->to($to)->send();
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
    protected function getActionSubject($action, $type = 'email')
    {
        switch ($action) {
            case 'create':
                $subject = LocalizationUtility::translate('tx_appointments_list.' . $type . '_create_subject', $this->extensionName);
                break;
            case 'update':
                $subject = LocalizationUtility::translate('tx_appointments_list.' . $type . '_update_subject', $this->extensionName);
                break;
            case 'delete':
                $subject = LocalizationUtility::translate('tx_appointments_list.' . $type . '_delete_subject', $this->extensionName);
        }
        return $subject;
    }

    /**
     * Gets the email sender.
     *
     * @return mixed Array or NULL on failure
     */
    protected function getSender()
    {
        if ($this->sender === null) {
            global $TYPO3_CONF_VARS;
            $extConf = GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class,
            )->get($this->extensionName);

            #@TODO add support to set email address/name from agenda
            if (isset($extConf['email_from'][0])) {
                $from = $extConf['email_from'];
                if (isset($extConf['email_name'][0])) { #@TODO document this in manual
                    $from = [
                        $from => $extConf['email_name'],
                    ];
                }
            } else {
                $from = $TYPO3_CONF_VARS['MAIL']['defaultMailFromAddress'];
                if (isset($TYPO3_CONF_VARS['MAIL']['defaultMailFromName'][0])) {
                    $from = [
                        $from => $TYPO3_CONF_VARS['MAIL']['defaultMailFromName'],
                    ];
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
     * @param Appointment $appointment Subject appointment of the text
     * @param string $action The email action [create / update / delete]
     * @param string $bodyType Sets what text property to get (email|calendar)
     * @param boolean $isHTML On TRUE, returns a HTML body. On FALSE, returns plain text
     * @return string Processed email/calendar text
     * @throws PropertyDeleted
     */
    protected function getText(Appointment $appointment, $action, $bodyType = 'email', $isHTML = false)
    {
        if (!isset($this->text)) { //put everything not-$isHTML-related in text var
            $agenda = $appointment->getAgenda();
            $body = match ($bodyType) {
                'calendar' => $agenda->getCalendarInviteText(),
                default => $agenda->getEmailText(),
            };

            $address = $appointment->getAddress();
            $type = $appointment->getType();
            if (!is_object($type)
                    || (!is_object($address) && !$type->getAddressDisable())
            ) {
                throw new PropertyDeleted('One or more object-properties of ' . $appointment::class . ':' . $appointment->getUid() . ' are not available and might have been deleted.');
            }

            //replaces variables
            $feUser = $appointment->getFeUser();
            $body = str_replace('###USER###', ($feUser === null ? '' : $feUser->getName()), $body);
            $body = str_replace('###AGENDA###', $agenda->getName(), $body);
            $body = str_replace('###TYPE###', $type->getName(), $body);
            $body = str_replace('###DATE###', $appointment->getBeginTime()->format('d-m-Y'), $body);
            $body = str_replace('###START_TIME###', $appointment->getBeginTime()->format('H:i'), $body);
            $body = str_replace('###END_TIME###', $appointment->getEndTime()->format('H:i'), $body);
            $body = str_replace('###NOTES###', $appointment->getNotes(), $body);
            $body = str_replace('###NOTES_SU###', $appointment->getNotesSu(), $body);
            $body = str_replace(
                '###SECURITY###',
                ($type->getAddressDisable() ? '' : $address->getSocialSecurityNumber()),
                $body,
            );

            // additional form field variables (if any)
            $fields = $appointment->getFormFieldValues();
            /** @var \Innologi\Appointments\Domain\Model\FormFieldValue $fV */
            foreach ($fields as $fV) {
                $body = str_replace('###' . \strtoupper($fV->getFormField()->getTitle()) . '###', $fV->getValue() ?? '', $body);
            }

            $this->text = $body;
        } else {
            $body = $this->text;
        }

        // build address supports a variable separator, so we'll let $isHTML decide
        if (str_contains($body, '###ADDRESS###')) {
            $body = str_replace(
                '###ADDRESS###',
                (
                    $appointment->getType()->getAddressDisable() ? '' :
                        $this->buildAddress(
                            $appointment->getAddress(),
                            ($isHTML ? '<br />' : "\n"),
                        )
                ),
                $body,
            );
        }

        // only build a link if action is not delete
        if (str_contains($body, '###LINK###')) {
            $body = str_replace(
                '###LINK###',
                ($action === 'delete' ? '' : $this->buildLink($appointment, $isHTML)),
                $body,
            );
        }

        if ($isHTML) { //html
            // convert newlines
            $body = nl2br($body);
        } else { //text
            //before stripping all HTML, replace anything that represents (a) newline(s)
            $body = preg_replace('/<p>/i', '', $body);
            $body = preg_replace('`</p>`i', "\n\n", $body);
            $body = preg_replace('`<br[\s]?[/]?>`i', "\n", $body);
            $body = strip_tags($body);
        }

        return $body;
    }

    /**
     * Builds a nicely formatted name & address.
     *
     * @param Address $address The address object
     * @param string $separator Separator between Name/Address and Address/Zip
     * @return string
     */
    protected function buildAddress(Address $address, $separator = "\n")
    {
        return $address->getName() . $separator .
            $address->getAddress() . $separator .
            $address->getZip() . ' ' . $address->getCity();
    }

    /**
     * Returns email recipients array.
     *
     * @param array $emailAddresses Contains objects with a getEmail() method
     * @return array Consists of email addresses
     */
    protected function getRecipientEmailArray($emailAddresses)
    {
        $emailArray = [];

        foreach ($emailAddresses as $address) {
            if (method_exists($address, 'getEmail')) {
                $email = $address->getEmail();
                // @LOW log erroneous email addresses?
                if (isset($email[0]) && GeneralUtility::validEmail($email)) {
                    $emailArray[] = $email;
                }
            }
        }

        return $emailArray;
    }

    /**
     * Sends a calendar action.
     *
     * @param string $action The calendar action [create / update / delete]
     * @param Appointment $appointment The appointment that is the subject of the calendar action
     * @return boolean TRUE on success, FALSE on failure
     */
    protected function sendCalendarAction($action, Appointment $appointment)
    {
        $agenda = $appointment->getAgenda();
        if ($this->isActionAllowed($action, $agenda->getCalendarInviteTypes())) {
            $ics = $this->getCalendarActionBody($action, $appointment);

            $mail = $this->initializeMail();
            $mail->subject($this->getActionSubject($action, 'calendar'));
            $mail->setBody(new TextPart($ics, 'utf-8', 'calendar'));
            $mail->attach($ics, 'invite.ics', 'application/ics');
            # @todo can we add "; method=REQUEST" to the content-type somehow? Symfony mailer doesn't seem to allow it if not using a custom implementation
            #$mail->getHeaders()->get('Content-Type')->setBody('text/calendar; charset=utf-8; method=REQUEST');

            // uses the old TYPO3 swiftmailer API for [ email => name ]
            $mail->setFrom($this->getSender());

            // @LOW google does not connect updates and outlook.com does not provide calendar interactivity, so maybe consider further imitating Google, e.g.:
            //$mail->addPart($description,'text/plain');
            //$mail->addPart($description,'text/html');
            //$mail->addPart($ics,'text/calendar');

            $toArray = $this->getRecipientEmailArray(
                $agenda->getCalendarInviteAddress()->toArray(),
            );
            // send to each recipient separately
            foreach ($toArray as $to) {
                $sendMail = clone $mail;
                $sendMail->to($to)->send();
            }
        }
    }

    /**
     * Gets Calendar Action body.
     *
     * @param string $action The calendar action [create / update / delete]
     * @param Appointment $appointment The appointment that is the subject of the calendar action
     * @return string The calendar action body
     */
    protected function getCalendarActionBody($action, Appointment $appointment)
    {
        //the configuration manager gets us access to the template paths, so we use that to retrieve the body template file
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $paths = array_reverse($extbaseFrameworkConfiguration['view']['templateRootPaths']);
        $body = '';
        foreach ($paths as $path) {
            // @extensionScannerIgnoreLine false positive
            $body = $this->fileResource($path . 'invite.ics');
            if ($body !== '') {
                break;
            }
        }

        $start = $appointment->getBeginTime()->getTimestamp();
        $end = $appointment->getEndTime()->getTimestamp();
        // for use with gmdate for UTC time representation
        $dateFormat = 'Ymd\THis\Z';
        $sender = $this->getSender();
        $sequence = 0;

        //id unique to this appointment and domain
        $id = 'typo3-' . $this->extensionName . '-a' . $appointment->getAgenda()->getUid() . '-t' . $appointment->getType()->getUid() . '-a' . $appointment->getUid() . '@' . GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');

        //escape certain chars and newlines for them to work as intended in a description
        #@TODO should it be wrapped at 74 chars (or 63 for first line), each line after the first indented with 1 space?
        $description = str_replace(
            "\r",
            '',
            str_replace(
                "\n",
                "\\n",
                str_replace(
                    ',',
                    '\,',
                    str_replace(
                        ';',
                        '\;',
                        str_replace(
                            '\\',
                            '\\\\',
                            $this->getText($appointment, $action, 'calendar'),
                        ),
                    ),
                ),
            ),
        );
        $feUser = $appointment->getFeUser();
        $markerArray = [
            '###START###' => gmdate($dateFormat, $start),
            '###END###' => gmdate($dateFormat, $end),
            '###CRDATE###' => gmdate($dateFormat, $appointment->getReservationTime()),
            '###TSTAMP###' => gmdate($dateFormat),
            '###FROM###' => is_array($sender) ? key($sender) : $sender,
            '###FROMCN###' => ($feUser !== null ? $feUser->getName() : (is_array($sender) ? current($sender) : $sender)),
            '###UID###' => $id,
            '###DESCRIPTION###' => $description,
            '###LOCATION###' => '',
            '###SUBJECT###' => $appointment->getType()->getName(),
        ];

        //action-dependant variables
        switch ($action) {
            case 'update':
                $sequence = time(); //makes sure it is always a higher sequence number than previous
                // @LOW maybe doing this instead of +1 is what causes failed connecting of updates for google?
                // no break
            case 'create':
                $markerArray2 = [
                    '###METHOD###' => 'REQUEST',
                    '###STATUS###' => 'CONFIRMED',
                    '###SEQUENCE###' => $sequence,
                ];
                break;
            case 'delete':
                $markerArray2 = [
                    '###METHOD###' => 'CANCEL',
                    '###STATUS###' => 'CANCELLED',
                    '###SEQUENCE###' => time(),
                ];
        }

        $markerArray = array_merge($markerArray, $markerArray2);
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
    protected function fileResource($resourcePath)
    {
        //replace EXT: if it's in, and create a full path out of the resourcePath
        $resourcePath = GeneralUtility::getFileAbsFileName($resourcePath);
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
    protected function isActionAllowed($action, $bitValue)
    {
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
     * @param Appointment $appointment The appointment subject of this action
     * @return array Contains recipient-objects
     */
    protected function collectAllowedRecipients($action, Appointment $appointment)
    {
        $recipientArray = [];

        $agenda = $appointment->getAgenda();

        if ($this->isActionAllowed($action, $agenda->getEmailOwnerTypes())) {
            $feUser = $appointment->getFeUser();
            if ($feUser !== null) {
                $recipientArray[] = $feUser;
            } else {
                // @TODO ____check if there is some kind of email-designated field?
            }
        }

        if ($this->isActionAllowed($action, $agenda->getEmailTypes())) {
            $recipientArray = array_merge(
                $recipientArray, //array(FeUser)
                $agenda->getEmailAddress()->toArray(), //array(Address)
            );
        }

        if ($this->isActionAllowed($action, $agenda->getEmailFieldTypes())) {
            $emailFormFieldValues = $appointment->getEmailFormFieldValues();
            foreach ($emailFormFieldValues as $formFieldValue) {
                $email = $formFieldValue->getValue();
                // we assume validation was applied, but not necessarily that the field was required
                if (isset($email[0])) {
                    // @TODO replace "new" calls for DI support
                    $emailObj = new SimpleEmailContainer();
                    $emailObj->setEmail($email);
                    $recipientArray[] = $emailObj;
                }
            }
        }

        return $recipientArray;
    }

    /**
     * Builds a link to the appointment (showAction) for use in email.
     *
     * @param boolean $isHTML Returns a HTML link on TRUE, only the URL on FALSE
     * @return string The link
     */
    protected function buildLink(Appointment $appointment, $isHTML = false)
    {
        $arguments = [
            'appointment' => $appointment,
        ];

        $uri = $this->uriBuilder
            ->reset()
            ->setCreateAbsoluteUri(true)
            ->uriFor('show', $arguments, 'Appointment', 'appointments', 'list');

        $text = LocalizationUtility::translate('tx_appointments_list.email_link_label', $this->extensionName);

        $link = $isHTML ? '<a href="' . $uri . '">' . $text . '</a>' : $text . ': ' . $uri;

        return $link;
    }
}
