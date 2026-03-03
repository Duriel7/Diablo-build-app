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
            $build->setAuthorId($result['author_id']);
            $build->setGame($result['game']);
            $build->setIsDraft((bool)$result['isDraft']);
            $build->setVersion($result['version']);
            $build->setCreatedAt(new \DateTimeImmutable($result['createdAt']));
            $build->setUpdatedAt(($result['updatedAt'] ?? null) ? new \DateTime($result['updatedAt']) : null);
            $build->setImageRepository($result['imageRepository']);
            $build->setImageFileName($result['imageFileName']);
            $buildsArray[] = $build;
        }
        return $buildsArray;
    }
    
    //Raw SQL method to catch all builds with author ID and paginated
    public function SqlGetAllBuildsPaginated(string $searchTerm, int $limit, int $offset): array {
        $sql = "
            SELECT 
                b.id, b.name, b.characterClass, b.description, b.game, b.createdAt,
                b.imageRepository, b.imageFileName,
                u.nickname AS authorNickname
            FROM builds b
            JOIN users u ON u.id = b.author_id
        ";

        if (!empty($searchTerm)) {
            $sql .= " WHERE (b.name LIKE :search1 OR b.characterClass LIKE :search2 OR b.game LIKE :search3) ";
        }

        $sql .= " ORDER BY b.createdAt DESC LIMIT :limit OFFSET :offset";

        $statement = $this->db->prepare($sql);

        if (!empty($searchTerm)) {
            $val = '%' . $searchTerm . '%';
            $statement->bindValue(':search1', $val, \PDO::PARAM_STR);
            $statement->bindValue(':search2', $val, \PDO::PARAM_STR);
            $statement->bindValue(':search3', $val, \PDO::PARAM_STR);
        }
        
        $statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $statement->execute();
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    //Counter function
    public function SqlCountBuilds(string $searchTerm): int {
        $sql = "SELECT COUNT(*) FROM builds";
        if (!empty($searchTerm)) {
            $sql .= " WHERE name LIKE :search1 OR characterClass LIKE :search2";
        }
        
        $statement = $this->db->prepare($sql);

        if (!empty($searchTerm)) {
            $val = '%' . $searchTerm . '%';
            $statement->bindValue(':search1', $val, \PDO::PARAM_STR);
            $statement->bindValue(':search2', $val, \PDO::PARAM_STR);
        }
        $statement->execute();
        return (int)$statement->fetchColumn();
    }

    public function SqlGetBuildById(int $id): ?Build {
        //Preparing statement
        $statement = $this->db->prepare("SELECT * FROM builds WHERE id = :id LIMIT 1");
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
            $build->setAuthorId($result['author_id']);
            $build->setGame($result['game']);
            $build->setIsDraft((bool)$result['isDraft']);
            $build->setVersion($result['version']);
            $build->setCreatedAt(new \DateTimeImmutable($result['createdAt']));
            $build->setUpdatedAt(($result['updatedAt'] ?? null) ? new \DateTime($result['updatedAt']) : null);
            $build->setImageRepository($result['imageRepository']);
            $build->setImageFileName($result['imageFileName']);
            return $build;
        }
        return null; // Return null if no build found with the given ID
    }

    public function SqlCreateBuild(Build $build): ?int {
        try {
            //Preparing statement
            $statement = $this->db->prepare("INSERT INTO builds (name, characterClass, description, author_id, game, isDraft, version, createdAt, updatedAt, imageRepository, imageFileName) VALUES (:name, :characterClass, :description, :author_id, :game, :isDraft, :version, :createdAt, :updatedAt, :imageRepository, :imageFileName)");
            $statement->bindValue(':name', $build->getName(), \PDO::PARAM_STR);
            $statement->bindValue(':characterClass', $build->getCharacterClass(), \PDO::PARAM_STR);
            $statement->bindValue(':description', $build->getDescription(), \PDO::PARAM_STR);
            $statement->bindValue(':author_id', $build->getAuthorId(), \PDO::PARAM_INT);
            $statement->bindValue(':game', $build->getGame(), \PDO::PARAM_STR);
            $statement->bindValue(':isDraft', $build->getIsDraft() ? 1 : 0, \PDO::PARAM_INT);
            $statement->bindValue(':version', $build->getVersion(), \PDO::PARAM_INT);
            $statement->bindValue(':createdAt', $build->getCreatedAt()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
            $statement->bindValue(':updatedAt', null, \PDO::PARAM_NULL);
            $statement->bindValue(':imageRepository', $build->getImageRepository(), \PDO::PARAM_STR);
            $statement->bindValue(':imageFileName', $build->getImageFileName(), \PDO::PARAM_STR);
            //Execute statement
            $statement->execute();

            return (int)$this->db->lastInsertId(); // Return the ID of the newly created build
        } catch (\Exception $e) {
            // Handle exception (you can log it or rethrow it)
            error_log("Error creating build: " . $e->getMessage());
            return null;
        }
    }

    public function SqlUpdateBuild(Build $build): ?Build {
        //Preparing statement
        $statement = $this->db->prepare("UPDATE builds SET name = :name, characterClass = :characterClass, description = :description, author_id = :author_id, game = :game, isDraft = :isDraft, version = :version, updatedAt = :updatedAt, imageRepository = :imageRepository, imageFileName = :imageFileName WHERE id = :id");
        $statement->bindValue(':id', $build->getId(), \PDO::PARAM_INT);
        $statement->bindValue(':name', $build->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':characterClass', $build->getCharacterClass(), \PDO::PARAM_STR);
        $statement->bindValue(':description', $build->getDescription(), \PDO::PARAM_STR);
        $statement->bindValue(':author_id', $build->getAuthorId(), \PDO::PARAM_INT);
        $statement->bindValue(':game', $build->getGame(), \PDO::PARAM_STR);
        $statement->bindValue(':isDraft', $build->getIsDraft(), \PDO::PARAM_BOOL);
        $statement->bindValue(':version', $build->getVersion() + 1, \PDO::PARAM_INT);
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
        $statement = $this->db->prepare("SELECT b.*, u.nickname
            FROM builds b
            JOIN users u ON u.id = b.author_id
            WHERE 
                b.name LIKE :searchTerm
                OR u.nickname LIKE :searchTerm
                OR b.characterClass LIKE :searchTerm
                OR b.description LIKE :searchTerm");
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
            $build->setAuthorId($result['author_id']);
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