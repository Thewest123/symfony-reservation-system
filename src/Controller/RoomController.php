<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\User;
use App\Form\DeleteType;
use App\Form\RoomType;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Security\Voter\RoomVoter;
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
        // Get user
        $user = $this->getUser();
        // Check authentication
        if ($user === null) {

            // User is not authenticated, return public rooms only
            $publicRooms = $this->roomRepository->findBy(['isPrivate' => false]);

            return $this->render('rooms/list.html.twig', [
                'public_rooms' => $publicRooms,
            ]);
        }

        // Cast user to User object
        $user = $user instanceof User ? $user : null;

        // Get user's rooms
        $myRooms = $user->getRooms();

        // Get rooms managed by the user (using RoomVoter)
        $allRooms = $this->roomRepository->findAll();
        $managedRooms = [];
        foreach ($allRooms as $room) {
            if ($this->isGranted(RoomVoter::MANAGE, $room)) {
                $managedRooms[] = $room;
            }
        }

        return $this->render('rooms/list.html.twig', [
            'my_rooms' => $myRooms,
            'managed_rooms' => $managedRooms,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {
        $room = $this->roomRepository->find($id);
        if ($room === null) {
            throw $this->createNotFoundException('Místnost s ID ' . $id . 'nenalezena!');
        }

        $this->denyAccessUnlessGranted(RoomVoter::VIEW, $room);

        return $this->render('rooms/detail.html.twig', [
            'room' => $room,
            'is_manager' => $this->isGranted(RoomVoter::MANAGE, $room)
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    #[Route('/create', name: 'create', defaults: ['id' => null])]
    public function edit(Request $request, ?int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($id !== null) {
            $room = $this->findOrFail($id);
        } else {
            $room = new Room();
            $room->setRoomManager($this->getUser());
        }

        $form = $this->createForm(RoomType::class, $room, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->roomRepository->save($room, true);

            return $this->redirectToRoute('rooms_detail', ['id' => $room->getId(),]);
        }

        return $this->render('rooms/edit.html.twig',
            ['form' => $form->createView(),]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(Request $request, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $entity = $this->findOrFail($id);

        $form = $this->createForm(DeleteType::class, [], [
            'entity' => 'room'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->roomRepository->remove($entity, true);

            $this->addFlash('success', "Room was deleted");
            return $this->redirectToRoute('rooms_list');
        }

        return $this->render('rooms/delete.html.twig', [
            'form' => $form->createView(),
            'entity' => $entity,
        ]);
    }

    private function findOrFail(int $id): Room
    {
        $user = $this->roomRepository->find($id);
        if ($user === null) {
            throw $this->createNotFoundException();
        }

        return $user;
    }

    #[Route('/{id}/remove-occupant/{occupantId}', name: 'remove-occupant', requirements: ['id' => '\d+', 'occupantId' => '\d+'])]
    public function removeOccupant(Request $request, int $id, int $occupantId): Response
    {
        // Find room
        $room = $this->roomRepository->find($id);
        if ($room === null) {
            throw $this->createNotFoundException('Místnost s ID ' . $id . 'nenalezena!');
        }

        // Check permissions
        $this->denyAccessUnlessGranted(RoomVoter::MANAGE, $room);

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