<?php

require_once 'analyse.class.php';
require_once 'Db.class.php';
//工具处理类
class processor{
	
	//脸部数据
	private  $landmark;
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
	//下颌宽度
	private $Mandibular;
	//额头宽度
	private $Forehead;
	//三线数组
	private $LineArray=array(
		
	);
	//准确度  也即过程分?
	public static $CLprocessRate=0;
	public static $GZLprocessRate=0;
	public static $FLprocessRate=0;
	public static $YLprocessRate=0;	
	public static $EDprocessRate=0;		
	//拟合数组
	public $fixarray=array();
	//容许误差值
	public $deviation;
	public $de;
	
	/**
	 * 构造函数
	 * @param array $facedata
	 */
	function __construct($facedata) {
        $analyse=new Analyse($facedata);
        $analyse->FaceInit();
        $this->distance_eye=$analyse->distance_eye;        
		$this->width_check=$analyse->width_check;
		$this->height_check=$analyse->height_check;
		$this->width_nose=$analyse->width_nose;
		$this->Mandibular=$analyse->Mandibular;
		$this->Forehead=$analyse->Forehead;
		$this->deviation=$analyse->kdeviation;
		$this->de=$analyse->deviation;
		$this->LineArray=array(
			'fl'=>$this->Forehead,
			'sl'=>$this->width_check,
			'tl'=>$this->Mandibular	
		);
		$this->fixarray=$analyse->chinK_array();
	}
/**
 * 比较三线长度
 * @return string
 */	
	private function Sanxian_longest(){
		$twoline=($this->LineArray['fl']>$this->LineArray['sl'])?'fl':'sl';
		$v_twoline=($this->LineArray['fl']>$this->LineArray['sl'])?$this->LineArray['fl']:$this->LineArray['sl'];
		$finalline=($v_twoline>$this->LineArray['tl'])?$twoline:'tl';
		return $finalline;
	}
	
/**
 * 第二步:比较脸型
 * @return string
 */	
	private function FacewidthVSheight(){
		
		$long=$this->Sanxian_longest();
		
		switch ($long){
			case 'fl':
				self::$CLprocessRate+=0.33;
				self::$FLprocessRate+=0.33;
				self::$GZLprocessRate+=0.33;
				if($this->width_check <= $this->height_check){
					self::$CLprocessRate+=0.33;
					
					$status='fl_h';
				}else{
					self::$FLprocessRate+=0.33;
					self::$GZLprocessRate+=0.33;
					$status='fl_w';
				}
				break;
			case 'sl':
				self::$CLprocessRate+=0.33;
				self::$EDprocessRate+=0.33;
				self::$YLprocessRate+=0.33;
				self::$FLprocessRate+=0.33;
				if($this->width_check <= $this->height_check){
					self::$CLprocessRate+=0.33;
					$status='sl_h';
				}else{
					self::$EDprocessRate+=0.33;
					self::$YLprocessRate+=0.33;
					self::$FLprocessRate+=0.33;
					$status='sl_w';
				}	
				break;
			case 'tl':
				self::$CLprocessRate+=0.33;
				self::$CLprocessRate+=0.33;
				self::$FLprocessRate+=0.33;
				if($this->width_check<=$this->height_check){
					self::$CLprocessRate+=0.33;
					$status='tl_h';
					
				}else{
					self::$CLprocessRate+=0.33;
					self::$FLprocessRate+=0.33;
					$status='tl_w';
				}
				break;
			default:
				break;	
		}
		return $status;
	}
/**
 * 第三步的处理斜率函数
 * @return string
 */	
	public function lineFix(){
		$tmp=$this->fixarray;
		//判断斜率大小 
		//依据为:直线方程求导所得为k=0的水平直线 而曲线方程求导所得的K必定大于零
		if(abs($tmp[1]-$tmp[0])-abs($tmp[2]-$tmp[1])<$this->deviation){
			if($tmp[3]<$this->deviation){
				return 'ao_chin';
			}else{
				return 'l_chin';
			}
		}else{
			return 'c_chin';
		}
	}
/**
 *三步处理函数的合并逻辑
 * @return string
 */	
	public function finalJG(){
		$status=$this->FacewidthVSheight();
		switch ($status){
			case 'fl_h':
				//强行为了脸型匹配度
				self::$CLprocessRate+=0.33;
				$type='CL';
				break;
			case 'fl_w':
				$fix=$this->lineFix();
				if($fix==='l_chin'){
					$type='GZL';
					self::$GZLprocessRate+=0.33;
				}else{
					$type='FL';
					self::$FLprocessRate+=0.33;
				}				
				break;
			case 'sl_h':
				self::$CLprocessRate+=0.33;
				$type='CL';				
				break;
			case 'sl_w':
				$fix=$this->lineFix();
				if($fix==='ao_chin'){
					self::$FLprocessRate+=0.33;
					$type='FL';
				}else if($fix==='l_chin'){
					self::$EDprocessRate+=0.33;
					$type='ED';
				}else if($fix==='c_chin'){
					self::$YLprocessRate+=0.33;
					$type='YL';
				}
				break;
			case 'tl_h':
				self::$CLprocessRate+=0.33;
				$type='CL';				
				break;
			case 'tl_w':
				$fix=$this->lineFix();
				if($fix==='l_chin'){
					self::$CLprocessRate+=0.33;
					$type='CL';
				}else{
					self::$FLprocessRate+=0.33;
					$type='FL';
				}
			default:
				break;
		}
		return $type;
	}

/**
 * 返回总和JSON数据
 * @return string
 */	
public function do2JsonData(){
	$resarr=array();
	$resarr['CL']=self::$CLprocessRate;
	$resarr['FL']=self::$FLprocessRate;
	$resarr['YL']=self::$YLprocessRate;
	$resarr['ED']=self::$EDprocessRate;
	$resarr['GZL']=self::$GZLprocessRate;
	return json_encode($resarr);
}		

}
?>