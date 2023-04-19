<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{

    #[Route('/user', name: 'user_index', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): Response
    {
        $humans = $doctrine
            ->getRepository(User::class)
            ->findAll();

        $data = [];

        foreach ($humans as $human) {
            $data[] = [
                'id' => $human->getId(),
                'email' => $human->getEmail(),
                'name' => $human->getName(),
                'plz' => $human->getPlz(),
                'ort' => $human->getOrt(),
                'telefon' => $human->getTelefon(),
                'password' => $human->getPassword(),
                'roles' => $human->getRoles(),
            ];
        }
        return $this->json($data);
    }





    #[Route('/user/{id}', name: 'user_show', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $user = $doctrine->getRepository(User::class)->find($id);

        if (!$user) {

            return $this->json('No User found for id' . $id, 404);
        }

        $data =  [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'plz' => $user->getPlz(),
            'ort' => $user->getOrt(),
            'telefon' => $user->getTelefon(),
            'password' => $user->getPassword(),
            'roles' => $user->getRoles(),
        ];

        return $this->json($data);
    }

    #[Route('/user/{id}', name: 'user_edit', methods: ['PUT'])]
    public function edit(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json('No User found for id' . $id, 404);
        }

        $content = $request->getContent();
        $contentArray = json_decode($content, true);

        try {
            $user->setEmail($contentArray['email']);
            $user->setName($contentArray['name']);
            $user->setPlz($contentArray['plz']);
            $user->setOrt($contentArray['ort']);
            $user->setPassword($contentArray['password']);
            $user->setTelefon($contentArray['telefon']);

            $entityManager->flush();
        } catch (\Exception $e) {
            dump($e->getMessage());
//        TODO Monolog
        }

        $data =  [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'plz' => $user->getPlz(),
            'ort' => $user->getOrt(),
            'telefon' => $user->getTelefon(),
            'password' => $user->getPassword(),
            'roles' => $user->getRoles(),
        ];

        return $this->json($data);
    }

    #[Route('/user/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json('No User found for id' . $id, 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json('Deleted a User successfully with id ' . $id);
    }
}
