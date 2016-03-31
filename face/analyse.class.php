<?php
class Analyse{

	//脸部数据
	public $landmark;
	//允许误差范围
	public $deviation;
	public $kdeviation;
	//瞳孔距离
	public $distance_eye;
	//脸颊距离
	public $width_check;
	//脸颊高度
	public  $height_check;
	//鼻翼宽度
	public $width_nose;
	//眼睛宽度
	public $width_eye;
	//下颌宽度
	public $Mandibular;
	//额头宽度
	public $Forehead;
	
	public function __construct($facedata){
		$this->deviation=6.0;
		$this->kdeviation=0.26;
		$this->landmark=$facedata;		
	}
/**
 * 勾股定理
 * @param unknown $width_L
 * @param unknown $width_R
 * @param unknown $height_L
 * @param unknown $height_R
 */	
	private  function GouGuFunction($L_Point,$R_Point){
		$dataarr=$this->landmark;
		$distance=sqrt(pow(($dataarr[$R_Point]['x']-$dataarr[$L_Point]['x']),2)+pow(($dataarr[$R_Point]['y']-$dataarr[$L_Point]['y']),2));
		return $distance;				
	}
/**
 * 计算一段曲线的Delta值
 * @param unknown $x1_Point
 * @param unknown $y1_Point
 * @param unknown $x2_Point
 * @param unknown $y2_Point
 * @return number
 */	
	private function  DeltaK($x1_Point,$y1_Point,$x2_Point,$y2_Point){
		$delta=(abs(floatval($y2_Point-$y1_Point))/abs(floatval($x2_Point-$x1_Point)));
		return $delta;
	}
	
/**
 * 提供左脸递减点的delta值   即 6->7->8->9->chin
 * @return multitype:
 */	
	public function chinK_array(){
		//初始化下巴点的数组
	  $chinK=array();
	  $dataarr=$this->landmark;
	  //获取四个Delta值
	  $left627=$this->DeltaK($dataarr['contour_left6']['x'], $dataarr['contour_left6']['y'], $dataarr['contour_left7']['x'], $dataarr['contour_left7']['y']);
	  $left728=$this->DeltaK($dataarr['contour_left7']['x'], $dataarr['contour_left7']['y'], $dataarr['contour_left8']['x'], $dataarr['contour_left8']['y']);
	  $left829=$this->DeltaK($dataarr['contour_left8']['x'], $dataarr['contour_left8']['y'], $dataarr['contour_left9']['x'], $dataarr['contour_left9']['y']);
	  $left920=$this->DeltaK($dataarr['contour_left9']['x'], $dataarr['contour_left9']['y'], $dataarr['contour_chin']['x'], $dataarr['contour_chin']['y']);
	  array_push($chinK, $left627);
	  array_push($chinK, $left728);
	  array_push($chinK, $left829);
	  array_push($chinK, $left920);
	  return $chinK;
	}
	
	
	
	
	/**
	 * 脸部初始化
	 */
	public function FaceInit(){
		$this->getCheckDistance();
		$this->getCheckHeight();
		$this->getEyeDistance();
		$this->getMandibular();
		$this->getNoseWidth();
		$this->getForehead();
		$this->getSingelEyeWidth();
	}
	/**
	 * 瞳孔
	 * @return number
	 */
	public function getEyeDistance(){
		
		//勾股定理算长度
		$distance=$this->GouGuFunction('left_eye_pupil','right_eye_pupil');
		$this->distance_eye=$distance;
		return $distance;
	}
/**
 * 脸颊宽度
 * @return number
 */	
	public function getCheckDistance(){
				
		$distance=$this->GouGuFunction('contour_left1', 'contour_right1');
		$this->width_check=$distance;
		return $distance;		
	}
/**
 * 额头宽度
 * @return number
 */	
   public function getForehead(){
   	$distance=$this->GouGuFunction('contour_left6','contour_right6');
   	$this->Forehead=$distance;
   	return $distance; 	
   }
   	
/**
 * 脸颊高度
 * @return number
 */	
	public function getCheckHeight(){
		$dataarr=$this->landmark;
		$height=abs($dataarr['contour_right5']['y']-$dataarr['contour_chin']['y'])*2;
		$this->height_check=$height;
		return $height;
	}
/**
 * 鼻子宽度
 * @return number
 */	
	public function getNoseWidth(){
		
		$width=$this->GouGuFunction('nose_left', 'nose_right');
		$this->width_nose=$width;
		return $width;		
	}
/**
 * 眼睛宽度
 * @return unknown
 */	
	public function getSingelEyeWidth(){
		$width=$this->GouGuFunction('right_eye_left_corner', 'right_eye_right_corner');
		$this->width_eye=$width;
		return $width;
	}
	
	public function CheckFace(){
		
	}
	
/**
 * 下颌宽度
 * @return unknown
 */	
	public function getMandibular(){
		$width=$this->GouGuFunction('contour_left5','contour_right5');
		$this->Mandibular=$width;
		return $width;
	}
/**
 * 判断是否是鹅蛋脸
 * @return boolean
 */	
	public function ifEggFace(){
		$cBe=floatval(floatval($this->width_check)/floatval($this->width_eye));

			return $cBe;

	}
/**
 * 判断是否是瓜子脸
 * @return boolean
 */	
	public function ifGuaZiFace(){
		$chBcw=floatval(floatval($this->height_check)/floatval($this->width_check) );
			return $chBcw;

	}
/**
 * 判断是否是巴掌脸
 * @return boolean
 */	
	public function ifBaZhangFace(){
		$nBse=floatval(floatval($this->width_nose) /floatval($this->width_eye) );
		$eBe=floatval($this->width_eye)/floatval($this->distance_eye);
		if($nBse>0.9 AND $nBse<1.1){
			if($eBe>0.9 AND $eBe<1.1){
				return $nBse;
			}
			else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}
	public function test(){
		$test=floatval($this->width_nose) ;
		return $test ;
	}
/**
 * 判断是否是方形脸
 * @return boolean
 */	
	public function ifSquareFace(){
		$mBc=floatval(floatval($this->width_check)/floatval($this->Mandibular) );
			return $mBc;
	}
	
/**
 * 判断是否是圆形脸
 * @return boolean
 */	
	public function ifCircleFace(){
		$hBw=floatval($this->height_check)/floatval($this->width_check);

			return $hBw;

	}
}