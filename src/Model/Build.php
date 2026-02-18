<?php

namespace Diablo\Model;
use JsonSerializable;
use Diablo\Database\DatabaseConnection;

class Build implements \JsonSerializable {
    //Properties
    private ?int $Id = null;
    private ?string $Name = null;
    private ?string $CharacterClass = null;
    private ?string $Description = null; //abstract about the build, can be empty
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

    public function getCharacterClass(): ?string
    {
        return $this->CharacterClass;
    }

    public function setCharacterClass(?string $CharacterClass): Build
    {
        $this->CharacterClass = $CharacterClass;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): Build
    {
        $this->Description = $Description;
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
    public static function SqlGetAllBuilds(int $number): array {
        //Connecting to DB
        $db = DatabaseConnection::getInstance();
        //Preparing statement
        $statement = $db->prepare("SELECT * FROM builds order by Id DESC LIMIT :limit");
        $statement->bindValue(':limit', $number, \PDO::PARAM_INT); // Limiting number of builds returned to user input
        //Execute statement
        $statement->execute();
        //Fetching results
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        //Mapping results to a build object
        $buildsArray = [];
        foreach ($results as $result) {
            $build = new Build();
            $build->setId($result['Id']);
            $build->setName($result['Name']);
            $build->setCharacterClass($result['CharacterClass']);
            $build->setDescription($result['Description']);
            $build->setAuthor($result['Author']);
            $build->setGame($result['Game']);
            $build->setIsDraft($result['IsDraft']);
            $build->setVersion($result['Version']);
            $build->setDateCreation(new \DateTime($result['CreatedAt']));
            $build->setUpdatedAt(new \DateTime($result['UpdatedAt']));
            $build->setImageRepository($result['ImageRepository']);
            $build->setImageFileName($result['ImageFileName']);
            $buildsArray[] = $build;
        }
        return $buildsArray;
    }

    public static function SqlGetBuildById(int $id): ?Build {
        //Connecting to DB
        $db = DatabaseConnection::getInstance();
        //Preparing statement
        $statement = $db->prepare("SELECT * FROM builds WHERE Id = :id");
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        //Execute statement
        $statement->execute();
        //Fetching result
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            $build = new Build();
            $build->setId($result['Id']);
            $build->setName($result['Name']);
            $build->setCharacterClass($result['CharacterClass']);
            $build->setDescription($result['Description']);
            $build->setAuthor($result['Author']);
            $build->setGame($result['Game']);
            $build->setIsDraft($result['IsDraft']);
            $build->setVersion($result['Version']);
            $build->setDateCreation(new \DateTime($result['CreatedAt']));
            $build->setUpdatedAt(new \DateTime($result['UpdatedAt']));
            $build->setImageRepository($result['ImageRepository']);
            $build->setImageFileName($result['ImageFileName']);
            return $build;
        }
        return null; // Return null if no build found with the given ID
    }

    public static function SqlCreateBuild(Build $build): int {
        try {
            //Connecting to DB
            $db = DatabaseConnection::getInstance();
            //Preparing statement
            $statement = $db->prepare("INSERT INTO builds (Name, Author, Game, IsDraft, Version, CreatedAt, UpdatedAt, ImageRepository, ImageFileName) VALUES (:name, :author, :game, :isDraft, :version, :createdAt, :updatedAt, :imageRepository, :imageFileName)");
            $statement->bindValue(':name', $build->getName(), \PDO::PARAM_STR);
            $statement->bindValue(':characterClass', $build->getCharacterClass(), \PDO::PARAM_STR);
            $statement->bindValue(':description', $build->getDescription(), \PDO::PARAM_STR);
            $statement->bindValue(':author', $build->getAuthor(), \PDO::PARAM_STR);
            $statement->bindValue(':game', $build->getGame(), \PDO::PARAM_STR);
            $statement->bindValue(':isDraft', $build->getIsDraft(), \PDO::PARAM_BOOL);
            $statement->bindValue(':version', $build->getVersion(), \PDO::PARAM_INT);
            $statement->bindValue(':createdAt', $build->getDateCreation()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
            $statement->bindValue(':updatedAt', $build->getUpdatedAt()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
            $statement->bindValue(':imageRepository', $build->getImageRepository(), \PDO::PARAM_STR);
            $statement->bindValue(':imageFileName', $build->getImageFileName(), \PDO::PARAM_STR);
            //Execute statement
            $statement->execute();

            return $db->lastInsertId(); // Return the ID of the newly created build
        } catch (\Exception $e) {
            // Handle exception (you can log it or rethrow it)
            error_log("Error creating build: " . $e->getMessage());
            return false;
        }
    }

    public static function SqlUpdateBuild(Build $build): ?Build {
        //Connecting to DB
        $db = DatabaseConnection::getInstance();
        //Preparing statement
        $statement = $db->prepare("UPDATE builds SET Name = :name, Author = :author, Game = :game, IsDraft = :isDraft, Version = :version, CreatedAt = :createdAt, UpdatedAt = :updatedAt, ImageRepository = :imageRepository, ImageFileName = :imageFileName WHERE Id = :id");
        $statement->bindValue(':id', $build->getId(), \PDO::PARAM_INT);
        $statement->bindValue(':name', $build->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':characterClass', $build->getCharacterClass(), \PDO::PARAM_STR);
        $statement->bindValue(':description', $build->getDescription(), \PDO::PARAM_STR);
        $statement->bindValue(':author', $build->getAuthor(), \PDO::PARAM_STR);
        $statement->bindValue(':game', $build->getGame(), \PDO::PARAM_STR);
        $statement->bindValue(':isDraft', $build->getIsDraft(), \PDO::PARAM_BOOL);
        $statement->bindValue(':version', $build->getVersion(), \PDO::PARAM_INT);
        $statement->bindValue(':createdAt', $build->getDateCreation()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $statement->bindValue(':updatedAt', $build->getUpdatedAt()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $statement->bindValue(':imageRepository', $build->getImageRepository(), \PDO::PARAM_STR);
        $statement->bindValue(':imageFileName', $build->getImageFileName(), \PDO::PARAM_STR);
        //Execute statement
        $statement->execute();

        return $build;
    }

    public static function SqlDeleteBuild(int $id): void {
        //Connecting to DB
        $db = DatabaseConnection::getInstance();
        //Preparing statement
        $statement = $db->prepare("DELETE FROM builds WHERE Id = :id");
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        //Execute statement
        $statement->execute();
    }

    public static function SqlSearchBuilds(string $searchTerm): array {
        //Connecting to DB
        $db = DatabaseConnection::getInstance();
        //Preparing statement
        $statement = $db->prepare("SELECT * FROM builds WHERE Name LIKE :searchTerm OR Author LIKE :searchTerm OR CharacterClass LIKE :searchTerm OR Description LIKE :searchTerm");
        $statement->bindValue(':searchTerm', '%' . $searchTerm . '%', \PDO::PARAM_STR);
        //Execute statement
        $statement->execute();
        //Fetching results
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        //Mapping results to build objects
        $buildsArray = [];
        foreach ($results as $result) {
            $build = new Build();
            $build->setId($result['Id']);
            $build->setName($result['Name']);
            $build->setCharacterClass($result['CharacterClass']);
            $build->setDescription($result['Description']);
            $build->setAuthor($result['Author']);
            $build->setGame($result['Game']);
            $build->setIsDraft($result['IsDraft']);
            $build->setVersion($result['Version']);
            $build->setDateCreation(new \DateTime($result['CreatedAt']));
            $build->setUpdatedAt(new \DateTime($result['UpdatedAt']));
            $build->setImageRepository($result['ImageRepository']);
            $build->setImageFileName($result['ImageFileName']);
            $buildsArray[] = $build;
        }
        return $buildsArray;
    }

    // Implementing JsonSerializable to control how the object is serialized to JSON
    public function jsonSerialize(): mixed {
        return [
            'Id' => $this->Id,
            'Name' => $this->Name,
            'CharacterClass' => $this->CharacterClass,
            'Description' => $this->Description,
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