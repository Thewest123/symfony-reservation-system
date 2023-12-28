<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    #[Route('/groups', name: 'groups_list')]
    public function list(Request $request): Response
    {

    }
    
    #[Route('/groups/{id}', name: 'groups_detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {

    }
}