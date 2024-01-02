<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Request as Req;
use App\Form\RequestType;
use App\Form\DeleteType;
use App\Repository\RequestRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/requests', name: 'requests_')]
class RequestController extends AbstractController
{
    public function __construct(private readonly RequestRepository $requestRepository,
                                private readonly UserRepository    $userRepository)
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
        $req = $this->requestRepository->find($id);
        if ($req === null) {
            throw $this->createNotFoundException('Žádost s ID ' . $id . 'nenalezena!');
        }

        return $this->render('requests/detail.html.twig', [
            'req' => $req,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    #[Route('/create', name: 'create', defaults: ['id' => null])]
    public function edit(Request $request, ?int $id): Response
    {
        if ($id !== null) {
            $request = $this->findOrFail($id);
        } else {
            $request = new Req();
            $request->setAuthor($this->getUser());
        }
        $form = $this->createForm(RequestType::class, [], ['request' => $request]);

        return $this->render('requests/add.html.twig',
            ['form' => $form->createView(),
             'request' => $request,]);
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
            $this->requestRepository->remove($entity, true);

            $this->addFlash('success', "Request was deleted");
            return $this->redirectToRoute('requests_list');
        }

        return $this->render('requests/delete.html.twig', [
            'form' => $form->createView(),
            'entity' => $entity,
        ]);
    }

    private function findOrFail(int $id): Req
    {
        $request = $this->requestRepository->find($id);
        if ($request === null) {
            throw $this->createNotFoundException();
        }

        return $request;
    }

    #[Route('/{id}/remove-attendee/{attendeeId}', name: 'remove-occupant', requirements: ['id' => '\d+', 'attendeeId' => '\d+'])]
    public function removeAttendee(Request $request, int $id, int $attendeeId): Response
    {
        // Find room
        $req = $this->requestRepository->find($id);
        if ($req === null) {
            throw $this->createNotFoundException('Místnost s ID ' . $id . 'nenalezena!');
        }

        // Find user
        $user = $this->userRepository->find($attendeeId);
        if ($user === null) {
            throw $this->createNotFoundException('Uživatel s ID ' . $attendeeId . 'nenalezen!');
        }

        // Remove user from room
        $req->removeAttendee($user);
        $this->requestRepository->save($req);

        // Redirect back to room detail
        return $this->redirectToRoute('requests_detail', ['id' => $id]);
    }
}