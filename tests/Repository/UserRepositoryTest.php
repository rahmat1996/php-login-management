<?php

namespace Tamhar\PhpLoginManagement\Repository;

use PHPUnit\Framework\TestCase;
use Tamhar\PhpLoginManagement\Config\Database;
use Tamhar\PhpLoginManagement\Domain\User;

class UserRepositoryTest extends TestCase
{

    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    public function testSaveSuccess()
    {
        $user = new User();
        $user->id = "rahmat";
        $user->name = "Rahmat";
        $user->password = "12345";

        $this->userRepository->save($user);

        $result  = $this->userRepository->findById($user->id);

        self::assertEquals($user->id, $result->id);
        self::assertEquals($user->name, $result->name);
        self::assertEquals($user->password, $result->password);
    }

    public function testFindByIdNotFound()
    {
        $user = $this->userRepository->findById("tidakada");
        self::assertNull($user);
    }

    public function testUpdate()
    {
        $user = new User();
        $user->id = "rahmat";
        $user->name = "Rahmat";
        $user->password = password_hash("12345", PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $user->name = "Kucing";
        $this->userRepository->update($user);

        $result = $this->userRepository->findById($user->id);

        self::assertEquals($user->id,$result->id);
        self::assertEquals($user->name,$result->name);
        self::assertTrue(password_verify("12345",$result->password));
    }
}
