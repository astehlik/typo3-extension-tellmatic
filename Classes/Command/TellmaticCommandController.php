<?php
namespace Sto\Tellmatic\Command;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Tellmatic API commands.
 */
class TellmaticCommandController extends CommandController {

	/**
	 * @inject
	 * @var \Sto\Tellmatic\Tellmatic\TellmaticClient
	 */
	protected $tellmaticClient;

	/**
	 * Shows the subscribe status.
	 *
	 * @param string $email
	 */
	public function subsribeStatusCommand($email) {
		$this->outputLine($this->tellmaticClient->getSubscribeState($email)->getSubscribeState());
	}
}