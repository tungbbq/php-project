<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RolesController extends AbstractController
{
    public function isRead(): void
    {
       $this->denyAccessUnlessGranted('ROLE_READ');
    }

    public function isUpdate(): void
    {
        $this->denyAccessUnlessGranted('ROLE_UPDATE');
    }

    public function isDelete(): void
    {
        $this->denyAccessUnlessGranted('ROLE_DELETE');
    }
}