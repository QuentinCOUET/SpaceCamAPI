<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;



use Symfony\Component\Serializer\Annotation\Groups;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[OA\Schema(
    schema: "Users",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "nom", type: "string"),
        new OA\Property(property: "prenom", type: "string")
    ]
)]
#[OA\Schema(
    schema: "UserInput",
    required: ["nom", "prenom", "password"],
    properties: [
        new OA\Property(property: "nom", type: "string"),
        new OA\Property(property: "prenom", type: "string"),
        new OA\Property(property: "password", type: "string")
    ]
)]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'cam:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['user:read', 'cam:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Groups(['user:read', 'cam:read'])]
    private ?string $prenom = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Cam::class)]
    private Collection $cams;

    public function __construct()
    {
        $this->cams = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->nom;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Cam>
     */
    public function getCams(): Collection
    {
        return $this->cams;
    }

    public function addCam(Cam $cam): static
    {
        if (!$this->cams->contains($cam)) {
            $this->cams->add($cam);
            $cam->setOwner($this);
        }

        return $this;
    }

    public function removeCam(Cam $cam): static
    {
        if ($this->cams->removeElement($cam)) {
            // set the owning side to null (unless already changed)
            if ($cam->getOwner() === $this) {
                $cam->setOwner(null);
            }
        }

        return $this;
    }
}
