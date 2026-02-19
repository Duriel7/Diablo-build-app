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
}