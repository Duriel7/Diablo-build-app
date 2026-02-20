<?php
namespace Diablo\Model;

use JsonSerializable;

class User implements JsonSerializable{
    //Properties
    private int  $id;
    private string $nickname;
    private ?string $bio; //abstract about the user, can be empty
    private \DateTimeImmutable $registeredAt;
    private string $city;
    private float $latitude; //filled when user creates an account, used for geolocation and local build suggestions
    private float $longitude; //filled when user creates an account, used for geolocation and local build suggestions
    private string $email;
    private string $passwordHashed;
    private string $role; //user or admin
    private ?string $avatarRepository;
    private ?string $avatarFileName;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): User
    {
        $this->nickname = $nickname;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): User
    {
        $this->bio = $bio;
        return $this;
    }

    public function getRegisteredAt(): \DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(\DateTimeImmutable $registeredAt): User
    {
        $this->registeredAt = $registeredAt;
        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): User
    {
        $this->city = $city;
        return $this;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): User
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): User
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    public function getPasswordHashed(): string
    {
        return $this->passwordHashed;
    }

    public function setPasswordHashed(string $passwordHashed): User
    {
        $this->passwordHashed = $passwordHashed;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): User
    {
        $this->role = $role;
        return $this;
    }

    public function getAvatarRepository(): ?string
    {
        return $this->avatarRepository;
    }

    public function setAvatarRepository(?string $avatarRepository): User
    {
        $this->avatarRepository = $avatarRepository;
        return $this;
    }

    public function getAvatarFileName(): ?string
    {
        return $this->avatarFileName;
    }

    public function setAvatarFileName(?string $avatarFileName): User
    {
        $this->avatarFileName = $avatarFileName;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nickname' => $this->nickname,
            'bio' => $this->bio,
            'registeredAt' => $this->registeredAt->format('Y-m-d H:i:s'),
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'role' => $this->role,
            'avatarRepository' => $this->avatarRepository,
            'avatarFileName' => $this->avatarFileName
        ];
    }
}