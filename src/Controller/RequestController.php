<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RequestController extends AbstractController
{
    #[Route('/requests', name: 'requests_list')]
    public function list(Request $request): Response
    {

    }

    #[Route('/requests/{id}', name: 'requests_detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {

    }
}