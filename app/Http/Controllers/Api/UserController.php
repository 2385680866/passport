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
        // $mobile=$request->input("mobile");
        // $pwd1=$request->input("pwd1");
        $pwd=$request->input("pwd");
        //判断参数是否为空
    	if(empty($name)||empty($email)||empty($pwd))
        {
            return json_encode(["code"=>40001,"msg"=>"参数不能为空"],JSON_UNESCAPED_UNICODE);
    	}
        //判断手机号格式是否正确
        // if(!preg_match("/^1[34578]\d{9}$/",$mobile))
        // {
        //     return json_encode(["code"=>40008 ,"msg"=>"手机号格式不正确" ],JSON_UNESCAPED_UNICODE);
        // }
        // //判断手机号和用户名是否一致
        // if($mobile==$name)
        // {
        //     return json_encode(["code"=>40009 ,"msg"=>"手机号和用户名称不能保持一致"],JSON_UNESCAPED_UNICODE);
        // }
        //判断密码与确认密码是否一致
    	// if($pwd1!=$pwd2)
     //    {
     //        return json_encode(["code"=>40002 ,"msg"=>"密码与确认密码不一致"],JSON_UNESCAPED_UNICODE);
    	// }
        //判断用户名称已被占用
        $info=UserModel::where(['name'=>$name])->first();
        if($info)
        {
            return json_encode(["code"=>40010,"msg"=>"用户名称已被占用"],JSON_UNESCAPED_UNICODE);
        }
        //判断手邮箱被占用
        $info=UserModel::where(['email'=>$email])->first();
        if($info)
        {
            return json_encode(["code"=>40010,"msg"=>"邮箱已被占用"],JSON_UNESCAPED_UNICODE);
        }
        //判断手机号是否被占用
        // $info=UserModel::where(['mobile'=>$mobile])->first();
        // if($info)
        // {
        //     return json_encode(["code"=>40010,"msg"=>"手机号已被占用"],JSON_UNESCAPED_UNICODE);
        // }
        //生成密码
        $pwd=password_hash($pwd,PASSWORD_BCRYPT);
    	$data=[
    		"name"=>$name,
    		"email"=>$email,
    		// "mobile"=>$mobile,
    		"pwd"=>$pwd,
    	];
        //入库
    	$res=UserModel::insert($data);
    	if($res){
            return json_encode(["code"=>40000,"msg"=>"注册成功"],JSON_UNESCAPED_UNICODE);
    	}else{
            return json_encode(["code"=>40004,"msg"=>"注册失败"],JSON_UNESCAPED_UNICODE);
    	}
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
            $response=["code"=>40001,"msg"=>"没有账号或密码"];
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
        //判断是否有此用户
    	if(empty($info))//如果没有
        {
            $response=["code"=>40005,"msg"=>"没有此用户"];
    	}else{ //如果有
            //判断密码是否正确
    		if(password_verify($pwd,$info->pwd)){ //如果正确
                //生成token
                $token=$this->getToken($info->id);
                $redis_token_key="str:user:token:".$info->id;
                Redis::set($redis_token_key,$token,86400);
                $response=[
                    "code"=>40000,
                    "msg"=>"登录成功",
                    "data"=>["token"=>$token,"id"=>$info->id]
                ];
	    	}else{ //如果不正确
                $response=["code"=>40005,"msg"=>"没有此用户",];
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
        $uid=$_SERVER['HTTP_UID'];
        $info=UserModel::where(['id'=>$uid])->first();
        return json_encode(['code'=>40000,"data"=>$info],JSON_UNESCAPED_UNICODE);
   	}
    /**
     * 鉴权
     * @return [type] [description]
     */
    public function auth()
    {

        $uid=$_POST['uid'];
        $token=$_POST['token'];
        if(empty($_POST['uid'])||empty($_POST['token']))
        {
            return json_encode(['code'=>40003,"msg"=>"没有UID或TOKEN"],JSON_UNESCAPED_UNICODE);
        }
        $redis_token_key="str:user:token:".$uid;
        //验证token是否有效
        $cache_token=Redis::get($redis_token_key);
        if($token==$cache_token)
        {
            $data=date("Y-m-d H:i:s");
            return json_encode(['code'=>40000,"msg"=>"ok"]);
        }else{
            return json_encode(['code'=>40004,"msg"=>"Token Not Valid!"]);
        }

    }
}
