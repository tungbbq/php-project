<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserValidatorService
{
    public function user($contentArray, ValidatorInterface $validator): Response
    {
        $user = new User();

        // ... do something to the $author object
        $user->setEmail($contentArray['email']);
        $user->setName($contentArray['name']);
        $user->setPlz($contentArray['plz']);
        $user->setOrt($contentArray['ort']);
        $user->setTelefon($contentArray['telefon']);
        $user->setPassword($contentArray['password']);

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            /*
             * Uses a __toString method on the $errors variable which is a
             * ConstraintViolationList object. This gives us a nice string
             * for debugging.
             */
            $errorsString = (string) $errors;

            return new Response($errorsString);
        }

        return new Response('The User is valid! Yes!');
    }
}