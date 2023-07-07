<?php

namespace App\Service;

use App\Entity\SearchValidation;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;


class UserService
{
    private ManagerRegistry $managerRegistry;
    private RequestStack $requestStack;
    private ValidatorInterface $validator;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(ManagerRegistry $managerRegistry, RequestStack $requestStack, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher)
    {
        $this->managerRegistry = $managerRegistry;
        $this->requestStack = $requestStack;
        $this->validator = $validator;
        $this->passwordHasher = $passwordHasher;
    }

    private function transformUsersToArray(array $users): array
    {
        $data = [];
        if (!count(array_filter($users))) {
            return [];
        }
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

        return $data;
    }

    private function errorValidator($jsonRequest)
    {
        $errors = $this->validator->validate($jsonRequest);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $errorMessages;
        }
    }


    public function getUsers(): array
    {
        try {
            $users = $this->managerRegistry
                ->getRepository(User::class)
                ->findAll();
            if (!$users) {
                throw new \Exception('test');
            }
            return $this->transformUsersToArray($users);
        } catch (\Exception $e) {
            return ['status' => 'failure', 'message' => $e->getMessage(), 'statusCode' => 500];

        }

    }

    public function searchUsers(): array
    {
        $entityManager = $this->managerRegistry->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        $content = $this->requestStack->getCurrentRequest()->getContent();
        $contentArray = json_decode($content, true);
        $validFields = ['email', 'name', 'plz', 'ort', 'telefon'];
        $jsonRequest = new SearchValidation();
        $parameters = [];

        foreach ($validFields as $field) {
            if (isset($contentArray[$field]) && $contentArray[$field] !== '' && $contentArray[$field] !== 0) {
                $jsonRequest->$field = $contentArray[$field];
                $parameters[$field] = [$field => $contentArray[$field]];
            }
        }

        $this->errorValidator($jsonRequest);

        $users = $userRepository->findBy($parameters);

        return $this->transformUsersToArray($users);
    }

    public function getUserById($id): array
    {

        $user = $this->managerRegistry->getRepository(User::class)->find($id);
        if (!$user) {
            $user = [];
        }
        return $this->transformUsersToArray([$user]);

    }


//{
//try {
//
//
//$user = $this->managerRegistry->getRepository(User::class)->find($id);
//
//if (!$user) {
//throw new \Exception('No User found for id' . $id, 404);
//}
//return $this->transformUsersToArray([$user]);
//} catch (\Exception $e) {
//    return ['status' => 'failure', 'message' => $e->getMessage(), 'code' => $e->getCode()];
//}
//    }

    public function updateUser($id): array
    {
        $entityManager = $this->managerRegistry->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->transformUsersToArray([]);;
        }

        $content = $this->requestStack->getCurrentRequest()->getContent();
        $contentArray = json_decode($content, true);
        $jsonRequest = new SearchValidation();
        $parameters = ['email', 'name', 'plz', 'ort', 'telefon', 'password'];

        foreach ($parameters as $param) {
            $jsonRequest->$param = $contentArray[$param];
        }

        $this->errorValidator($jsonRequest);

        try {
            $user->setEmail($contentArray['email']);
            $user->setName($contentArray['name']);
            $user->setPlz($contentArray['plz']);
            $user->setOrt($contentArray['ort']);

            // Vergleich passwort
            if ($contentArray['password'] !== '') {
                $passwordFromDB = $user->getPassword();
                $newPassword = $this->passwordHasher->hashPassword($user, $contentArray['password']);
                if ($newPassword !== $passwordFromDB && $contentArray['password'] !== $passwordFromDB) {
                    $plaintextPassword = $contentArray['password'];
                    $hashedPassword = $this->passwordHasher->hashPassword(
                        $user,
                        $plaintextPassword
                    );
                    $user->setPassword($hashedPassword);
                }
            }
            $user->setTelefon($contentArray['telefon']);

            if (isset($contentArray['roles'])) {

                $user->setRoles(($contentArray['roles']));
            }

            $entityManager->flush();
        } catch (\Exception $e) {
            dump($e->getMessage());
        }
        return ['success' => true];
    }

    public function deleteUser($id): array
    {
        $entityManager = $this->managerRegistry->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->transformUsersToArray([]);;
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return ['success' => true];
    }
}