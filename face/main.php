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
			//调用脸部处理
			  $process=new processor($landmark);
            $line=abs($landmark['contour_left5']['y']-$landmark['contour_right5']['y']);
            if($line>$process->de){
            	echo $line;
            }else{
				$type=$process->finalJG();
				//echo $type;
				$data=$process->do2JsonData();
				var_dump($data);
            }	
        }


}

