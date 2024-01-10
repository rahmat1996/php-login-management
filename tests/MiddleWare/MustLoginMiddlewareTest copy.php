<?php


namespace Tamhar\PhpLoginManagement\Middleware {

    require_once __DIR__ . "/../Helper/helper.php";

    use PHPUnit\Framework\TestCase;
    use Tamhar\PhpLoginManagement\Config\Database;
    use Tamhar\PhpLoginManagement\Domain\Session;
    use Tamhar\PhpLoginManagement\Domain\User;
    use Tamhar\PhpLoginManagement\Repository\SessionRepository;
    use Tamhar\PhpLoginManagement\Repository\UserRepository;
    use Tamhar\PhpLoginManagement\Service\SessionService;

    class MustLoginMiddlewareTest extends TestCase
    {
        private MustLoginMiddleware $middleware;
        private UserRepository $userRepository;
        private SessionRepository $sessionRepository;

        protected function setUp(): void
        {
            $this->middleware = new MustLoginMiddleware();

            $this->userRepository = new UserRepository(Database::getConnection());
            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->sessionRepository->deleteAll();
            $this->userRepository->deleteAll();
            putenv("mode=test");
        }

        public function testBeforeGuest()
        {
            $this->middleware->before();
            $this->expectOutputRegex("[Location:/users/login]");
        }

        public function testBeforeLoginUser()
        {
            $user = new User();
            $user->id = 'rahmat';
            $user->name = 'Rahmat';
            $user->password = password_hash('12345', PASSWORD_BCRYPT);
            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->middleware->before();
            $this->expectOutputString("");
        }
    }
}
