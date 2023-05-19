<?php

namespace App\Controller;

use App\Entity\JsonRequestValidator;
use App\Entity\SearchValidation;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Lcobucci\JWT\Validation\Validator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{

    #[Route('/user/search', name: 'user_search', methods: ['POST'])]
    public function search(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator): Response
    {
        $entityManager = $doctrine->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        $content = $request->getContent();
        $contentArray = json_decode($content, true);
        $jsonRequest = new SearchValidation();

        $parameters =[];

        if (isset($contentArray['email']) && $contentArray['email'] !== ''){
            $jsonRequest->email = $contentArray['email'];
            $parameters['email'] = $contentArray['email'];
        }

        if (isset($contentArray['name']) && $contentArray['name'] !== ''){
            $jsonRequest->name = $contentArray['name'];
            $parameters['name'] = ['name' => $contentArray['name']];
        }

        if (isset($contentArray['plz']) && $contentArray['plz'] !== ''){
            $jsonRequest->plz = $contentArray['plz'];
            $parameters['plz'] = ['plz' => $contentArray['plz']];
        }

        if (isset($contentArray['ort']) && $contentArray['ort'] !== ''){
            $jsonRequest->ort = $contentArray['ort'];
            $parameters['ort'] = ['ort' => $contentArray['ort']];
        }

        if (isset($contentArray['telefon']) && $contentArray['telefon'] !== ''){
            $jsonRequest->telefon = $contentArray['telefon'];
            $parameters['telefon'] = ['telefon' => $contentArray['telefon']];
        }

        $errors = $validator->validate($jsonRequest);

        if (count($errors) > 0) {
            // Handle validation errors
            // For example, you can return a JSON response with the errors
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['erorrs' => $errorMessages], 400);
        }

        $users = $userRepository->findBy($parameters);

        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'plz' => $user->getPlz(),
                'ort' => $user->getOrt(),
                'telefon' => $user->getTelefon(),
                'password' => $user->getPassword(),
                'roles' => $user->getRoles(),
                'isVerified' => $user->isVerified(),
            ];
        }

        return $this->json($data);


    }

    #[Route('/user', name: 'user_index', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): Response
    {
        $users = $doctrine
            ->getRepository(User::class)
            ->findAll();

        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'plz' => $user->getPlz(),
                'ort' => $user->getOrt(),
                'telefon' => $user->getTelefon(),
                'password' => $user->getPassword(),
                'roles' => $user->getRoles(),
                'isVerified' => $user->isVerified(),
            ];
        }
        return $this->json($data);
    }


    #[Route('/user/{id}', name: 'user_show', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        /*
         * 1. User geht zu My Data
         *  --> Zeigen alle Daten aber mit disabled
         *  --> Rechts zu jedem Feld gibts einen Button zu ändern
         *  --> Erst wenn Button geklicked ist, darf man eingeben
         *
         * 2. Submit
         *  --> Valideren Email/Password --> Wichtig!!!
         *  --> Alle anderen Daten don't care
         *  --> Fertig
         *
         *
         *
         */
        $user = $doctrine->getRepository(User::class)->find($id);

        if (!$user) {

            return $this->json('No User found for id' . $id, 404);
        }

        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'plz' => $user->getPlz(),
            'ort' => $user->getOrt(),
            'telefon' => $user->getTelefon(),
            'password' => $user->getPassword(), // --> hashed Text --> sollte nicht gemacht werden
//            'password' => 'currentPassword',
            'roles' => $user->getRoles(),
            'isVerified' => $user->isVerified(),
        ];

        return $this->json($data);
    }

    #[Route('/user/{id}', name: 'user_edit', methods: ['PUT'])]
    public function edit(ManagerRegistry $doctrine, Request $request, int $id, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json('No User found for id' . $id, 404);
        }

        $content = $request->getContent();
        $contentArray = json_decode($content, true);
        $jsonRequest = new SearchValidation();
        $jsonRequest->email = $contentArray['email'];
        $jsonRequest->name = $contentArray['name'];
        $jsonRequest->plz = $contentArray['plz'];
        $jsonRequest->ort = $contentArray['ort'];
        $jsonRequest->telefon = $contentArray['telefon'];
        $jsonRequest->password = $contentArray['password'];

        $errors = $validator->validate($jsonRequest);

        if (count($errors) > 0) {

            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json(['erorrs' => $errorMessages], 400);
        }


//        TODO Valideren email&password
        try {
            $user->setEmail($contentArray['email']);
            $user->setName($contentArray['name']);
            $user->setPlz($contentArray['plz']);
            $user->setOrt($contentArray['ort']);

            // Vergleich passwort
            $passwordFromDB = $user->getPassword();
            $newPassword = $passwordHasher->hashPassword($user, $contentArray['password']);
            if ($newPassword !== $passwordFromDB && $contentArray['password'] !== $passwordFromDB) {
                $plaintextPassword = $contentArray['password'];
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plaintextPassword
                );
                $user->setPassword($hashedPassword);
            }
            $user->setTelefon($contentArray['telefon']);

            if(isset($contentArray['roles'])) {

                $user->setRoles(array(($contentArray['roles'])));
            }

            $entityManager->flush();
        } catch (\Exception $e) {
            dump($e->getMessage());
//        TODO Monolog
        }

//        $data = [
//            'id' => $user->getId(),
//            'email' => $user->getEmail(),
//            'name' => $user->getName(),
//            'plz' => $user->getPlz(),
//            'ort' => $user->getOrt(),
//            'telefon' => $user->getTelefon(),
//            'password' => $user->getPassword(),
//            'roles' => $user->getRoles(),
//        ];

        return $this->json(['success' => true]);
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

        return $this->json(['success' => true]);
    }
}
