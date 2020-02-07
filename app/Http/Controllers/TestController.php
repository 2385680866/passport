<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
	/**签名
	 * [check description]
	 * @return [type] [description]
	 */
    public function md5SignGet()
    {
    	$key = "1905";
    	$data = $_GET['data'];
    	$sign = $_GET['sign'];
    	$s= md5($data . $key);
    	if($s==$sign){
    		echo "验证通过"; 
    	}else{
    		echo  "验证失败";
    	}
    }
    /**测试签名 POST
     * [signPost description]
     * @return [type] [description]
     */
    public function md5SignPost()
    {
        $key = "fule"; //签名key
        $json_data = $_POST['data'];//接收数据
        $sign = $_POST['sign']; //接收签名
        //计算签名
        $sign2=md5($json_data,$key);
        // echo "接收的签名:" . $sign;echo "</br>";
        // echo "计算出的签名:" . $sign2;echo "</br>";
        //判断签名是否一致
        if($sign == $sign2){
            echo "验签成功";
        }else{
            echo "验签失败";
        }
    }
}
