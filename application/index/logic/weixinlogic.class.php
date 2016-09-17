<?php
/**
 * Created by PhpStorm.
 * User: chanceJaw
 * Date: 16/1/28
 * Time: 下午4:28
 */

namespace app\index\logic;

use Common\Lib\Org\Util\Http;

class weixinlogic
{
    public static function is_weixin()
    {
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
        }
        return false;
    }

    /**
     * 登录回调地址
     * 通过code获取openid
     */
    public static function oauth2()
    {
        //require_once getcwd() .'/Application/Front/Model/WxPay.Config.php';

        $code = $_GET['code'];
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".\WxPayConfig::APPID."&secret=".\WxPayConfig::APPSECRET."&code=".$code."&grant_type=authorization_code";
        $result = http::get($url ,30);

        //print_r($result);exit;
        $result = json_decode($result, true);
        cookie('wx-openid', $result['openid'], time() + 7200);
//        $result['unionid'];
        if (empty(cookie('head_url'))) {
            $token = self::getAccessToken(true);
            $url = sprintf('https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN',$token, $result['openid']);
            $result = http::get($url, 30);
            $result = json_decode($result, true);
            cookie('head_url', $result['headimgurl'], 86400 * 30);
        }

        return $result['openid'];
    }

    public static function goAuth()
    {
        //require_once getcwd() .'/Application/Front/Model/WxPay.Config.php';

        $baseUrl =  urlencode('http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]);
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . \WxPayConfig::APPID . "&redirect_uri=" . $baseUrl . "&response_type=code&scope=snsapi_userinfo&state=3d6be0a4035d839573b04816624a415e#wechat_redirect";

        Header("Location: ".$url);
    }

    public static function getAccessToken($force = false)
    {

        if (!$force) {
            $token = D('Common/Redis')->get('wx-access-token');
            if (!empty($token)) {
                return $token;
            }
        }
        $url = sprintf('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s', C('APPID'), C('APPSECRET'));
        $result = http::get($url, 30);
        $result = json_decode($result, 1);
        if (isset($result['access_token']) && isset($result['expires_in'])) {
            D('Common/Redis')->setex('wx-access-token', $result['access_token'], $result['expires_in']);
            return $result['access_token'];
        } else {
            return false;
        }
    }

    private static function sendTemplateMsg($data, $token)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='. $token;
        $result =  http::post($url, json_encode($data));
        $result = json_decode($result, 1);
        if ($result['errcode'] == 40001) {
            self::getAccessToken(1);
            $result =  http::post( $url, json_encode($data) );
        }
        return $result;
    }

    public static function sendCouponReceived($entity)
    {
        $token = self::getAccessToken();
        if ($token == false) {
            return false;
        }
        $data  = array(
            'touser' => $entity->openid,
            'template_id' => 'qsPkusAgROuxbEXjI0nCGQgovoNcAYTPjrMr3fEiwtQ',
            'url'         => '',
            'data'        => array(
                'first'   => ['value' => $entity->title, 'color' =>  '#173177'],
                'toName'  => ['value' => $entity->phone, 'color' =>  '#173177'],
                'gift'    => ['value' => $entity->actName . ' ' . $entity->amount .'元', 'color' =>  '#173177'],
                'time'    => ['value' => date('Y-m-d H:i', $entity->start), 'color' =>  '#173177'],
                'remark'  => ['value' => sprintf('优惠券有效期至:%s', date('Y-m-d', $entity->expired)), 'color' =>  '#173177']
            ),
        );
        return json_decode(self::sendTemplateMsg($data, $token));
    }
}
