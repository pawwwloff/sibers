<?php
/**
 * orm class
 * 
 * metods: 
 * static function update($connect=false, $table=false, $id=0, $data)
 * static function add($connect=false, $table=false, $data)
 * static function delete($connect=false, $table=false, $id=0)
 * static function getByField($connect=false, $table=false, $page=0, $field = '*', $data=array(), $limit=4)
 * private function getPagination($connect, $table, $route, $page, $sql, $limit)
 */
namespace core;
class sqlOrm{
	/**
	 * UPDATE
	 * 
	 * @param string $connect
	 * @param string $table
	 * @param number $id
	 * @param array $data
	 * @return boolean|string
	 */
	static function update($connect=false, $table=false, $id=0, $data){
		if($table!=false && $connect!=false && $id>0){
			$sql = "UPDATE ".$table." SET";
			$dataCount = count($data);
			$i = 0;
			foreach ($data as $key=>$value){
				$i++;
				$sql .= " $key='".$connect->real_escape_string($value)."'";
				if ($i!=$dataCount) {
					$sql .= ",";
				}
			}
			$sql .= " WHERE ID=".$id."";

			$results = $connect->query($sql, false);
			if($results === TRUE){
				return TRUE;
			}else{
				return $connect->error;
			}
		}
	}
	
	/** 
	 * INSERT INTO
	 * 
	 * @param string $connect
	 * @param string $table
	 * @param unknown $data
	 * @return boolean|string
	 */
	static function add($connect=false, $table=false, $data){
		if($table!=false && $connect!=false){
			$sql = "INSERT INTO ".$table." SET";
			$dataCount = count($data);
			$i = 0;
			foreach ($data as $key=>$value){
				$i++;
				$sql .= " $key='".$connect->real_escape_string($value)."'";
				if ($i!=$dataCount) {
					$sql .= ",";
				}
			}
			$results = $connect->query($sql, false);
			if($results === TRUE){
				return TRUE;
			}else{
				return $connect->error;
			}
		}
	}
	
	/**
	 * DELETE FROM
	 * 
	 * @param string $connect
	 * @param string $table
	 * @param number $id
	 * @return boolean|string
	 */
	static function delete($connect=false, $table=false, $id=0){
		if($table!=false && $connect!=false && $id>0){
			$sql = "DELETE FROM ".$table." WHERE ID='".$id."'";
			$results = $connect->query($sql, false);
			if($results === TRUE){
				return TRUE;
			}else{
				return $connect->error;
			}
		}
	}
	
	/**
	 * SELECT FROM
	 * 
	 * @param string $connect
	 * @param string $table
	 * @param number $page
	 * @param string $field
	 * @param array $data
	 * @param number $limit
	 * @return array|string
	 */
	static function getByField($connect=false, $table=false, $page=1, $route='/', $field = '*', $data=array(), $limit=2){
		$startPage = $page*$limit-$limit;
		$arReturn = array();
		$sortBy = isset($_COOKIE['sortBy'])&&$_COOKIE['sortBy']!=''?$_COOKIE['sortBy']:'ID';
		$sortDir = isset($_COOKIE['sortDir'])&&$_COOKIE['sortDir']!=''?$_COOKIE['sortDir']:'asc';
		$arReturn['sort']['by'] = $sortBy;
		$arReturn['sort']['dir'] = $sortDir;
		$arReturn['sort']['link'] = $sortDir=='asc'?'desc':'asc';
		if($table!=false && $connect!=false){
			$sqlStart = "SELECT ".$field." FROM ".$table;
			$sql = '';
			$dataCount = count($data);
			$i = 0;
			if($dataCount>0){
				$sql .= " WHERE";
				foreach ($data as $key=>$value){
					$i++;
					$sql .= " $key='".$connect->real_escape_string($value)."'";
					if ($i!=$dataCount) {
						$sql .= " &&";
					}
				}
			}
			$sqlSort = " ORDER BY $sortBy $sortDir";
			$sqlEnd = " LIMIT ".$startPage.", ".$limit;

			$results = $connect->query($sqlStart.$sql.$sqlSort.$sqlEnd, false);
			if($results){
				while($item = $results->fetch_array(MYSQLI_ASSOC)){	
					$arReturn['items'][] = $item;
				}
				$arReturn['pagination'] = self::getPagination($connect, $table, $route, $page, $sql, $limit);
				return $arReturn;
			}else{
				return $connect->error;
			}
		}
	}
	/**
	 * 
	 * @param $connect
	 * @param $table
	 * @param $route
	 * @param $page
	 * @param $sql
	 * @param $limit
	 * @return array $return
	 */
	private function getPagination($connect, $table, $route, $page, $sql, $limit){
		$return= array();
		$sql = "SELECT COUNT(*) FROM ".$table.$sql;
		$counts = $connect->query($sql, false);
		$total = $counts->fetch_row();
		$return['total'] = $total[0];
		$return['totalPages'] = ceil($return['total']/$limit);
		$return['startPage'] = $page;
		$return['limit'] = $limit;
		if($route){
			if($page<=1){
				$return['currentUrl'] = $return['pages']['back'] = "/".implode('/', $route);
				$return['pages']['link']['1']['active'] = true;
			}
			$return['pages']['link']['1']['url'] = "/".implode('/', $route);
			$return['pages']['link']['1']['name'] = '1';
			for($i=2; $i<=$return['totalPages']; $i++){
				if($i==$page){
					$return['pages']['link'][$i]['active'] = true;
					$return['currentUrl'] = "/".implode('/', $route).'/page/'.$i;
					$return['pages']['back'] = "/".implode('/', $route).'/page/'.$i-1;
				}
				$return['pages']['link'][$i]['url'] = "/".implode('/', $route).'/page/'.$i;
				$return['pages']['link'][$i]['name'] = $i;
			}
		}
		if($page>$return['totalPages']){
			$endElem = end($return['pages']['link']);
			header("Location: ".$endElem['url']);
		}
		return $return;
	}
}
?>