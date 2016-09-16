<?php
namespace user\widgets;

use user\models\User;
use user\UserModule;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Represents contributor line with e-mail and name.
 *
 * Returns line like this:
 * ```html
 * My name <myemail@domen.ltd>
 * ```
 */
class ContributorLine extends Widget
{
    /**
     * @var string
     */
    public $contributorName;

    /**
     * @var string
     */
    public $contributorEmail;

    /**
     * @var string VCS type by UserAccount::TYPE_* constants
     */
    public $vcsType;

    /**
     * @var string Avatar size: small, middle, normal
     */
    public $avatarSize = 'middle';

    /**
     * @var boolean Show user's link
     */
    public $useLink = true;

    /**
     * User model if user exists in a system.
     *
     * Or user model if already used in memory.
     *
     * @var User
     */
    public $user;

    /**
     * Retreive user model if it exists at database
     */
    public function init()
    {
        parent::init();

        // get user model
        /* @var $api UserModule */
        $api = Yii::$app->getModule('user');

        if (!($this->user instanceof User)) {
            $this->user = $api->getUserByUsername($this->vcsType, $this->contributorName, $this->contributorEmail);
        }
    }

    /**
     * Render widget with user model
     *
     * @return string
     */
    protected function renderWithUserModel()
    {
        $avatar =  UserAvatar::widget([
            'user' => $this->user,
            'size' => $this->avatarSize,
        ]);
        $text = $this->renderSimple(false);

        return $this->useLink ? $avatar . Html::a($text, '#', [
            'data' => [
                'user-id' => $this->user->id,
            ],
            'role' => 'user-popup-button',
        ]) : $avatar . $text;
    }

    /**
     * Render widget without user model
     *
     * @param boolean $withAvatar Render avatar placeholder
     *
     * @return string
     */
    protected function renderSimple($withAvatar = true)
    {
        $ret = '';

        if ($withAvatar) {
            $ret .= UserAvatar::widget([
                'size' => $this->avatarSize,
            ]);
        }

        $ret .= Html::encode($this->contributorName);

        if ($this->contributorEmail) {
            $ret .= ' &lt;' . Html::encode($this->contributorEmail) . '&gt;';
        }

        return $ret;
    }

    /**
     * Render widget.
     *
     * @return string
     */
    public function run()
    {
        if ($this->user instanceof User) {
            return $this->renderWithUserModel();
        }

        return $this->renderSimple() . ' ' . Html::tag('span', Yii::t('user', 'Not registered'), [
            'class' => 'label label-danger'
        ]);
    }
}
