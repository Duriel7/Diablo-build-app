<?php
namespace Diablo\Repository;

use Diablo\Model\Build;

class BuildRepository {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    //Methods for SQL queries
    public function SqlGetAllBuilds(int $number): array {
        //Preparing statement
        $statement = $this->db->prepare("SELECT * FROM builds order by id DESC LIMIT :limit");
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
            $build->setIsDraft((bool)$result['isDraft']);
            $build->setVersion($result['version']);
            $build->setCreatedAt(new \DateTimeImmutable($result['createdAt']));
            $build->setUpdatedAt($result['updatedAt'] ? new \DateTime($result['updatedAt']) : null);
            $build->setImageRepository($result['imageRepository']);
            $build->setImageFileName($result['imageFileName']);
            $buildsArray[] = $build;
        }
        return $buildsArray;
    }

    public function SqlGetBuildById(int $id): ?Build {
        //Preparing statement
        $statement = $this->db->prepare("SELECT * FROM builds WHERE id = :id");
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
            $build->setIsDraft((bool)$result['isDraft']);
            $build->setVersion($result['version']);
            $build->setCreatedAt(new \DateTimeImmutable($result['createdAt']));
            $build->setUpdatedAt($result['updatedAt'] ? new \DateTime($result['updatedAt']) : null);
            $build->setImageRepository($result['imageRepository']);
            $build->setImageFileName($result['imageFileName']);
            return $build;
        }
        return null; // Return null if no build found with the given ID
    }

    public function SqlCreateBuild(Build $build): ?int {
        try {
            //Preparing statement
            $statement = $this->db->prepare("INSERT INTO builds (name, characterClass, description, author, game, isDraft, version, createdAt, updatedAt, imageRepository, imageFileName) VALUES (:name, :characterClass, :description, :author, :game, :isDraft, :version, :createdAt, :updatedAt, :imageRepository, :imageFileName)");
            $statement->bindValue(':name', $build->getName(), \PDO::PARAM_STR);
            $statement->bindValue(':characterClass', $build->getCharacterClass(), \PDO::PARAM_STR);
            $statement->bindValue(':description', $build->getDescription(), \PDO::PARAM_STR);
            $statement->bindValue(':author', $build->getAuthor(), \PDO::PARAM_STR);
            $statement->bindValue(':game', $build->getGame(), \PDO::PARAM_STR);
            $statement->bindValue(':isDraft', $build->getIsDraft(), \PDO::PARAM_BOOL);
            $statement->bindValue(':version', $build->getVersion(), \PDO::PARAM_INT);
            $statement->bindValue(':createdAt', $build->getCreatedAt()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
            $statement->bindValue(':updatedAt', $build->getUpdatedAt() ? $build->getUpdatedAt()->format('Y-m-d H:i:s') : null, $build->getUpdatedAt() ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $statement->bindValue(':imageRepository', $build->getImageRepository(), \PDO::PARAM_STR);
            $statement->bindValue(':imageFileName', $build->getImageFileName(), \PDO::PARAM_STR);
            //Execute statement
            $statement->execute();

            return $this->db->lastInsertId(); // Return the ID of the newly created build
        } catch (\Exception $e) {
            // Handle exception (you can log it or rethrow it)
            error_log("Error creating build: " . $e->getMessage());
            return null; //FIX ME : need to throw an exception
        }
    }

    public function SqlUpdateBuild(Build $build): ?Build {
        //Preparing statement
        $statement = $this->db->prepare("UPDATE builds SET name = :name, characterClass = :characterClass, description = :description, author = :author, game = :game, isDraft = :isDraft, version = :version, updatedAt = :updatedAt, imageRepository = :imageRepository, imageFileName = :imageFileName WHERE id = :id");
        $statement->bindValue(':id', $build->getId(), \PDO::PARAM_INT);
        $statement->bindValue(':name', $build->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':characterClass', $build->getCharacterClass(), \PDO::PARAM_STR);
        $statement->bindValue(':description', $build->getDescription(), \PDO::PARAM_STR);
        $statement->bindValue(':author', $build->getAuthor(), \PDO::PARAM_STR);
        $statement->bindValue(':game', $build->getGame(), \PDO::PARAM_STR);
        $statement->bindValue(':isDraft', $build->getIsDraft(), \PDO::PARAM_BOOL);
        $statement->bindValue(':version', $build->getVersion(), \PDO::PARAM_INT);
        $statement->bindValue(':updatedAt', $build->getUpdatedAt() ? $build->getUpdatedAt()->format('Y-m-d H:i:s') : null, $build->getUpdatedAt() ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
        $statement->bindValue(':imageRepository', $build->getImageRepository(), \PDO::PARAM_STR);
        $statement->bindValue(':imageFileName', $build->getImageFileName(), \PDO::PARAM_STR);
        //Execute statement
        $statement->execute();

        return $build;
    }

    public function SqlDeleteBuild(int $id): void {
        //Preparing statement
        $statement = $this->db->prepare("DELETE FROM builds WHERE id = :id");
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        //Execute statement
        $statement->execute();
    }

    public function SqlSearchBuilds(string $searchTerm): array {
        //Preparing statement
        $statement = $this->db->prepare("SELECT * FROM builds WHERE name LIKE :searchTerm OR author LIKE :searchTerm OR characterClass LIKE :searchTerm OR description LIKE :searchTerm");
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
            $build->setIsDraft((bool)$result['isDraft']);
            $build->setVersion($result['version']);
            $build->setCreatedAt(new \DateTimeImmutable($result['createdAt']));
            $build->setUpdatedAt($result['updatedAt'] ? new \DateTime($result['updatedAt']) : null);
            $build->setImageRepository($result['imageRepository']);
            $build->setImageFileName($result['imageFileName']);
            $buildsArray[] = $build;
        }
        return $buildsArray;
    }
}