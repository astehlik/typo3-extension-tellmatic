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
class UnsubscribeRequest implements TellmaticRequestInterface
{
    /**
     * @var bool
     */
    protected $doNotSendEmails = false;

    /**
     * The email address that should be subscribed.
     *
     * @var string
     */
    protected $email;

    /**
     * @var int
     */
    protected $historyId;

    /**
     * @var Memo
     */
    protected $memo = '';

    /**
     * @var int
     */
    protected $newsletterId;

    /**
     * @var int
     */
    protected $queueId;

    /**
     * Initializes a new SubscribeRequest for the given email address.
     *
     * @param string $email The email address that should be subscribed.
     */
    public function __construct($email)
    {
        if (empty($email) || !GeneralUtility::validEmail($email)) {
            throw new \RuntimeException('The provided email address is invalid: ' . $email);
        }

        $this->email = $email;
        $this->memo = GeneralUtility::makeInstance(Memo::class);
    }

    /**
     * Initializes the given HTTP request with the required parameters.
     *
     * @param AccessibleHttpRequest $httpRequest
     */
    public function initializeHttpRequest(AccessibleHttpRequest $httpRequest)
    {
        $this->memo->appendDefaultMemo($this);

        $memo = $this->memo->getMemo();
        if (!empty($memo)) {
            $httpRequest->addPostParameter('memo', $memo);
        }

        $httpRequest->addPostParameter('email', $this->email);

        if ($this->doNotSendEmails) {
            $httpRequest->addPostParameter('doNotSendEmails', true);
        }

        if (!empty($this->newsletterId)) {
            $httpRequest->addPostParameter('nl_id', $this->newsletterId);
        }

        if (!empty($this->queueId)) {
            $httpRequest->addPostParameter('q_id', $this->queueId);
        }

        if (!empty($this->historyId)) {
            $httpRequest->addPostParameter('h_id', $this->historyId);
        }
    }

    /**
     * @return bool
     */
    public function getDoNotSendEmails()
    {
        return $this->doNotSendEmails;
    }

    /**
     * @return int
     */
    public function getHistoryId()
    {
        return $this->historyId;
    }

    /**
     * @return Memo
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * @return int
     */
    public function getNewsletterId()
    {
        return $this->newsletterId;
    }

    /**
     * @return int
     */
    public function getQueueId()
    {
        return $this->queueId;
    }

    /**
     * @param bool $doNotSendEmails
     */
    public function setDoNotSendEmails($doNotSendEmails)
    {
        $this->doNotSendEmails = (bool)$doNotSendEmails;
    }

    /**
     * @param int $historyId
     */
    public function setHistoryId($historyId)
    {
        $this->historyId = (int)$historyId;
    }

    /**
     * @param int $newsletterId
     */
    public function setNewsletterId($newsletterId)
    {
        $this->newsletterId = (int)$newsletterId;
    }

    /**
     * @param int $queueId
     */
    public function setQueueId($queueId)
    {
        $this->queueId = (int)$queueId;
    }
}
