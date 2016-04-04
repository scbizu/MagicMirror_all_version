<?php

require_once 'Db.class.php';
//APP 功能逻辑类
class app {

	private $db;
		
	public function __construct() {
		
		$this->db=new DB();
			
	}
	
	/**
	 * 检查用户之前的脸型识别结果
	 * @param string $openid
	 */
	public function checkFace($openid){
		$lastface=$this->db->query("SELECT * FROM mm_main WHERE openid=:oid",array('oid'=>$openid));
		return $lastface;
	}
	
	/**
	 * 备份用户脸型到数据库
	 * @param string $table
	 * @param string $openid
	 * @param string $data
	 * @param string $type
	 * @return boolean
	 */
	public function saveData($table,$openid,$data,$type){
		$faceid=substr(md5(time()),1,10);
		$row=$this->db->query("INSERT INTO ".$table."(openid,faceid,facedata,facetype)  VALUES(:oid,:fid,:data,:type)",array('oid'=>$openid,'fid'=>$faceid,'data'=>$data,'type'=>$type));
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
	public function updateSet($table,$openid,$data,$type){
		$db=new DB();
		$faceid=substr(md5(time()),1,10);
		$row=$this->db->query("UPDATE".$table."SET faceid=:fid,facedate=:data,facetype=:type WHERE openid=:oid",array('fid'=>$faceid,'data'=>$data,'type'=>$type,'oid'=>$openid));
		return $row;
	}
}

?>