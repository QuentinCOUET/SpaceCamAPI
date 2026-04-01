<?php

namespace App\Controller\Api;

use App\Entity\Photo;
use App\Entity\Cam;
use App\Repository\PhotoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use App\Repository\CamRepository;

use OpenApi\Attributes as OA;

#[Route('/api/photos')]
#[OA\Tag(name: 'Photos')]
class PhotoController extends AbstractController
{
    /**
     * List all photos
     */
    #[Route('', name: 'api_photos_index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of photos',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'nom', type: 'string'),
                    new OA\Property(property: 'imageUrl', type: 'string'),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                ]
            )
        )
    )]
    public function index(PhotoRepository $photoRepository, SerializerInterface $serializer): JsonResponse
    {
        $photos = $photoRepository->findAll();
        $data = $serializer->serialize($photos, 'json', ['groups' => 'photo:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Get a single photo by ID
     */
    #[Route('/{id}', name: 'api_photos_show', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a single photo',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'nom', type: 'string'),
                new OA\Property(property: 'imageUrl', type: 'string'),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
            ]
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The ID of the photo to retrieve',
        schema: new OA\Schema(type: 'integer')
    )]
    public function show(Photo $photo, SerializerInterface $serializer): JsonResponse
    {
        $data = $serializer->serialize($photo, 'json', ['groups' => 'photo:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Get all photos for a specific camera
     */
    #[Route('/cam/{id}', name: 'api_photos_by_cam', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of photos for a camera',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'nom', type: 'string'),
                    new OA\Property(property: 'imageUrl', type: 'string'),
                    new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                ]
            )
        )
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The ID of the camera',
        schema: new OA\Schema(type: 'integer')
    )]
    public function byCam(Cam $cam, PhotoRepository $photoRepository, SerializerInterface $serializer): JsonResponse
    {
        $photos = $photoRepository->findBy(['cam' => $cam]);
        $data = $serializer->serialize($photos, 'json', ['groups' => 'photo:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * Delete a photo
     */
    #[Route('/{id}', name: 'api_photos_delete', methods: ['DELETE'])]
    #[OA\Response(
        response: 204,
        description: 'Deletes a photo'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'The ID of the photo to delete',
        schema: new OA\Schema(type: 'integer')
    )]
    public function delete(Photo $photo, EntityManagerInterface $em): Response
    {
        $em->remove($photo);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * Create a new photo record
     */
    #[Route('', name: 'api_photos_new', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Creates a new photo record and returns it',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'nom', type: 'string'),
                new OA\Property(property: 'imageUrl', type: 'string'),
                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
            ]
        )
    )]
    #[OA\RequestBody(
        description: 'Photo object that needs to be added',
        required: true,
        content: new OA\JsonContent(
            required: ['nom', 'imageUrl', 'cam'],
            properties: [
                new OA\Property(property: 'nom', type: 'string'),
                new OA\Property(property: 'imageUrl', type: 'string'),
                new OA\Property(property: 'cam', type: 'integer', description: 'Camera ID'),
            ]
        )
    )]
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, CamRepository $camRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $photo = new Photo();
        $photo->setNom($data['nom']);
        $photo->setImageUrl($data['imageUrl']);
        $photo->setCreatedAt(new \DateTimeImmutable());

        $cam = $camRepository->find($data['cam']);
        $photo->setCam($cam);

        $em->persist($photo);
        $em->flush();

        $data = $serializer->serialize($photo, 'json', ['groups' => 'photo:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }
}
