<?php

namespace Diablo\Model;
use JsonSerializable;

class Build implements \JsonSerializable {
    //Properties
    private ?int $Id = null;
    private ?string $Name = null;
    private ?string $Author = null;
    private ?string $Game = null;
    private ?bool $IsDraft = false; //if drafted, it will be visible only to the author and admins + it won't increment version when updated
    private ?int $Version = null; //auto increment in database with each update
    private ?\DateTime $CreatedAt = null;
    private ?\DateTime $UpdatedAt = null;
    private ?string $ImageRepository = null;
    private ?string $ImageFileName = null;

    //Getters and Setters
    public function getId(): ?int
    {
        return $this->Id;
    }

    public function setId(?int $Id): Build
    {
        $this->Id = $Id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(?string $Name): Build
    {
        $this->Name = $Name;
        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->Author;
    }

    public function setAuthor(?string $Author): Build
    {
        $this->Author = $Author;
        return $this;
    }

    public function getGame(): ?string
    {
        return $this->Game;
    }

    public function setGame(?string $Game): Build
    {
        $this->Game = $Game;
        return $this;
    }

    public function getIsDraft(): ?bool
    {
        return $this->IsDraft;
    }

    public function setIsDraft(?bool $IsDraft): Build
    {
        $this->IsDraft = $IsDraft;
        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->Version;
    }

    public function setVersion(?int $Version): Build
    {
        $this->Version = $Version;
        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->CreatedAt;
    }

    public function setDateCreation(?\DateTime $CreatedAt): Build
    {
        $this->CreatedAt = $CreatedAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(?\DateTime $UpdatedAt): Build
    {
        $this->UpdatedAt = $UpdatedAt;
        return $this;
    }

    public function getImageRepository(): ?string
    {
        return $this->ImageRepository;
    }

    public function setImageRepository(?string $ImageRepository): Build
    {
        $this->ImageRepository = $ImageRepository;
        return $this;
    }

    public function getImageFileName(): ?string
    {
        return $this->ImageFileName;
    }

    public function setImageFileName(?string $ImageFileName): Build
    {
        $this->ImageFileName = $ImageFileName;
        return $this;
    }

    //Methods for SQL queries
    

    // Implementing JsonSerializable to control how the object is serialized to JSON
    public function jsonSerialize(): mixed {
        return [
            'Id' => $this->Id,
            'Name' => $this->Name,
            'Author' => $this->Author,
            'Game' => $this->Game,
            'IsDraft' => $this->IsDraft,
            'Version' => $this->Version,
            'CreatedAt' => $this->CreatedAt?->format('Y-m-d H:i:s'),
            'UpdatedAt' => $this->UpdatedAt?->format('Y-m-d H:i:s'),
            'ImageRepository' => $this->ImageRepository,
            'ImageFileName' => $this->ImageFileName
        ];
    }
}