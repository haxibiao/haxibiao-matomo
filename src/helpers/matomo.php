<?php

/**
 * 主要的web网页请求事件track到matomo
 */
function track_web($category, $action, $name = null, $value = null)
{
    $web_idSite = config('matomo.web_id');
    $web_url    = config('matomo.web_url');
    $tracker    = new \MatomoTracker($web_idSite, $web_url);
    $tracker->doTrackEvent($category, $action, $name, $value);
}

/**
 * 主要的后端事件track到matomo
 */
function app_track_event($category, $action, $name = false, $value = false)
{
    $event['category'] = $category;
    $event['action']   = $action;
    $event['name']     = $name;

    //避免进入的value有对象，不是String会异常
    $event['value'] = $value instanceof String ? $value : false;

    $event = wrapMatomoEventData($event);

    if (config('matomo.use_swoole')) {
        //TCP发送事件数据
        sendMatomoTcpEvent($event);
    } else {

        //直接发送，兼容matomo 3.13.6
        $tracker = new \MatomoTracker(config('matomo.matomo_id'), config('matomo.matomo_url'));
        $tracker->setCustomVariable(1, "服务器", gethostname(), "visit");
        $user_type = '游客';
        if ($user = checkUser()) {
            $user_type = $user->create_at > today() ? '老用户' : '新用户';
        }
        $tracker->setCustomVariable(2, "用户", $user_type, "visit");
        $tracker->setCustomVariable(3, "机型", request()->header('brand'), "visit");

        $tracker->setUserId(getUniqueUserId());
        $tracker->setIp(getIp());
        $tracker->setTokenAuth("64b4543a0f6b01cbfa9eb7ed5dde840b");
        $tracker->setRequestTimeout(1); //最多卡1s

        //设备系统
        $tracker->setCustomTrackingParameter('dimension1', $event['dimension1']);
        //安装来源
        $tracker->setCustomTrackingParameter('dimension2', $event['dimension2']);
        //APP版本
        $tracker->setCustomTrackingParameter('dimension3', $event['dimension3']);
        //APP build
        $tracker->setCustomTrackingParameter('dimension4', $event['dimension4']);
        //新老用户分类
        $tracker->setCustomTrackingParameter('dimension5', $event['dimension5']);
        //用户机型
        $tracker->setCustomTrackingParameter('dimension6', $event['dimension6']);

        try {
            //send
            $tracker->doTrackEvent($category, $action, $name, $value);
        } catch (\Throwable $ex) {
            return false;
        }
    }
}

function wrapMatomoEventData($event)
{
    $event['user_id'] = getUniqueUserId();
    $event['ip']      = getIp();

    //传给自定义变量 服务器
    $event['server'] = gethostname();

    $event['dimension1'] = getOsSystemVersion(); //设备系统带版本
    $event['dimension2'] = get_referer(); //下载渠道
    $event['dimension3'] = getAppVersion(); //版本
    $event['dimension4'] = getAppVersion() . "(build" . getAppBuild() . ")"; //热更新
    $event['dimension5'] = getUserCategoryTag(); //新老用户分类
    $event['dimension6'] = getDeviceBrand(); //用户机型品牌

    $event['siteId'] = config('matomo.matomo_id');
    return $event;
}

function sendMatomoTcpEvent(array $event)
{
    $event['cdt'] = time();
    try {
        $client = new \swoole_client(SWOOLE_SOCK_TCP); //同步阻塞？？
        //默认0.1秒就timeout, 所以直接丢给本地matomo:server
        $port = config('matomo.proxy_port');
        $client->connect('127.0.0.1', $port) or die("swoole connect failed\n");
        $client->set([
            'open_length_check'     => true,
            'package_length_type'   => 'n',
            'package_length_offset' => 0,
            'package_body_offset'   => 2,
        ]);
        $client->send(tcp_pack(json_encode($event)));
    } catch (\Throwable $ex) {
        return false;
    }
    return true;
}

function tcp_pack(string $data): string
{
    return pack('n', strlen($data)) . $data;
}
function tcp_unpack(string $data): string
{
    return substr($data, 2, unpack('n', substr($data, 0, 2), 0)[1]);
}

//FIXME: 开始主要用这个埋点，能快速区别新老用户的事件趋势和分布， 重构答赚里的这个不同的名字...
function app_track_user_event($action, $name = false, $value = 1)
{
    $category = getUserCategoryTag();
    app_track_event($category, $action, $name, $value);
}

function app_track_reward_video($action, $name = false, $value = false)
{
    app_track_event("激励视频", $action, $name, $value);
}

function app_track_user($action, $name = false, $value = false)
{
    app_track_event("用户行为", $action, $name, $value);
}

function app_track_question($action, $name = false, $value = false)
{
    app_track_event("答题出题", $action, $name, $value);
}

function app_track_task($action, $name = false, $value = false)
{
    app_track_event("任务", $action, $name, $value);
}

function getUniqueUserId()
{
    try {
        return getUserId();
    } catch (\Exception $ex) {
        return getIp();
    }
}

function app_track_app_download()
{
    app_track_user('App下载', 'app_download');
}

function app_track_send_message()
{
    app_track_user('发送消息');
}
