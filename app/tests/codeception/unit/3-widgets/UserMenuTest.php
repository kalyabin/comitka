<?php

namespace widgets;

use tests\codeception\fixtures\UserFixture;
use UnitWebTestCase;
use user\widgets\UserMenu;
use user\models\User;

/**
 * Test user menu widget
 */
class UserMenuTest extends UnitWebTestCase
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
        $userId = $user->id;

        $expectedString = '<ul id="w1" class="nav">
            <li><a href="/users/' . $userId . '/update">Common settings</a></li>
            <li><a href="/users/' . $userId . '/vcs-bindings">VCS bindings</a></li>
        </ul>';

        $expectedDom = new \DOMDocument();
        $expectedDom->loadXML($expectedString);

        $result = UserMenu::widget([
            'model' => $user,
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
        UserMenu::widget();
    }
}
