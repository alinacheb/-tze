<?php
// обновляет значение свойства () для всех товаров, всех каталогов

if(!defined('AGENT_PROCESS')) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(__DIR__)));

    $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

    define('NO_KEEP_STATISTIC', true);
    define('NOT_CHECK_PERMISSIONS', true);
    define('BX_NO_ACCELERATOR_RESET', true);
    define('PUBLIC_AJAX_MODE', true);
    define('NO_CUSTOM_REDIRECT', true); // редиректы на поддомены

    define('SITE_ID', 's1');

    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
}

/*********************/
require($_SERVER['DOCUMENT_ROOT'] . '/tz/eksmo/classes/Create.php');
mb_parse_str($argv[1], $params);
$count = intval($params['count']);
if($_REQUEST['count']){
    $count = intval($_REQUEST['count']);
}
if($count > 0){
    $ob = new Tz\Eksmo\Create();
    $ob->createEntities();
    $ob->full($count);
}

/*********************/


// /usr/bin/php -f /home/bitrix/ext_www/site/tz/eksmo/cron/create.php count=100 && echo 'create OK'

if(!defined('AGENT_PROCESS')) {
    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
}
