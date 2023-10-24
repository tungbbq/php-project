<?php

namespace App\Controller;
use App\Service\PaginationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

#[Route('/api', name: 'api_')]
class PaginationController extends AbstractController
{
    private UserRepository $userRepository;
    private RolesController $rolesController;
    private LoggerInterface $logger;
    private PaginationService $paginationService;

    public function __construct( UserRepository $userRepository,
                                 RolesController $rolesController,
                                 LoggerInterface $logger,
                                 PaginationService $paginationService
    )
    {
        $this->userRepository = $userRepository;
        $this->rolesController = $rolesController;
        $this->logger = $logger;
        $this->paginationService = $paginationService;
    }

    #[Route('/pagination', name: 'page_count', methods: ['GET'])]
    public function getPaginationCount(): Response
    {
        try {
            $this->rolesController->isRead();
            $response = $this->userRepository->getTotalUserCount();
            return $this->json(
                $response,
                Response::HTTP_OK
            );
        } catch(AccessDeniedException $e){
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            return $this->json([
                'status' => 'failure',
                'message' => "Sie haben keine Berechtigung für diesen Bereich."
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    #[Route('/page/{pageNumber}', name: 'page_show', methods: ['GET'])]
    public function showSinglePage($pageNumber): Response
    {
        $this->rolesController->isRead();
        $response = $this->paginationService->getNewPage($pageNumber);
        return $this->json(
            $response['data'],
            Response::HTTP_OK
        );

    }
}