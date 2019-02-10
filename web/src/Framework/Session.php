<?php declare(strict_types=1);

namespace Pulse\Framework;

use Pulse\Utils;
use DB;

define('USER_EXPIRATION_DAYS', 1);
define('SALT_LENGTH', 32);


class Session
{
    private $userId;
    private $sessionKey;

    /**
     * Session constructor.
     * @throws \Exception if user does not exist
     * @param string $userId User id of the user
     * @param string $sessionKey Session key
     */
    private function __construct(string $userId, string $sessionKey)
    {
        // Check a user exists
        $user = User::getUserIfExists($userId);
        if ($user == null) {
            throwException(new \Exception("User Does Not Exist", 601));
        }

        $this->userId = $userId;
        $this->sessionKey = $sessionKey;
    }

    /**
     * Create a new user session
     * @param string $userId User ID of the user to create session
     * @return Session Created Session Object
     * @throws \Exception if user does not exist
     */
    public static function createSession(string $userId): Session
    {
        $ipAddress = Utils::getClientIP();
        $userAgent = UserAgent::fromCurrentUserAgent();
        $sessionKey = Session::getEncryptedSessionKey($userId, $userAgent, $ipAddress);

        $primaryKey = array(
            'user' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent->getId());
        $record = array(
            'created' => DB::sqleval("NOW()"),
            'expires' => DB::sqleval("ADDDATE(NOW(), " . USER_EXPIRATION_DAYS . ")"),
            'session_key' => DB::sqleval("UNHEX('$sessionKey')")
        );

        $query = DB::queryFirstRow('SELECT HEX(session_key) FROM sessions WHERE user=%s AND ip_address=%s AND user_agent=%i',
            $userId, $ipAddress, $userAgent->getId());
        if ($query != null) {
            // Exists: Get existing data - Must Not Update since old session keys will become invalid
            $sessionKey = $query['HEX(session_key)'];
        } else {
            // Does Not Exist: Insert session
            DB::insert('sessions', array_merge($primaryKey, $record));
        }

        return new Session($userId, $sessionKey);
    }

    /**
     * Generate a session key using a user-defined salt
     * @param string $userId User id of the user to generate session key
     * @param UserAgent $userAgent User agent of the session
     * @param string $ip IP of the session
     * @return string Generated session key
     */
    private static function getEncryptedSessionKey(string $userId, UserAgent $userAgent, string $ip): string
    {
        return sha1(Session::generateSalt() . time() . $userId . $userAgent . $ip);
    }

    /**
     * Set salts for session key
     */
    private static function generateSalt(): string
    {
        return Utils::generateRandomString(SALT_LENGTH);
    }

    /**
     * Resumes a user session
     * @param string $userId User Id to resume session
     * @param string $sessionKey Session Key of the session to resume
     * @return Session|null Created session(null if session key is invalid)
     * @throws \Exception if user does not exist
     */
    public static function resumeSession(string $userId, string $sessionKey): ?Session
    {
        $ipAddress = Utils::getClientIP();
        $userAgent = UserAgent::fromCurrentUserAgent();

        $query = DB::queryFirstRow('SELECT session_key FROM sessions ' .
            'WHERE user = %s AND ip_address = %s AND user_agent = %i AND ' .
            'session_key = UNHEX(%s) AND expires > NOW() ',
            $userId, $ipAddress, $userAgent->getId(), $sessionKey);

        // Return null if session didn't exist
        if ($query == null) {
            return null;
        }

        return new Session($userId, $sessionKey);
    }

    public static function closeAllSessionsOfUser($userId)
    {
        DB::delete('sessions', "user=%s", $userId);
    }

    /**
     * Close a user session
     */
    public function closeSession()
    {
        Session::closeSessionWithContext($this->getUserId(), $this->getSessionKey());
    }

    public static function closeSessionWithContext(string $id, string $sessionKey)
    {
        DB::delete('sessions', "user = %s AND session_key = UNHEX(%s)", $id, $sessionKey);
    }

    private function getUserId(): string
    {
        return $this->userId;
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }
}