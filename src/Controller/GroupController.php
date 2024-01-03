<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\DeleteType;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/groups', name: 'groups_')]
class GroupController extends AbstractController
{
    public function __construct(private readonly GroupRepository $groupRepository,
                                private readonly RoomRepository  $roomRepository)
    {
    }

    #[Route('', name: 'list')]
    public function list(Request $request): Response
    {
        return $this->render('groups/list.html.twig', [
            'groups' => $this->groupRepository->findAll(),
        ]);
    }

    #[Route('/{id}/subgroups', name: 'subgroups')]
    public function subgroups(Request $request, int $id): Response
    {
        return $this->render('groups/subgroups.html.twig', [
            'groups' => $this->groupRepository->findRecursive($id),
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {
        $group = $this->groupRepository->find($id);
        if ($group === null) {
            throw $this->createNotFoundException('Skupina s ID ' . $id . 'nenalezena!');
        }

        return $this->render('groups/detail.html.twig', [
            'group' => $group,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    #[Route('/create', name: 'create', defaults: ['id' => null])]
    public function edit(Request $request, ?int $id): Response
    {
        #$group = ($id !== null) ? $this->findOrFail($id) : new Group();
        if ($id !== null) {
            $group = $this->findOrFail($id);
        } else {
            $group = new Group();
            $group->setGroupManager($this->getUser());
        }

        // get available groups
        $user = $this->getUser();
        $groups = array_merge($user->getManagedGroups()->toArray(), $user->getGroups()->toArray());
        $subgroups = [];
        foreach($groups as $key => $subgroup) {
            $subgroups = array_merge($subgroups, $this->groupRepository->findRecursive($subgroup->getId()));
        }
        $subgroups = array_unique($subgroups, SORT_REGULAR);

        $form = $this->createForm(GroupType::class, $group, ['groups' => $subgroups]);

        return $this->render('groups/add.html.twig',
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
            $this->groupRepository->remove($entity, true);

            $this->addFlash('success', "Group was deleted");
            return $this->redirectToRoute('groups_list');
        }

        return $this->render('groups/delete.html.twig', [
            'form' => $form->createView(),
            'entity' => $entity,
        ]);
    }

    private function findOrFail(int $id): Group
    {
        $group = $this->groupRepository->find($id);
        if ($group === null) {
            throw $this->createNotFoundException();
        }

        return $group;
    }

    #[Route('/{id}/remove-subgroup/{subId}', name: 'remove-subgroup', requirements: ['id' => '\d+', 'subId' => '\d+'])]
    public function removeSubgroup(Request $request, int $id, int $subId): Response
    {
        // Find group
        $group = $this->groupRepository->find($id);
        if ($group === null) {
            throw $this->createNotFoundException('Skupina s ID ' . $id . 'nenalezena!');
        }

        // Find subgroup
        $subgroup = $this->groupRepository->find($subId);
        if ($subgroup === null) {
            throw $this->createNotFoundException('Podskupina s ID ' . $subId . 'nenalezena!');
        }

        // Remove subgroup
        $group->removeSubgroup($subgroup);
        $this->groupRepository->save($group);

        // Redirect back to detail
        return $this->redirectToRoute('groups_detail', ['id' => $id]);
    }

    #[Route('/{id}/remove-room/{roomId}', name: 'remove-room', requirements: ['id' => '\d+', 'roomId' => '\d+'])]
    public function removeRoom(Request $request, int $id, int $roomId): Response
    {
        // Find group
        $group = $this->groupRepository->find($id);
        if ($group === null) {
            throw $this->createNotFoundException('Skupina s ID ' . $id . 'nenalezena!');
        }

        // Find room
        $room = $this->roomRepository->find($roomId);
        if ($room === null) {
            throw $this->createNotFoundException('Místnost s ID ' . $roomId . 'nenalezena!');
        }

        // Remove room
        $group->removeRoom($room);
        $this->groupRepository->save($group);

        // Redirect back to detail
        return $this->redirectToRoute('groups_detail', ['id' => $id]);
    }

}
