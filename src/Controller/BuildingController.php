<?php

namespace App\Controller;

use App\Entity\Building;
use App\Form\DeleteType;
use App\Repository\BuildingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/buildings', name: 'buildings_')]
class BuildingController extends AbstractController
{
    public function __construct(private readonly BuildingRepository $buildingRepository)
    {
    }

    #[Route('', name: 'list')]
    public function list(Request $request): Response
    {

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
        $entity = $this->findOrFail($id);

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
}