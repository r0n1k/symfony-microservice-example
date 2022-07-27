<?php


namespace App\Http\Formatter;

class CustomJsonResponse
{

    private $response;

    private int $statusCode;


    public function __construct($response, int $statusCode = 200)
    {
        $this->response = $response;
        $this->statusCode = $statusCode;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
