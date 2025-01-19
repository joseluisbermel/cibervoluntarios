<?php

namespace App\Entity;

use App\Repository\MotorbikeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER')"),
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    order: ['updatedAt' => 'DESC', 'brand' => 'ASC'],
    paginationEnabled: false
)]
#[ORM\Entity(repositoryClass: MotorbikeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Motorbike
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read', 'write'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['read', 'write'])]
    private string $model;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups(['read', 'write'])]
    private int $engineCapacity;

    #[ORM\Column(length: 40)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 40)]
    #[Groups(['read', 'write'])]
    private string $brand;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups(['read', 'write'])]
    private string $type;

    #[ORM\Column(type: "json")]
    #[Assert\NotBlank]
    #[Assert\Count(max: 20)]
    #[Groups(['read', 'write'])]
    private array $extras = [];

    #[ORM\Column(nullable: true)]
    #[Groups(['read', 'write'])]
    private ?int $weight = null;

    #[ORM\Column]
    #[Groups(['read'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Groups(['read'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s'])]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Groups(['read', 'write'])]
    private bool $limitedEdition;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getEngineCapacity(): int
    {
        return $this->engineCapacity;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getExtras(): array
    {
        return $this->extras;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getLimitedEdition(): bool
    {
        return $this->limitedEdition;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function setEngineCapacity(int $engineCapacity): void
    {
        $this->engineCapacity = $engineCapacity;
    }

    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setExtras(array $extras): void
    {
        $this->extras = $extras;
    }

    public function setWeight(?int $weight): void
    {
        $this->weight = $weight;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function setLimitedEdition(bool $limitedEdition): void
    {
        $this->limitedEdition = $limitedEdition;
    }
}