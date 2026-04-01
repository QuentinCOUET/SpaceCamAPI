<?php

namespace App\Controller\Api;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use OpenApi\Attributes as OA;

#[Route('/api/users')]
#[OA\Tag(name: 'Users')]
class UserController extends AbstractController
{
    /**
     * List all users
     */
    #[Route('', name: 'api_users_index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of users',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'nom', type: 'string'),
                    new OA\Property(property: 'prenom', type: 'string'),
                ]
            )
        )
    )]
    public function index(UsersRepository $usersRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $usersRepository->findAll();
        $data = $serializer->serialize($users, 'json', ['groups' => 'user:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Get a single user by ID
     */
    #[Route('/{id}', name: 'api_users_show', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single user',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'nom', type: 'string'),
                new OA\Property(property: 'prenom', type: 'string'),
            ]
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The ID of the user to retrieve',
        schema: new OA\Schema(type: 'integer')
    )]
    public function show(Users $user, SerializerInterface $serializer): JsonResponse
    {
        $data = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
