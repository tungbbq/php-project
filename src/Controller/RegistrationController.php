<?php

namespace App\Controller;

use App\Service\RegistrationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class RegistrationController extends AbstractController
{
    private RegistrationService $registrationService;

    #[Route('/user/{id}/{verifyCode}', name: 'user_verification', methods: ['GET'])]
    public function verify(int $id, int $verifyCode): void
    {
     $this->registrationService->verifyUser($id, $verifyCode);
    }

    #[Route('/register', name: 'user_new', methods: ['POST'])]
    public function new(): Response
    {

        return $this->json($this->registrationService->newUser());
    }


}



