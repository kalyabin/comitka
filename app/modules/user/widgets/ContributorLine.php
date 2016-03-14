<?php
namespace user\widgets;

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
     * Render widget.
     *
     * @return string
     */
    public function run()
    {
        $ret = Html::encode($this->contributorName);

        if ($this->contributorEmail) {
            $ret .= ' &lt;' . Html::encode($this->contributorEmail) . '&gt;';
        }

        return $ret;
    }
}
