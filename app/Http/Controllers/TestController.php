<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
	/**签名
	 * [check description]
	 * @return [type] [description]
	 */
    public function check()
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
}
