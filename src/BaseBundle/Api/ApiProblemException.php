<?php

namespace BaseBundle\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiProblemException extends HttpException
{
    private $apiProblem;

    public function __construct(ApiProblem $apiProblem, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        $this->apiProblem = $apiProblem;
        $statusCode = $apiProblem->getStatusCode();
        $message = $apiProblem->getTitle();

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    public function getApiProblem()
    {
        return $this->apiProblem;
    }

    /***
     * Return exception default ApiProblem type
     * @param       $title
     * @param int   $errorType
     * @param array $extra
     */
    public static function throw($title, $errorType = 4000, $extra = [])
    {
        $problem = new ApiProblem($errorType);
        $problem->setTitle($title);

        if (count($extra) > 0 ){
            $problem->setExtra($extra);
        }

        throw new ApiProblemException($problem);

    }
}
