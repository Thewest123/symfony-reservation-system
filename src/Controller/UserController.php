<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'users_list')]
    public function list(Request $request): Response
    {

    }
    
    #[Route('/users/{id}', name: 'users_detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {

    }
}