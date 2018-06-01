<?php
/**
 * User: malkov alexey
 * Date: 01.06.2018
 * Time: 13:31
 */

return [
    'class' => 'yii\web\UrlManager',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        '' => 'site/index',  //формирует при запорсе корня site/index, и наоборот, ппри формировании ссылки к корню - пустую строку.
        '<_a:login|logout>' => 'site/<_a>',  //чотбы убрать из запроса к логин и логаут путь site
    ],
];