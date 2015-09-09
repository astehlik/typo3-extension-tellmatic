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
 * The response provided by Tellmatic is invalid.
 */
class InvalidResponseException extends \Exception {

	/**
	 * @param string $message
	 * @param int $code
	 * @param null $previous
	 */
	public function __construct($message, $code = 1441711344, $previous = NULL) {
		parent::__construct($message, $code, $previous);
	}
}