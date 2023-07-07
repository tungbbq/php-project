<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    private UserService $userService;
    private RolesController $rolesService;

    public function __construct(UserService $userService, RolesController $rolesService)
    {
        $this->userService = $userService;
        $this->rolesService = $rolesService;
    }

    #[Route('/user/search', name: 'user_search', methods: ['POST'])]
    public function search(): Response
    {
        $this->rolesService->isRead();
        return $this->json($this->userService->searchUsers());
    }

    #[Route('/user', name: 'user_index', methods: ['GET'])]
    public function index(): Response
    {
        $this->rolesService->isRead();
        return $this->json($this->userService->getUsers());
    }


    #[Route('/user/{id}', name: 'user_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $this->rolesService->isRead();
        return $this->json($this->userService->getUserById($id));
    }

    #[Route('/user/{id}', name: 'user_edit', methods: ['PUT'])]
    public function edit(int $id): Response
    {
        $this->rolesService->isUpdate();
        return $this->json($this->userService->updateUser($id));
    }

    #[Route('/user/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        $this->rolesService->isDelete();
        return $this->json($this->userService->deleteUser($id));
    }
}
