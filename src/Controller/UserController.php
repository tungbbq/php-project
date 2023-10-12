<?php

namespace App\Controller;

use App\Exception\NoUserException;
use App\Exception\ValidationErrorException;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;


#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    private UserService $userService;
    private RolesController $rolesService;
    private LoggerInterface $logger;

    public function __construct(UserService $userService, RolesController $rolesService, LoggerInterface $logger)
    {
        $this->userService = $userService;
        $this->rolesService = $rolesService;
        $this->logger = $logger;
    }

    #[Route('/user/search', name: 'user_search', methods: ['POST'])]
    public function search(): Response
    {
        try {
            $this->rolesService->isRead();
            $response = $this->userService->searchUsers();

        } catch(AccessDeniedException $e){
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->json([
                'status' => 'failure',
                'message' => "Sie haben keine Berechtigung für diesen Bereich."
            ],
                 Response::HTTP_UNAUTHORIZED
            );

        } catch (ValidationErrorException $e2){
            $this->logger->error($e2->getMessage(), ['exception' => $e2]);
            return $this->json([
                'status' => 'failure',
                'message' => $e2->getMessage()
            ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json(
            $response['data'],
            Response::HTTP_OK
        );
    }

    #[Route('/user', name: 'user_index', methods: ['GET'])]
    public function index(): Response
    {
        try {
            $this->rolesService->isRead();
            $response = $this->userService->getUsers();

        }catch (AccessDeniedException $e){
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->json([
                'status' => 'failure',
                'message' => "Sie haben keine Berechtigung für diesen Bereich."
            ],
                Response::HTTP_UNAUTHORIZED
            );

        }catch (NoUserException $e2){
            $this->logger->error($e2->getMessage(), ['exception' => $e2]);
            return $this->json([
                'status' => 'failure',
                'message' => $e2->getMessage()
            ],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            $response['data'],
            Response::HTTP_OK
        );
    }


    #[Route('/user/{id}', name: 'user_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        try {
            $this->rolesService->isRead();
            $response = $this->userService->getUserById($id);
        }catch (AccessDeniedException $e){
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->json([
                'status' => 'failure',
                'message' => "Sie haben keine Berechtigung für diesen Bereich."
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }catch (NoUserException $e2){
            $this->logger->error($e2->getMessage(), ['exception' => $e2]);
            return $this->json([
                'status' => 'failure',
                'message' => $e2->getMessage()
            ],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            $response['data'],
            Response::HTTP_OK
        );
    }

    #[Route('/user/{id}', name: 'user_edit', methods: ['PUT'])]
    public function edit(int $id): Response
    {
        try {
            $this->rolesService->isUpdate();
            $this->userService->updateUser($id);
        }catch(AccessDeniedException $e){
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->json([
                'status' => 'failure',
                'message' => "Sie haben keine Berechtigung für diesen Bereich."
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }catch (NoUserException $e2){
            $this->logger->error($e2->getMessage(), ['exception' => $e2]);
            return $this->json([
                'status' => 'failure',
                'message' => $e2->getMessage()
            ],
                Response::HTTP_NOT_FOUND
            );
        }catch (ValidationErrorException $e3){
            $this->logger->error($e3->getMessage(), ['exception' => $e3]);
            return $this->json([
                'status' => 'failure',
                'message' => $e3->getMessage()
            ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json(
            [
                'status' => 'success',
                'message' => "UserId {$id} wurde erfolgreich bearbeitet."
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/user/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        try{
            $this->rolesService->isDelete();
            $this->userService->deleteUser($id);
        }catch (AccessDeniedException $e){
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->json([
                'status' => 'failure',
                'message' => "Sie haben keine Berechtigung für diesen Bereich."
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }catch (NoUserException $e2){
            $this->logger->error($e2->getMessage(), ['exception' => $e2]);
            return $this->json([
                'status' => 'failure',
                'message' => $e2->getMessage()
            ],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            [
                'status' => 'success',
                'message' => "UserId {$id} wurde erfolgreich entfernt."
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/pagination', name: 'page', methods: ['GET'])]
    public function pagination(): Response
    {
        $test = $this->userService->totalUserCount();
        return $this->json([
            'status' => 'failure',
            'message' => $test
        ],
            Response::HTTP_OK
        );

    }
}
