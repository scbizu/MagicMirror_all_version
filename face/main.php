<?php
require_once 'facepp_sdk.php';
require_once 'analyse.class.php';
require_once 'processor.php';

$facepp = new Facepp();


$facepp->api_key       = '8b8d737b74acc5d76b50dd1691397fda';
$facepp->api_secret    = '4vAuKTZ0aa6JkN3UfiqfVpIZJRlWOGhh';

/**
 * 错误码:
 *   1000:不是正脸
 *   1001:
 *   
 *  正确识别码:
 *  2000:是正脸 
 */

#detect image by url
//接收到的图片URL
$params['url']          = 'http://7xqdui.com1.z0.glb.clouddn.com/1453480457432.jpg';
$response               = $facepp->execute('/detection/detect',$params);
$json=json_encode($response);
//var_dump($json);
//var_dump($json);
if($response['http_code'] == 200) {
    #json decode 
    $data = json_decode($response['body'], 1);

    #get face landmark

        $response = $facepp->execute('/detection/landmark', array('face_id' => $data['face'][0]['face_id']));
        if($response['http_code']===200){
        	$resdata=json_decode($response['body'],1);
            $landmark=$resdata['result'][0]['landmark'];
            //脸部算法类
            $analyse=new Analyse($landmark);

            $line=abs($landmark['contour_left5']['y']-$landmark['contour_right5']['y']);
            if($line>$analyse->deviation){
            	echo $line;
            }else{
            	$analyse->FaceInit();
           

//             	if($analyse->ifBaZhangFace()){
//             		$t=$analyse->ifBaZhangFace();
//             		echo "当前为巴掌脸\n";
//             		echo $t;
//             	}else if($analyse->ifCircleFace()){
//             		$t=$analyse->ifCircleFace();
//             		echo "当前为圆脸\n";
//             		echo $t;
//             	}else if($analyse->ifEggFace()){
//             		$t=$analyse->ifEggFace();
//             		echo "当前为鹅蛋脸\n";
//             		echo $t;
//             	}else if($analyse->ifGuaZiFace()){
//             		$t=$analyse->ifGuaZiFace();
//             		echo "当前为瓜子脸\n";
//             		echo $t;
//             	}else if($analyse->ifSquareFace()){
//             		$t=$analyse->ifSquareFace();
//             		$t2=floatval(floatval($analyse->width_check)/floatval($analyse->distance_eye));
//             		echo $t2;
//             		echo "当前为方形脸\n";
//             		echo $t;            		
//             	}else{
//             		echo "未识别脸型\n";
//             	}
            	
            	//LOG Analyse
            	echo "<br>"; 
            	echo "<br>"; 
            	//评估值Array
            	$value=array(
       
            	);
            	//bz           	
            	$t1=floatval($analyse->width_nose) /floatval($analyse->width_eye);
            	$percent_bz=processor::valueAccess('bz',$t1);
//             	array_push($value, $percent_bz);
				$value['bz']=$percent_bz;
            	$t2=floatval($analyse->height_check)/floatval($analyse->width_check);
            	$percent_yx=processor::valueAccess('yx',$t2);
//             	array_push($value, $percent_yx);
				$value['yx']=$percent_yx;
            	$t4=floatval($analyse->width_check)/floatval($analyse->width_eye);
            	$percent_ed=processor::valueAccess('ed',$t4);
//             	array_push($value, $percent_ed);
				$value['ed']=$percent_ed;
            	$t5=floatval($analyse->height_check)/floatval($analyse->width_check) ;
            	$percent_gz=processor::valueAccess('gz',$t5);
//             	array_push($value, $percent_gz);
				$value['gz']=$percent_gz;
            	$t6=floatval($analyse->width_check)/floatval($analyse->Mandibular);
            	$percent_fx=processor::valueAccess('fx',$t6);
//             	array_push($value, $percent_fx);
				$value['fx']=$percent_fx;
            	
            	//rSort Start
            	
				
				
				uasort($value, 'cmp');
           //     var_dump($value);
//             	echo ("巴掌脸特征值:");
//                 echo $value[0];
//                 echo "<br>";
//             	echo ("圆脸特征值:");
//             	echo $value[1];
//             	echo "<br>";
//             	echo ("鹅蛋脸特征值:");
//             	echo $value[2];
//             	echo "<br>";
//             	echo ("瓜子脸特征值:");
//             	echo $value[3];
//             	echo "<br>";
//             	echo ("方形脸特征值:");
//             	echo $value[4];
				$json=json_encode($value);
				echo $json;
            }	

        }
       // var_dump($response);

}
function cmp($a, $b) {
	if ($a == $b) {
		return 0;
	}
	return ($a < $b) ? 1 : -1;
}
