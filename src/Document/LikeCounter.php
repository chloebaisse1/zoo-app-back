<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
#[MongoDB\Document]
class LikeCounter
{
    #[MongoDB\Id]
    private ?string $id = null;

    #[MongoDB\Field]
    private string $animalId;

    #[MongoDB\Field]
    private int $count;

    public function __construct(string $animalId)
    {
        $this->animalId = $animalId;
        $this->count = 0; // Initialise le compteur à 0
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAnimalId(): string
    {
        return $this->animalId;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function increment(): void
    {
        $this->count++;
    }
}