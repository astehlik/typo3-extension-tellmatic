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

use Sto\Tellmatic\Http\HttpRequestInterface;

/**
 * Request for searching addresses in the tellmatic database.
 */
class AddressSearchRequest implements TellmaticRequestInterface
{
    /**
     * @var bool
     */
    protected $fetchDetails = true;

    /**
     * @var int
     */
    protected $groupId = 0;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var array
     */
    protected $search = [];

    /**
     * @var string
     */
    protected $sortIndex = '';

    /**
     * @var int
     */
    protected $sortType = 0;

    /**
     * Initializes the given HTTP request with the required parameters.
     *
     * @param HttpRequestInterface $httpRequest
     */
    public function initializeHttpRequest(HttpRequestInterface $httpRequest)
    {
        $httpRequest->addPostParameter('fetchDetails', $this->fetchDetails);
        $httpRequest->addPostParameter('groupId', $this->groupId);
        $httpRequest->addPostParameter('id', $this->id);
        $httpRequest->addPostParameter('limit', $this->limit);
        $httpRequest->addPostParameter('offset', $this->offset);
        $httpRequest->addPostParameter('sortIndex', $this->sortIndex);
        $httpRequest->addPostParameter('sortType', $this->sortType);

        foreach ($this->search as $field => $value) {
            $httpRequest->addPostParameter('search[' . $field . ']', $value);
        }
    }

    /**
     * @return bool
     */
    public function getFetchDetails()
    {
        return $this->fetchDetails;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return array
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @return string
     */
    public function getSortIndex()
    {
        return $this->sortIndex;
    }

    /**
     * @return int
     */
    public function getSortType()
    {
        return $this->sortType;
    }

    /**
     * @param bool $fetchDetails
     */
    public function setFetchDetails($fetchDetails)
    {
        $this->fetchDetails = (bool)$fetchDetails;
    }

    /**
     * @param int $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = (int)$groupId;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = (int)$id;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int)$limit;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = (int)$offset;
    }

    /**
     * @param array $search
     */
    public function setSearch(array $search)
    {
        $this->search = $search;
    }

    /**
     * @param string $sortIndex
     */
    public function setSortIndex($sortIndex)
    {
        $this->sortIndex = (string)$sortIndex;
    }

    /**
     * @param int $sortType
     */
    public function setSortType($sortType)
    {
        $this->sortType = (int)$sortType;
    }
}
