<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER')"),
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    order: ['id' => 'DESC', 'name' => 'ASC'],
    paginationEnabled: false
)]
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read', 'write'])]
    private int $id;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['read', 'write'])]
    private string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read', 'write'])]
    private ?string $name;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['read', 'write'])]
    private bool $isSubscribed;

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function isSubscribed(): bool
    {
        return $this->isSubscribed;
    }

    public function setIsSubscribed(bool $isSubscribed): self
    {
        $this->isSubscribed = $isSubscribed;
        return $this;
    }
}
