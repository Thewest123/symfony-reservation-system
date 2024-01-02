<?php

namespace App\Controller;

use App\Forms\AddUserToGroupType;
use App\Repository\GroupRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users', name: 'users_')]
class UserController extends AbstractController
{
    public function __construct(private readonly UserRepository  $userRepository,
                                private readonly GroupRepository $groupRepository,
                                private readonly RoomRepository  $roomRepository)
    {
    }

    #[Route('', name: 'list')]
    public function list(Request $request): Response
    {
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('users/list.html.twig', [
            // TODO: Replace with actual data
            'basic_users' => $this->userRepository->findAll(),
            'manager_users' => $this->userRepository->findAll(),
            'admin_users' => $this->userRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {
        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw $this->createNotFoundException('Uživatel s ID ' . $id . ' nenalezen!');
        }

        return $this->render('users/detail.html.twig', [
            'user' => $user,
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

    #[Route('/{id}/remove-group/{groupId}', name: 'remove-group', requirements: ['id' => '\d+', 'groupId' => '\d+'])]
    public function removeGroup(Request $request, int $id, int $groupId): Response
    {
        // Find user
        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw $this->createNotFoundException('Uživatel s ID ' . $id . ' nenalezen!');
        }

        // Find group
        $group = $this->groupRepository->find($groupId);
        if ($group === null) {
            throw $this->createNotFoundException('Skupina s ID ' . $groupId . ' nenalezena!');
        }

        // Remove user from group
        $user->removeGroup($group);
        $this->userRepository->save($user);

        // Redirect back to user detail
        return $this->redirectToRoute('users_detail', ['id' => $id]);
    }

    #[Route('/{id}/add-group', name: 'add-group', requirements: ['id' => '\d+'])]
    public function addGroup(Request $request, int $id): Response
    {
        // Find user
        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw $this->createNotFoundException('Uživatel s ID ' . $id . ' nenalezen!');
        }

        // Create form to choose a group
        $form = $this->createForm(AddUserToGroupType::class, null, [
            'user' => $user,
        ]);
        $form->handleRequest($request);

        // Handle form
        if ($form->isSubmitted() && $form->isValid()) {
            $group = $form->get('group')->getData();

            if ($group === null) {
                throw $this->createNotFoundException('Skupina nenalezena!');
            }

            // Add user to group
            $user->addGroup($group);
            $this->userRepository->save($user);

            // Redirect back to user detail
            return $this->redirectToRoute('users_detail', ['id' => $id]);
        }

        // Render form
        return $this->render('users/add-group.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/{id}/remove-managed-group/{groupId}', name: 'remove-managed-group', requirements: ['id' => '\d+', 'groupId' => '\d+'])]
    public function removeManagedGroup(Request $request, int $id, int $groupId): Response
    {
        // Find user
        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw $this->createNotFoundException('Uživatel s ID ' . $id . ' nenalezen!');
        }

        // Find group
        $group = $this->groupRepository->find($groupId);
        if ($group === null) {
            throw $this->createNotFoundException('Skupina s ID ' . $groupId . ' nenalezena!');
        }

        // Remove user from group
        $user->removeManagedGroup($group);
        $this->userRepository->save($user);

        // Redirect back to user detail
        return $this->redirectToRoute('users_detail', ['id' => $id]);
    }

    #[Route('/{id}/remove-managed-room/{roomId}', name: 'remove-managed-room', requirements: ['id' => '\d+', 'roomId' => '\d+'])]
    public function removeManagedRoom(Request $request, int $id, int $roomId): Response
    {
        // Find user
        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw $this->createNotFoundException('Uživatel s ID ' . $id . ' nenalezen!');
        }

        // Find room
        $room = $this->roomRepository->find($roomId);
        if ($room === null) {
            throw $this->createNotFoundException('Místnost s ID ' . $roomId . ' nenalezena!');
        }

        // Remove user from room
        $user->removeManagedRoom($room);
        $this->userRepository->save($user);

        // Redirect back to user detail
        return $this->redirectToRoute('users_detail', ['id' => $id]);
    }
}