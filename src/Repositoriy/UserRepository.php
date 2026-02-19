<?php
namespace Diablo\Repository;

use Diablo\Model\User;

class UserRepository {
    private \PDO $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    //Methods for SQL queries
    public function SqlGetAllUsers(int $number): array {
        //Preparing statement
        $statement = $this->db->prepare("SELECT * FROM users order by id DESC LIMIT :limit");
        $statement->bindValue(':limit', $number, \PDO::PARAM_INT); // Limiting number of users returned to user input
        //Execute statement
        $statement->execute();
        //Fetching results
        $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

        //Mapping results to a user object
        $usersArray = [];
        foreach ($results as $result) {
            $user = new User();
            $user->setId($result['id']);
            $user->setNickname($result['nickname']);
            $user->setBio($result['bio']);
            $user->setRegisteredAt(new \DateTimeImmutable($result['registeredAt']));
            $user->setCity($result['city']);
            $user->setLatitude((float)$result['latitude']);
            $user->setLongitude((float)$result['longitude']);
            $user->setEmail($result['email']);
            $user->setPasswordHashed($result['passwordHashed']);
            $user->setRole($result['role']);
            $user->setAvatarRepository($result['avatarRepository']);
            $user->setAvatarFileName($result['avatarFileName']);
            $usersArray[] = $user;
        }
        return $usersArray;
    }

    public function SqlGetUserById(int $id): ?User {
        //Preparing statement
        $statement = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        //Execute statement
        $statement->execute();
        //Fetching result
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            $user = new User();
            $user->setId($result['id']);
            $user->setNickname($result['nickname']);
            $user->setBio($result['bio']);
            $user->setRegisteredAt(new \DateTimeImmutable($result['registeredAt']));
            $user->setCity($result['city']);
            $user->setLatitude((float)$result['latitude']);
            $user->setLongitude((float)$result['longitude']);
            $user->setEmail($result['email']);
            $user->setPasswordHashed($result['passwordHashed']);
            $user->setRole($result['role']);
            $user->setAvatarRepository($result['avatarRepository']);
            $user->setAvatarFileName($result['avatarFileName']);
            return $user;
        }
        return null;
    }

    public function SqlCreateUser(User $user): ?int {
        try {
            //Preparing statement
            $statement = $this->db->prepare("INSERT INTO users (nickname, bio, registeredAt, city, latitude, longitude, email, passwordHashed, role, avatarRepository, avatarFileName) VALUES (:nickname, :bio, :registeredAt, :city, :latitude, :longitude, :email, :passwordHashed, :role, :avatarRepository, :avatarFileName)");
            $statement->bindValue(':nickname', $user->getNickname(), \PDO::PARAM_STR);
            $statement->bindValue(':bio', $user->getBio(), \PDO::PARAM_STR);
            $statement->bindValue(':registeredAt', $user->getRegisteredAt()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
            $statement->bindValue(':city', $user->getCity(), \PDO::PARAM_STR);
            $statement->bindValue(':latitude', $user->getLatitude(), \PDO::PARAM_STR);
            $statement->bindValue(':longitude', $user->getLongitude(), \PDO::PARAM_STR);
            $statement->bindValue(':email', $user->getEmail(), \PDO::PARAM_STR);
            $statement->bindValue(':passwordHashed', $user->getPasswordHashed(), \PDO::PARAM_STR);
            $statement->bindValue(':role', $user->getRole(), \PDO::PARAM_STR);
            $statement->bindValue(':avatarRepository', $user->getAvatarRepository(), \PDO::PARAM_STR);
            $statement->bindValue(':avatarFileName', $user->getAvatarFileName(), \PDO::PARAM_STR);
            //Execute statement
            $statement->execute();

            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            // Handle exception (you can log it or rethrow it)
            error_log("Error creating build: " . $e->getMessage());
            return null; //FIX ME : need to throw an exception
        }
    }

    public function SqlUpdateUser(User $user): bool {
        try {
            //Preparing statement
            $statement = $this->db->prepare("UPDATE users SET nickname = :nickname, bio = :bio, city = :city, latitude = :latitude, longitude = :longitude, email = :email, passwordHashed = :passwordHashed, role = :role, avatarRepository = :avatarRepository, avatarFileName = :avatarFileName WHERE id = :id");
            $statement->bindValue(':id', $user->getId(), \PDO::PARAM_INT);
            $statement->bindValue(':nickname', $user->getNickname(), \PDO::PARAM_STR);
            $statement->bindValue(':bio', $user->getBio(), \PDO::PARAM_STR);
            $statement->bindValue(':city', $user->getCity(), \PDO::PARAM_STR);
            $statement->bindValue(':latitude', $user->getLatitude(), \PDO::PARAM_STR);
            $statement->bindValue(':longitude', $user->getLongitude(), \PDO::PARAM_STR);
            $statement->bindValue(':email', $user->getEmail(), \PDO::PARAM_STR);
            $statement->bindValue(':passwordHashed', $user->getPasswordHashed(), \PDO::PARAM_STR);
            $statement->bindValue(':role', $user->getRole(), \PDO::PARAM_STR);
            $statement->bindValue(':avatarRepository', $user->getAvatarRepository(), \PDO::PARAM_STR);
            $statement->bindValue(':avatarFileName', $user->getAvatarFileName(), \PDO::PARAM_STR);
            //Execute statement
            return $statement->execute();
        } catch (\Exception $e) {
            // Handle exception (you can log it or rethrow it)
            error_log("Error updating user: " . $e->getMessage());
            return false; //FIX ME : need to throw an exception
        }
    }

    public function SqlDeleteUser(int $id): bool {
        try {
            //Preparing statement
            $statement = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $statement->bindValue(':id', $id, \PDO::PARAM_INT);
            //Execute statement
            return $statement->execute();
        } catch (\Exception $e) {
            // Handle exception (you can log it or rethrow it)
            error_log("Error deleting user: " . $e->getMessage());
            return false; //FIX ME : need to throw an exception
        }
    }

    public function SqlSearchUsers(string $searchTerm): ?User {
        //Preparing statement
        $statement = $this->db->prepare("SELECT * FROM users WHERE nickname LIKE :searchTerm OR email LIKE :searchTerm OR role LIKE :searchTerm");
        $statement->bindValue(':searchTerm', '%' . $searchTerm . '%', \PDO::PARAM_STR);
        //Execute statement
        $statement->execute();
        //Fetching result
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        //Mapping results to build objects
        if ($result) {
            $user = new User();
            $user->setId($result['id']);
            $user->setNickname($result['nickname']);
            $user->setBio($result['bio']);
            $user->setRegisteredAt(new \DateTimeImmutable($result['registeredAt']));
            $user->setCity($result['city']);
            $user->setLatitude((float)$result['latitude']);
            $user->setLongitude((float)$result['longitude']);
            $user->setEmail($result['email']);
            $user->setPasswordHashed($result['passwordHashed']);
            $user->setRole($result['role']);
            $user->setAvatarRepository($result['avatarRepository']);
            $user->setAvatarFileName($result['avatarFileName']);
            return $user;
        }
        return null;
    }
}
