<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Json;
/**
 * Get local enviroment params
 */

$filters = [
    'db', 'db.host', 'db.name', 'db.username', 'db.password',
    'host', 'host.info', 'host.baseUrl',
    'git', 'git.cmd',
    'hg', 'hg.cmd',
    'emailFrom',
    'smtp', 'smtp.host', 'smtp.port', 'smtp.username', 'smtp.password', 'smtp.encryption',
];

return ArrayHelper::filter(Json::decode(
    file_get_contents(dirname(dirname(__DIR__)) . '/conf.d/parameters.json')
), $filters);
