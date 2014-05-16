<?php
/**
 * File Name: UserAction.class.php
 * Author: chen
 * Created Time: 2013-11-11 17:45:17
*/
class UserAction extends HomeAction{

	/**
	 * 会员列表
	 */
	public function userList(){
		$userObj = D('User');
		$count = $userObj->getCount();
        $page = page($count);
        $fileList = array(

		$userList = $userObj->limit($page->firstRow, $page->listRows)->select();
		foreach($userList as $k => $v){
			$userList[$k] = $userObj->format($v, $arrFormatField);
		}
		$this->assign($tplData);
		$this->display("Public:list");
	}

	/**
	 * 用户生成操作
	 */
	public function add(){
        $data = $this->_post();
        if(empty($data)){
            $this->display();
            exit;
        }
		$userObj = D('User');
		$password = mt_rand();
		$data['password'] = md5($password);
		$data['group_id'] = '2';
        $data['date_reg'] = $data['date_log'] = time();
		$userObj->add($data);
		echo ('用户名:'.$data['name']);
        echo ('<br>');
		echo ('密码:'.$password);
	}

	/**
	 * 会员登录后获取到的自身信息
	 */
	public function basic(){
		$data = $this->_post();
		$userObj = D('User');
        if(empty($data)){
		    $id = $_SESSION['uid'];
		    $userInfo = $userObj->getInfoById($id);
		    $userInfo = $userObj->format($userInfo, array('url', 'avatar_name'));
            $tpl_data = array(
                'title'=>'User basic',
                'url'=>U('User/basic'),
                'list'=>array(
                ),
                'userInfo'=>$userInfo,
            );
            $this->assign($tpl_data);
		    $this->display('Public:info');
            exit;
        }
		if(!empty($_FILES['pic']['name'])){
			$picList = uploadPic();
			if($picList['code'] != 'error'){
				$data['avatar'] = D('GalleryMeta')->addImg($picList['pic']['savename']);
			}
		}
        $result = $userObj->save($data);
        if(empty($result)){
            echo json_encode(array('code'=>'0','msg'=>'更新错误'));
        }else{
            echo json_encode(array('code'=>'1','msg'=>'更新成功'));
        }
	}

    /**
     * 更新密码
     */
	public function password() {
        if(empty($_POST)){
            $this->assign('current', 'user_pwd');
            $this->display();
            exit;
        }
		$userObj = D('User');
		$map['id'] = $_SESSION['uid'];
		$map['password'] = md5($_POST['oldpassword']);
        if(!$userObj->where($map)->find()){
            echo json_encode(array('status'=>'0', 'msg'=>'原始密码输入错误'));
		}else{
            $password = md5($_POST['newpassword']);
            if($userObj->where('id='.$_SESSION['uid'])->setField('password', $password)){
                echo json_encode(array('status'=>'1', 'msg'=>'密码修改成功'));
            }else{
                echo json_encode(array('status'=>'0', 'msg'=>'密码修改失败'));
            }
         }
    }

    /**
     * 删除用户
     */
    public function del()
    {
		$delIds = array();
		$postIds = $this->_post('id');
		if (!empty($postIds)) {
			$delIds = $postIds;
		}
		$getId = intval($this->_get('id'));
		if (!empty($getId)) {
			$delIds[] = $getId;
		}
		if (empty($delIds)) {
			$this->error('请选择您要删除的数据');
		}
		$map['id'] = array('in', $delIds);
		D('User')->where($map)->delete();
		$this->success('删除成功');
    }

    /*******************分组管理****************************/
    /**
     * group list
     */
    public function groupList()
    {
        $groupObj = D('UserGroup');
        $arrField = array();
        $arrMap = array();
        $arrOrder = array();
        $groupList = $groupObj->getList($arrField, $arrMap, $arrOrder);
        foreach($groupList as $k=>$v){
            $groupList[$k]['count'] = D('User')->getCount(array('group_id'=>$v['id']));
        }
        $data = array(
            'groupList' => $groupList,
            'groupInfoUrl' => U('Home/User/groupInfo'),
        );
        $this->assign($data);
        $this->display();
    }

    /**
     * gruop info
     */
    public function groupInfo()
    {
        $groupObj = D('UserGroup');
        if(empty($_POST)){
            $group_id = $this->_get('group_id', 'intval');
            if(!empty($group_id)){
                $groupInfo = $groupObj->getInfoById($group_id);
                $this->assign('groupInfo', $groupInfo);
            }
            $this->assign('groupInfoUrl', U('Home/User/groupInfo'));
            $this->display();
            exit;
        }
        $data = $this->_post();
        $data['date_modify'] = time();
        if(empty($data['id'])){
            $data['date_add'] = time();
            $groupObj->add($data);
        }else{
            $groupObj->save($data);
        }
        $this->success('操作成功');
    }

}
