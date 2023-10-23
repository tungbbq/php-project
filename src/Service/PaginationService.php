<?php

namespace App\Service;

use App\Entity\User;

class PaginationService
{
    private UserService $userService;
    private UserRepository $userRepository;

    public function __construct(UserService $userService,
                                UserRepository $userRepository,
    )
    {
        $this->userService = $userService;
        $this->userRepository = $userRepository;
    }
    public function totalUserCount(): int
    {
        return $this->userRepository->getTotalUserCount();
    }

    public function getNewPage($pageNumber)
    {
        $limit = 10; // Number of items per page

        // Ensure that $pageNumber is a positive integer; if not, default to 1
        $pageNumber = max(1, (int)$pageNumber);

        // Calculate the offset to start fetching from
        $offset = ($pageNumber - 1) * $limit;

        // Fetch the data using findBy
        $users = $this->managerRegistry
            ->getRepository(User::class)
            ->findBy([], null, $limit, $offset);

        return $this->transformUsersToArray($users);
    }


}