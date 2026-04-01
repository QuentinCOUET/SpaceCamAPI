<?php

namespace App\Controller\Api;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use OpenApi\Attributes as OA;

#[Route('/api')]
#[OA\Tag(name: 'Authentication')]
class AuthController extends AbstractController
{
    /**
     * Register a new user
     */
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'User registered successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'nom', type: 'string', example: 'Dupont'),
                new OA\Property(property: 'prenom', type: 'string', example: 'Jean'),
            ]
        )
    )]
    #[OA\RequestBody(
        description: 'User registration data',
        required: true,
        content: new OA\JsonContent(
            required: ['nom', 'prenom', 'password'],
            properties: [
                new OA\Property(property: 'nom', type: 'string', example: 'Dupont'),
                new OA\Property(property: 'prenom', type: 'string', example: 'Jean'),
                new OA\Property(property: 'password', type: 'string', example: 'SecurePassword123'),
            ]
        )
    )]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        SerializerInterface $serializer
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation basique
            if (!isset($data['nom'], $data['prenom'], $data['password'])) {
                return new JsonResponse(
                    ['error' => 'Missing required fields: nom, prenom, password'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Vérifier que l'utilisateur n'existe pas déjà
            $existingUser = $em->getRepository(Users::class)->findBy(['nom' => $data['nom']]);
            if (!empty($existingUser)) {
                return new JsonResponse(
                    ['error' => 'User with this name already exists'],
                    Response::HTTP_CONFLICT
                );
            }

            // Créer le nouvel utilisateur
            $user = new Users();
            $user->setNom($data['nom']);
            $user->setPrenom($data['prenom']);

            // Hasher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']); // Rôle par défaut

            $em->persist($user);
            $em->flush();

            $serialized = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

            return new JsonResponse($serialized, Response::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred during registration: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Login user (form-based)
     */
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Login page or form',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Send POST with nom and password'),
            ]
        )
    )]
    public function login(): JsonResponse
    {
        $user = $this->getUser();

        if ($user) {
            return new JsonResponse([
                'message' => 'Already logged in',
                'user' => $user->getNom(),
            ]);
        }

        return new JsonResponse([
            'message' => 'Send POST request with nom and password in form data',
            'example' => [
                'nom' => 'john',
                'password' => 'password123',
            ],
        ]);
    }

    /**
     * Check login (Custom authentication)
     */
    #[Route('/login_check', name: 'api_login_check', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Successfully logged in',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'nom', type: 'string', example: 'john'),
                new OA\Property(property: 'prenom', type: 'string', example: 'John Doe'),
            ]
        )
    )]
    #[OA\RequestBody(
        description: 'Login credentials',
        required: true,
        content: new OA\JsonContent(
            required: ['nom', 'password'],
            properties: [
                new OA\Property(property: 'nom', type: 'string', example: 'alice'),
                new OA\Property(property: 'password', type: 'string', example: 'Password123'),
            ]
        )
    )]
    public function loginCheck(
        Request $request,
        UsersRepository $usersRepository,
        SerializerInterface $serializer,
        \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['nom'], $data['password'])) {
            return new JsonResponse(
                ['error' => 'Missing nom or password'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Rechercher l'utilisateur
        $user = $usersRepository->findOneBy(['nom' => $data['nom']]);

        if (!$user) {
            return new JsonResponse(
                ['error' => 'Invalid credentials'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Vérifier le mot de passe
        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(
                ['error' => 'Invalid credentials'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Créer la session - Symfony va gérer automatiquement le cookie
        $this->container->get('security.token_storage')->setToken(
            new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
                $user,
                'main',
                $user->getRoles()
            )
        );

        $serialized = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($serialized, Response::HTTP_OK, [], true);
    }


    /**
     * Get current user info
     */
    #[Route('/me', name: 'api_me', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Current user information',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'nom', type: 'string', example: 'john'),
                new OA\Property(property: 'prenom', type: 'string', example: 'John Doe'),
            ]
        )
    )]
    public function me(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(
                ['error' => 'Not authenticated'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $serialized = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($serialized, Response::HTTP_OK, [], true);
    }
}

