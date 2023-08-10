<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements \JsonSerializable, UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\Email(message: 'Keine valide Email Adresse.')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\Length(
        max: 32,
        maxMessage: 'Es sind nur 32 Zeichen erlaubt.'
    )]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\Positive]
    #[Assert\Length(
        exactly: 5,
        exactMessage: 'Bitte gebe einen 5-Stelligen Code an.'
    )]
    private ?int $plz = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\Type('alpha')]
    private ?string $ort = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    private ?string $telefon = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];


    #[ORM\Column]
    private ?int $verifyCode = null;


    // Only for test purpose
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getVerifyCode(): ?int
    {
        return $this->verifyCode;
    }

    /**
     * @param int|null $verifyCode
     */
    public function setVerifyCode(?int $verifyCode): void
    {
        $this->verifyCode = $verifyCode;
    }


    /**
     * @return string|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPlz(): ?int
    {
        return $this->plz;
    }

    public function setPlz(int $plz): self
    {
        $this->plz = $plz;

        return $this;
    }

    public function getOrt(): ?string
    {
        return $this->ort;
    }

    public function setOrt(string $ort): self
    {
        $this->ort = $ort;

        return $this;
    }


    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    public function setTelefon(string $telefon): self
    {
        $this->telefon = $telefon;

        return $this;
    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'plz' => $this->plz,
            'ort' => $this->ort,
            'telefon' => $this->telefon,
            'password' => $this->password,
            'roles' => $this->roles,
            'verifyCode' => $this->verifyCode,
        ];
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.)
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_READ';

        return array_unique($roles);
    }

    public function addRole(string $role): void
    {
        $this->roles[] = $role;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
         $this->plainPassword = null;
    }

}
