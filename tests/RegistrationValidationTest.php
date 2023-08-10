<?php

namespace App\Tests;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationValidationTest extends TestCase
{
    protected ValidatorInterface $validator;
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = Validation::createValidator();
    }
    public function testEmailConstraintNotBlank()
    {
        $testUser = new User();
        $testUser->setEmail('');

        $errors = $this->validator->validate($testUser->getEmail(), [
            new Assert\NotBlank(),
        ]);

        // Assert
        $this->assertCount(1, $errors);
    }

    public function testEmailConstraintEmail()
    {
        $testUser = new User();
        $testUser->setEmail('HalloAtWeltPunktCom');

        $errors = $this->validator->validate($testUser->getEmail(), [
            new Assert\Email(),
        ]);

        // Assert
        $this->assertCount(1, $errors);
    }

    public function testNameConstraintNotBlank()
    {
        $testUser = new User();
        $testUser->setName('');

        $errors = $this->validator->validate($testUser->getName(), [
            new Assert\NotBlank(),
        ]);

        // Assert
        $this->assertCount(1, $errors);
    }

    public function testNameConstraintMaxLength()
    {
        $testUser = new User();
        $testUser->setName('2353453453453454345645645645645645634253453454353463645646546466546');

        $errors = $this->validator->validate($testUser->getName(), [
            new Assert\Length(null, null, 32),
        ]);

        // Assert
        $this->assertCount(1, $errors);
    }

    public function testPlzConstraintZero()
    {
        $testUser = new User();
        $testUser->setPlz(0);

        $errors = $this->validator->validate($testUser->getPlz(), [
            new Assert\Positive(),
        ]);

        // Assert
        $this->assertCount(1, $errors);
    }

    public function testPlzConstraintNegative()
    {
        $testUser = new User();
        $testUser->setPlz(-231);

        $errors = $this->validator->validate($testUser->getPlz(), [
            new Assert\Positive(),
        ]);

        // Assert
        $this->assertCount(1, $errors);
    }

    public function testPlzConstraintLessThan5Digits()
    {
        $testUser = new User();
        $testUser->setPlz(231);

        $errors = $this->validator->validate($testUser->getPlz(), [
            new Assert\Length(5),
        ]);

        $this->assertCount(1, $errors);
    }

    public function testPlzConstraintMoreThan5Digits()
    {
        $testUser = new User();
        $testUser->setPlz(9898789);

        $errors = $this->validator->validate($testUser->getPlz(), [
            new Assert\Length(5),
        ]);

        $this->assertCount(1, $errors);
    }

    public function testOrtConstraintNotBlank()
    {
        $testUser = new User();
        $testUser->setOrt('');

        $errors = $this->validator->validate($testUser->getOrt(), [
            new Assert\NotBlank(),
        ]);

        $this->assertCount(1, $errors);
}

    public function testOrtConstraintTypeAlpha()
    {
        $testUser = new User();
        $testUser->setOrt('TestStadt23');

        $errors = $this->validator->validate($testUser->getOrt(), [
            new Assert\Type('alpha'),
        ]);

        $this->assertCount(1, $errors);
    }

    public function testTelefonConstraintNotBlank()
    {
        $testUser = new User();
        $testUser->setTelefon('');

        $errors = $this->validator->validate($testUser->getTelefon(), [
            new Assert\NotBlank,
        ]);

        $this->assertCount(1, $errors);
    }

    public function testPasswordConstraintNotBlank()
    {
        $testUser = new User();
        $testUser->setPassword('');

        $errors = $this->validator->validate($testUser->getPassword(), [
            new Assert\NotBlank,
        ]);

        $this->assertCount(1, $errors);
    }

}
