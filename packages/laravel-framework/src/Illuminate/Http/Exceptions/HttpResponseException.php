<?php

namespace Illuminate\Http\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class HttpResponseException extends RuntimeException
{
    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;

        parent::__construct();
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
