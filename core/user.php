<?php
/*
 * current user
 * 
 * properties:
 * public $fields;            
 * static public $int = 3600; // cookie lifetime
 */
namespace core;
class user{
	public $fields;
	static public $int = 3600;
	public function __construct(){
		if(isset($_COOKIE['hash']) && !empty($_COOKIE['hash']) && isset($_COOKIE['login']) && !empty($_COOKIE['login'])){
			$connect = new \core\connectMysqli();
			$data = \core\sqlOrm::getByField($connect, 'users', 1, '', '*', array('login'=>$_COOKIE['login'], 'hash'=>$_COOKIE['hash']));
			$this->fields = $data['items'][0];
			$this->fields['authorize'] = true;
			self::setCookie("hash",$_COOKIE['hash'],time()+self::$int);
			self::setCookie("login",$_COOKIE['login'],time()+self::$int);
		}else{
			$this->fields = array('authorize'=>false);
		}
	}
	
	static public function setCookie($name, $value = '', $time=false){	
		if(!$time){
			$time = time()+self::$int;
		}
		setcookie($name,$value,$time,'/',false);
		$_COOKIE[$name] = $value;
	}
}
?>
