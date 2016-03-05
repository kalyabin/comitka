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

$id = md5($commit->getId() . $path);
?>

<table id="<?= $id ?>">
    <?php foreach ($diffs as $diff):?>
        <?php foreach ($diff->getLines() as $description => $group):?>
            <?php
            $aNum = (int) $group['beginA'];
            $bNum = (int) $group['beginB'];
            ?>
            <tr>
                <td colspan="4"><?= Html::encode($description) ?></td>
            </tr>
            <?php foreach ($group['lines'] as $line):?>
                <?php
                $a = StringHelper::startsWith($line, '-') || StringHelper::startsWith($line, ' ') || strlen($line) === 0 ? $aNum : null;
                $b = StringHelper::startsWith($line, '+') || StringHelper::startsWith($line, ' ') || strlen($line) === 0 ? $bNum : null;

                $line = str_replace(' ', '&nbsp;', Html::encode(substr($line, 1)));

                /**
                 * @todo fix highlighting and do something with tabs
                 */
                ?>
                <tr>
                    <td width="10"><?= $a ?></td>
                    <td><code class="php"><?= is_null($a) ? '' : $line ?></code></td>
                    <td width="10"><?= $b ?></td>
                    <td><code class="php"><?= is_null($b) ? '' : $line ?></code></td>
                </tr>
                <?php
                $aNum += is_null($a) ? 0 : 1;
                $bNum += is_null($b) ? 0 : 1;
                ?>
            <?php endforeach;?>
        <?php endforeach;?>
    <?php endforeach;?>
</table>

<script type="text/javascript">
    $(document).ready(function() {
        $('#<?= $id ?> code').each(function(i, block) {
            hljs.highlightBlock(block);
        });
    });
</script>
