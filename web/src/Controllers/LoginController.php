<?php declare(strict_types=1);

namespace Pulse\Controllers;

use DB;
use Pulse\BaseController;
use Pulse\Exceptions\UserNotExistException;
use Pulse\Models\LoginService;

class LoginController extends BaseController
{
    public function show()
    {
        $text = "";
        $userId = $this->getRequest()->getBodyParameter('user');
        $password = $this->getRequest()->getBodyParameter('password');

        try {
            $session = LoginService::logInSession($userId, $password);
        } catch (UserNotExistException $ex) {
            echo "User $userId Not Found";
            exit;
        }

        if ($session == null){
            echo "Invalid Credentials";
            exit;
        }

        $db_session_query = DB::query("SELECT user, ip_address, user_agent, created, expires, HEX(session_key) FROM sessions;");
        $user_agents = DB::query("SELECT id, user_agent FROM user_agents;");
        $user_agents_mapped = array();

        for ($i = 0; $i < count($user_agents); $i++) {
            $user_agents_mapped[$user_agents[$i]['id']] = $user_agents[$i]['user_agent'];
        }
        $data = [
            'session' => $session,
            'user_agents' => $user_agents_mapped,
            'db_session' => $db_session_query
        ];
        $this->render('LoginTemplate.html.twig', $data);
    }
}