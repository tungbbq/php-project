<?php

namespace App\Controller;

use App\Entity\User;

//use App\Form\RegistrationFormType;
use App\Entity\JsonRequestValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class RegistrationController extends AbstractController
{
    private $verifyEmailHelper;
    private $mailer;

    public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer)
    {
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
    }

    #[Route('/user/{id}/{verifyCode}', name: 'user_verification', methods: ['GET'])]
    public function verifyUser(ManagerRegistry $doctrine, Request $request, string $id, string $verifyCode): void
    {
//        exit('Exidisofidosf');

        // Ablauf

        // 1. Input validieren
        // OK --> weiter
        // !OK --> Fehlermeldung zurück
        // 2. Suchen User mit ID
        // OK --> weiter
        // !OK --> Fehlermeldung zurück zum Browser
        // 3. User gefunden --> Updaten User
        // OK --> weiter
        // !OK --> Fehler zurück
        // 4. Schluss

        // 1. Validieren
        // Validate type
        if (!is_numeric($id) || !is_numeric($verifyCode)) {
            exit('ID/Code nicht korrekt');
        }

        // Validate range
        if ($id <= 0 || $verifyCode <= 999 || $verifyCode >= 10000) {
            exit('ID/Code nicht korrekt');
        }

        // Everything is OK --> continue with update

        $entityManager = $doctrine->getManager();
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

    #[Route('/register', name: 'user_new', methods: ['POST'])]
    public function new(MailerInterface $mailer, ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): Response
    {
        $content = $request->getContent();
        $contentArray = json_decode($content, true);
        $jsonRequest = new JsonRequestValidator();
        $jsonRequest->email = $contentArray['email'];
        $jsonRequest->name = $contentArray['name'];
        $jsonRequest->plz = $contentArray['plz'];
        $jsonRequest->ort = $contentArray['ort'];
        $jsonRequest->telefon = $contentArray['telefon'];
        $jsonRequest->password = $contentArray['password'];

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
            $user->setVerifyCode(mt_rand(1111, 9999));

            $entityManager->persist($user);
            $entityManager->flush();

            $confirmationURL = 'http://localhost:8000/api/user/' . $user->getId() . '/' . $user->getVerifyCode();

            $email = (new Email())
                ->from('test@mail.com')
                ->to($user->getEmail())
                ->subject('Confirmation')
                ->text($confirmationURL);

            $mailer->send($email);

        } catch (\Exception $e) {
            dump($e->getMessage());

        }
        return $this->json(['success' => true]);
    }

//    #[Route('/verify', name: 'registration_confirmation_route')]
//    public function verifyUserEmail(Request $request): Response
//    {
//        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
//        $user = $this->getUser();
//
//        // Do not get the User's Id or Email Address from the Request object
//        try {
//            $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
//        } catch (VerifyEmailExceptionInterface $e) {
//            $this->addFlash('verify_email_error', $e->getReason());
//
//            return $this->json('error');
//        }
//
//        // Mark your user as verified. e.g. switch a User::verified property to true
//
//        $this->addFlash('success', 'Your e-mail address has been verified.');
//
//        return $this->json('successful');
//    }


}



