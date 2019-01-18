<?php

namespace Shaarli\Api\Exceptions;

/**
 * Class ApiLinkNotFoundException
 *
 * Link selected by ID couldn't be found, results in a 404 error.
 *
 * @package Shaarli\Api\Exceptions
 */
class ApiLinkNotFoundException extends ApiException
{
    /**
     * ApiLinkNotFoundException constructor.
     */
    public function __construct()
    {
        $this->message = 'Link not found';
    }

    /**
     * {@inheritdoc}
     */
    public function getApiResponse()
    {
        return $this->buildApiResponse(404);
    }
}
