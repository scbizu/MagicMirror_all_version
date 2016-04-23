<?php
require_once 'facepp_sdk.php';
require_once 'analyse.class.php';
require_once 'processor.class.php';
require_once 'app.class.php';
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/qiniu/autoload.php';

//use Psr\Http\Message\ServerRequestInterface;
//use Psr\Http\Message\ResponseInterface;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
///////接收的POST数据

//$useLast=$_POST['useLast'];
$useLast=FALSE;

$facepp = new Facepp();

$app=new app();
//Slim api框架的引入
$apiObj=new \Slim\App();
//七牛
$accessKey = '_S5oPZGakasmUFjD-ZDKv04fce2W7nX0DE6GZ9b7';
$secretKey = 'Ud4fabiU0txFI5qU65OgpYIogr3VOBoVb1hmHeaK';
$auth = new Auth($accessKey, $secretKey);

$openid='fromUser';

$facepp->api_key       = '8b8d737b74acc5d76b50dd1691397fda';
$facepp->api_secret    = '4vAuKTZ0aa6JkN3UfiqfVpIZJRlWOGhh';

/**
 * 错误码:
 *   1000:不是正脸 
 *   1001:脸型备份到数据库失败
 *   
 *  正确识别码:
 *  2000:是正脸 
 */
//////////////////图片上传路由////////////////////////
$apiObj->post('/img', function() use($apiObj){
	$req=$apiObj->request();
	$img=$req->post('img');
	$bucket = 'magicmirror';	
	// 生成上传Token
	$token = $auth->uploadToken($bucket);	
	$imgkey=substr(md5(time()), 2,10);
	// 构建 UploadManager 对象
	$uploadMgr = new UploadManager();
	list($ret, $err) =$uploadMgr->putFile($token, $imgkey, $img);
	if($err!==null){
		$errno=array('errno'=>'图片传输错误!');
		$data=json_encode($errno);
		exit();
	}
	//接收到的图片URL
	$params['url']          = 'http://7xtb5w.com2.z0.glb.clouddn.com/'.$ret['key'];
	$response               = $facepp->execute('/detection/detect',$params);
	$json=json_encode($response);
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
				//
				if($app->checkFace($openid) && $useLast===TRUE){
					$lastface=$app->checkFace($openid);
					$data=$lastface['facedata'];
				}else if($app->checkFace($openid) && $useLast===FALSE){
					$type=$process->finalJG();
					$data=$process->do2JsonData();
	
					$updateOk=$app->updateSet('mm_main', $openid, $data, $type);
	
					if(!$updateOk){
						echo '1001';
						exit();
					}
				}else{
					$type=$process->finalJG();
					$data=$process->do2JsonData();
	
					$saveOk=$app->saveData('mm_main', $openid, $data, $type);
	
					if(!$saveOk){
						echo '1001';
						exit();
					}
				}
			}
		}	
	}	
	echo $data;		
});


