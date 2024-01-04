<?php
namespace App\Api\Controller;

use App\Api\Model\GroupInput;
use App\Api\Model\GroupOutput;
use App\Entity\Group;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use App\Repository\GroupRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Rest\Route(path: '/api/groups', name: 'api_groups')]
class GroupRestController extends AbstractRestController
{
    public function __construct(private readonly UserRepository $userRepository, private readonly GroupRepository $groupRepository, private readonly RoomRepository $roomRepository){

    }

    #[Rest\Get('/', name: 'api_groups_list')]
    public function list(Request $request) : Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $groups = \array_map(fn(Group $entity) => GroupOutput::fromEntity($entity), $this->groupRepository->findAll());

        return $this->respond($groups, Response::HTTP_OK);
    }

    #[Rest\Get('/{id}', name: 'api_groups_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $group = GroupOutput::fromEntity($this->findOrFail($id));
        return $this->respond($group, Response::HTTP_OK);
    }

    #[Rest\Put('/create', name: 'api_groups_create')]
    #[ParamConverter('newGroup', converter: 'fos_rest.request_body')]
    public function create(GroupInput $newGroup): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $group = $this->groupRepository->findByName($newGroup->name);

        if ($group !== null)
            return $this->respond(['error' => "This group name is already taken."], Response::HTTP_CONFLICT);

        $manager = $this->userRepository->find($newGroup->group_manager_id);
        if ($manager === null) {
            throw $this->createNotFoundException("Manager was not found");
        }

        $group = $newGroup->toEntity($manager);
        $this->groupRepository->save($group);

        return $this->respond(GroupOutput::fromEntity($group), Response::HTTP_CREATED);
    }

    #[Rest\Post('/{groupId}/add-room/{roomId}', name: 'api_groups_add_room')]
    public function addRoom (int $groupId, int $roomId) : Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $group = $this->findOrFail($groupId);
        $room = $this->roomRepository->find($roomId);

        if ($room === null) {
            throw $this->createNotFoundException("There is no such room by id $roomId");
        }

        $room->setBelongsTo($group);
        $this->roomRepository->save($room);

        $groupName = $group->getName();
        return $this->respond([ "message" => "Room by id $roomId was successfully signed to group $groupName"], Response::HTTP_OK);
    }

    #[Rest\Delete('/{groupId}/remove-room/{roomId}', name: 'api_groups_remove_room')]
    public function removeRoom (int $groupId, int $roomId) : Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $group = $this->findOrFail($groupId);
        $room = $this->roomRepository->find($roomId);

        if ($room === null) {
            throw $this->createNotFoundException("Room by id $roomId does not exist");
        }

        $room->setBelongsTo(null);
        $this->roomRepository->save($room);

        $groupName = $group->getName();
        return $this->respond([ "message" => "Room by id $roomId was successfully removed from group $groupName"], Response::HTTP_OK);
    }


    #[Rest\Post('/{groupId}/add-user/{userId}', name: 'api_groups_add_user')]
    public function addUser (int $groupId, int $userId) : Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $group = $this->findOrFail($groupId);
        $user = $this->userRepository->find($userId);

        if ($user === null) {
            throw $this->createNotFoundException("There is no such user by id $userId");
        }

        if ($user->getGroups()->contains($group))
            return $this->respond(['error' => "This user is already member of specified group."], Response::HTTP_BAD_REQUEST);

        $user->getGroups()->add($group);
        $this->userRepository->save($user);

        $groupName = $group->getName();
        return $this->respond([ "message" => "User by id $userId was successfully signed to group $groupName"], Response::HTTP_OK);
    }

    #[Rest\Delete('/{groupId}/remove-user/{userId}', name: 'api_groups_remove_user')]
    public function removeUser (int $groupId, int $userId) : Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $group = $this->findOrFail($groupId);
        $user = $this->userRepository->find($userId);


        if ($user === null) {
            throw $this->createNotFoundException("User by id $userId does not exist");
        }

        $user->getGroups()->removeElement($group);
        $this->userRepository->save($user);

        $groupName = $group->getName();
        return $this->respond([ "message" => "User by id $userId was successfully removed from group $groupName"], Response::HTTP_OK);
    }


    private function findOrFail(int $id): Group
    {
        $group = $this->groupRepository->find($id);
        if ($group === null) {
            throw $this->createNotFoundException("Group was not found");
        }

        return $group;
    }
}