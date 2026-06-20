<?php
class ModelExtensionModuleHoNewsletter extends Model {

	public function checkExists($email = ""){
		if(!empty($email)){
			$query = $this->db->query("SELECT `subscribe_id` FROM ".DB_PREFIX."ho_newsletter_subscribe WHERE `email`='".$this->db->escape($email)."'");
			if($query->num_rows > 0){
				return true;
			}
		}
		return false;
	}

	
	public function storeSubscribe($data = array()){
		if(!empty($data)){
			if(!$this->checkExists($data['email'])){
				$sql = "INSERT INTO ".DB_PREFIX."ho_newsletter_subscribe ( `";
				$tmp = array();
				$vals = array();
				foreach( $data as $key => $value ){
					$tmp[] = $key;
					$vals[]=$this->db->escape($value);
				}
				$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
				$this->db->query( $sql );
				return true;
			}
			
		}
		return false;
	}


}