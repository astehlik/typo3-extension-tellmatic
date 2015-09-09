<?php
namespace Sto\Tellmatic\Formhandler;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Sto\Tellmatic\Tellmatic\Exception\TellmaticException;
use Sto\Tellmatic\Utility\FormhandlerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This finisher executes a Tellmatic subscribe request.
 * It uses the same field configuration as the DB finisher.
 */
class SubscribeValidator extends \Tx_Formhandler_AbstractValidator {

	/**
	 * Validates the submitted values using given settings
	 *
	 * @param array $errors Reference to the errors array to store the errors occurred
	 * @return boolean
	 */
	public function validate(&$errors) {

		// if there are already errors we do not proceed
		if (count($errors)) {
			$this->utilityFuncs->debugMessage('Skipping tellmatic subscription since previous errors have been detected.');
			return FALSE;
		}

		$exception = NULL;
		$success = FALSE;

		$this->settings['validateOnly'] = 1;

		try {
			$this->getFormhandlerUtility()->sendSubscribeRequest($this->gp, $this->settings);
			$success = TRUE;
		} catch (TellmaticException $exception) {
			$errors['tellmatic'] = $exception->getFailureCode();
		} catch (\Exception $exception) {
			$errors['tellmatic'] = 'unknown';
		}

		if (isset($exception)) {
			$this->utilityFuncs->debugMessage('Exception during tellmatic request: ' . $exception->getMessage());
		}

		return $success;
	}

	/**
	 * @return FormhandlerUtility
	 */
	protected function getFormhandlerUtility() {
		$formahandlerUtility = GeneralUtility::makeInstance(FormhandlerUtility::class);
		$formahandlerUtility->initialize($this);
		return $formahandlerUtility;
	}
}