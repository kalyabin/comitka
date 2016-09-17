<?php
namespace app\components;

use app\models\ContributorInterface;
use app\models\UnregisteredContributor;
use user\models\User;
use user\models\UserAccount;
use yii\base\Component;
use yii\db\ActiveQuery;

/**
 * Contributors manager API
 */
class ContributorApi extends Component
{
    /**
     * Get contributor by id
     *
     * @param integer $contributorId Contributor identifier
     *
     * @return ContributorInterface
     */
    public function getContributorById($contributorId)
    {
        static $cached = [];

        if (!isset($cached[$contributorId])) {
            $cached[$contributorId] = User::find()->andWhere(['id' => $contributorId])->one();
        }

        return $cached[$contributorId];
    }

    /**
     * Retrieve contributor model by vcs type, contributorName, contributorEmail.
     *
     * If contributor registered at the system, returns it, if else - returns UnregisteredContributor model.
     *
     * @see UserAccount
     * @see User
     *
     * @param string $vcsType VCS type (git, hg, etc.)
     * @param string $contributorName VCS contributor name (e.g. commiter name)
     * @param string $contributorEmail VCS contributor e-mail (e.g. commiter e-mail, if exists)
     *
     * @return ContributorInterface Returns registered user, or UnregisteredContributor model.
     */
    public function getContributor($vcsType, $contributorName, $contributorEmail = null)
    {
        /* @var $cached ContributorInterface[] */
        static $cached = [];

        $cacheKey = $vcsType . '_' . $contributorName . '_' . $contributorEmail;

        if (!isset($cached[$cacheKey])) {
            /* @var $res ActiveQuery */
            $res = User::find()
                ->joinWith('accounts')
                ->orWhere([
                    UserAccount::tableName() . '.username' => $contributorName,
                    UserAccount::tableName() . '.type' => $vcsType
                ]);
            if (!empty($contributorEmail)) {
                $res->orWhere([
                    User::tableName() . '.email' => $contributorEmail
                ]);
            }

            $res->groupBy(User::tableName() . '.id');

            $cached[$cacheKey] = $res->one();

            if (!$cached[$cacheKey]) {
                // contributor not found
                // set as unregistered
                $cached[$cacheKey] = new UnregisteredContributor([
                    'contributorName' => $contributorName,
                    'contributorEmail' => $contributorEmail,
                ]);
            }
        }

        return $cached[$cacheKey];
    }
}
