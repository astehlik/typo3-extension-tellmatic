<?php
namespace Sto\Tellmatic\Tellmatic\Exception;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * The address submitted for unsubscription does not exist in the Tellmatic database.
 */
class UnsubscribeAddressUnknownException extends TellmaticException {

	/**
	 * @param string $message
	 * @param int $code
	 * @param null $previous
	 */
	public function __construct($message, $code = 1441711838, $previous = NULL) {
		parent::__construct($message, $code, $previous);
	}
}