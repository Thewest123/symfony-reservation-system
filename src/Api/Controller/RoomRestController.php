<?php

namespace App\Api\Controller;

use App\Api\Model\RoomOutput;
use App\Entity\Room;
use App\Repository\RoomRepository;
use App\Security\Voter\RoomVoter;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Rest\Route(path: '/api/rooms', name: 'api_rooms')]
class RoomRestController extends AbstractRestController
{
    public function __construct(private readonly RoomRepository $roomRepository,
    ){
    }

    #[Rest\Get('/', name: 'api_rooms_list')]
    public function list(Request $request) : Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');
        $rooms = \array_map(fn(Room $entity) => RoomOutput::fromEntity($entity),  $this->roomRepository->findAll);
        return $this->respond($rooms, Response::HTTP_OK);
    }

    #[Rest\Get('/{id}', name: 'api_rooms_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id): Response
    {
        $room = $this->findOrFail($id);
        $this->denyAccessUnlessGranted(RoomVoter::VIEW, $room);

        $roomDto = RoomOutput::fromEntity($room);
        return $this->respond( $roomDto, Response::HTTP_OK);
    }

    private function findOrFail(int $id): Room
    {
        $room = $this->roomRepository->find($id);
        if ($room === null) {
            throw $this->createNotFoundException("Room was not found");
        }

        return $room ;
    }
}