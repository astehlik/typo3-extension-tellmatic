<?php

namespace Sto\Tellmatic\Tellmatic\Request;

/*                                                                        *
 * This script belongs to the TYPO3 extension "tellmatic".                *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Request for adding a new subscriber to Tellmatic.
 */
class Memo
{
    /**
     * The memo text.
     *
     * @var string
     */
    protected $memo = '';

    /**
     * @param string $line
     * @return string
     */
    public function addLineToMemo($line)
    {
        $this->memo .= $line . LF;
    }

    /**
     * Adds some information to the memo that is stored in the Tellmatic address record.
     *
     * @param object $class
     */
    public function appendDefaultMemo($class)
    {
        $this->addLineToMemo('Handler: ' . get_class($class) . ' of tellmatic TYPO3 Extension');
        $this->addLineToMemo('IP address: ' . GeneralUtility::getIndpEnv('REMOTE_ADDR'));
        $this->addLineToMemo('Date / time (Server): ' . date('c'));
    }

    /**
     * @return string
     */
    public function getMemo()
    {
        return $this->memo;
    }
}
