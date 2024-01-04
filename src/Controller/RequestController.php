<?php

namespace App\Controller;

use App\Entity\Request as Req;
use App\Form\DeleteType;
use App\Form\RequestType;
use App\Repository\RequestRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Security\Voter\RequestVoter;
use App\Security\Voter\RoomVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/requests', name: 'requests_')]
class RequestController extends AbstractController
{
    public function __construct(private readonly RequestRepository $requestRepository,
                                private readonly UserRepository    $userRepository,
                                private readonly RoomRepository    $roomRepository)
    {
    }

    #[Route('', name: 'list')]
    public function list(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        // Get all requests
        $reqs = $this->requestRepository->findAll();

        // Get user's requests (can be viewed by the user)
        $myReqs = [];
        foreach ($reqs as $req) {
            if ($this->isGranted(RequestVoter::VIEW, $req)) {
                $myReqs[] = $req;
            }
        }

        // Sort user's requests by status
        $approvedReqs = [];
        $awaitingReqs = [];
        foreach ($myReqs as $req) {
            if ($req->isApproved()) {
                $approvedReqs[] = $req;
            } else {
                $awaitingReqs[] = $req;
            }
        }

        return $this->render('requests/list.html.twig', [
            'approved_requests' => $approvedReqs,
            'awaiting_requests' => $awaitingReqs,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(Request $request, int $id): Response
    {
        $req = $this->requestRepository->find($id);
        if ($req === null) {
            throw $this->createNotFoundException('Žádost s ID ' . $id . 'nenalezena!');
        }

        $this->denyAccessUnlessGranted(RequestVoter::VIEW, $req);

        return $this->render('requests/detail.html.twig', [
            'req' => $req,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    #[Route('/create', name: 'create', defaults: ['id' => null])]
    public function edit(Request $request, ?int $id): Response
    {
        if ($id !== null) {
            $req = $this->findOrFail($id);
        } else {
            $req = new Req();
            $req->setAuthor($this->getUser());

            // Pre-set room if in query parameter
            $forRoomId = $request->query->get('forRoomId') ?? null;
            if ($forRoomId !== null) {
                $forRoom = $this->roomRepository->find($forRoomId);
                if ($forRoom !== null) {
                    $req->setRequestedRoom($forRoom);
                }
            }
        }
        $this->denyAccessUnlessGranted(RequestVoter::EDIT, $req);

        // Get all rooms the user can choose from
        $allowedRooms = [];
        foreach ($this->roomRepository->findAll() as $room) {
            if ($this->isGranted(RoomVoter::VIEW, $room)) {
                $allowedRooms[] = $room;
            }
        }

        // Check permissions
        $canApprove = $this->isGranted(RequestVoter::MANAGE, $req);

        $form = $this->createForm(RequestType::class, $req, ['request' => $req, 'allowed_rooms' => $allowedRooms, 'can_approve' => $canApprove]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // If new request is created by non-manager, they can't set isApproved, therefore it's null
            if ($req->isApproved() === null) {
                $req->setApproved(false);
            }

            $this->requestRepository->save($req, true);

            return $this->redirectToRoute('requests_detail', ['id' => $req->getId(),]);
        }

        return $this->render('requests/edit.html.twig',
            ['form' => $form->createView(),
                'request' => $request,]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(Request $request, int $id): Response
    {
        $entity = $this->findOrFail($id);
        $this->denyAccessUnlessGranted(RequestVoter::EDIT, $entity);

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
        // Find attendee
        $req = $this->requestRepository->find($id);
        if ($req === null) {
            throw $this->createNotFoundException('Místnost s ID ' . $id . 'nenalezena!');
        }

        $this->denyAccessUnlessGranted(RequestVoter::EDIT, $req);

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