<?php

namespace App\Controller;

use App\Exception\NoUserException;
use App\Exception\ValidationErrorException;
use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
        } catch (ValidationErrorException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->json([
                'status' => 'failure',
                'message' => $e->getMessage()
            ],
                Response::HTTP_BAD_REQUEST
            );
        } catch (NoUserException $e2) {
            $this->logger->error($e2->getMessage(), ['exception' => $e2]);
            return $this->json([
                'status' => 'failure',
                'message' => $e2->getMessage()
            ],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json($response['data'], $response['statusCode']);
    }

    #[Route('/register', name: 'user_new', methods: ['POST'])]
    public function create(): JsonResponse
    {
        try {
            return $this->json($this->registrationService->createUser());
        } catch (ValidationErrorException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->json([
                'status' => 'failure',
                'message' => $e->getMessage()
            ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

}





