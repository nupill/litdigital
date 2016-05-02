<?php
require_once(dirname(__FILE__) . "/../config/general.php");

function mysqlx_real_escape_string( $unescaped_string) {
	global $con;
	return mysqli_real_escape_string($con, $unescaped_string);
	
}

function mysqlx_result($res,$row=0,$col=0){
	$numrows = mysqli_num_rows($res);
	if ($numrows && $row <= ($numrows-1) && $row >=0){
		mysqli_data_seek($res,$row);
		$resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
		if (isset($resrow[$col])){
			return $resrow[$col];
		}
	}
	return false;
}

?>