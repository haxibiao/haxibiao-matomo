<?php

return [
    'use_swoole' => env('MATOMO_USE_SWOOLE', false), //是否借用swoole的proxy实现tcp+bulk发送
    'proxy_port' => env('MATOMO_PROXY_PORT', 9502),
    'token_auth' => env('MATOMO_TOKEN_AUTH'),

    'matomo_id'  => env('MATOMO_ID'), //后端事件统计的site_id
    'matomo_url' => env('MATOMO_URL'), //后端事件统计查看的matomo url

    'app_id'     => env('MATOMO_APP_ID', env('MATOMO_ID')), //APP事件统计的site_id 前端可以直接上报了,基本不借道后端了
    'app_url'    => env('MATOMO_APP_URL', env('MATOMO_URL')), //APP事件统计查看的matomo url

    'web_id'     => env('MATOMO_WEB_ID', env('MATOMO_ID')), //Web事件统计的site_id
    'web_url'    => env('MATOMO_WEB_URL', env('MATOMO_URL')), //Web事件统计查看的matomo url
];

// 答赚示范(区分了后端，前端 和Web),也可以混合一起配置一样即可
// MATOMO_ID=1
// MATOMO_URL=

// MATOMO_APP_ID=1
// MATOMO_APP_URL=

// MATOMO_WEB_ID=2
// MATOMO_WEB_URL=
