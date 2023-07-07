<?php

namespace App\Tests;

use App\Service\RegistrationService;
use PHPUnit\Framework\TestCase;

class registrationServiceTest extends TestCase
{
    private RegistrationService $registrationService;

    public function testVerifyUser()
    {
//     $this->expectException();
        $this->assertNull($this->registrationService->verifyUser('dfgd', 3232));
    }
}
