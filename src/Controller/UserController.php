<?php

namespace App\Controller;

use App\Service\UserService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/user/search', name: 'user_search', methods: ['POST'])]
    public function search(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_READ');
        return $this->json($this->userService->searchUsers());

    }

    #[Route('/user', name: 'user_index', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_READ');
        return $this->json($this->userService->getUsers());
    }


    #[Route('/user/{id}', name: 'user_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_READ');
        return $this->json($this->userService->getUserById($id));
    }

    #[Route('/user/{id}', name: 'user_edit', methods: ['PUT'])]
    public function edit(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_UPDATE');
        return $this->json($this->userService->updateUser($id));
    }

    #[Route('/user/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_DELETE');
        return $this->json($this->userService->deleteUser($id));
    }
}
