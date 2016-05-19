<?php

require_once 'analyse.class.php';
require_once 'Db.class.php';
require_once 'app.class.php';
//工具处理类
class processor{
	
	//脸部数据
	//private  $landmark;
	//
	private $HvsW;
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
	//准确度算子
	private $firstStep;
	private $secondStep;	
	private $thirdStep;
		
	//准确度  
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
		$app=new app();
        $analyse=new Analyse($facedata);
		$this->HvsW=$app->getdw();
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
			'sl'=>$this->width_check*$app->getSlopro(),
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
		$res=array();
		array_push($res, $this->LineArray['fl']);
		array_push($res, $this->LineArray['sl']);
		array_push($res, $this->LineArray['tl']);
		sort($res);
		//第一步的特征值:1线领先于2线和3线的平均增量
		$this->firstStep=(($res[2]-$res[1])/$res[1]+($res[2]-$res[0])/$res[0])/2;
		return $finalline;
	}
	
/**
 * 第二步:比较脸型
 * @return string
 */	
	private function FacewidthVSheight(){
		
		$long=$this->Sanxian_longest();
		$wORh=($this->height_check>$this->width_check)?$this->height_check:$this->width_check;
		$this->secondStep=abs($this->height_check-$this->width_check)/$wORh;		
		switch ($long){
			case 'fl':
				self::$CLprocessRate+=$this->firstStep;
				self::$FLprocessRate+=$this->firstStep;
				self::$GZLprocessRate+=$this->firstStep;
				//第二步
				if($this->width_check *$this->HvsW <= $this->height_check){
					//第二特征值: 长宽的增量
					self::$CLprocessRate+=$this->secondStep;
					$status='fl_h';
				}else{
					//self::$FLprocessRate+=$this->secondStep;
					self::$GZLprocessRate+=$this->secondStep;
					$status='fl_w';
				}
				break;
			case 'sl':
				self::$CLprocessRate+=$this->firstStep;
				self::$EDprocessRate+=$this->firstStep;
				self::$YLprocessRate+=$this->firstStep;
				self::$FLprocessRate+=$this->firstStep;
				
				//
				if($this->width_check *$this->HvsW<= $this->height_check){
					self::$CLprocessRate+=$this->secondStep;
					$status='sl_h';
				}else{
					self::$EDprocessRate+=$this->secondStep;
					self::$YLprocessRate+=$this->secondStep;
					self::$FLprocessRate+=$this->secondStep;
					$status='sl_w';
				}	
				break;
			case 'tl':
				self::$CLprocessRate+=$this->firstStep;
				self::$CLprocessRate+=$this->firstStep;
				self::$FLprocessRate+=$this->firstStep;
				if($this->width_check<=$this->height_check){
					self::$CLprocessRate+=$this->secondStep;
					$status='tl_h';
					
				}else{
					self::$CLprocessRate+=$this->secondStep;
					self::$FLprocessRate+=$this->secondStep;
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
		//第三个特征值
		$dev=abs(abs($tmp[1]-$tmp[0])-abs($tmp[2]-$tmp[1]));
		//根据最大数 控制百分比
		$this->thirdStep+=$this->deviation;
		//判断斜率大小 
		//依据为:直线方程求导所得为k=0的水平直线 而曲线方程求导所得的K必定大于零
		if($dev<$this->deviation){
			
			//为线性下巴 重新计算第三特征值
			$this->thirdStep=(1/($this->thirdStep+$tmp[3]))/floatval(10);

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
		//TODO:It's just a joke
		self::$GZLprocessRate=$this->deviation-$this->thirdStep;
		$status=$this->FacewidthVSheight();
		switch ($status){
			case 'fl_h':

				self::$CLprocessRate+=$this->deviation;
				if(self::$CLprocessRate>=99){
					self::$CLprocessRate=0.98;
				}
				$type='CL';
				break;
			case 'fl_w':
				$fix=$this->lineFix();
				if($fix==='l_chin'){
					$type='GZL';
					//Joke again
					self::$GZLprocessRate+=$this->thirdStep;
				}else{
					$type='FL';
					self::$FLprocessRate+=$this->thirdStep;
				}				
				break;
			case 'sl_h':
				self::$CLprocessRate+=$this->deviation;
				$fix=$this->lineFix();
				if($fix==='ao_chin'){
					self::$FLprocessRate+=$this->thirdStep;
				}else if($fix==='l_chin'){
					self::$EDprocessRate+=$this->thirdStep;
				}else if($fix==='c_chin'){
					self::$YLprocessRate+=$this->thirdStep;
				}
				$type='CL';				
				break;
			case 'sl_w':
				$fix=$this->lineFix();
				if($fix==='ao_chin'){
					self::$FLprocessRate+=$this->thirdStep;
					$type='FL';
				}else if($fix==='l_chin'){
					self::$EDprocessRate+=$this->thirdStep;
					$type='ED';
				}else if($fix==='c_chin'){
					self::$YLprocessRate+=$this->thirdStep;
					$type='YL';
				}
				break;
			case 'tl_h':
				self::$CLprocessRate+=$this->deviation;
				if(self::$CLprocessRate>0.98){
					self::$CLprocessRate=0.98;
				}
				$type='CL';				
				break;
			case 'tl_w':
				$fix=$this->lineFix();
				if($fix==='l_chin'){
					self::$CLprocessRate+=$this->thirdStep;
					$type='CL';
				}else{
					self::$FLprocessRate+=$this->thirdStep;
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
	$resarr=array(
		'CL'=>self::$CLprocessRate,
		'FL'=>self::$FLprocessRate,
		'YL'=>self::$YLprocessRate,
		'ED'=>self::$EDprocessRate,
		'GZL'=>self::$GZLprocessRate,
	);
	return json_encode($resarr);
}		
/**
 * 计算脸型分数
 */
public function calScore($type){
		switch ($type){
			case 'CL':
				$score=self::$CLprocessRate*10000;
				break;
			case 'FL':
				$score=self::$FLprocessRate*10000;
				break;
			case 'YL':
				$score=self::$YLprocessRate*10000;
				break;
			case 'ED':
				$score=self::$EDprocessRate*10000;
				break;
			case 'GZL':
				$score=self::$GZLprocessRate*10000;
				break;
			default:
				break;	
		}
		return $score;
}

}
?>