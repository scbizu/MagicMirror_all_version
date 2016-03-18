<?php
class Analyse{

	//脸部数据
	public $landmark;
	//允许误差范围
	public $deviation;
	//瞳孔距离
	private $distance_eye;
	//脸颊距离
	private $width_check;
	//脸颊高度
	private  $height_check;
	//鼻翼宽度
	private $width_nose;
	//眼睛宽度
	private $width_eye;

	public function __construct($facedata){
		$this->deviation=3.0;
		$this->landmark=$facedata;		
	}
/**
 * 勾股定理
 * @param unknown $width_L
 * @param unknown $width_R
 * @param unknown $height_L
 * @param unknown $height_R
 */	
	private function GouGuFunction($L_Point,$R_Point){
		$dataarr=$this->landmark;
		$distance=sqrt(pow(($dataarr[$R_Point]['x']-$dataarr[$L_Point]['x']),2)+pow(($dataarr[$R_Point]['y']-$dataarr[$L_Point]['y']),2));				
	}
	/**
	 * 瞳孔
	 * @return number
	 */
	public function getEyeDistance(){
		
		//勾股定理算长度
		$distance=$this->GouGuFunction('left_eye_pupli', 'right_eye_pupli');
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
 * 脸颊高度
 * @return number
 */	
	public function getCheckHeight(){
		$dataarr=$this->landmark;
		$height=($dataarr['contour_right5']['y']-$dataarr['contour_chin']['y'])*2;
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
}