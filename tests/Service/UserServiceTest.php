<?php

namespace Tamhar\PhpLoginManagement\Service;

use PHPUnit\Framework\TestCase;
use Tamhar\PhpLoginManagement\Config\Database;
use Tamhar\PhpLoginManagement\Domain\Session;
use Tamhar\PhpLoginManagement\Domain\User;
use Tamhar\PhpLoginManagement\Exception\ValidationException;
use Tamhar\PhpLoginManagement\Model\UserLoginRequest;
use Tamhar\PhpLoginManagement\Model\UserPasswordUpdateRequest;
use Tamhar\PhpLoginManagement\Model\UserProfileUpdateRequest;
use Tamhar\PhpLoginManagement\Model\UserRegisterRequest;
use Tamhar\PhpLoginManagement\Repository\SessionRepository;
use Tamhar\PhpLoginManagement\Repository\UserRepository;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $connection = Database::getConnection();
        $this->userRepository = new UserRepository($connection);
        $this->sessionRepository = new SessionRepository($connection);
        $this->userService = new UserService($this->userRepository);
        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    public function testRegisterSuccess()
    {
        $request = new UserRegisterRequest();
        $request->id = "rahmat";
        $request->name = "Rahmat";
        $request->password = "12345";

        $response = $this->userService->register($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertEquals($request->name, $response->user->name);
        self::assertNotEquals($request->password, $response->user->password);

        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testRegisterFailed()
    {
        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->id = "";
        $request->name = "Rahmat";
        $request->password = "12345";

        $this->userService->register($request);
    }

    public function testRegisterDuplicate()
    {
        $user = new User();
        $user->id = "rahmat";
        $user->name = "Rahmat";
        $user->password = "12345";

        $this->userRepository->save($user);

        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->id = "rahmat";
        $request->name = "Rahmat";
        $request->password = "12345";

        $this->userService->register($request);
    }

    public function testLoginNotFound()
    {
        $this->expectException(ValidationException::class);
        $request = new UserLoginRequest();
        $request->id = "rahmat";
        $request->password = "12345";

        $this->userService->login($request);
    }

    public function testLoginWrongPassword()
    {
        $user = new User();
        $user->id = "rahmat";
        $user->name = "Rahmat";
        $user->password = password_hash("12345", PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $this->expectException(ValidationException::class);
        $request = new UserLoginRequest();
        $request->id = "rahmat";
        $request->password = "1234";

        $this->userService->login($request);
    }

    public function testLoginSuccess()
    {
        $user = new User();
        $user->id = "rahmat";
        $user->name = "Rahmat";
        $user->password = password_hash("12345", PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserLoginRequest();
        $request->id = "rahmat";
        $request->password = "12345";

        $response = $this->userService->login($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testUpdateSuccess()
    {
        $user = new User();
        $user->id = "rahmat";
        $user->name = "Rahmat";
        $user->password = password_hash("12345", PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserProfileUpdateRequest();
        $request->id = $user->id;
        $request->name = "Kucing";

        $this->userService->updateProfile($request);

        $result = $this->userRepository->findById($user->id);

        self::assertEquals($request->id, $result->id);
        self::assertEquals($request->name, $result->name);
    }

    public function testUpdateValidationError()
    {
        $this->expectException(ValidationException::class);

        $request = new UserProfileUpdateRequest();
        $request->id = "";
        $request->name = "";

        $this->userService->updateProfile($request);
    }

    public function testUpdateNotFound()
    {
        $this->expectException(ValidationException::class);

        $request = new UserProfileUpdateRequest();
        $request->id = "rahmat";
        $request->name = "Rahmat";

        $this->userService->updateProfile($request);
    }

    public function testUpdatePasswordSuccess()
    {
        $user = new User();
        $user->id = "rahmat";
        $user->name = "Rahmat";
        $user->password = password_hash("12345", PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = "rahmat";
        $request->oldPassword = "12345";
        $request->newPassword = "1234";

        $this->userService->updatePassword($request);

        $result = $this->userRepository->findById($user->id);
        self::assertTrue(password_verify($request->newPassword, $result->password));
    }

    public function testUpdatePasswordValidationError()
    {
        $this->expectException(ValidationException::class);

        $request = new UserPasswordUpdateRequest();
        $request->id = "rahmat";
        $request->oldPassword = "";
        $request->newPassword = "";

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordWrongOldPassword()
    {
        $this->expectException(ValidationException::class);

        $user = new User();
        $user->id = "rahmat";
        $user->name = "Rahmat";
        $user->password = password_hash("12345", PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserPasswordUpdateRequest();
        $request->id = "rahmat";
        $request->oldPassword = "54321";
        $request->newPassword = "1234";

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordNotFound()
    {
        $this->expectException(ValidationException::class);

        $request = new UserPasswordUpdateRequest();
        $request->id = "rahmat";
        $request->oldPassword = "12345";
        $request->newPassword = "1234";

        $this->userService->updatePassword($request);
    }
}
