<?php
namespace app\index\controller;

use app\index\logic\weixinlogic;

class customer
{
    public function index()
    {
        $token  = weixinlogic::getAccessToken();
        $nextOpenId = '';
        
        $apiUrl = sprintf("https://api.weixin.qq.com/cgi-bin/user/get?access_token=%s&next_openid=%s", $token, $nextOpenId);
        return '';
    }
}
