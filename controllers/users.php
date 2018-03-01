<?php
/**
 * Users controller
 * 
 * metods:
 * public function action_add()
 * public function action_edit($id)
 * public function action_delete($id)
 * public function action_login()
 * public function action_index()
 * private function loginUser($user)
 * */
namespace controllers;
class users extends controller{
	/**
	 * action for register new user (users/add.html)
	 */
	protected static $onlyAdmin = true;
	public function action_add(){
		self::$onlyAdmin = false;
		if(isset($_POST['action'])){
			self::$data = array(
					'login'         =>	isset($_POST['login'])?$_POST['login']:'',
					'password'      =>	isset($_POST['password'])?$_POST['password']:'',
					'first_name'    =>	isset($_POST['first_name'])?$_POST['first_name']:'',
					'second_name'   =>	isset($_POST['second_name'])?$_POST['second_name']:'',
					'last_name'     =>	isset($_POST['last_name'])?$_POST['last_name']:'',
					'date_birthday' =>	isset($_POST['date_birthday'])?$_POST['date_birthday']:'',
			);
			$reqpassword = isset($_POST['req_password'])?$_POST['req_password']:'';
			if(!preg_match("/^[a-zA-Z0-9]+$/",self::$data['login'])){
				self::$err[] = "Login can consist only of letters of the English alphabet and numbers";
			}
			if(strlen(self::$data['login']) < 3 or strlen(self::$data['login']) > 30){
				self::$err[] = "Login must be at least 3 characters and not more than 30";
			}
			if(strlen(self::$data['password']) < 6){
				self::$err[] = "Password must be at least 6 characters";
			}
			if(self::$data['password'] != $reqpassword){
				self::$err[] = "Passwords do not match";
			}
			
			# check for the existence of the user
			if(count(self::$err) == 0){
				$strSql = "SELECT id FROM users WHERE login='".self::$connect->real_escape_string($data['login'])."'";
				$results = self::$connect->query($strSql, false);
				if($results->num_rows > 0){
					self::$err[] = "Login is busy";
				}
			}
			if(count(self::$err) == 0){
				# remove spaces and encryption
				self::$data['password'] = md5(md5(self::$data['password']));
				$results = \core\sqlOrm::add(self::$connect, 'users', self::$data);
				if($results === TRUE){
					self::$successes[] = "User added";
				}else{
					self::$err[] = self::$connect->error;
				}
				self::$data = array();
			}
		}
		$this->showView('users/add.html', 'Registration', self::$data);
	}
	
	/**
	 * action for edit user by ID (users/edit.html)
	 * 
	 * @param int $id
	 */
	public function action_edit($id){
		if(self::$user->fields['authorize']!=true){
			self::$err[] = "Access denied";
			$this->showView('users/error.html', 'Access denied!');
			exit();
		}elseif(self::$user->fields['admin']==false && self::$user->fields['ID']!=$id){
			self::$err[] = "Access denied";
			$this->showView('users/error.html', 'Access denied!');
			exit();
		}
		$results = \core\sqlOrm::getByField(self::$connect, 'users', 1, self::$routes, '*', array('ID'=>$id));
		if(count($results['items']) <= 0){
			self::$err[] = "User with this ID does not exist";
			$this->showView('users/error.html', 'User with this ID does not exist!');
			exit();
		}elseif(!isset($_POST['action'])){
			if($user = $results['items'][0]){
				self::$data = array(
						'ID' =>	$user['ID'],
						'login' =>	$user['login'],
						'first_name' =>	$user['first_name'],
						'second_name' =>	$user['second_name'],
						'last_name' =>	$user['last_name'],
						'date_birthday' =>	$user['date_birthday'],
						'admin' =>	$user['admin'],
				);
			}
		}elseif(isset($_POST['action'])){
			self::$data = array(
				'login' =>	isset($_POST['login'])?$_POST['login']:'',
				'password' =>	isset($_POST['password'])?$_POST['password']:'',
				'first_name' =>	isset($_POST['first_name'])?$_POST['first_name']:'',
				'second_name' =>	isset($_POST['second_name'])?$_POST['second_name']:'',
				'last_name' =>	isset($_POST['last_name'])?$_POST['last_name']:'',
				'date_birthday' =>	isset($_POST['date_birthday'])?$_POST['date_birthday']:'',
				'admin' =>	isset($_POST['admin'])?$_POST['admin']:'',
			);
			$reqpassword = isset($_POST['req_password'])?$_POST['req_password']:'';
			if(!preg_match("/^[a-zA-Z0-9]+$/",self::$data['login'])){
				self::$err[] = "Login can consist only of letters of the English alphabet and numbers";
			}
			if(strlen(self::$data['login']) < 3 or strlen(self::$data['login']) > 30){
				self::$err[] = "Login must be at least 3 characters and not more than 30";
			}
			if(strlen(self::$data['password']) > 0 && strlen(self::$data['password']) < 6){
				self::$err[] = "Password must be at least 6 characters";
			}
			if(self::$data['password'] != $reqpassword){
				self::$err[] = "Passwords do not match";
			}
			if(count(self::$err) == 0){
				$results = \core\sqlOrm::getByField(self::$connect, 'users', 1, self::$routes, '*', array('!ID'=>$id, 'login'=>self::$data['login']));
				if(isset($results['items']) && count($results['items']) > 0){
					self::$err[] = "Login is busy";
				}
			}
			if(count(self::$err) == 0){
				if(strlen(self::$data['password'])>0){
					self::$data['password'] = md5(md5(self::$data['password']));
				}else{
					unset(self::$data['password']);
				}
				$results = \core\sqlOrm::update(self::$connect, 'users', $id, self::$data);
				if($results === TRUE){
					self::$successes[] = "User changed";
				}else{
					self::$err[] = $results;
				}
				unset(self::$data['password']);
			}
		}
		self::$data['edit_id'] = $id;
		$this->showView('users/edit.html', 'Edit users', self::$data);
	}
	
	/**
	 * action for delete user (users/delete.html)
	 * 
	 * @param int $id
	 */
	public function action_delete($id){
		if(self::$user->fields['authorize']==true && self::$user->fields['admin']!=false && $id!=self::$user->fields['ID']){
			$results = \core\sqlOrm::delete(self::$connect, 'users', $id);
			if($results === TRUE){
				self::$successes[] = "User deleted";
			}else{
				self::$err[] = $results;
			}
		}elseif(self::$user->fields['authorize']==true && $id==self::$user->fields['ID']){
			self::$err[] = "You can not delete your account";
		}else{
			self::$err[] = "Access denied";
		}
		$this->showView('users/error.html', 'Error');
	}
	
	/**
	 * action for login user (users/login.html)
	 */
	public function action_login(){
		self::$onlyAdmin = false;
		if(self::$user->fields['authorize']==true){
			self::$successes[] = 'You are already authorized!';
		}else{
			if(isset($_POST['action'])){
				self::$data = array(
						'login' =>	isset($_POST['login'])?$_POST['login']:'',
						'password' =>	isset($_POST['password'])?$_POST['password']:'',
				);
				if(strlen(self::$data['login'])<=0){
					self::$err[] = "Enter login";
				}
				if(strlen(self::$data['password'])<=0){
					self::$err[] = "Enter password";
				}
				if(count(self::$err) == 0){
					$results = \core\sqlOrm::getByField(self::$connect, 'users', 1, self::$routes, '*', array('login'=>self::$data['login'], 'password'=>md5(md5(self::$data['password']))));
					if(count($results['items'])==0){
						self::$err[] = "Wrong login or password";
					}else{
						$login = $this->loginUser($results['items'][0]);
					}
				}
			}
		}
		$this->showView('users/login.html', 'Login', self::$data);
	}
	
	/**
	 * action for user list (users/index.html)
	 */
	public function action_index(){
		$users = array();
		$results = \core\sqlOrm::getByField(self::$connect, 'users', self::$page, self::$routes);
		if($results){
			$users = $results;
		}
		$this->showView('users/index.html', 'List of users', $users);
	}
	
	/**
	 * user exit (users/exit.html)
	 */
	public function action_exit(){
		self::$onlyAdmin = false;
		\core\user::setCookie("hash",'',0);
		\core\user::setCookie("login",'',0);
		header('Location: /');
	}
	
	/**
	 * function for lodin user
	 * 
	 * @param array $user
	 * @return array $err
	 */
	private function loginUser($user){
		//$int = self::$user::$int;
		$err['success'] = $err['error'] = '';
		$hash =  md5($_SERVER['REMOTE_ADDR'].$user['password']);
		\core\user::setCookie("hash",$hash);
		\core\user::setCookie("login",$user['login'],0);
		/*setcookie("hash",$hash,time()+$int);
		setcookie("login",$user['login'],time()+$int);*/
		$results = \core\sqlOrm::update(self::$connect, 'users', $user['ID'], array('hash'=>$hash));
		if($results === TRUE){
			$err['success'] = "Вы авторизованы";
			header('Location: /users');
		}else{
			$err['error'] = $results;
		}
		return $err;
	}
}
?>