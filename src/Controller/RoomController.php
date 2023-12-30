<?php

namespace App\Controller;

use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/rooms', name: 'rooms_')]
class RoomController extends AbstractController
{
    public function __construct(private readonly RoomRepository $roomRepository)
    {
    }

    #[Route('', name: 'list')]
    public function list(Request $request): Response
    {
        return $this->render('rooms/list.html.twig', [
            // TODO: Replace with actual data
            'my_rooms' => $this->roomRepository->findAll(),
            'managed_rooms' => $this->roomRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {

    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    #[Route('/create', name: 'create', defaults: ['id' => null])]
    public function edit(Request $request, ?int $id): Response
    {

    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(Request $request, int $id): Response
    {

    }
}