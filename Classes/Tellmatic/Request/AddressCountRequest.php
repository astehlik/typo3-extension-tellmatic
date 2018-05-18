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

/**
 * Request for counting addresses in the Tellmatic database.
 */
class AddressCountRequest implements TellmaticRequestInterface
{
    /**
     * @var int
     */
    protected $groupId = 0;

    /**
     * @var array
     */
    protected $search = [];

    /**
     * Initializes the given HTTP request with the required parameters.
     *
     * @param AccessibleHttpRequest $httpRequest
     */
    public function initializeHttpRequest(AccessibleHttpRequest $httpRequest)
    {
        foreach ($this->search as $field => $value) {
            $httpRequest->addPostParameter('search[' . $field . ']', $value);
        }

        if (!empty($this->groupId)) {
            $httpRequest->addPostParameter('groupId', $this->groupId);
        }
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @return array
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param int $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @param array $search
     */
    public function setSearch(array $search)
    {
        $this->search = $search;
    }
}
