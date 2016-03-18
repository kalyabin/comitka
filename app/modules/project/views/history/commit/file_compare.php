<?php

use user\widgets\ContributorLine;
use VcsCommon\BaseCommit;
use VcsCommon\BaseDiff;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\web\View;

/* @var $this View */
/* @var $diffs BaseDiff[] */
/* @var $path string */
/* @var $commit BaseCommit */
?>

<p class="diff-description">
    <strong><?= Yii::t('project', 'Author') ?>:</strong>
    <?= ContributorLine::widget([
        'contributorName' => $commit->contributorName,
        'contributorEmail' => $commit->contributorEmail,
    ]) ?><br />
</p>

<table class="diff-table">
    <?php foreach ($diffs as $diff):?>
        <?php foreach ($diff->getLines() as $description => $group):?>
            <?php
            $aNum = (int) $group['beginA'];
            $bNum = (int) $group['beginB'];
            ?>
            <tr>
                <td colspan="4" class="cell-description"><?= Html::encode($description) ?></td>
            </tr>
            <?php foreach ($group['lines'] as $n => $line):?>
                <?php
                $type = StringHelper::startsWith($line, '-') ? 'del' : (StringHelper::startsWith($line, '+') ? 'new' : 'old');
                $line = Html::encode(substr($line, 1));
                $line = str_replace(
                    [
                        " ",
                        "\t"
                    ],
                    [
                        '&nbsp;',
                        '&thinsp;',
                    ],
                    $line
                );
                $a = $type === 'del' || $type === 'old' ? $aNum : null;
                $b = $type === 'new' || $type === 'old' ? $bNum : null;
                ?>
                <tr class="row-<?= $type ?><?php if ($n === count($group['lines']) - 1):?> last-line<?php endif;?>">
                    <td width="10" class="cell-a-num"><?= $a ? $a : '+' ?></td>
                    <td class="cell-content"><?= $a ? $line : '' ?></td>
                    <td width="10" class="cell-b-num"><?= $b ? $b : '-' ?></td>
                    <td class="cell-content"><?= $b ? $line : '' ?></td>
                </tr>
                <?php
                $aNum += is_null($a) ? 0 : 1;
                $bNum += is_null($b) ? 0 : 1;
                ?>
            <?php endforeach;?>
        <?php endforeach;?>
    <?php endforeach;?>
</table>
