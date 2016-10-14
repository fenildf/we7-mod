<?php
/**
 * 美女倒计时模块微站定义
 *
 * @author Yoby
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class Yoby_clockModuleSite extends WeModuleSite {

public function doWebClock() {
			global $_W,$_GPC;
		$yobyurl = $_W['siteroot']."addons/yoby_clock/";
		$weid = $_W['uniacid'];
		$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		load()->func('tpl'); 
		if('post' == $op){//添加或修改
			$id = intval($_GPC['id']);
			if(!empty($id)){
			$item = pdo_fetch("SELECT id,title,createtime FROM ".tablename('yoby_clock')." where id=$id");
			empty($item)?message('亲,数据不存在！', '', 'error'):"";	
			}
			
			
			if(checksubmit('submit')){
				empty ($_GPC['title'])?message('亲,名称不能为空'):$title =$_GPC['title'];
				empty ($_GPC['createtime'])?message('亲,时间不能为空'):$createtime =strtotime($_GPC['createtime']);
				if(empty($id)){
						pdo_insert('yoby_clock', array('weid'=>$weid,'title'=>$title,'createtime'=>$createtime));//添加数据
						message('倒计时添加成功！', $this->createWebUrl('clock', array('op' => 'display')), 'success');
				}else{
						pdo_update('yoby_clock', array('title'=>$title,'createtime'=>$createtime), array('id' => $id));
						message('倒计时更新成功！', $this->createWebUrl('clock', array('op' => 'display')), 'success');
				}
				
				
			}else{
				include $this->template('index');
			}
			
		}else if('del' == $op){//删除
		
		
			if(isset($_GPC['delete'])){
				$ids = implode(",",$_GPC['delete']);
				$sqls = "delete from  ".tablename('yoby_clock')."  where id in(".$ids.")"; 
				pdo_query($sqls);
				message('删除成功！', referer(), 'success');
			}
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('yoby_clock')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				//dump($_GPC);
				message('抱歉，倒计时不存在或是已经被删除！', $this->createWebUrl('clock', array('op' => 'display')), 'error');
			}
			pdo_delete('yoby_clock', array('id' => $id));
			message('删除成功！', referer(), 'success');
			
		}else if('display' == $op){//显示
			$pindex = max(1, intval($_GPC['page']));
			$psize =20;//每页显示
			
			$list = pdo_fetchall("SELECT id,title,createtime  FROM ".tablename('yoby_clock') ." where weid={$weid}  ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);//分页
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yoby_clock')."  where weid={$weid}" );
			$pager = pagination($total, $pindex, $psize);
			include $this->template('index');
	}

}


}