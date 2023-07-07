<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
class verifyCodeAndIdValidator
{
    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\NotNull(message: 'Wert erforderlich.')]
    #[Assert\Type(type:'integer', message: 'Id {{ value }} ist kein erlaubter Typ.')]
    #[Assert\Positive(message:"Id muss positiv sein.")]
    private int $id;

    #[Assert\NotBlank(message: 'Wert darf nicht leer sein.')]
    #[Assert\NotNull(message: 'Wert erforderlich.')]
    #[Assert\Type(type:'integer', message: 'Code {{ value }} ist kein erlaubter Typ.'  )]
    #[Assert\Positive(message:"Id muss positiv sein.")]
    #[Assert\Range(min:1, max:9999, notInRangeMessage: 'Code ist nicht in Reichweite.')]
    private int $verifyCode;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getVerifyCode(): int
    {
        return $this->verifyCode;
    }

    /**
     * @param int $verifyCode
     */
    public function setVerifyCode(int $verifyCode): void
    {
        $this->verifyCode = $verifyCode;
    }


}