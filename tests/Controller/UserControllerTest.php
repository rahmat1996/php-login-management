<?php

namespace Tamhar\PhpLoginManagement\Controller {

    require_once __DIR__ . "/../Helper/helper.php";

    use PHPUnit\Framework\TestCase;
    use Tamhar\PhpLoginManagement\Config\Database;
    use Tamhar\PhpLoginManagement\Domain\Session;
    use Tamhar\PhpLoginManagement\Domain\User;
    use Tamhar\PhpLoginManagement\Repository\SessionRepository;
    use Tamhar\PhpLoginManagement\Repository\UserRepository;
    use Tamhar\PhpLoginManagement\Service\SessionService;

    use function PHPUnit\Framework\assertEquals;

    class UserControllerTest extends TestCase
    {

        private UserController $userController;
        private UserRepository $userRepository;
        private SessionRepository $sessionRepository;

        protected function setUp(): void
        {
            $this->userController = new UserController();

            $this->sessionRepository = new SessionRepository(Database::getConnection());
            $this->sessionRepository->deleteAll();

            $this->userRepository = new UserRepository(Database::getConnection());
            $this->userRepository->deleteAll();
            putenv("mode=test");
        }

        public function testRegister()
        {
            $this->userController->register();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register New User]");
        }

        public function testPostRegisterSuccess()
        {
            $_POST['id'] = 'rahmat';
            $_POST['name'] = 'Rahmat';
            $_POST['password'] = '12345';

            $this->userController->postRegister();

            $this->expectOutputRegex("[Location:/users/login]");
        }

        public function testPostRegisterValidationError()
        {
            $_POST['id'] = '';
            $_POST['name'] = 'Rahmat';
            $_POST['password'] = '12345';

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register New User]");
            $this->expectOutputRegex("[Id, Name, Password cannot blank]");
        }

        public function testPostRegisterDuplicate()
        {
            $user = new User();
            $user->id = 'rahmat';
            $user->name = 'Rahmat';
            $user->password = '12345';

            $this->userRepository->save($user);

            $_POST['id'] = 'rahmat';
            $_POST['name'] = 'Rahmat';
            $_POST['password'] = '12345';

            $this->userController->postRegister();

            $this->expectOutputRegex("[Register]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Register New User]");
            $this->expectOutputRegex("[User Id already exists]");
        }

        public function testLogin()
        {
            $this->userController->login();

            $this->expectOutputRegex('[Login User]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[Password]');
        }

        public function testLoginSuccess()
        {
            $user = new User();
            $user->id = "rahmat";
            $user->name = "Rahmat";
            $user->password = password_hash("12345", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST['id'] = "rahmat";
            $_POST['password'] = '12345';

            $this->userController->postLogin();
            $this->expectOutputRegex("[Location:/]");
            $this->expectOutputRegex("[X-PLM-SESSION: ]");
        }

        public function testLoginValidationError()
        {
            $_POST['id'] = '';
            $_POST['password'] = '';
            $this->userController->postLogin();

            $this->expectOutputRegex('[Login User]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Id, Password can not blank]');
        }

        public function testLoginUserNotFound()
        {
            $_POST['id'] = 'notfound';
            $_POST['password'] = 'notfound';

            $this->userController->postLogin();

            $this->expectOutputRegex('[Login User]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Id or password is wrong]');
        }

        public function testLoginWrongPassword()
        {
            $user = new User();
            $user->id = "rahmat";
            $user->name = "Rahmat";
            $user->password = password_hash("12345", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $_POST['id'] = 'rahmat';
            $_POST['password'] = '1234';

            $this->userController->postLogin();

            $this->expectOutputRegex('[Login user]');
            $this->expectOutputRegex('[Id]');
            $this->expectOutputRegex('[Password]');
            $this->expectOutputRegex('[Id or password is wrong]');
        }

        public function testLogout()
        {
            $user = new User();
            $user->id = "rahmat";
            $user->name = "Rahmat";
            $user->password = password_hash("12345", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->logout();

            $this->expectOutputRegex("[Location:/]");
            $this->expectOutputRegex("[X-PLM-SESSION:]");
        }

        public function testUpdateProfile()
        {

            $user = new User();
            $user->id = "rahmat";
            $user->name = "Rahmat";
            $user->password = password_hash("12345", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updateProfile();

            $this->expectOutputRegex("[Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[rahmat]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Rahmat]");
        }

        public function testPostUpdateProfileSuccess()
        {
            $user = new User();
            $user->id = "rahmat";
            $user->name = "Rahmat";
            $user->password = password_hash("12345", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = 'Kucing';
            $this->userController->postUpdateProfile();

            $this->expectOutputRegex("[Location:/]");

            $result = $this->userRepository->findById($user->id);
            assertEquals("Kucing", $result->name);
        }

        public function testPostUpdateProfileValidationError()
        {
            $user = new User();
            $user->id = "rahmat";
            $user->name = "Rahmat";
            $user->password = password_hash("12345", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['name'] = '';
            $this->userController->postUpdateProfile();

            $this->expectOutputRegex("[Profile]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[rahmat]");
            $this->expectOutputRegex("[Name]");
            $this->expectOutputRegex("[Id, Name can not blank]");
        }

        public function testUpdatePassword()
        {
            $user = new User();
            $user->id = "rahmat";
            $user->name = "Rahmat";
            $user->password = password_hash("12345", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $this->userController->updatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[rahmat]");
            $this->expectOutputRegex("[Old Password]");
            $this->expectOutputRegex("[New Password]");
        }

        public function testPostUpdatePasswordSuccess()
        {
            $user = new User();
            $user->id = "rahmat";
            $user->name = "Rahmat";
            $user->password = password_hash("12345", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = '12345';
            $_POST['newPassword'] = '54321';

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Location:/]");

            $result = $this->userRepository->findById($user->id);

            self::assertTrue(password_verify("54321", $result->password));
        }

        public function testPostUpdatePasswordValidationError()
        {
            $user = new User();
            $user->id = "rahmat";
            $user->name = "Rahmat";
            $user->password = password_hash("12345", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = '';
            $_POST['newPassword'] = '';

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[rahmat]");
            $this->expectOutputRegex("[Old Password]");
            $this->expectOutputRegex("[New Password]");
            $this->expectOutputRegex("[Id, Old Password, New Password cannot blank]");
        }

        public function testPostUpdatePasswordWrongOldPassword()
        {
            $user = new User();
            $user->id = "rahmat";
            $user->name = "Rahmat";
            $user->password = password_hash("12345", PASSWORD_BCRYPT);

            $this->userRepository->save($user);

            $session = new Session();
            $session->id = uniqid();
            $session->userId = $user->id;
            $this->sessionRepository->save($session);

            $_COOKIE[SessionService::$COOKIE_NAME] = $session->id;

            $_POST['oldPassword'] = '11111';
            $_POST['newPassword'] = '54321';

            $this->userController->postUpdatePassword();

            $this->expectOutputRegex("[Password]");
            $this->expectOutputRegex("[Id]");
            $this->expectOutputRegex("[rahmat]");
            $this->expectOutputRegex("[Old Password]");
            $this->expectOutputRegex("[New Password]");
            $this->expectOutputRegex("[Old password is wrong]");
        }
    }
}
