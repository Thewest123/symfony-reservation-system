<?php

namespace App\Api\Controller;

use App\Api\Model\RequestInput;
use App\Api\Model\RequestOutput;
use App\Repository\GroupRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Repository\RequestRepository;
use App\Security\Voter\RequestVoter;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Rest\Route(path: '/api/requests', name: 'api_requests')]
class RequestRestController extends AbstractRestController
{
    public function __construct(private readonly RequestRepository $requestRepository
    ){
    }

    #[Rest\Get('/', name: 'api_requests_list')]
    public function list(Request $request) : Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $reqs = $this->requestRepository->findAll();

        $myReqs = [];
        foreach ($reqs as $req) {
            if ($this->isGranted(RequestVoter::VIEW, $req)) {
                $myReqs[] = $req;
            }
        }

        $requests = \array_map(fn(\App\Entity\Request $entity) => RequestOutput::fromEntity($entity), $myReqs);

        return $this->respond($requests, Response::HTTP_OK);
    }

    #[Rest\Get('/{id}', name: 'api_requests_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id): Response
    {
        $request = $this->findOrFail($id);
        $this->denyAccessUnlessGranted(RequestVoter::VIEW, $request);

        $requestDto = RequestOutput::fromEntity($request);
        return $this->respond( $requestDto, Response::HTTP_OK);
    }

    #[Rest\Post('/', name: 'api_requests_create')]
    #[ParamConverter('newRequest', converter: 'fos_rest.request_body')]
    public function create(RequestInput $newRequest): Response
    {
//        $request = $this->findOrFail($id);
//        $this->denyAccessUnlessGranted(RequestVoter::VIEW, $request);

//        $requestDto = RequestOutput::fromEntity($request);
//        return $this->respond( $requestDto, Response::HTTP_OK);
    }


    #[Rest\Delete('/{id}', name: 'api_requests_remove', requirements: ['id' => '\d+'])]
    public function remove(int $id): Response
    {
        $request = $this->findOrFail($id);
        $this->denyAccessUnlessGranted(RequestVoter::MANAGE, $request);

        $this->requestRepository->remove($id);
        return $this->respond([ "message" => "Request by id $id was successfully removed from database"], Response::HTTP_OK);
    }


    private function findOrFail(int $id): \App\Entity\Request
    {
        $request= $this->requestRepository->find($id);
        if ($request=== null) {
            throw $this->createNotFoundException("Request was not found");
        }

        return $request;
    }

}