<?php

namespace api;

use app\components\ContributorApi;
use app\models\ContributorInterface;
use app\models\UnregisteredContributor;
use tests\codeception\fixtures\UserAccountFixture;
use tests\codeception\fixtures\UserFixture;
use UnitTestCase;
use UnitTester;
use user\models\User;
use user\models\UserAccount;
use Yii;

/**
 * Tests contributor helpers
 */
class ContributorApiTest extends UnitTestCase
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var ContributorApi
     */
    protected $contributorApi;

    /**
     * Tests fixtures
     */
    public function _fixtures()
    {
        return [
            'users' => UserFixture::className(),
            'userAccounts' => UserAccountFixture::className(),
        ];
    }

    public function setUp()
    {
        parent::setUp();
        $this->contributorApi = Yii::$app->contributors;
    }

    /**
     * Test get contributor by id
     */
    public function testGetContributorById()
    {
        /* @var $user1 User */
        $user1 = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');

        $contributor = $this->contributorApi->getContributorById($user1->id);
        $this->assertInstanceOf(ContributorInterface::class, $contributor);
        $this->assertEquals($contributor->getContributorId(), $user1->id);
    }

    /**
     * Test get contributor
     */
    public function testGetContributor()
    {
        /* @var $user1 User */
        $user1 = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');
        /* @var $user2 User */
        $user2 = $this->getModule('Yii2')->grabFixture('users', 'activeUser2');

        foreach ($user1->accounts as $account) {
            $contributor = $this->contributorApi->getContributor($account->type, $account->username);
            $this->assertEquals($contributor->getId(), $user1->id);
            $this->assertEquals($contributor->getContributorEmail(), $user1->email);
        }

        foreach ($user2->accounts as $account) {
            $contributor = $this->contributorApi->getContributor($account->type, $account->username);
            $this->assertInstanceOf(ContributorInterface::class, $contributor);
            $this->assertEquals($contributor->getId(), $user2->id);
            $this->assertEquals($contributor->getContributorEmail(), $user2->email);
        }

        // test unregistered contributor
        foreach ([UserAccount::TYPE_GIT, UserAccount::TYPE_HG, UserAccount::TYPE_SVN] as $type) {
            $username = 'unregistered user name' . $type;
            $useremail = 'unregistered user email' . $type;
            $contributor = $this->contributorApi->getContributor($type, $username, $useremail);
            $this->assertInstanceOf(UnregisteredContributor::className(), $contributor);
            $this->assertEquals($username, $contributor->getContributorName());
            $this->assertEquals($useremail, $contributor->getContributorEmail());
            $this->assertNull($contributor->getDefaultViewerId());
            $this->assertNull($contributor->getContributorId());
            $this->assertNull($contributor->getAvatarUrl());
        }
    }
}
