<?php

namespace Shaarli\Api\Exceptions;

/**
 * Class ApiInternalException
 *
 * Generic exception, return a 500 HTTP code.
 */
class ApiInternalException extends ApiException
{
    /**
     * @inheritdoc
     */
    public function getApiResponse()
    {
        return $this->buildApiResponse(500);
    }
}
