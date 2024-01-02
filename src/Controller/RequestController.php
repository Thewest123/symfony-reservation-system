<?php

namespace App\Controller;

use App\Repository\RequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/requests', name: 'requests_')]
class RequestController extends AbstractController
{
    public function __construct(private readonly RequestRepository $requestRepository)
    {
    }

    #[Route('', name: 'list')]
    public function list(Request $request): Response
    {
        return $this->render('requests/list.html.twig', [
            // TODO: Replace with actual data
            'approved_requests' => $this->requestRepository->findAll(),
            'awaiting_requests' => $this->requestRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {
        return $this->render('requests/detail.html.twig', [
            'req' => $this->requestRepository->find($id),
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
}