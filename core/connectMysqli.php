<?php 
/**
 * class for settings database
 */
namespace core;
class connectMysqli extends \mysqli {
	private $DBHost = "localhost";
	private $DBLogin = "****";
	private $DBPassword = "****";
	private $DBName = "****";
	public function __construct() {
		parent::__construct($this->DBHost, $this->DBLogin, $this->DBPassword, $this->DBName);
		if (mysqli_connect_error()) {
			die('Ошибка подключения (' . mysqli_connect_errno() . ') '
					. mysqli_connect_error());
		}
	}
}
?>
