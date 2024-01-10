<?php

namespace Tamhar\PhpLoginManagement\Controller;

use Tamhar\PhpLoginManagement\App\View;
use Tamhar\PhpLoginManagement\Config\Database;
use Tamhar\PhpLoginManagement\Domain\User;
use Tamhar\PhpLoginManagement\Repository\SessionRepository;
use Tamhar\PhpLoginManagement\Repository\UserRepository;
use Tamhar\PhpLoginManagement\Service\SessionService;

class HomeController
{
    private SessionService $sessionService;

    public function __construct()
    {
        $connection = Database::getConnection();
        $sessionRepository = new SessionRepository($connection);
        $userRepository = new UserRepository($connection);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    public function index(): void
    {
        $user = $this->sessionService->current();
        if ($user == null) {
            View::render('Home/index', [
                "title" => "PHP Login Management"
            ]);
        } else {
            View::render('Home/dashboard', [
                "title" => "Dashboard",
                "user" => [
                    "name" => $user->name
                ]
            ]);
        }
    }
}
