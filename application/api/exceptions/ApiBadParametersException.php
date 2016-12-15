<?php

namespace Shaarli\Api\Exceptions;

/**
 * Class ApiBadParametersException
 *
 * Invalid request exception, return a 400 HTTP code.
 */
class ApiBadParametersException extends ApiException
{
    /**
     * {@inheritdoc}
     */
    public function getApiResponse()
    {
        return $this->buildApiResponse(400);
    }
}
