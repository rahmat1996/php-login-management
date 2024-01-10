<?php

namespace Tamhar\PhpLoginManagement\Middleware;

use Tamhar\PhpLoginManagement\App\View;
use Tamhar\PhpLoginManagement\Config\Database;
use Tamhar\PhpLoginManagement\Repository\SessionRepository;
use Tamhar\PhpLoginManagement\Repository\UserRepository;
use Tamhar\PhpLoginManagement\Service\SessionService;

class MustLoginMiddleware implements Middleware
{

    private SessionService $sessionService;

    function __construct()
    {
        $sessionRepository = new SessionRepository(Database::getConnection());
        $userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    function before(): void
    {
        $user = $this->sessionService->current();
        if ($user == null) {
            View::redirect('/users/login');
        }
    }
}
