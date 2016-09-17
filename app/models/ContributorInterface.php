<?php
namespace app\models;

/**
 * Base contributor interface: user name and e-mail.
 */
interface ContributorInterface
{
    /**
     * Retrieve contributor id
     *
     * @return string
     */
    public function getContributorId();

    /**
     * Retrieve contributor user name
     *
     * @return string
     */
    public function getContributorName();

    /**
     * Retrieve contributor e-mail
     *
     * @return string
     */
    public function getContributorEmail();

    /**
     * Returns user avatar URL
     *
     * @return string
     */
    public function getAvatarUrl();

    /**
     * Returns contributor reviewer identifier
     *
     * @return string
     */
    public function getDefaultViewerId();
}
