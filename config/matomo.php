<?php

return [
    'proxy_port' => env('MATOMO_PROXY_PORT', 9502),
    'site_id'    => env('MATOMO_SITE_ID'), //APP事件统计的site_id
    'web_id'     => env('MATOMO_WEB_ID'), //Web事件统计的site_id
    'app_url'    => env('MATOMO_APP_URL'), //APP事件统计查看的matomo url
    'web_url'    => env('MATOMO_WEB_URL'), //Web事件统计查看的matomo url

];

// 答赚示范(区分了APP前端 和Web的track 实例，方便不同matomo实例的配置)，也可以混合一起
// MATOMO_SITE_ID=1
// MATOMO_WEB_ID=2
// MATOMO_APP_URL=http://matomo.haxibiao.com
// MATOMO_WEB_URL=http://matomo.datizhuanqian.com
