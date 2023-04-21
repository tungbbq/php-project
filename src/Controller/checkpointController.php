<?php
//
//namespace App\Controller;
//
//use App\Entity\User;
//
////use App\Form\RegistrationFormType;
//use Doctrine\ORM\EntityManagerInterface;
//use Doctrine\Persistence\ManagerRegistry;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
//use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
//use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Component\Mailer\MailerInterface;
//use Symfony\Component\Mime\Email;
//use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
//use Symfony\Bridge\Twig\Mime\TemplatedEmail;
//
//#[Route('/api', name: 'api_')]
//class RegistrationController extends AbstractController
//{
//    private $verifyEmailHelper;
//    private $mailer;
//
//    public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer)
//    {
//        $this->verifyEmailHelper = $helper;
//        $this->mailer = $mailer;
//    }
//
//    #[Route('/user', name: 'user_new', methods: ['POST'])]
//    public function new(MailerInterface $mailer, ManagerRegistry $doctrine, Request $request, UserPasswordHasherInterface $passwordHasher): Response
//    {
//        $content = $request->getContent();
//        $contentArray = json_decode($content, true);
//
//        try {
//            $entityManager = $doctrine->getManager();
//            $user = new User();
//            $user->setEmail($contentArray['email']);
//            $user->setName($contentArray['name']);
//            $user->setPlz($contentArray['plz']);
//            $user->setOrt($contentArray['ort']);
//            $plaintextPassword = $contentArray['password'];
//            $hashedPassword = $passwordHasher->hashPassword(
//                $user,
//                $plaintextPassword
//            );
//            $user->setPassword($hashedPassword);
//            $user->setTelefon($contentArray['telefon']);
//
//            $entityManager->persist($user);
//            $entityManager->flush();
//
//            $signatureComponents = $this->verifyEmailHelper->generateSignature(
//                'registration_confirmation_route',
//                $user->getId(),
//                $user->getEmail()
//            );
//
//            $email = (new TemplatedEmail())
//                ->from('test@mail.com')
//                ->to($user->getEmail())
//                ->subject('Confirmation')
//                ->htmlTemplate('email/signup.html.twig')
//                ->context([
//                    'signedUrl' => $signatureComponents->getSignedUrl(),
//                ]);
//
//            $mailer->send($email);
//
//        } catch (\Exception $e) {
//            dump($e->getMessage());
////        TODO Monolog
//        }
//        return $this->json('Created new User successfully with id ' . $user->getId());
//    }
//
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
//
//
//}
//
