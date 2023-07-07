<?php

namespace App\Service;

use App\Entity\JsonRequestValidator;
use App\Entity\User;
use App\Entity\verifyCodeAndIdValidator;
use App\Exception\MyFirstCustomException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use function PHPUnit\Framework\throwException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    public function verifyUser($id, $verifyCode): array
    {
        $request = new verifyCodeAndIdValidator();
        $request->setId($id);
        $request->setVerifyCode($verifyCode);

        $errors = $this->validator->validate($request);
        if (count($errors) > 0) {
            // Handle validation errors
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return ['content' => ['status' => 'failure', 'code' =>Response::HTTP_BAD_REQUEST,  'message' => ['errors' => $errorMessages], 'data' => []], 'statusCode' => Response::HTTP_BAD_REQUEST];
        }

        $entityManager = $this->managerRegistry->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return ['content' => ['status' => 'failure', 'code' =>Response::HTTP_NOT_FOUND,  'message' => "UserID {$id} existiert nicht.", 'data' => []], 'statusCode' => Response::HTTP_NOT_FOUND];
//            throw new NotFoundHttpException("UserID {$id} existiert nicht.");
        } elseif ($user->getId() === $id && $user->getVerifyCode() === $verifyCode) {
            $user->setVerifyCode(1);
            $entityManager->flush();
            return ['content' => ['status' => 'success', 'code' => Response::HTTP_OK, 'message' => 'User wurde erfolgreich verifiziert.', 'data' => []], 'statusCode' => Response::HTTP_OK];
        }
        return ['content' => ['status' => 'failure', 'code' =>Response::HTTP_UNAUTHORIZED,  'message' => 'Verifizierungslink ist nicht gültig.', 'data' => []], 'statusCode' => Response::HTTP_UNAUTHORIZED];
    }

    public function createUser(): array
    {
        $content = $this->requestStack->getCurrentRequest()->getContent();
        $contentArray = json_decode($content, true);
        $jsonRequest = new JsonRequestValidator();
        $parameters = ['email', 'name', 'plz', 'ort', 'telefon', 'password'];
        foreach ($parameters as $para) {
            if (isset($contentArray[$para])) {
                $jsonRequest->$para = $contentArray[$para];
            }
        }
        $errors = $this->validator->validate($jsonRequest);

        if (count($errors) > 0) {
            // Handle validation errors
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return ['errors' => $errorMessages, 400];
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

            $confirmationURL = 'http://localhost:5173/Verification/' . $user->getId() . '/' . $user->getVerifyCode();

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
        return ['success' => true, 201];
    }
}