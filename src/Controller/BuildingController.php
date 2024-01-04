<?php

namespace App\Controller;

use App\Entity\Building;
use App\Form\BuildingType;
use App\Form\DeleteType;
use App\Repository\BuildingRepository;
use App\Repository\RoomRepository;
use App\Security\Voter\BuildingVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/buildings', name: 'buildings_')]
class BuildingController extends AbstractController
{
    public function __construct(private readonly BuildingRepository $buildingRepository,
                                private readonly RoomRepository     $roomRepository)
    {
    }

    #[Route('', name: 'list')]
    public function list(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('buildings/list.html.twig', [
            'buildings' => $this->buildingRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {
        $building = $this->buildingRepository->find($id);
        if ($building === null) {
            throw $this->createNotFoundException('Budova s ID ' . $id . 'nenalezena!');
        }

        $this->denyAccessUnlessGranted(BuildingVoter::VIEW, $building);

        return $this->render('buildings/detail.html.twig', [
            'building' => $building,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    #[Route('/create', name: 'create', defaults: ['id' => null])]
    public function edit(Request $request, ?int $id): Response
    {
        $building = ($id !== null) ? $this->findOrFail($id) : new Building();
        $this->denyAccessUnlessGranted(BuildingVoter::EDIT, $building);

        $form = $this->createForm(BuildingType::class, $building);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->buildingRepository->save($building, true);

            return $this->redirectToRoute('buildings_detail', ['id' => $building->getId(),]);
        }

        return $this->render('buildings/edit.html.twig',
            ['form' => $form->createView(),]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(Request $request, int $id): Response
    {
        $entity = $this->findOrFail($id);
        $this->denyAccessUnlessGranted(BuildingVoter::EDIT, $entity);

        $form = $this->createForm(DeleteType::class, [], [
            'entity' => 'request'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->buildingRepository->remove($entity, true);

            $this->addFlash('success', "Building was deleted");
            return $this->redirectToRoute('buildings_list');
        }

        return $this->render('buildings/delete.html.twig', [
            'form' => $form->createView(),
            'entity' => $entity,
        ]);
    }

    private function findOrFail(int $id): Building
    {
        $building = $this->buildingRepository->find($id);
        if ($building === null) {
            throw $this->createNotFoundException();
        }

        return $building;
    }

    #[Route('/{id}/remove-room/{roomId}', name: 'remove-room', requirements: ['id' => '\d+', 'roomId' => '\d+'])]
    public function removeRoom(Request $request, int $id, int $roomId): Response
    {
        // Find building
        $building = $this->buildingRepository->find($id);
        if ($building === null) {
            throw $this->createNotFoundException('Budova s ID ' . $id . 'nenalezena!');
        }

        $this->denyAccessUnlessGranted(BuildingVoter::EDIT, $building);

        // Find room
        $room = $this->roomRepository->find($roomId);
        if ($room === null) {
            throw $this->createNotFoundException('Místnost s ID ' . $roomId . 'nenalezena!');
        }

        // Remove room
        $building->removeRoom($room);
        $this->buildingRepository->save($building);

        // Redirect back to detail
        return $this->redirectToRoute('buildings_detail', ['id' => $id]);
    }
}