<?php

/**
 * 主要的web网页请求事件track到matomo
 */
function track_web($category, $action = null, $name = null, $value = null)
{
    $web_idSite = config('matomo.web_id');
    $web_url    = config('matomo.web_url');
    $tracker    = new \MatomoTracker($web_idSite, $web_url);
    $tracker->doTrackEvent($category, $action ?? $category, $name, $value);
}

/**
 * 主要的后端事件track到matomo
 */
function app_track_event($category, $action = null, $name = false, $value = false)
{
    $event['category'] = $category;
    $event['action']   = $action ?? $category;
    $event['name']     = $name;
    //避免进入的value有对象，不是String会异常
    $event['value'] = $value instanceof String ? $value : false;
    $event['cdt']   = now()->timestamp;

    //包装必要的事件参数进入数组
    $event = wrapMatomoEventData($event);

    if (config('matomo.use_swoole')) {
        //TCP发送事件数据
        sendMatomoTcpEvent($event);
    } else {
        //直接发送，兼容matomo 3.13.6
        $tracker = new \MatomoTracker(config('matomo.matomo_id'), config('matomo.matomo_url'));
        //用户机型
        // $tracker->setCustomVariable(1, '机型', $event['dimension5'], 'visit');

        $tracker->setUserId(getUniqueUserId());
        $tracker->setIp(getIp());
        $tracker->setTokenAuth(config('matomo.token_auth'));
        $tracker->setRequestTimeout(1); //最多卡1s
        $tracker->setForceVisitDateTime(time());

        $tracker->setCustomVariable(1, '系统', $event['dimension1'], 'visit');
        $tracker->setCustomVariable(2, '机型+来源', $event['dimension5'] . "-" . $event['dimension2'], 'visit');
        $tracker->setCustomVariable(3, '版本', $event['dimension3'], 'visit');
        $tracker->setCustomVariable(4, '用户', $event['dimension4'], 'visit');
        $tracker->setCustomVariable(5, "服务器", gethostname(), "visit");

        try {
            //直接发送到matomo
            $tracker->doTrackEvent($category, $action, $name, $value);
            // $url = $tracker->getUrlTrackEvent($category, $action, $name, $value);
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
    $event['cdt']    = time();

    $event['dimension1'] = getOsSystemVersion(); //设备系统带版本
    $event['dimension2'] = get_referer(); //下载渠道
    $event['dimension3'] = getAppVersion(); //版本
    $event['dimension4'] = getUserCategoryTag(); //新老用户分类
    $event['dimension5'] = getDeviceBrand(); //用户机型品牌

    $event['siteId'] = config('matomo.matomo_id');
    return $event;
}

function sendMatomoTcpEvent(array $event)
{
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
    //区分新老用户在事件分类不好算维度，还是靠真的维度去区分吧
    // $category = getUserCategoryTag();
    $category = $action;
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

/**
 * 用户分类（匿名,新,未提现,老）
 */
function getUserCategoryTag()
{
    $user = getUser(false);
    if (blank($user)) {
        return "匿名用户";
    }
    if ($user->created_at > now()->subDay()) {
        return '新用户';
    }
    //FIXME: 需要用户表维护最后提现时间字段withdraw_at
    if (isset($user->withdraw_at)) {
        return "未提现用户";
    }
    return '老用户';
}
