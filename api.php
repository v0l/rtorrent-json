<?php
	/* 
		rTorrent-JSON https://github.com/KieranH92/rtorrent-json
		Copyright (C) 2014 Kieran Harkin

		This program is free software: you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation, either version 3 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
	 */
	 
	header("Content-Type: application/json");
	require_once("func.php");
	$rpc_connect = "unix:///var/run/rtorrent.sock";
	
	if(isset($_GET["cmd"])){
		switch($_GET["cmd"]){
			case "list":{
				echo json_encode(get_full_list("main"));
				break;
			}
			case "getFiles":{
				echo json_encode(get_file_list($_GET["hash"]));
				break;
			}
			default:{
				echo json_encode(["msg" => "You suck!"]);
			}
		}
	}else{
		echo json_encode(["msg" => "You suck!"]);
	}
?>