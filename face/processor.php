<?php



//工具处理类
class processor {
	
	public $Marray;
	
	
	// TODO - Insert your code here
	function __construct() {
		
		// TODO - Insert your code here
	}
/**
 * 按照脸型匹配度  对脸进行排序
 */	
	public static  function faceSort($facearray){
		krsort($facearray);
		return $facearray;
	}
/**
 * 返回脸型评估值  即符合度
 */	
	public static function valueAccess($type,$value){
		$array=array();
		switch ($type){
			//巴掌脸的情况
			case 'bz':
				$standard=floatval(1);
				$res=floatval(1)-abs($value-$standard)/floatval($standard);
				break;
			//鹅蛋脸的情况
			case 'ed':
				$standard=floatval(5);
				$res=floatval(1)-abs($value-$standard)/floatval($standard);
				break;
			//瓜子脸	
			case 'gz':
				$standard=floatval(1);
				$res=floatval(1)-abs($value-$standard)/floatval($standard);
				break;
			//方形脸:
			case 'fx':
				$standard=floatval(1);
				$res=floatval(1)-abs($value-$standard)/floatval($standard);
				break;
			//圆形
			case 'yx':
				$standard=floatval(1);
				$res=floatval(1)-abs($value-$standard)/floatval($standard);
				break;
			default:
				break;	
		}
		
		
		return $res;
	}

	

}
?>