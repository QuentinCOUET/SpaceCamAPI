<?php

namespace App\Controller\Api;

use App\Entity\Cam;
use App\Entity\Users;
use App\Repository\CamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\HttpFoundation\Request;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;

use OpenApi\Attributes as OA;

#[Route('/api/cams')]
#[OA\Tag(name: 'Cameras')]
class CamController extends AbstractController
{
    /**
     * List all cameras
     */
    #[Route('', name: 'api_cams_index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of cameras',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'nom', type: 'string'),
                    new OA\Property(property: 'videoUrl', type: 'string'),
                    new OA\Property(property: 'ipCam', type: 'string'),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                ]
            )
        )
    )]
    public function index(CamRepository $camRepository, SerializerInterface $serializer): JsonResponse
    {
        $cams = $camRepository->findAll();
        $data = $serializer->serialize($cams, 'json', ['groups' => 'cam:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Get a single camera by ID
     */
    #[Route('/{id}', name: 'api_cams_show', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single camera',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'nom', type: 'string'),
                new OA\Property(property: 'videoUrl', type: 'string'),
                new OA\Property(property: 'ipCam', type: 'string'),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
            ]
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The ID of the camera to retrieve',
        schema: new OA\Schema(type: 'integer')
    )]
    public function show(Cam $cam, SerializerInterface $serializer): JsonResponse
    {
        $data = $serializer->serialize($cam, 'json', ['groups' => 'cam:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Get all cameras for a specific user
     */
    #[Route('/user/{id}', name: 'api_cams_by_user', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of cameras for a user',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'nom', type: 'string'),
                    new OA\Property(property: 'videoUrl', type: 'string'),
                    new OA\Property(property: 'ipCam', type: 'string'),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                ]
            )
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The ID of the user',
        schema: new OA\Schema(type: 'integer')
    )]
    public function byUser(Users $user, CamRepository $camRepository, SerializerInterface $serializer): JsonResponse
    {
        $cams = $camRepository->findBy(['owner' => $user]);
        $data = $serializer->serialize($cams, 'json', ['groups' => 'cam:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Create a new camera
     */
    #[Route('', name: 'api_cams_new', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Creates a new camera and returns it',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'nom', type: 'string'),
                new OA\Property(property: 'videoUrl', type: 'string'),
                new OA\Property(property: 'ipCam', type: 'string'),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
            ]
        )
    )]
    #[OA\RequestBody(
        description: 'Camera object that needs to be added',
        required: true,
        content: new OA\JsonContent(
            required: ['nom', 'videoUrl', 'ipCam'],
            properties: [
                new OA\Property(property: 'nom', type: 'string'),
                new OA\Property(property: 'videoUrl', type: 'string'),
                new OA\Property(property: 'ipCam', type: 'string'),
            ]
        )
    )]
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $cam = new Cam();
        $cam->setNom($data['nom']);
        $cam->setVideoUrl($data['videoUrl']);
        $cam->setIpCam($data['ipCam']);
        $cam->setCreatedAt(new \DateTimeImmutable());

        $cam->setOwner($user);

        $em->persist($cam);
        $em->flush();

        $data = $serializer->serialize($cam, 'json', ['groups' => 'cam:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }
}
