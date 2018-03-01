<?php
/**
 * base class for controllers
 */
namespace controllers;
class controller{
	protected static $twig;
	protected static $connect;
	protected static $user;
	protected static $err;
	protected static $successes;
	protected static $data;
	protected static $page;
	protected static $routes;
	protected static $name;
	protected static $onlyAdmin = false;
	
	public function __construct($page = 1, $routes = array(), $name){
		self::$connect = new \core\connectMysqli();
		self::setUser(new \core\user());
		self::$twig = new \core\twig(self::$user);
		self::$page = $page;
		self::$routes = $routes;
		self::$name = $name;
	}
	
	/**
	 * set property user
	 * 
	 * @param array $user
	 */
	protected static function setUser($user){
		self::$user = $user;
	}
	
	/**
	 * show views
	 * 
	 * @param string $page
	 * @param string $title
	 * @param array $data
	 */
	protected function showView($page='index.html', $title='Home page', $data=array()){
		if(static::$onlyAdmin && (!self::$user->fields['authorize'] || !self::$user->fields['admin'])){
			echo self::$twig->renderTwig('error.html', array('title' => "Access denied!",
					'errors' => array("Access denied!"),
			));
		}else{
			echo self::$twig->renderTwig($page, array('title' => $title,
					'data' => $data,
					'errors' => self::$err,
					'successes' => self::$successes
			));
		}
	}
	
	/**
	 * action for user list (users/index.html)
	 */
	public function action_index(){
		$users = array();
		$results = \core\sqlOrm::getByField(self::$connect, self::$name, self::$page, self::$routes);
		if($results){
			$users = $results;
		}
		$this->showView(self::$name.'/index.html', "Page ".self::$name, $users);
	}
}?>