<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BuildingController extends AbstractController
{
	#[Route('/buildings', name: 'buildings_list')]
    public function list(Request $request): Response
    {

    }
    
    #[Route('/buildings/{id}', name: 'buildings_detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {

    }
}