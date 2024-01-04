<?php

namespace App\Api\Controller;

use App\Api\Model\UserInput;
use App\Api\Model\UserOutput;
use App\Entity\User;
use App\Repository\UserRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Rest\Route(path: '/api/users', name: 'api_users')]
class UserRestController extends AbstractRestController
{
    public function __construct(private readonly UserRepository $userRepository, private UserPasswordHasherInterface $hasher){

    }

    #[Rest\Get('/me', name: 'api_users_me')]
    public function me(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = $this->getUser();

        return $this->respond(['username' => $user->getUsername()], Response::HTTP_OK);
    }

    #[Rest\Put('/register', name: 'api_users_register')]
    #[ParamConverter('newUser', converter: 'fos_rest.request_body')]
    public function register(UserInput $newUser): Response
    {
        $user = $this->userRepository->findByUsername($newUser->username);

        if ($user !== null)
            return $this->respond(['error' => "This username is already taken."], Response::HTTP_CONFLICT);

        $user = $newUser->toEntity($this->hasher);
        $this->userRepository->save($user);

        return $this->respond(UserOutput::fromEntity($user), Response::HTTP_CREATED);
    }


    #[Rest\Get('/', name: 'api_users_list')]
    public function list(Request $request) : Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $users = \array_map(fn(User $entity) => UserOutput::fromEntity($entity), $this->userRepository->findAll());

        return $this->respond($users, Response::HTTP_OK);
    }

    #[Rest\Get('/{id}', name: 'api_users_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = UserOutput::fromEntity($this->findOrFail($id));
        return $this->respond($user, Response::HTTP_OK);
    }

    private function findOrFail(int $id): User
    {
        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw $this->createNotFoundException("User was not found");
        }

        return $user;
    }

}