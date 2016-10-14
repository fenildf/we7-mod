<?php
/**
 * 美女倒计时模块处理程序
 *
 * @author Yoby
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
	function countDown1($settime)
	{
        $time = time();
        $settime  = strtotime($settime);
        $interval = $settime - $time;
        $days = $interval/(24*60*60);//精确到天数
        $days = intval($days);
        $hours = $interval /(60*60) - $days*24;//精确到小时
        $hours = intval($hours);
        $minutes = $interval /60 - $days*24*60 - $hours*60;//精确到分钟
        $minutes = intval($minutes);
        $seconds = $interval - $days*24*60*60 - $hours*60*60 - $minutes*60;//精确到秒
        $seconds = intval($seconds);
		$str = $days."天".$hours."小时".$minutes."分".$seconds."秒";
		if(intval($days)<0){
		$str=0;
		}
		return $str;
	}
	
	function curl_get_contents1($url)   
    {   
        $ch = curl_init();   
        curl_setopt($ch, CURLOPT_URL, $url);            //设置访问的url地址   
        curl_setopt($ch, CURLOPT_HEADER, 0);            //是否显示头部信息   
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);           //设置超时   
        curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);   //用户访问代理 User-Agent   
        curl_setopt($ch, CURLOPT_REFERER,_REFERER_);        //设置 referer   
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);      //跟踪301   
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        //返回结果   
        $data = curl_exec($ch);   
        curl_close($ch);   
        return $data;   
    }
class Yoby_clockModuleProcessor extends WeModuleProcessor {
public function respond() {
	global $_W;
		$content = $this->message['content'];
	$weid = $_W['weid'];
        $url = "http://www.sodao.com/index/bc_get";
        $json = curl_get_contents1($url);
        $object = json_decode($json,true);
        $array = $object['data'][0]['data'];
        $i = rand(0,1);//实现随机返回
        $Title = $array[$i]['nickName'].$array[$i]['age'].$array[$i]['takeCity'].$array[$i]['takeAddress'];
        $PicUrl = $array[$i]['path'];
        $now = date("Y-m-d H:i:s",time());
        
        $rs =  pdo_fetchall("select * from ".tablename('yoby_clock')." where weid=$weid order by createtime asc");
        $data = '';
        if(empty($rs)){
        $data .= "";
        }else{
        foreach($rs as $list){
          if(countDown1(date('Y-m-d',$list['createtime']))){
        $data .= "\n\n距离".$list['title']."还有".countDown1(date('Y-m-d',$list['createtime']));
        }
        }
        }

        $Description = "当前时间".$now.$data;
        $content1 = array("Title"=>$Title, "Description"=>$Description, "PicUrl"=>$PicUrl, "Url"=>$PicUrl);

	

return $this->respNews($content1);
	}
}