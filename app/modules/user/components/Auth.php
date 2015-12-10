<?php
namespace user\components;

use user\models\User;
use yii\web\IdentityInterface;
use yii\web\User as WebUser;

/**
 * Redeclare WebUser.
 *
 * Before login user, checks it's status.
 */
class Auth extends WebUser
{
    /**
     * Before user sign in. Returns true if user can sign in.
     *
     * @param IdentityInterface $identity the user identity information
     * @param boolean $cookieBased whether the login is cookie-based
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * If 0, it means login till the user closes the browser or the session is manually destroyed.
     * @return boolean whether the user should continue to be logged in
     */
    public function beforeLogin($identity, $cookieBased, $duration)
    {
        /* @var $identity User */
        if ($identity instanceof User && !$identity->canSignIn()) {
            return false;
        }
        return parent::beforeLogin($identity, $cookieBased, $duration);
    }

    /**
     * Logout user if it's blocked
     */
    public function renewAuthStatus()
    {
        parent::renewAuthStatus();
        /* @var $identity User */
        if ($this->identity instanceof User && !$this->identity->canSignIn() && !$this->isGuest) {
            $this->logout();
        }
    }
}
