<?php

namespace Diablo\Model;
use JsonSerializable;
use Diablo\Database\DatabaseConnection;

class Build implements \JsonSerializable {
    //Properties
    private int $id;
    private string $name;
    private string $characterClass;
    private ?string $description; //abstract about the build, can be empty
    private string $author;
    private string $game;
    private bool $isDraft; //if drafted, it will be visible only to the author and admins + it won't increment version when updated
    private int $version; //auto increment in database with each update
    private \DateTime $createdAt;
    private ?\DateTime $updatedAt;
    private ?string $imageRepository;
    private ?string $imageFileName;

    //Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Build
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Build
    {
        $this->name = $name;
        return $this;
    }

    public function getCharacterClass(): ?string
    {
        return $this->characterClass;
    }

    public function setCharacterClass(?string $characterClass): Build
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): Build
    {
        $this->author = $author;
        return $this;
    }

    public function getGame(): ?string
    {
        return $this->game;
    }

    public function setGame(?string $game): Build
    {
        $this->game = $game;
        return $this;
    }

    public function getIsDraft(): ?bool
    {
        return $this->isDraft;
    }

    public function setIsDraft(?bool $isDraft): Build
    {
        $this->isDraft = $isDraft;
        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(?int $version): Build
    {
        $this->version = $version;
        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setDateCreation(?\DateTime $createdAt): Build
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

    //Methods for SQL queries
    public static function SqlGetAllBuilds(int $number): array {
        //Connecting to DB
        $db = DatabaseConnection::getInstance();
        //Preparing statement
        $statement = $db->prepare("SELECT * FROM builds order by id DESC LIMIT :limit");
        $statement->bindValue(':limit', $number, \PDO::PARAM_INT); // Limiting number of builds returned to user input
        //Execute statement
        $statement->execute();
        //Fetching results
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        //Mapping results to a build object
        $buildsArray = [];
        foreach ($results as $result) {
            $build = new Build();
            $build->setId($result['id']);
            $build->setName($result['name']);
            $build->setCharacterClass($result['characterClass']);
            $build->setDescription($result['description']);
            $build->setAuthor($result['author']);
            $build->setGame($result['game']);
            $build->setIsDraft($result['isDraft']);
            $build->setVersion($result['version']);
            $build->setDateCreation(new \DateTime($result['createdAt']));
            $build->setUpdatedAt(new \DateTime($result['updatedAt']));
            $build->setImageRepository($result['imageRepository']);
            $build->setImageFileName($result['imageFileName']);
            $buildsArray[] = $build;
        }
        return $buildsArray;
    }

    public static function SqlGetBuildById(int $id): ?Build {
        //Connecting to DB
        $db = DatabaseConnection::getInstance();
        //Preparing statement
        $statement = $db->prepare("SELECT * FROM builds WHERE id = :id");
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        //Execute statement
        $statement->execute();
        //Fetching result
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            $build = new Build();
            $build->setId($result['id']);
            $build->setName($result['name']);
            $build->setCharacterClass($result['characterClass']);
            $build->setDescription($result['description']);
            $build->setAuthor($result['author']);
            $build->setGame($result['game']);
            $build->setIsDraft($result['isDraft']);
            $build->setVersion($result['version']);
            $build->setDateCreation(new \DateTime($result['createdAt']));
            $build->setUpdatedAt(new \DateTime($result['updatedAt']));
            $build->setImageRepository($result['imageRepository']);
            $build->setImageFileName($result['imageFileName']);
            return $build;
        }
        return null; // Return null if no build found with the given ID
    }

    public static function SqlCreateBuild(Build $build): int {
        try {
            //Connecting to DB
            $db = DatabaseConnection::getInstance();
            //Preparing statement
            $statement = $db->prepare("INSERT INTO builds (name, characterClass, description, author, game, isDraft, version, createdAt, updatedAt, imageRepository, imageFileName) VALUES (:name, :characterClass, :description, :author, :game, :isDraft, :version, :createdAt, :updatedAt, :imageRepository, :imageFileName)");
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
        $statement = $db->prepare("UPDATE builds SET name = :name, characterClass = :characterClass, description = :description, author = :author, game = :game, isDraft = :isDraft, version = :version, createdAt = :createdAt, updatedAt = :updatedAt, imageRepository = :imageRepository, imageFileName = :imageFileName WHERE id = :id");
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
        $statement = $db->prepare("DELETE FROM builds WHERE id = :id");
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        //Execute statement
        $statement->execute();
    }

    public static function SqlSearchBuilds(string $searchTerm): array {
        //Connecting to DB
        $db = DatabaseConnection::getInstance();
        //Preparing statement
        $statement = $db->prepare("SELECT * FROM builds WHERE name LIKE :searchTerm OR author LIKE :searchTerm OR characterClass LIKE :searchTerm OR description LIKE :searchTerm");
        $statement->bindValue(':searchTerm', '%' . $searchTerm . '%', \PDO::PARAM_STR);
        //Execute statement
        $statement->execute();
        //Fetching results
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        //Mapping results to build objects
        $buildsArray = [];
        foreach ($results as $result) {
            $build = new Build();
            $build->setId($result['id']);
            $build->setName($result['name']);
            $build->setCharacterClass($result['characterClass']);
            $build->setDescription($result['description']);
            $build->setAuthor($result['author']);
            $build->setGame($result['game']);
            $build->setIsDraft($result['isDraft']);
            $build->setVersion($result['version']);
            $build->setDateCreation(new \DateTime($result['createdAt']));
            $build->setUpdatedAt(new \DateTime($result['updatedAt']));
            $build->setImageRepository($result['imageRepository']);
            $build->setImageFileName($result['imageFileName']);
            $buildsArray[] = $build;
        }
        return $buildsArray;
    }

    // Implementing JsonSerializable to control how the object is serialized to JSON
    public function jsonSerialize(): mixed {
        return [
            'Id' => $this->id,
            'Name' => $this->name,
            'CharacterClass' => $this->characterClass,
            'Description' => $this->description,
            'Author' => $this->author,
            'Game' => $this->game,
            'IsDraft' => $this->isDraft,
            'Version' => $this->version,
            'CreatedAt' => $this->createdAt?->format('Y-m-d H:i:s'),
            'UpdatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'ImageRepository' => $this->imageRepository,
            'ImageFileName' => $this->imageFileName
        ];
    }
}