<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoomController extends AbstractController
{
    #[Route('/rooms', name: 'rooms_list')]
    public function list(Request $request): Response
    {

    }
    
    #[Route('/rooms/{id}', name: 'rooms_detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {

    }
}