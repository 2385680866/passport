<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    /**公钥签名
     * [rsaSign description]
     * @return [type] [description]
     */
    public function rsaSign()
    {
        $data=$_GET['sign'];
        $data = base64_decode(urldecode($data));
        //验证签名
        $path = storage_path("keys/testpub.key");
        $pubkeyid = openssl_pkey_get_public("file://" . $path);
        $ok = openssl_verify($data, $signature, $pubkeyid);
        
        if ($ok == 1) {
            echo "good";
        } elseif ($ok == 0) {
            echo "bad";
        } else {
            echo "ugly, error checking signature";
        }
        openssl_free_key($pubkeyid);
    }
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
