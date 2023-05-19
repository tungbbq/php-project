<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class JsonRequestValidator
{
    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\NotNull(message: 'Wert erforderlich.')]
    #[Assert\Email(message: 'Keine valide Email Adresse.')]
    #[Assert\Type('string')]
    public $email;

    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\NotNull(message: 'Wert erforderlich.')]
    #[Assert\Type('string')]
    public $name;

    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\NotNull(message: 'Wert erforderlich.')]
    #[Assert\PositiveOrZero]
    #[Assert\Type('int')]
    #[Assert\Length(
        min: 5,
        max: 5,
        minMessage: 'Bitte gebe einen 5-Stelligen Code an.',
        maxMessage: 'Bitte gebe einen 5-Stelligen Code an.'
    )]
    public $plz;

    #[Assert\Type('alpha')]
    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\NotNull(message: 'Wert erforderlich.')]
    public $ort;

    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\NotNull(message: 'Wert erforderlich.')]
    #[Assert\Type('string')]
//    #[Assert\Regex('/(\(?([\d \-\)\–\+\/\(]+){6,}\)?([ .\-–\/]?)([\d]+))/g')]
    public $telefon;

    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\NotNull(message: 'Wert erforderlich.')]
    #[Assert\Type('string')]
    public $password;
}