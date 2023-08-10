<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationValidator
{
    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\Email(message: 'Keine valide Email Adresse.')]
    #[Assert\Type('string')]
    private string $email;

    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\Type('string')]
    private string $name;

    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\PositiveOrZero]
    #[Assert\Type('int')]
    #[Assert\Length(
        min: 5,
        max: 5,
        minMessage: 'Bitte gebe einen 5-Stelligen Code an.',
        maxMessage: 'Bitte gebe einen 5-Stelligen Code an.'
    )]
    private int $plz;

    #[Assert\Type('alpha')]
    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    private string $ort;

    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\Type('string')]
//    #[Assert\Regex('/(\(?([\d \-\)\–\+\/\(]+){6,}\)?([ .\-–\/]?)([\d]+))/g')]
    private string $telefon;

    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\Type('string')]
    private string $password;

    /**
     * @return mixed
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPlz(): int
    {
        return $this->plz;
    }

    /**
     * @param int $plz
     */
    public function setPlz(int $plz): void
    {
        $this->plz = $plz;
    }

    /**
     * @return string
     */
    public function getOrt(): string
    {
        return $this->ort;
    }

    /**
     * @param mixed $ort
     */
    public function setOrt(string $ort): void
    {
        $this->ort = $ort;
    }

    /**
     * @return string
     */
    public function getTelefon(): string
    {
        return $this->telefon;
    }

    /**
     * @param string $telefon
     */
    public function setTelefon(string $telefon): void
    {
        $this->telefon = $telefon;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

}