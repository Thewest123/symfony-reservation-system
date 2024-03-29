<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AddUserToGroupType;
use App\Form\DeleteType;
use App\Form\UserType;
use App\Repository\GroupRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        //$this->denyAccessUnlessGranted(UserVoter::VIEW, $this->getUser());

        // Get users that manage at least one room or group (inverse association)
        $managerUsers = [];
        $basicUsers = [];
        foreach ($this->userRepository->findAll() as $user) {
            if (count($user->getManagedRooms()) > 0 || count($user->getManagedGroups()) > 0) {
                $managerUsers[] = $user;
            } else {
                $basicUsers[] = $user;
            }
        }

        // Get admin users
        $adminUsers = $this->userRepository->findByRole('ROLE_ADMIN');

        return $this->render('users/list.html.twig', [
            'manager_users' => $managerUsers,
            'admin_users' => $adminUsers,
            'basic_users' => $basicUsers,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {
        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw $this->createNotFoundException('Uživatel s ID ' . $id . ' nenalezen!');
        }

        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);

        return $this->render('users/detail.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    #[Route('/create', name: 'create', defaults: ['id' => null])]
    public function edit(Request $request, ?int $id): Response
    {
        $user = ($id !== null) ? $this->findOrFail($id) : new User();
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->groupRepository->save($user, true);

            return $this->redirectToRoute('users_detail', ['id' => $user->getId(),]);
        }

        return $this->render('users/edit.html.twig',
            ['form' => $form->createView(),]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(Request $request, int $id): Response
    {
        $entity = $this->findOrFail($id);

        $form = $this->createForm(DeleteType::class, [], [
            'entity' => 'request'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userRepository->remove($entity, true);

            $this->addFlash('success', "User was deleted");
            return $this->redirectToRoute('users_list');
        }

        return $this->render('users/delete.html.twig', [
            'form' => $form->createView(),
            'entity' => $entity,
        ]);

    }

    private function findOrFail(int $id): User
    {
        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw $this->createNotFoundException();
        }

        return $user;
    }

    #[Route('/{id}/remove-group/{groupId}', name: 'remove-group', requirements: ['id' => '\d+', 'groupId' => '\d+'])]
    public function removeGroup(Request $request, int $id, int $groupId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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

        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

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

        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        // Remove user from room
        $user->removeManagedRoom($room);
        $this->userRepository->save($user);

        // Redirect back to user detail
        return $this->redirectToRoute('users_detail', ['id' => $id]);
    }
}