<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PhotoRepository;
use Doctrine\ORM\Mapping as ORM;



use Symfony\Component\Serializer\Annotation\Groups;


use OpenApi\Attributes as OA;


#[ORM\Entity(repositoryClass: PhotoRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['photo:read']],
    denormalizationContext: ['groups' => ['photo:write']]
)]
#[OA\Schema(
    schema: "Photo",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "nom", type: "string"),
        new OA\Property(property: "imageUrl", type: "string"),
        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
        new OA\Property(property: "cam", ref: "#/components/schemas/Cam")
    ]
)]
#[OA\Schema(
    schema: "PhotoInput",
    required: ["nom", "imageUrl", "cam"],
    properties: [
        new OA\Property(property: "nom", type: "string"),
        new OA\Property(property: "imageUrl", type: "string"),
        new OA\Property(property: "cam", type: "integer", description: "The ID of the camera")
    ]
)]
class Photo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['photo:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['photo:read', 'photo:write'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['photo:read', 'photo:write'])]
    private ?string $imageUrl = null;

    #[ORM\Column]
    #[Groups(['photo:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'photos')]
    #[Groups(['photo:read', 'photo:write'])]
    private ?Cam $cam = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCam(): ?Cam
    {
        return $this->cam;
    }

    public function setCam(?Cam $cam): static
    {
        $this->cam = $cam;

        return $this;
    }

}
