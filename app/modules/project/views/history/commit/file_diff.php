<?php

use VcsCommon\BaseCommit;
use VcsCommon\BaseDiff;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\web\View;

/* @var $this View */
/* @var $diffs BaseDiff[] */
/* @var $path string */
/* @var $commit BaseCommit */

$contributor = $commit->contributorName;
if ($commit->contributorEmail) {
    $contributor .= ' <' . $commit->contributorEmail . '>';
}
$id = md5($commit->getId() . $path);
?>

<p class="diff-description">
    <strong><?= Yii::t('project', 'Author') ?>:</strong> <?= Html::encode($contributor) ?><br />
</p>

<div id="<?= $id ?>" class="diff-inner">
    <table id="<?= $id ?>" class="diff-table">
        <?php foreach ($diffs as $diff):?>
            <?php foreach ($diff->getLines() as $description => $group):?>
                <?php
                $aNum = (int) $group['beginA'];
                $bNum = (int) $group['beginB'];
                ?>
                <tr>
                    <td colspan="3" class="cell-description"><?= Html::encode($description) ?></td>
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
                        <td width="10" class="cell-b-num"><?= $b ? $b : '-' ?></td>
                        <td class="cell-content"><?= $line ?></td>
                    </tr>
                    <?php
                    $aNum += is_null($a) ? 0 : 1;
                    $bNum += is_null($b) ? 0 : 1;
                    ?>
                <?php endforeach;?>
            <?php endforeach;?>
        <?php endforeach;?>
    </table>
</div>
