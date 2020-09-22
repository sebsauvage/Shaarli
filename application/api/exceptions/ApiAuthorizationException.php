<?php

namespace Shaarli\Api\Exceptions;

/**
 * Class ApiAuthorizationException
 *
 * Request not authorized, return a 401 HTTP code.
 */
class ApiAuthorizationException extends ApiException
{
    /**
     * {@inheritdoc}
     */
    public function getApiResponse()
    {
        $this->setMessage('Not authorized');
        return $this->buildApiResponse(401);
    }

    /**
     * Set the exception message.
     *
     * We only return a generic error message in production mode to avoid giving
     * to much security information.
     *
     * @param $message string the exception message.
     */
    public function setMessage($message)
    {
        $original = $this->debug === true ? ': ' . $this->getMessage() : '';
        $this->message = $message . $original;
    }
}
