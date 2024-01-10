<?php



namespace Tamhar\PhpLoginManagement\Service;

require_once __DIR__ . "/../Helper/helper.php";

use PHPUnit\Framework\TestCase;
use Tamhar\PhpLoginManagement\Config\Database;
use Tamhar\PhpLoginManagement\Domain\Session;
use Tamhar\PhpLoginManagement\Domain\User;
use Tamhar\PhpLoginManagement\Repository\SessionRepository;
use Tamhar\PhpLoginManagement\Repository\UserRepository;


class SessionServiceTest extends TestCase
{
    
    private SessionService $sessionService;
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();

        $user = new User();
        $user->id = 'rahmat';
        $user->name = 'Rahmat';
        $user->password = '12345';
        $this->userRepository->save($user);
    }

    public function testCreate()
    {
        $session = $this->sessionService->create('rahmat');
        $this->expectOutputRegex("[X-PLM-SESSION: $session->id]");

        $result = $this->sessionRepository->findById($session->id);

        self::assertEquals("rahmat", $result->userId);
    }

    public function testDestroy()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = "rahmat";

        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $this->sessionService->destroy();

        $this->expectOutputRegex("[X-PLM-SESSION: ]");

        $result = $this->sessionRepository->findById($session->id);
        self::assertNull($result);
    }

    public function testCurrent()
    {
        $session = new Session();
        $session->id = uniqid();
        $session->userId = 'rahmat';

        $this->sessionRepository->save($session);

        $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

        $user = $this->sessionService->current();

        self::assertEquals($session->userId, $user->id);
    }
}
