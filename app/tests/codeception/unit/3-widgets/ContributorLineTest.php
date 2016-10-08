<?php

namespace widgets;

use app\components\ContributorApi;
use app\models\UnregisteredContributor;
use app\widgets\ContributorLine;
use tests\codeception\fixtures\UserFixture;
use UnitWebTestCase;
use user\models\User;
use Yii;

/**
 * Test contributor line widget
 */
class ContributorLineTest extends UnitWebTestCase
{
    /**
     * @var ContributorApi
     */
    protected $contributorApi;

    /**
     * Fixtures
     *
     * @return array
     */
    public function _fixtures()
    {
        return [
            'users' => UserFixture::className(),
        ];
    }

    protected function setUp()
    {
        parent::setUp();
        $this->contributorApi = Yii::$app->contributors;
    }

    /**
     * Test unknown contributor avatar
     */
    public function testWithUnknownContributor()
    {
        $contributor = $this->contributorApi->getContributor('git', 'Test contributor', 'test@domain.ltd');
        $this->assertInstanceOf(UnregisteredContributor::className(), $contributor);

        $result = ContributorLine::widget([
            'contributor' => $contributor,
            'avatarSize' => 'test-avatar-size',
            'showEmail' => true
        ]);

        $this->assertContains('<span class="avatar avatar-test-avatar-size"></span>', $result);
        $this->assertContains('Test contributor &lt;test@domain.ltd&gt;', $result);
        $this->assertContains('Not registered', $result);

        $result = ContributorLine::widget([
            'contributor' => $contributor,
            'avatarSize' => 'test-avatar-size',
            'showEmail' => false
        ]);

        $this->assertContains('<span class="avatar avatar-test-avatar-size"></span>', $result);
        $this->assertContains('Test contributor', $result);
        $this->assertContains('Not registered', $result);
        $this->assertNotContains('&lt;test@domain.ltd&gt;', $result);
    }

    /**
     * Test widget with known contributor
     */
    public function testWithKnownContributor()
    {
        /* @var $contributor User */
        $contributor = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');

        $result = ContributorLine::widget([
            'contributor' => $contributor,
            'avatarSize' => 'test-avatar-size',
            'showEmail' => false,
            'useLink' => false,
        ]);

        $this->assertContains('<span class="avatar avatar-test-avatar-size"></span>', $result);
        $this->assertContains($contributor->name, $result);
        $this->assertNotContains('Not registered', $result);

        $result = ContributorLine::widget([
            'contributor' => $contributor,
            'avatarSize' => 'test-avatar-size',
            'showEmail' => false,
            'useLink' => true,
        ]);
        $this->assertContains('<span class="avatar avatar-test-avatar-size"></span>', $result);
        $this->assertContains($contributor->name, $result);
        $this->assertNotContains('Not registered', $result);
        $this->assertContains('<a href="#" data-user-id="' . $contributor->id . '" role="user-popup-button">', $result);
    }
}
