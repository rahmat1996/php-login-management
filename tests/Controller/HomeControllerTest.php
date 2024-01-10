<?php

namespace Tamhar\PhpLoginManagement\Controller;

use PHPUnit\Framework\TestCase;
use Tamhar\PhpLoginManagement\Config\Database;
use Tamhar\PhpLoginManagement\Domain\Session;
use Tamhar\PhpLoginManagement\Domain\User;
use Tamhar\PhpLoginManagement\Repository\SessionRepository;
use Tamhar\PhpLoginManagement\Repository\UserRepository;
use Tamhar\PhpLoginManagement\Service\SessionService;

class HomeControllerTest extends TestCase
{
    private HomeController $homeController;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    protected function setUp(): void
    {
        $this->homeController = new HomeController();
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    public function testGuest()
    {
        $this->homeController->index();
        $this->expectOutputRegex("[Login Management]");
    }

    public function testUserLogin()
    {
        $user = new User();
        $user->id = 'rahmat';
        $user->name = 'Rahmat';
        $user->password = '12345';

        $this->userRepository->save($user);

        $session = new Session();
        $session->id = uniqid();
        $session->userId = $user->id;

        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $this->homeController->index();
        $this->expectOutputRegex("[Hello Rahmat]");
    }
}
