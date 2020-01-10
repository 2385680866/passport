<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserModel;

class UserController extends Controller
{
	/**
	 * 注册
	 * @return [type] [description]
	 */
    public function reg()
    {
    	if(empty($_POST['name'])){
    		return json_encode(["code"=>40001,"msg"=>"没有参数"],JSON_UNESCAPED_UNICODE);
    	}
    	if(empty($_POST['email'])){
    		return json_encode(["code"=>40001,"msg"=>"没有参数"],JSON_UNESCAPED_UNICODE);
    	}
    	if(empty($_POST['mobile'])){
    		return json_encode(["code"=>40001,"msg"=>"没有参数"],JSON_UNESCAPED_UNICODE);
    	}
    	if(empty($_POST['pwd1'])){
    		return json_encode(["code"=>40001,"msg"=>"没有参数"],JSON_UNESCAPED_UNICODE);
    	}
    	if($_POST['pwd1']!=$_POST['pwd2']){
    		return json_encode(["code"=>40002,"msg"=>"密码与确认密码不一致"],JSON_UNESCAPED_UNICODE);
    	}
    	$data=[
    		"name"=>$_POST['name'],
    		"email"=>$_POST['email'],
    		"mobile"=>$_POST['mobile'],
    		"pwd"=>$_POST['pwd1'],
    	];
    	$res=UserModel::insert($data);
    	if($res){
    		return json_encode(["code"=>40000,"msg"=>"注册成功"],JSON_UNESCAPED_UNICODE);
    	}else{
    		return json_encode(["code"=>40004,"msg"=>"注册失败","data"=>$_POST],JSON_UNESCAPED_UNICODE);
    	}
    }
    /**
     * 登录
     * @return [type] [description]
     */
    public function login()
    {
    	if(empty($_POST['pwd'])){
    		return json_encode(["code"=>40001,"msg"=>"没有参数"],JSON_UNESCAPED_UNICODE);
    	}
        if(strpos($_POST['account'],'@')){
            $where=['email'=>$_POST['account']];
            $info=UserModel::where($where)->first();
        }else{
            $where=['mobile'=>$_POST['account']];
            $info=UserModel::where($where)->first();
        }
    	
    	if(empty($info)){
    		return json_encode(["code"=>40005,"msg"=>"没有此用户"],JSON_UNESCAPED_UNICODE);
    	}else{
    		if($info['pwd']!=$_POST['pwd']){
	    		return json_encode(["code"=>40005,"msg"=>"没有此用户"],JSON_UNESCAPED_UNICODE);
	    	}else{
	    		$token=md5(uniqid(rand(11111,99999)));
	    		UserModel::where(["name"=>$info['name']])->update(['token'=>$token]);
	    		return json_encode(["code"=>40000,"msg"=>"登录成功","token"=>$token],JSON_UNESCAPED_UNICODE);
	    	}
    	}
    }
    /**
     * 用户列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
   	public function list(Request $request)
   	{
        $id=$_GET['id'];
   		$data=UserModel::where(["id"=>$id])->first();
        if(!empty($data)){
            return json_encode(["code"=>40000,"data"=>$data],JSON_UNESCAPED_UNICODE);
        }else{
            return json_encode(["code"=>40000,"data"=>"没有此用户"],JSON_UNESCAPED_UNICODE);
        }	
   	}
}
