<?php

namespace App\Controller;

use App\Entity\User;

//use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class RegistrationController extends AbstractController
{
    #[Route('/user', name: 'user_new', methods: ['POST'])]
    public function new(ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $content = $request->getContent();
        $contentArray = json_decode($content, true);

        try {
            $entityManager = $doctrine->getManager();
            $user = new User();
            $user->setEmail($contentArray['email']);
            $user->setName($contentArray['name']);
            $user->setPlz($contentArray['plz']);
            $user->setOrt($contentArray['ort']);
            $plaintextPassword = $contentArray['password'];
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);
            $user->setTelefon($contentArray['telefon']);

            $entityManager->persist($user);
            $entityManager->flush();

        } catch (\Exception $e) {
            dump($e->getMessage());
//        TODO Monolog
        }

        return $this->json('Created new User successfully with id ' . $user->getId());
    }
}

