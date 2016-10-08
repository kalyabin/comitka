<?php

namespace widgets;

use app\components\ContributorApi;
use app\models\UnregisteredContributor;
use app\widgets\ContributorAvatar;
use DOMDocument;
use tests\codeception\fixtures\UserFixture;
use UnitWebTestCase;
use user\models\User;
use Yii;

/**
 * Test contributor avatar widget
 */
class ContributorAvatarTest extends UnitWebTestCase
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
        $contributor = $this->contributorApi->getContributor('git', 'Test contributor');
        $this->assertInstanceOf(UnregisteredContributor::className(), $contributor);

        $expectedString = '<span class="avatar avatar-test-avatar-size"></span>';
        $expectedDom = new DOMDocument();
        $expectedDom->loadXML($expectedString);

        $result = ContributorAvatar::widget([
            'contributor' => $contributor,
            'size' => 'test-avatar-size',
            'asBlock' => false,
        ]);
        $resultDom = new DOMDocument();
        $resultDom->loadXML($result);

        $this->assertEqualXMLStructure($expectedDom->firstChild, $resultDom->firstChild, true);
    }

    /**
     * Test known contributor without avatar
     */
    public function testWithKnownContributorWithoutAvatar()
    {
        /* @var $contributor User */
        $contributor = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');

        $expectedString = '<span class="avatar avatar-test-avatar-size"></span>';
        $expectedDom = new DOMDocument();
        $expectedDom->loadXML($expectedString);

        $result = ContributorAvatar::widget([
            'contributor' => $contributor,
            'size' => 'test-avatar-size',
            'asBlock' => false,
        ]);
        $resultDom = new DOMDocument();
        $resultDom->loadXML($result);

        $this->assertEqualXMLStructure($expectedDom->firstChild, $resultDom->firstChild, true);
    }

    /**
     * Test known contributor with avatar
     */
    public function testWithKnownContributorWithAvatar()
    {
        /* @var $contributor User */
        $contributor = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');
        $contributor->avatar = 'test.jpg';
        $contributor->getAvatarUrl();

        $expectedString = '<span class="avatar avatar-test-avatar-size">'
                . '<img src="' . User::AVATAR_URL . 'test.jpg" alt="">'
                . '</span>';

        $result = ContributorAvatar::widget([
            'contributor' => $contributor,
            'size' => 'test-avatar-size',
            'asBlock' => false,
        ]);

        $this->assertEquals($expectedString, $result);
    }
}
