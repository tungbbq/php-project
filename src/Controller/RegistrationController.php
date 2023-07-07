<?php

namespace App\Controller;

use App\Exception\MyFirstCustomException;
use App\Service\RegistrationService;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class RegistrationController extends AbstractController
{
    private RegistrationService $registrationService;
    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    #[Route('/user/{id}/{verifyCode}', name: 'user_verification', methods: ['GET'])]
    public function verify(int $id, int $verifyCode): JsonResponse
    {
        try {
            $response = $this->registrationService->verifyUser($id, $verifyCode);

        } catch (\NotFoundHttpException ) {
            return $this->json(['success' => false], Response::HTTP_NOT_FOUND);
        } catch (\InvalidArgumentException ) {
            return $this->json(['success' => false], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($response['content'], $response['statusCode']);
    }

    #[Route('/register', name: 'user_new', methods: ['POST'])]
    public function create(): JsonResponse
    {
        return $this->json($this->registrationService->createUser());
    }

}



