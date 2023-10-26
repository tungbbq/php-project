<?php

namespace App\Service;

use App\Entity\RegistrationValidator;
use App\Entity\User;
use App\Entity\verifyCodeAndIdValidator;
use App\Exception\NoUserException;
use App\Exception\ValidationErrorException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
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

    public function __construct(ValidatorInterface          $validator,
                                UserPasswordHasherInterface $passwordHasher,
                                ManagerRegistry             $managerRegistry,
                                MailerInterface             $mailer,
                                RequestStack                $requestStack,
                                UserService                 $userService)
    {
        $this->managerRegistry = $managerRegistry;
        $this->mailer = $mailer;
        $this->requestStack = $requestStack;
        $this->userService = $userService;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    /**
     * @throws ValidationErrorException
     * @throws NoUserException
     */
    public function verifyUser($id, $verifyCode): array
    {
        $codeAndId = new verifyCodeAndIdValidator();
        $codeAndId->setId($id);
        $codeAndId->setVerifyCode($verifyCode);

        $errors = $this->validator->validate($codeAndId);
        if (count($errors) > 0) {
            // Handle validation errors
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new ValidationErrorException($errorMessages);
//            return [
//                'data' => [
//                    'status' => 'failure',
//                    'message' => [
//                        'errors' => $errorMessages
//                    ]],
//                'statusCode' => Response::HTTP_BAD_REQUEST
//            ];
        }

        $entityManager = $this->managerRegistry->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
//            return [
//                'data' => [
//                    'status' => 'failure',
//                    'message' => "UserID {$id} existiert nicht."
//                ],
//                'statusCode' => Response::HTTP_NOT_FOUND
//            ];
            throw new NoUserException("UserID {$id} existiert nicht.");
        } elseif ($user->getId() === $id && $user->getVerifyCode() === $verifyCode) {
            $user->setVerifyCode(1);
            $entityManager->flush();
            return ['data' => [
                'status' => 'success',
                'message' => 'User wurde erfolgreich verifiziert.'
            ],
                'statusCode' => Response::HTTP_OK
            ];
        }
        return [
            'data' => [
                'status' => 'failure',
                'message' => 'Verifizierungslink ist nicht gültig.'
            ],
            'statusCode' => Response::HTTP_UNAUTHORIZED
        ];
    }

    public function createUser(): array
    {
        $content = $this->requestStack->getCurrentRequest()->getContent();
        $contentArray = json_decode($content, true);
        $user = new User();

        $user->setEmail($contentArray['email']);
        $user->setName($contentArray['name']);
        $user->setPlz($contentArray['plz']);
        $user->setOrt($contentArray['ort']);
        $user->setTelefon($contentArray['telefon']);
        $user->setPassword($contentArray['password']);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
//             Handle validation errors
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new ValidationErrorException($errorMessages);
//            return [
//                'data' => [
//                    'status' => 'failure',
//                    'message' => [
//                        'errors' => $errorMessages
//                    ]],
//                'statusCode' => Response::HTTP_BAD_REQUEST];

        }

        $entityManager = $this->managerRegistry->getManager();

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $contentArray['password']
        );
        $user->setPassword($hashedPassword);
        $user->setVerifyCode(mt_rand(1111, 9999));

        $entityManager->persist($user);
        $entityManager->flush();

        $confirmationURL = 'http://localhost:5173/Verification/id/' . $user->getId() . '/code/' . $user->getVerifyCode();

        $email = (new Email())
            ->from('test@mail.com')
            ->to($user->getEmail())
            ->subject('Confirmation')
            ->text($confirmationURL);
        $this->mailer->send($email);

        return [
            'data' => [
                'status' => 'success',
                'message' => 'User wurde erfolgreich angelegt.'],
            'statusCode' => Response::HTTP_CREATED
        ];
    }
}