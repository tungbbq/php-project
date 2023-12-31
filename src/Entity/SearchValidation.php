<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class SearchValidation
{
    #[Assert\Email(message: 'Keine valide Email Adresse.')]
    #[Assert\Type('string')]
    public $email;

    #[Assert\Type('string')]
    public $name;


    #[Assert\PositiveOrZero]
    #[Assert\Type('int')]

    public $plz;

    #[Assert\Type('alpha')]
    public $ort;

    #[Assert\Type('string')]
//    #[Assert\Regex('/(\(?([\d \-\)\–\+\/\(]+){6,}\)?([ .\-–\/]?)([\d]+))/g')]
    public $telefon;


}