<?php
/**
 * 官方示例模块微站定义
 *
 * @author 微擎团队
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
function sn(){
return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}
//get获取
function get($url,$ssl=TRUE){   
 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_HEADER, 0);
if($ssl){
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
}
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
$data  =  curl_exec($ch);
curl_close($ch);
return $data; 
}
function downimg($meid){
$token = WeAccount::token();
$data =get("http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$token&media_id=$meid",false);

return $data;
}
class We7_demoModuleSite extends WeModuleSite {

	public function doMobileIndex1() {
	global $_W;
 $yobyurl = $_W['siteroot'].'addons/we7_demo/template/mobile/';
 $surl = $this->shorturl("http://weixin.yoby123.cn/app/index.php?i=1&c=entry&do=fm&m=yoby_game");//长网址转换短网址
 $weid = $_W["uniacid"];
include $this->template('index1');
	}
	
	public function doMobileajax1(){
	global $_W, $_GPC;
	load()->func('file');
	$weid = $_W['uniacid'];
	$mid = $_GPC['mid'];
	$sn = sn();
	$data =downimg($mid);
	$filename ="images/$weid/yobydemo".$sn.'.jpg';
 file_write($filename, $data);
 echo '{"src":"'.tomedia($filename).'","v":"'.$filename.'"}';
	}
		public function doMobileceshi() {
	global $_W, $_GPC;
	load()->func('file');
	
	dump($_GPC);
	}
		public function doMobileajax2() {
	global $_W, $_GPC;
	load()->func('file');
	$filename =IA_ROOT.'/addons/we7_demo/template/mobile/1.jpg';
	$data = $this->upimg($filename);

 echo $data;
	}

public function emojien($str){
    if(!is_string($str))return $str;
    if(!$str || $str=='undefined')return '';

    $text = json_encode($str); //暴露出unicode
    $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){
        return addslashes($str[0]);
    },$text); 
    return json_decode($text);
}
public function emojide($str){
    $text = json_encode($str); //暴露出unicode
    $text = preg_replace_callback('/\\\\\\\\/i',function($str){
        return '\\';
    },$text);
    return json_decode($text);
}
	public function doMobileIndex2() {
		global $_W, $_GPC;
dump($_W);
		
				
		include $this->template('index2');
	}
	
	public function doMobilePay() {
		global $_W, $_GPC;
		//验证用户登录状态，此处测试不做验证
		checkauth();
		
		$params['tid'] = date('YmdH');
		$params['user'] = $_W['member']['uid'];
		$params['fee'] = floatval($_GPC['price']);
		$params['title'] = '测试支付公众号名称';
		$params['ordersn'] = random(5,1);
		$params['virtual'] = false;
		
		if (checksubmit('submit')) {
			if ($_GPC['type'] == 'credit') {
				$setting = uni_setting($_W['uniacid'], array('creditbehaviors'));
				$credtis = mc_credit_fetch($_W['member']['uid']);
				//此处需要验证积分数量
				if ($credtis[$setting['creditbehaviors']['currency']] < $params['fee']) {
					message('抱歉，您帐户的余额不够支付该订单，请充值！', '', 'error');
				}
			}
		} else {
			$this->pay($params);
		}
	}
	
		public function get_userinfo($openid){
	global $_W;
	$token = WeAccount::token();
	 $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid={$openid}&lang=zh_CN";
       $data  = json_decode($this->get($url,1),1);
    
        $data['avatar']= $data['headimgurl'];
        unset($data['headimgurl']);
       return $data;
	}
	
	/**
	 * 支付完成后更改业务状态
	 */
	public function payResult($params) {
		/*
		 * $params 结构
		 * 
		 * weid 公众号id 兼容低版本
		 * uniacid 公众号id
		 * result 支付是否成功 failed/success
		 * type 支付类型 credit 积分支付 alipay 支付宝支付 wechat 微信支付  delivery 货到付款
		 * tid 订单号
		 * user 用户id
		 * fee 支付金额
		 * 
		 * 注意：货到付款会直接返回支付失败，请在订单中记录货到付款的订单。然后发货后收取货款
		 */
		$fee = intval($params['fee']);
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		//如果是微信支付，需要记录transaction_id。
		if ($params['type'] == 'wechat') {
			$data['transid'] = $params['tag']['transaction_id'];
		}
		//此处更改业务方面的记录，例如把订单状态更改为已付款
		//pdo_update('shopping_order', $data, array('id' => $params['tid']));
		
		//如果消息是用户直接返回（非通知），则提示一个付款成功
		if ($params['from'] == 'return') {
			if ($params['type'] == 'credit') {
				message('支付成功！', $this->createMobileUrl('index1'), 'success');
			} elseif ($params['type'] == 'delivery') {
				message('请您在收到货物时付清货款！', $this->createMobileUrl('index1'), 'success');
			} else {
				message('支付成功！', '../../' . $this->createMobileUrl('index1'), 'success');
			}
		}
	}
	
	public function doWebManage1() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_W, $_GPC;
		$title = '测试标题1';
		include $this->template('manage1');
	}
	public function doWebManage2() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_W, $_GPC;
		include $this->template('manage2');
	}
	public function doMobileNav1() {
		//这个操作被定义用来呈现 微站首页导航图标
		exit('doMobileNav1');
	}
	public function doMobileNav2() {
		//这个操作被定义用来呈现 微站首页导航图标
		exit('doMobileNav2');
	}
	public function doMobileUc1() {
		//这个操作被定义用来呈现 微站个人中心导航
		exit('doMobileUc1');
	}
	public function doMobileUc2() {
		//这个操作被定义用来呈现 微站个人中心导航
		exit('doMobileUc2');
	}
	public function doMobileQuick1() {
		//这个操作被定义用来呈现 微站快捷功能导航
		exit('doMobileQuick1');
	}
	public function doMobileQuick2() {
		//这个操作被定义用来呈现 微站快捷功能导航
		exit('doMobileQuick2');
	}
    public function post($url,$msg,$ssl=false){//post ssl
$ch = curl_init();
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_URL,$url);
if($ssl){
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
}
curl_setopt($ch, CURLOPT_POSTFIELDS,$msg);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);

return $data;
    }
 public function get($url,$ssl=false){   
 $ch = curl_init();
 curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_HEADER, 0);
if($ssl){
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
}
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
$data  =  curl_exec($ch);
curl_close($ch);
return $data; 
} 
public function shorturl($url){//长网址转换短网址
$token = WeAccount::token();
$msg = '{
"action":"long2short",
"long_url":"'.$url.'"
}';
$data = $this->post("https://api.weixin.qq.com/cgi-bin/shorturl?access_token=$token",$msg,TRUE);
$data = json_decode($data,1);
$data = ($data['errmsg']=='ok')?$data['short_url']:$data['errmsg'];
return $data;
} 
public function downimg($meid){//下载多媒体
$token = WeAccount::token();
$data = $this->get("http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$token&media_id=$meid");


return $data;
}
public function upimg($filename,$type='image'){//上传多媒体
$token = WeAccount::token();
$msg = array('media'=>'@'.$filename);
$data = $this->post("http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=$token&type=$type",$msg);

return $data;
}

}