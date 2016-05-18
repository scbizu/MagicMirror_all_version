<?php

use Qiniu\json_decode;
require_once 'Db.class.php';
//APP 功能逻辑类
class app {

	private $db;
		
	public function __construct() {
		
		$this->db=new DB();
			
	}
/**
 * 获取全部用户信息
 * @return Ambigous <mixed, NULL, multitype:>
 */	
	public function Alluser(){
		$lastface=$this->db->query("SELECT * FROM mm_main ");
		$res=$lastface;
		return $res;
	}

/**
 * 获取当前用户数量(不加读锁)
 * @return integer
 */	
	public function Usercount(){
		$count=$this->db->query("SELECT COUNT(*)  AS count FROM mm_main");
		
		return $count[0]['count'];
	}

	/**
	 * 根据脸型获取did
	 * @TODO:这里的模型应该是1对多的模型  待完善
	 * @param $facetype
	 * @return mixed
	 */
	public function fetchDid($facetype){
			$data=$this->db->query("SELECT did FROM mm_draw WHERE facetype=:facetype",array('facetype'=>$facetype));
			return $data[0]['did'];
	}
		/**
	 * 检查用户之前的脸型识别结果
	 * @param string $openid
	 */
	public function checkFace($openid){
		$lastface=$this->db->query("SELECT * FROM mm_main WHERE openid=:oid",array('oid'=>$openid));
		$res=$lastface;
		return $res;
	}
	
	/**
	 * 备份用户脸型到数据库
	 * @param string $table
	 * @param string $openid
	 * @param string $data
	 * @param string $type
	 * @return boolean
	 */
	public function saveData($url,$score,$table,$openid,$data,$type){
		$faceid=substr(md5(time()),1,10);
		$row=$this->db->query("INSERT INTO ".$table."(openid,faceid,facedata,facetype,faceurl,score)  VALUES(:oid,:fid,:data,:type,:faceurl,:score)",array('oid'=>$openid,'fid'=>$faceid,'data'=>$data,'type'=>$type,'faceurl'=>$url,'score'=>$score));
		if($row>0){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	/**
	 * 更新用户脸型到数据库
	 * @param string $table
	 * @param string $openid
	 * @param string $data
	 * @param string $type
	 * @return Ambigous <mixed, NULL, multitype:>
	 */
	public function updateSet($url,$faceid,$openid,$data,$type,$score){
		$row=$this->db->query("UPDATE mm_main SET faceurl=:url,facedata=:data,facetype=:type,score=:score WHERE openid=:oid AND faceid=:fid",array('url'=>$url,'fid'=>$faceid,'data'=>$data,'type'=>$type,'oid'=>$openid,'score'=>$score,'fid'=>$faceid));
		return $row;
	}

/**
 * 化妆后的拍照
 * @param string $url
 * @param string $faceid
 * @param string $openid
 * @param string $data
 * @param string $type
 * @param integer $score
 * @return integer
 */	
	public function updateSet_after($url,$faceid,$openid,$data,$type,$score){
		$row=$this->db->query("UPDATE mm_main SET after_faceurl=:url,after_facedata=:data,after_facetype=:type,after_score=:score,status=:sta WHERE openid=:oid AND faceid=:fid",array('url'=>$url,'fid'=>$faceid,'data'=>$data,'type'=>$type,'oid'=>$openid,'score'=>$score,'fid'=>$faceid,'sta'=>1));
		return $row;
	}	
	/**
	 * 获取PK所需信息
	 * @param string $faceid
	 * @return Ambigous <mixed, NULL, multitype:>
	 */
	public function getScore($faceid){
		$data=$this->db->query("SELECT faceurl,score,after_faceurl,after_score FROM mm_main WHERE faceid=:fid",array('fid'=>$faceid));
		return $data;
	}	
	
	
	/**
	 * 只更新脸型的数据
	 * @param string $table
	 * @param string $openid
	 * @param string $data
	 * @return Ambigous <mixed, NULL, multitype:>
	 */
	public function updateOnlyfacedata($table,$openid,$data){
		$row=$this->db->query("UPDATE".$table."SET facedata=:fd WHERE openid=:oid",array('fd'=>$data,'oid'=>$openid));
		return $row;
		
	}
	/**
	 * 返回当前用户的脸型数据
	 * @param string $table
	 * @param string $openid
	 * @return array
	 */
	public function fetchUserSet($table,$openid){
		$row=$this->db->query("SELECT facedata FROM".$table."WHERE openid=:oid",array('oid'=>$openid));
		$facedata=json_decode($row['facedata']);
		return $facedata;
	}
	

	
	/**
	 * 返回最大的键值对
	 * @param array $array
	 * @return array $res
	 */
	public function MostSuitable($array){
		$res=array();
		$max=floatval(0);
		foreach ($array as $k=>$v){
			if($v>=$max){
				$res['key']=$k;
				$res['value']=$v;
				$max=$v;
			}
		}
		return $res;
	}
/**
 * 判断当前脸型的系统匹配率是否为100%
 * @param string $facetype
 * @param integer $did
 * @return boolean
 */
	public function checkWholeIfMax($facetype,$did){
		$row=$this->db->query("SELECT type_acc FROM mm_sys_accuracy WHERE facetype=:ft AND did=:did",array('ft'=>$facetype,'did'=>$did));
		if($row['type_acc']==floatval(1)){
			return FALSE;	
		}else{
			return $row['type_acc'];
		}
	}
	/**
	 * 更新整体模型
	 * @param integer $did
	 * @param string $facetype
	 * @param float $data
	 * @return integer
	 */
	public function updateWholeACC($did,$facetype,$data){
		$row=$this->db->query("UPDATE mm_sys_accuracy SET data=:data WHERE facetype=:ty AND did=:did",array('data'=>$data,'ty'=>$facetype,'did'=>$did));
		return $row;
	}

	/**
	 * 获取商品List
	 * @param unknown $did
	 * @param unknown $facestep
	 * @return string
	 */
	public function GetGoodsList($did,$facestep){
		$res=array();
		$data=$this->db->query("SELECT * FROM mm_goods WHERE did=:did AND facestep=:fs ORDER BY goodsid DESC",array('did'=>$did,'fs'=>$facestep));
		if(empty($data)){
			return 'null';
		}else{
			foreach ($data as $k=>$v){
				$res[$k]['goodsname']=$v['goodsname'];
				$res[$k]['shopname']=$v['shopname'];
				$res[$k]['price']=$v['price'];
				$res[$k]['imglink']=$v['link'];
			}
			return json_encode($res);
		}
	}
}

?>