<?php

namespace Diablo\Model;
use JsonSerializable;

class Build implements JsonSerializable {
    //Properties
    private int $id;
    private string $name;
    private string $characterClass;
    private ?string $description; //abstract about the build, can be empty
    private int $authorId;
    private string $game;
    private bool $isDraft; //if drafted, it will be visible only to the author and admins + it won't increment version when updated
    private int $version; //auto increment in database with each update
    private \DateTimeImmutable $createdAt;
    private ?\DateTime $updatedAt;
    private ?string $imageRepository;
    private ?string $imageFileName;

    //Getters and Setters
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Build
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Build
    {
        $this->name = $name;
        return $this;
    }

    public function getCharacterClass(): string
    {
        return $this->characterClass;
    }

    public function setCharacterClass(string $characterClass): Build
    {
        $this->characterClass = $characterClass;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Build
    {
        $this->description = $description;
        return $this;
    }

    public function getAuthorId(): int
    {
        return $this->authorId;
    }

    public function setAuthorId(int $authorId): Build
    {
        $this->authorId = $authorId;
        return $this;
    }

    public function getGame(): string
    {
        return $this->game;
    }

    public function setGame(string $game): Build
    {
        $this->game = $game;
        return $this;
    }

    public function getIsDraft(): bool
    {
        return $this->isDraft;
    }

    public function setIsDraft(bool $isDraft): Build
    {
        $this->isDraft = $isDraft;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): Build
    {
        $this->version = $version;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): Build
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): Build
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getImageRepository(): ?string
    {
        return $this->imageRepository;
    }

    public function setImageRepository(?string $imageRepository): Build
    {
        $this->imageRepository = $imageRepository;
        return $this;
    }

    public function getImageFileName(): ?string
    {
        return $this->imageFileName;
    }

    public function setImageFileName(?string $imageFileName): Build
    {
        $this->imageFileName = $imageFileName;
        return $this;
    }

    // Implementing JsonSerializable to control how the object is serialized to JSON
    public function jsonSerialize(): mixed {
        return [
            'Id' => $this->id,
            'Name' => $this->name,
            'CharacterClass' => $this->characterClass,
            'Description' => $this->description,
            'Author' => $this->authorId,
            'Game' => $this->game,
            'IsDraft' => $this->isDraft,
            'Version' => $this->version,
            'CreatedAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'UpdatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'ImageRepository' => $this->imageRepository,
            'ImageFileName' => $this->imageFileName
        ];
    }
}