<?php
/**
 * 官方示例模块处理程序
 *
 * @author 微擎团队
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class We7_demoModuleProcessor extends WeModuleProcessor {
	public function respond() {
	global $_W;
		$openid = $this->message['from'];

		return $this->respText($openid);
	}
	
}