<?php

namespace App\Api\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractRestController extends AbstractFOSRestController
{
    protected function respond($data, int $statusCode = 200): Response
    {
        return $this->handleView($this->view($data, $statusCode));
    }
}