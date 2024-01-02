<?php

namespace App\Controller;

use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/rooms', name: 'rooms_')]
class RoomController extends AbstractController
{
    public function __construct(private readonly RoomRepository $roomRepository,
                                private readonly UserRepository $userRepository)
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
        $room = $this->roomRepository->find($id);
        if ($room === null) {
            throw $this->createNotFoundException('Místnost s ID ' . $id . 'nenalezena!');
        }

        return $this->render('rooms/detail.html.twig', [
            'room' => $room,
        ]);
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

    #[Route('/{id}/remove-occupant/{occupantId}', name: 'remove-occupant', requirements: ['id' => '\d+', 'occupantId' => '\d+'])]
    public function removeOccupant(Request $request, int $id, int $occupantId): Response
    {
        // Find room
        $room = $this->roomRepository->find($id);
        if ($room === null) {
            throw $this->createNotFoundException('Místnost s ID ' . $id . 'nenalezena!');
        }

        // Find user
        $user = $this->userRepository->find($occupantId);
        if ($user === null) {
            throw $this->createNotFoundException('Uživatel s ID ' . $occupantId . 'nenalezen!');
        }

        // Remove user from room
        $room->removeOccupant($user);
        $this->roomRepository->save($room);

        // Redirect back to room detail
        return $this->redirectToRoute('rooms_detail', ['id' => $id]);
    }
}