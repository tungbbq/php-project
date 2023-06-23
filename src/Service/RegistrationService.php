<?php

namespace App\Service;

use App\Entity\JsonRequestValidator;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationService
{

    private MailerInterface $mailer;
    private ManagerRegistry $managerRegistry;
    private RequestStack $requestStack;
    private UserService $userService;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $managerRegistry, VerifyEmailHelperInterface $helper, MailerInterface $mailer, RequestStack $requestStack, UserService $userService)
    {
        $this->managerRegistry = $managerRegistry;
        $this->mailer = $mailer;
        $this->requestStack = $requestStack;
        $this->userService = $userService;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    public function verifyUser($id, $verifyCode): void
    {

        if (!is_numeric($id) || !is_numeric($verifyCode)) {
            exit('ID/Code nicht korrekt');
        }

        // Validate range
        if ($id <= 0 || $verifyCode <= 999 || $verifyCode >= 10000) {
            exit('ID/Code nicht korrekt');
        }

        $entityManager = $this->managerRegistry->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            exit('User existiert nicht.');
        } else if (!$verifyCode) {
            exit('Code ist falsch.');
        }

        try {
            $user->setIsVerified(true);
            $entityManager->flush();
        } catch (\Exception $e) {
            dump($e->getMessage());
        }
        header('Location:http://localhost:5173/confirmNewUser');
        exit();
    }

    public function newUser(): Response
    {
        $content = $this->requestStack->getCurrentRequest()->getContent();
        $contentArray = json_decode($content, true);
        $jsonRequest = new JsonRequestValidator();
        $parameters = ['email', 'name', 'plz', 'ort', 'telefon'];
        foreach($parameters as $para)
        {
            $jsonRequest->$para = $contentArray[$para];
        }

        $errors = $this->validator->validate($jsonRequest);

        if (count($errors) > 0) {
            // Handle validation errors
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return ['erorrs' => $errorMessages, 400];
        }

        try {
            $entityManager = $this->managerRegistry->getManager();
            $user = new User();
            $user->setEmail($contentArray['email']);
            $user->setName($contentArray['name']);
            $user->setPlz($contentArray['plz']);
            $user->setOrt($contentArray['ort']);
            $plaintextPassword = $contentArray['password'];
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);
            $user->setTelefon($contentArray['telefon']);
            $user->setVerifyCode(mt_rand(1111, 9999));

            $entityManager->persist($user);
            $entityManager->flush();

            $confirmationURL = 'https://myproject.ddev.site/api/user/' . $user->getId() . '/' . $user->getVerifyCode();

            $email = (new Email())
                ->from('test@mail.com')
                ->to($user->getEmail())
                ->subject('Confirmation')
                ->text($confirmationURL);
            $this->mailer->send($email);

        } catch (\Exception $e) {
            dump($e->getMessage());

        } catch (TransportExceptionInterface $e) {
            dump($e->getMessage());
        }
        return ['success' => true];
    }
}