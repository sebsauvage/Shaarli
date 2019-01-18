<?php

namespace Shaarli\Api\Exceptions;

/**
 * Class ApiTagNotFoundException
 *
 * Tag selected by name couldn't be found in the datastore, results in a 404 error.
 *
 * @package Shaarli\Api\Exceptions
 */
class ApiTagNotFoundException extends ApiException
{
    /**
     * ApiLinkNotFoundException constructor.
     */
    public function __construct()
    {
        $this->message = 'Tag not found';
    }

    /**
     * {@inheritdoc}
     */
    public function getApiResponse()
    {
        return $this->buildApiResponse(404);
    }
}
