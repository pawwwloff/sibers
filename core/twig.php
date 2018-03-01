<?php
namespace core;
class twig{
	protected static $twig;
	protected static $user;
	private $params = array(
			//'cache' => $_SERVER['DOCUMENT_ROOT'].'/../cache',
	);
	public function __construct($user = array()){
		self::$user = $user;
		$loader = new \Twig_Loader_Filesystem($_SERVER['DOCUMENT_ROOT'].'/../views');
		self::$twig = new \Twig_Environment($loader, $this->params);
	}
	
	/**
	 * function for render twig templates
	 * 
	 * @param string $page
	 * @param array $array
	 */
	public function renderTwig($page='index.html', $array=array()){
		$array['curUser'] = self::$user->fields;
		echo self::$twig->render($page, $array);
	}
}?>