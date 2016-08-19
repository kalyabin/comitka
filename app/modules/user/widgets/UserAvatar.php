<?php
namespace user\widgets;

use user\models\User;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Retreive user avatar if it exists.
 */
class UserAvatar extends Widget
{
    /**
     * @var User|null User model or null if non-registered user avatar
     */
    public $user;

    /**
     * @var string Avatar size: small, middle, normal
     */
    public $size = 'middle';

    /**
     * @var boolean Show as block
     */
    public $asBlock = false;

    /**
     *
     */
    public function run()
    {
        $classes = [
            'avatar', 'avatar-' . $this->size,
        ];

        $userAvatar = '';

        if ($this->user instanceof User && ($avatarUrl = $this->user->getAvatarUrl()) !== false) {
            $userAvatar =  Html::img($avatarUrl);
        }

        if ($this->asBlock) {
            $classes[] = 'avatar-block';

            return Html::tag('div', $userAvatar, ['class' => $classes]);
        }

        return Html::tag('span', $userAvatar, ['class' => $classes]);
    }
}
