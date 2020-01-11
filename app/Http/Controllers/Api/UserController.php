<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserModel;
use Illuminate\Support\Facades\Redis;
class UserController extends Controller
{
    /**
	 * 注册
	 * @return [type] [description]
	 */
    public function reg(Request $request)
    {
        //接受数据
        $name=$request->input("name");
        $email=$request->input("email");
        $mobile=$request->input("mobile");
        $pwd1=$request->input("pwd1");
        $pwd2=$request->input("pwd2");
        //判断参数是否为空
    	if(empty($name)||empty($email)||empty($mobile)||empty($pwd1))
        {
            $response=[
                "code"=>40001,
                "msg"=>"参数不能为空"
            ];
            return $response;
    	}
        //判断密码与确认密码是否一致
    	if($pwd1!=$pwd2)
        {
            $response=[
                "code"=>40002,
                "msg"=>"密码与确认密码不一致"
            ];
    	}
        $info=UserModel::where(['name'=>$name])->first();
        if($info)
        {
            exit("名称已存在");
        }
        $info=UserModel::where(['email'=>$email])->first();
        if($info)
        {
            exit("邮箱已存在");
        }
        $info=UserModel::where(['mobile'=>$mobile])->first();
        if($info)
        {
            exit("手机号已存在");
        }
        //生成密码
        $pwd=password_hash($pwd1,PASSWORD_BCRYPT);
    	$data=[
    		"name"=>$name,
    		"email"=>$email,
    		"mobile"=>$mobile,
    		"pwd"=>$pwd,
    	];
        //入库
    	$res=UserModel::insert($data);
    	if($res){
            $response=[
                "code"=>40000,
                "msg"=>"注册成功"
            ];
    	}else{
            $response=[
                "code"=>40004,
                "msg"=>"注册失败"
            ];
    	}
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    }
    /**
     * 登录
     * @return [type] [description]
     */
    public function login(Request $request)
    {
        $pwd=$request->input('pwd');
        $name=$request->input("name");
        //判断密码是否存在
    	if(empty($pwd)||empty($name)){
            //不存在则返回
            $response=[
                "code"=>40001,
                "msg"=>"没有账号或密码"
            ];
    	}
        //判断账号
        if(strpos($name,'@'))//如果是邮箱则
        {
            $where=['email'=>$name];
            $info=UserModel::where($where)->first();
        }elseif(preg_match("/^1[34578]\d{9}$/",$name)) //如果是手机则
        {
            $where=['mobile'=>$name];
            $info=UserModel::where($where)->first();
        }else{  //否则是名称
            $where=['name'=>$name];
            $info=UserModel::where($where)->first();
        }
    	if(empty($info)){
            $response=[
                "code"=>40005,
                "msg"=>"没有此用户"
            ];
    	}else{
    		if(password_verify($pwd,$info->pwd)){
                //生成token
                $token=$this->getToken($info->id);
                $redis_token_key="str:user:token:".$info->id;
                Redis::set($redis_token_key,$token,86400);
                $response=[
                    "code"=>40000,
                    "msg"=>"登录成功",
                    "data"=>[
                        "token"=>$token,
                        "id"=>$info->id
                    ]
                ];
	    	}else{
                $response=[
                    "code"=>40005,
                    "msg"=>"没有此用户",
                ];
	    	}
    	}
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    }
    /**
     * 获取token
     * @return [type] [description]
     */
    public function getToken($id)
    {
        $token=substr(md5(uniqid(rand(11111,99999)).$id),5,20);
        return $token;
    }
    /**
     * 用户列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
   	public function list(Request $request)
   	{
        $token=$_SERVER['HTTP_TOKEN'];
        $uid=$_SERVER['HTTP_UID'];
        if(empty($token)||empty($uid))
        {
            $response=[
                "code"=>40000,
                "msg"=>"token或uid不存在"
            ];
        }
        $redis_token_key="str:user:token:".$uid;
        $cache_token=Redis::get($redis_token_key);
        if($token==$cache_token)
        {
            $data=date("Y-m-d H:i:s");
            $response=[
                "code"=>40000,
                "msg"=>"ok",
                "data"=>$data
            ];
        }else{
            $response=[
                "code"=>40004,
                "msg"=>"token或uid有误",
            ];
        }
        return json_encode($response,JSON_UNESCAPED_UNICODE);
   	}
}
