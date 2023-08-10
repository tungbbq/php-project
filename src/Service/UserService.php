<?php

namespace App\Service;

use App\Entity\SearchValidation;
use App\Exception\NoUserException;
use App\Exception\ValidationErrorException;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
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
                'roles' => $user->getRoles()
            ];
        }

        return [
            'data' => [
                'status' => 'success',
                'message' => 'User(s) wurden erfolgreich bereitgestellt.',
                'response' => $data
            ]
        ];
    }

    private function errorCheck($jsonRequest): string
    {
        $errorMessage = '';
        $errors = $this->validator->validate($jsonRequest);

        if (count($errors) > 0) {

            foreach ($errors as $error) {
                $errorMessage .= $error->getPropertyPath() . ' = ' . $error->getMessage();
            }
//            return $errorMessage;
        }
        return $errorMessage;
    }


    /**
     * @throws NoUserException
     */
    public function getUsers(): array
    {
        $users = $this->managerRegistry
            ->getRepository(User::class)
            ->findAll();
        // check sinnvoll?
        if (!$users) {
            throw new NoUserException('Kein User vorhanden');
        }
        return $this->transformUsersToArray($users);
    }

    /**
     * @throws ValidationErrorException
     */
    public function searchUsers(): array
    {
        $entityManager = $this->managerRegistry->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        $content = $this->requestStack->getCurrentRequest()->getContent();
        $contentArray = json_decode($content, true);
        $validFields = ['email', 'name', 'plz', 'ort', 'telefon'];
        $searchQuery = new SearchValidation();
        $parameters = [];

        foreach ($validFields as $field) {
            if (isset($contentArray[$field]) && $contentArray[$field] !== '' && $contentArray[$field] !== 0) {
                $searchQuery->$field = $contentArray[$field];
                $parameters[$field] = [$field => $contentArray[$field]];
            }
        }

        $errors = $this->errorCheck($searchQuery);

        if ($errors !== '') {
            throw new ValidationErrorException($errors);
        }

        $users = $userRepository->findBy($parameters);

        return $this->transformUsersToArray($users);
    }

    /**
     * @throws NoUserException
     */
    public function getUserById($id): array
    {

        $user = $this->managerRegistry->getRepository(User::class)->find($id);
        if (!$user) {
            throw new NoUserException(`UserID {$id} existiert nicht.`);
        }
        return $this->transformUsersToArray([$user]);

    }


    /**
     * @throws NoUserException
     * @throws ValidationErrorException
     */
    public function updateUser($id): void
    {
        $entityManager = $this->managerRegistry->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw new NoUserException(`UserID {$id} existiert nicht.`);
        }

        $content = $this->requestStack->getCurrentRequest()->getContent();
        $contentArray = json_decode($content, true);
        $updateParam = new SearchValidation();
        $parameters = ['email', 'name', 'plz', 'ort', 'telefon', 'password'];

        foreach ($parameters as $param) {
            $updateParam->$param = $contentArray[$param];
        }

        $error = $this->errorCheck($updateParam);
        if ($error != ''){
            throw new ValidationErrorException($error);
        }

        $user->setEmail($contentArray['email']);
        $user->setName($contentArray['name']);
        $user->setPlz($contentArray['plz']);
        $user->setOrt($contentArray['ort']);

        // Vergleich passwort
        if ($contentArray['password'] !== '') {
            $hashedPasswordFromDB = $user->getPassword();
            $newHashedPassword = $this->passwordHasher->hashPassword($user, $contentArray['password']);
            if ($newHashedPassword !== $hashedPasswordFromDB && $contentArray['password'] !== $hashedPasswordFromDB) {
                $user->setPassword($newHashedPassword);
            }
        }
        $user->setTelefon($contentArray['telefon']);

        if (isset($contentArray['roles'])) {

            $user->setRoles($contentArray['roles']);
        }

        $entityManager->flush();
    }

    /**
     * @throws NoUserException
     */
    public function deleteUser($id): void
    {
        $entityManager = $this->managerRegistry->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw new NoUserException(`UserID {$id} existiert nicht.`);

        }

        $entityManager->remove($user);
        $entityManager->flush();

    }
}