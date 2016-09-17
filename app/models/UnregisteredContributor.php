<?php
namespace app\models;

use yii\base\Object;

/**
 * Unregistered contributor model
 */
class UnregisteredContributor extends Object implements ContributorInterface
{
    /**
     * @var string Contributor name
     */
    public $contributorName;

    /**
     * @var string Contributor e-mail
     */
    public $contributorEmail;

    /**
     * Unregistered contributor has no identifiers
     *
     * @return null
     */
    public function getContributorId()
    {
        // has no identifier
        return null;
    }

    /**
     * Contributor e-mail
     *
     * @return string
     */
    public function getContributorEmail()
    {
        return $this->contributorEmail;
    }

    /**
     * Contributor user name
     *
     * @return string
     */
    public function getContributorName()
    {
        return $this->contributorName;
    }

    /**
     * Get avatar URL
     *
     * @return null
     */
    public function getAvatarUrl()
    {
        return null;
    }

    /**
     * Get default reviewer id
     *
     * @return null
     */
    public function getDefaultViewerId()
    {
        return null;
    }
}
