<?php
namespace Diablo\Model;

use DateTime;
use Diablo\Database\DatabaseConnection;

class User {
    //Properties
    private ?int  $Id;
    private string $Nickname;
    private ?string $Bio; //abstract about the user, can be empty
    private \DateTime $RegisteredAt;
    private string $City;
    private ?float $latitude;
    private ?float $longitude;
    private string $Email;
    private string $Password;
    private string $Role;
    private ?string $AvatarRepository;
    private ?string $AvatarFileName;
}