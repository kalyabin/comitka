<?php

namespace widgets;

use tests\codeception\fixtures\UserFixture;
use UnitWebTestCase;
use user\widgets\ProfileMenu;
use user\models\User;

/**
 * Test profile menu widget
 */
class ProfileMenuTest extends UnitWebTestCase
{
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

    /**
     * Test widget
     */
    public function testMe()
    {
        /* @var $user User */
        $user = $this->getModule('Yii2')->grabFixture('users', 'activeUser1');

        $expectedString = '<ul id="w0" class="nav">
            <li><a href="/profile">Common settings</a></li>
            <li><a href="/profile/vcs-binginds">VCS bindings</a></li>
        </ul>';

        $expectedDom = new \DOMDocument();
        $expectedDom->loadXML($expectedString);

        $result = ProfileMenu::widget([
            'authUser' => $user,
        ]);

        $resultDom = new \DOMDocument();
        $resultDom->loadXML($result);

        $this->assertEqualXMLStructure($expectedDom->firstChild, $resultDom->firstChild, true);
    }

    /**
     * Test widget exception
     *
     * @expectedException yii\base\InvalidParamException
     */
    public function testExceptionThrow()
    {
        ProfileMenu::widget();
    }
}
