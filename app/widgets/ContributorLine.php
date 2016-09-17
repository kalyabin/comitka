<?php
namespace app\widgets;

use app\models\ContributorInterface;
use app\models\UnregisteredContributor;
use app\widgets\ContributorAvatar;
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
     * @var ContributorInterface Contributor model
     */
    public $contributor;

    /**
     * @var string Avatar size: small, middle, normal
     */
    public $avatarSize = 'middle';

    /**
     * @var boolean Show user's link
     */
    public $useLink = true;

    /**
     * @var boolean Show email flag
     */
    public $showEmail = false;

    /**
     * Render widget as registered contributor
     *
     * @return string
     */
    protected function renderRegistered()
    {
        $avatar =  ContributorAvatar::widget([
            'contributor' => $this->contributor,
            'size' => $this->avatarSize,
        ]);
        $text = $this->renderSimple(false);

        return $this->useLink ? $avatar . Html::a($text, '#', [
            'data' => [
                'user-id' => $this->contributor->getContributorId(),
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
            $ret .= ContributorAvatar::widget([
                'size' => $this->avatarSize,
            ]);
        }

        $ret .= Html::encode($this->contributor->getContributorName());

        if ($this->showEmail && ($email = $this->contributor->getContributorEmail()) !== false) {
            $ret .= ' &lt;' . Html::encode($email) . '&gt;';
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
        if (!($this->contributor instanceof UnregisteredContributor)) {
            return $this->renderRegistered();
        }

        return $this->renderSimple() . ' ' . Html::tag('span', Yii::t('user', 'Not registered'), [
            'class' => 'label label-danger'
        ]);
    }
}
