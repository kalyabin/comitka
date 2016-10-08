<?php
namespace app\widgets;

use user\models\User;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Retreive contributor avatar if it exists.
 */
class ContributorAvatar extends Widget
{
    /**
     * @var ContributorInterface|null Contributor model or null if non-registered contributor avatar
     */
    public $contributor;

    /**
     * @var string Avatar size: small, middle, normal
     */
    public $size = 'middle';

    /**
     * @var boolean Show as block
     */
    public $asBlock = false;

    /**
     * Run widget
     *
     * @return string
     */
    public function run()
    {
        $classes = [
            'avatar', 'avatar-' . $this->size,
        ];

        $userAvatar = '';

        if ($this->contributor instanceof \app\models\ContributorInterface) {
            $avatarUrl = $this->contributor->getAvatarUrl();
            $userAvatar = $avatarUrl ? Html::img($avatarUrl) : '';
        }

        if ($this->asBlock) {
            $classes[] = 'avatar-block';

            return Html::tag('div', $userAvatar, ['class' => $classes]);
        }

        return Html::tag('span', $userAvatar, ['class' => $classes]);
    }
}
