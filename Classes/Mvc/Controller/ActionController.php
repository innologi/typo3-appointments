<?php

namespace Innologi\Appointments\Mvc\Controller;

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
use Innologi\Appointments\Mvc\Exception\EarlyResponseThrowable;
use Innologi\Appointments\Mvc\Exception\PropertyDeleted;
use Innologi\TYPO3AssetProvider\ProviderControllerTrait;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentValueException;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Appointments Action Controller.
 *
 * Replacement for the Extbase Action Controller for inheritance by the
 * domain controllers. It unites all appointments-specific code that is
 * to be shared between all domain controllers.
 *
 * Provides a (necessary) try and catch construction for resolving
 * controller arguments from the database.
 *
 * Also provides a united error messaging feature in initializeAction(),
 * containing all necessary checks like those agenda or feUser related.
 *
 * @package appointments
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ActionController extends SettingsOverrideController
{
    use ProviderControllerTrait;

    /**
     * agendaRepository
     *
     * @var \Innologi\Appointments\Domain\Repository\AgendaRepository
     */
    protected $agendaRepository;

    /**
     * appointmentRepository
     *
     * @var \Innologi\Appointments\Domain\Repository\AppointmentRepository
     */
    protected $appointmentRepository;

    /**
     * typeRepository
     *
     * @var \Innologi\Appointments\Domain\Repository\TypeRepository
     */
    protected $typeRepository;

    /**
     * @var \Innologi\Appointments\Service\UserService
     */
    protected $userService;

    /**
     * @var \Innologi\Appointments\Domain\Service\SlotService
     */
    protected $slotService;

    /**
     * Logged in frontend user
     *
     * @var \Innologi\Appointments\Domain\Model\FrontendUser
     */
    protected $feUser;

    /**
     * Agenda
     *
     * @var \Innologi\Appointments\Domain\Model\Agenda
     */
    protected $agenda;

    /**
     * Indicates if user needs to be logged in
     *
     * Can be overridden by extending domain controllers
     *
     * @var boolean
     */
    protected $requireLogin = true;

    /**
     * @var string
     */
    protected $extensionName = 'Appointments';

    public function injectAgendaRepository(\Innologi\Appointments\Domain\Repository\AgendaRepository $agendaRepository): void
    {
        $this->agendaRepository = $agendaRepository;
    }

    public function injectAppointmentRepository(\Innologi\Appointments\Domain\Repository\AppointmentRepository $appointmentRepository): void
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    public function injectTypeRepository(\Innologi\Appointments\Domain\Repository\TypeRepository $typeRepository): void
    {
        $this->typeRepository = $typeRepository;
    }

    public function injectUserService(\Innologi\Appointments\Service\UserService $userService): void
    {
        $this->userService = $userService;
    }

    public function injectSlotService(\Innologi\Appointments\Domain\Service\SlotService $slotService): void
    {
        $this->slotService = $slotService;
    }

    /**
     * Initializes the controller before invoking an action method.
     *
     * Sets some prerequisite variables. If it fails because of any error related to these,
     * it will set appropriate error messages and redirect to the appropriate action.
     *
     * @throws EarlyResponseThrowable
     */
    protected function initializeAction()
    {
        parent::initializeAction();

        if ($this->actionMethodName !== 'noneAction') {
            $errors = [];

            // is user logged in as required?
            $this->feUser = $this->userService->getCurrentUser();
            if ($this->requireLogin && !$this->feUser) {
                $errors[] = LocalizationUtility::translate('tx_appointments.login_error', $this->extensionName);
            }

            // is an agenda record set?
            $this->agenda = $this->agendaRepository->findByUid($this->settings['agendaUid']);
            if ($this->agenda === null) {
                $errors[] = LocalizationUtility::translate('tx_appointments.no_agenda', $this->extensionName);
            }

            // errors!
            if (!empty($errors)) {
                // we'll need it for the FlashMessageQueue
                foreach ($errors as $flashMessage) {
                    $this->addFlashMessage($flashMessage, '', ContextualFeedbackSeverity::ERROR);
                }
                throw new EarlyResponseThrowable(new ForwardResponse('none'));
            }

            // no errors? initialize slotService as it is used in most actions
            $this->slotService->initialize($this->extensionName, intval($this->settings['freeSlotInMinutes']), intval($this->settings['shiftSlotPerInterval']));
        }
    }

    /**
     * Disables (or enables) requireLogin by action
     *
     * @param array $actions
     *            For which to disable requireLogin
     */
    protected function disableRequireLogin(array $actions = [])
    {
        $this->requireLogin = !in_array(substr($this->actionMethodName, 0, -6), $actions);
    }

    /**
     * Validates a request based on $tokenArgument, through TYPO3's internal
     * CSRF protection.
     * If invalid, will throw an early response.
     *
     * @throws EarlyResponseThrowable
     */
    protected function validateRequest(string $tokenArgument = 'stoken'): void
    {
        // @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController->forwardToReferringRequest()
        $referringRequestArguments = $this->request->getAttribute('extbase')?->getInternalArgument('__referrer') ?? null;
        if (is_string($referringRequestArguments['@request'] ?? null)) {
            $referrerArray = json_decode($this->hashService->validateAndStripHmac($referringRequestArguments['@request']), true);
            $arguments = [];
            if (is_string($referringRequestArguments['arguments'] ?? null)) {
                $arguments = unserialize(base64_decode($this->hashService->validateAndStripHmac($referringRequestArguments['arguments'])));
            }
            $replacedArguments = array_replace_recursive($arguments, $referrerArray);
            if (!empty($replacedArguments)) {
                $controllerName = (string) ($replacedArguments['@controller'] ?? 'Standard');
                $objectType = strtolower((string) $controllerName);
                if ($this->request->hasArgument($tokenArgument) && $this->request->hasArgument($objectType) && \TYPO3\CMS\Core\FormProtection\FormProtectionFactory::get('frontend')->validateToken($this->request->getArgument($tokenArgument), $controllerName, (string) ($replacedArguments['@action'] ?? 'index'), $this->request->getArgument($objectType)['__identity'] ?? '')) {
                    return;
                }
            }
        }

        $this->addFlashMessage(LocalizationUtility::translate('tx_appointments.csrf_invalid', $this->extensionName), '', ContextualFeedbackSeverity::ERROR);

        throw new EarlyResponseThrowable($this->errorAction());
    }

    /**
     * action none
     *
     * If the plugin is supposed to do nothing but present flash messages.
     *
     * Note that you should FORWARD, not REDIRECT, to this action,
     * or we would need conditions and a redirect here as well.
     */
    public function noneAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * Gets types according to settings.
     *
     * @throws EarlyResponseThrowable
     */
    protected function getTypes(): array
    {
        $superUser = $this->userService->isInGroup($this->settings['suGroup']);
        $this->view->assign('superUser', $superUser);

        # $this->typeUidArray = t3lib_div::trimExplode(',', $this->settings['appointmentTypeList'], 1); #@TODO _need to reuse this as showTypes/allowTypes or something
        # $types = empty($this->typeUidArray) ? $this->typeRepository->findAll($superUser) : $this->typeRepository->findIn($this->typeUidArray,$superUser);
        $types = $superUser ? $this->agenda->getTypes()->toArray() : $this->typeRepository->findIn($this->agenda->getTypes()->toArray())->toArray();
        if (!empty($types)) {
            // types found
            return $types;
        }

        // no types found
        $flashMessage = LocalizationUtility::translate('tx_appointments.no_types', $this->extensionName);
        $this->addFlashMessage($flashMessage, '', ContextualFeedbackSeverity::ERROR);
        throw new EarlyResponseThrowable(new ForwardResponse('none'));
    }

    /**
     * Maps arguments delivered by the request object to the local controller arguments.
     *
     * Try and catch construction makes sure a controller argument which no longer exists
     * in the database, doesn't produce a full stop. It catches it, and produces a flashMessage.
     *
     * This concerns f.e. an object that was deleted in TCA or FE or by task. An appointment
     * in the making which expired but wasn't deleted yet, will still be retrievable.
     *
     * @throws EarlyResponseThrowable
     */
    protected function mapRequestArgumentsToControllerArguments()
    {
        try {
            parent::mapRequestArgumentsToControllerArguments();
        } catch (InvalidArgumentValueException | TargetNotFoundException) {
            $flashMessage = LocalizationUtility::translate('tx_appointments.appointment_no_longer_available', $this->extensionName); # @TODO __the message doesn't cover cases where the appointment was not finished
            $this->addFlashMessage($flashMessage, '', ContextualFeedbackSeverity::ERROR);
            throw new EarlyResponseThrowable($this->redirect('list'));
        } catch (PropertyDeleted | Exception) {
            $flashMessage = LocalizationUtility::translate('tx_appointments.appointment_property_deleted', $this->extensionName);
            $this->addFlashMessage($flashMessage, '', ContextualFeedbackSeverity::ERROR);

            // in case not the original argument, but one of its object-properties no longer exist, try to redirect to the appropriate action
            $redirectTo = 'list';
            $arguments = [];
            $argumentName = 'appointment';
            if ($this->request->hasArgument($argumentName)) {
                $appointment = $this->request->getArgument($argumentName); // get from request, as controller argument mapping was just disrupted
                if (isset($appointment['__identity'])) { // getting the entire array would also require the hmac property, we only need uid
                    $arguments[$argumentName] = $appointment['__identity'];
                    // sending to the appropriate form will regenerate missing objects (but not their values)
                    switch ($this->actionMethodName) {
                        case 'createAction':
                            $redirectTo = 'new2';
                            break;
                        case 'updateAction':
                            $redirectTo = 'edit';
                    }
                }
            }
            throw new EarlyResponseThrowable($this->redirect($redirectTo, null, null, $arguments));
        }
    }

    public function processRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return parent::processRequest($request);
        } catch (EarlyResponseThrowable $t) {
            return $t->getResponse();
        }
    }
}
