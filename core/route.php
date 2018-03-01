<?php 
/**
 * route processing
 */
namespace core;
class route{
	static public $routes = null;

    static function start(){
    	$page = 1;
        if (self::$routes == null) {
            $url = explode('?', substr(strtolower($_SERVER['REQUEST_URI']), 1));
            $routes = explode('/', $url[0]);
            
            /*set sort parameter*/
            if(isset($_POST['sortBy']) || isset($_POST['sortDir'])){
            	if(isset($_POST['sortBy'])){
            		user::setCookie("sortBy",$_POST['sortBy']);
            	}
            	if(isset($_POST['sortDir']) && in_array($_POST['sortDir'], array('asc','desc'))){
            		user::setCookie("sortDir",$_POST['sortDir']);
            	}elseif($_POST['sortDir']==''){
            		user::setCookie("sortDir",'asc');
            	}
            }
            
            /*reset last slash(/)*/
            if(count($routes)>1 && end($routes)==''){
            	$key = key($routes);
            	unset($routes[$key]);
            	header("Location: /".implode('/', $routes));
            }elseif(current($routes)=='index.php'){
            	$key = key($routes);
            	unset($routes[$key]);
            	header("Location: /".implode('/', $routes));
            }
            reset($routes);
            $length = count($routes);
            for($i = 0; $i < $length; ++$i) {
            	if(current($routes)=='page'){//set pagination
            		$page = next($routes);
            		break;
            	}
            	self::$routes[] = current($routes);
            	next($routes);
            }
        }
        $controller_name = 'main';
        $action_name = 'index';
        $action_id = false;
        if (!empty(self::$routes[0])) {
            $controller_name = self::$routes[0];
        }
        if (!empty(self::$routes[1])) {
            $action_name = self::$routes[1];
        }     
        if (!empty(self::$routes[2])) {
        	$action_id = self::$routes[2];
        }

        $controller_class = '\\controllers\\'.$controller_name;
        $action_name = 'action_'.$action_name;
        if(class_exists($controller_class)){
        	$controller = new $controller_class($page, self::$routes, $controller_name);
	        if(method_exists($controller, $action_name)) {
	        	if($action_id!=false){
	        		$controller->$action_name($action_id);
	        	}else{
	            	$controller->$action_name();
	        	}
	        }
	        else {
	            route::Error404();
	        }
	    }elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/../views/".implode('/', self::$routes).'/index.html')){
	    	$controller_name = implode('/', self::$routes);
	    	$controller = new \controllers\controller($page, self::$routes, $controller_name);
	    	$controller->action_index();
        }else{
        	route::Error404();
        }
    }

    public static function Error404(){
    	$twig = new twig(new user());
    	header('HTTP/1.1 404 Not Found');
    	header("Status: 404 Not Found");
    	$twig->renderTwig('404.html', array('title' => 'Страница не найдена'));
    }
}
?>