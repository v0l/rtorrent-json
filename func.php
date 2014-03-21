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
 
function scgi_send($socket, $content) {
 $headers = "CONTENT_LENGTH" . "\0" . strlen($content) . "\0" .
    "SCGI" . "\0" . "1" . "\0";

  $ns = strlen($headers).":".$headers.",";
  $encoded = $ns . $content;
  fwrite($socket, $encoded);
  fflush($socket);


  while ($f = fgets($socket)) {
    if(trim($f) == "") { break; }
  }

  $result = stream_get_contents($socket); // TODO error handling
  return $result;
}
function do_xmlrpc($request) {
	global $rpc_connect;
	if(substr ($rpc_connect, 0, strlen('http://')) == 'http://') {
		$context = stream_context_create(array('http' => array('method' => "POST",'header' =>"Content-Type: text/xml",'content' => $request)));
		if (! $file = @file_get_contents($rpc_connect, false, $context)) {
			die ("<h1>Cannot connect to rtorrent :(<br />Please, ensure that rtorrent is running.</h1>");
		}
	} elseif(substr ($rpc_connect, 0, strlen('unix://')) == 'unix://') {
		$sock = stream_socket_client($rpc_connect, $errno, $errstr, 20, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT);
		if($errno != 0) {
			die ("<h1>Cannot connect to rtorrent :(</h1>");
		}
		// stream_set_blocking($sock,1);
		$file = scgi_send($sock, $request);
		fclose($sock);
	}
	$file=str_replace("i8","double",$file);
	$file = utf8_encode($file); 
	return xmlrpc_decode($file);
}
// Get full list - retrieve full list of torrents 
function get_full_list($view) {
   $request = xmlrpc_encode_request("d.multicall",
       array($view,"d.get_base_filename=","d.get_base_path=","d.get_bytes_done=","d.get_chunk_size=","d.get_chunks_hashed=","d.get_complete=","d.get_completed_bytes=","d.get_completed_chunks=","d.get_connection_current=","d.get_connection_leech=","d.get_connection_seed=","d.get_creation_date=","d.get_directory=","d.get_down_rate=","d.get_down_total=","d.get_free_diskspace=","d.get_hash=","d.get_hashing=","d.get_ignore_commands=","d.get_left_bytes=","d.get_local_id=","d.get_local_id_html=","d.get_max_file_size=","d.get_message=","d.get_peers_min=","d.get_name=","d.get_peer_exchange=","d.get_peers_accounted=","d.get_peers_complete=","d.get_peers_connected=","d.get_peers_max=","d.get_peers_not_connected=","d.get_priority=","d.get_priority_str=","d.get_ratio=","d.get_size_bytes=","d.get_size_chunks=","d.get_size_files=","d.get_skip_rate=","d.get_skip_total=","d.get_state=","d.get_state_changed=","d.get_tied_to_file=","d.get_tracker_focus=","d.get_tracker_numwant=","d.get_tracker_size=","d.get_up_rate=","d.get_up_total=","d.get_uploads_max=","d.is_active=","d.is_hash_checked=","d.is_hash_checking=","d.is_multi_file=","d.is_open=","d.is_private="));
   $response = do_xmlrpc($request);

   if (xmlrpc_is_fault($response)) {
       trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
   } else {
      $index=0;
      foreach($response AS $item) {
         $retarr[$index]['base_filename']=$item[0];
         $retarr[$index]['base_path']=$item[1];
         $retarr[$index]['bytes_done']=$item[2];
         $retarr[$index]['chunk_size']=$item[3];
         $retarr[$index]['chunks_hashed']=$item[4];
         $retarr[$index]['complete']=$item[5];
         $retarr[$index]['completed_bytes'] = $item[7] * $item[3]; // completed_chunks * chunk_size
         $retarr[$index]['completed_chunks']=$item[7];
         $retarr[$index]['connection_current']=$item[8];
         $retarr[$index]['connection_leech']=$item[9];
         $retarr[$index]['connection_seed']=$item[10];
         $retarr[$index]['creation_date']=$item[11];
         $retarr[$index]['directory']=$item[12];
         $retarr[$index]['down_rate']=$item[13];
         $retarr[$index]['down_total']=$item[14];
         $retarr[$index]['free_diskspace']=$item[15];
         $retarr[$index]['hash']=$item[16];
         $retarr[$index]['hashing']=$item[17];
         $retarr[$index]['ignore_commands']=$item[18];
         $retarr[$index]['left_bytes']=$item[19];
         $retarr[$index]['local_id']=$item[20];
         $retarr[$index]['local_id_html']=$item[21];
         $retarr[$index]['max_file_size']=$item[22];
         $retarr[$index]['message']=$item[23];
         $retarr[$index]['peers_min']=$item[24];
         $retarr[$index]['name']=$item[25];
         $retarr[$index]['peer_exchange']=$item[26];
         $retarr[$index]['peers_accounted']=$item[27];
         $retarr[$index]['peers_complete']=$item[28];
         $retarr[$index]['peers_connected']=$item[29];
         $retarr[$index]['peers_max']=$item[30];
         $retarr[$index]['peers_not_connected']=$item[31];
         $retarr[$index]['priority']=$item[32];
         $retarr[$index]['priority_str']=$item[33];
         $retarr[$index]['ratio']=$item[34];
         $retarr[$index]['size_bytes']=$item[36] * $item[3]; // size_chunks * chunk_size
         $retarr[$index]['size_chunks']=$item[36];
         $retarr[$index]['size_files']=$item[37];
         $retarr[$index]['skip_rate']=$item[38];
         $retarr[$index]['skip_total']=$item[39];
         $retarr[$index]['state']=$item[40];
         $retarr[$index]['state_changed']=$item[41];
         $retarr[$index]['tied_to_file']=$item[42];
         $retarr[$index]['tracker_focus']=$item[43];
         $retarr[$index]['tracker_numwant']=$item[44];
         $retarr[$index]['tracker_size']=$item[45];
         $retarr[$index]['up_rate']=$item[46];
         $retarr[$index]['up_total']=$item[7] * $item[3] * ($item[34]/1000);
         $retarr[$index]['uploads_max']=$item[48];
         $retarr[$index]['is_active']=$item[49];
         $retarr[$index]['is_hash_checked']=$item[50];
         $retarr[$index]['is_hash_checking']=$item[51];
         $retarr[$index]['is_multi_file']=$item[52];
         $retarr[$index]['is_open']=$item[53];
         $retarr[$index]['is_private']=$item[54];

         $retarr[$index]['percent_complete']=@floor(($retarr[$index]['completed_bytes'])/($retarr[$index]['size_bytes'])*100);
         $retarr[$index]['bytes_diff']=($retarr[$index]['size_bytes']-$retarr[$index]['completed_bytes']);

         if ($retarr[$index]['is_active']==0) $retarr[$index]['status_string']="Stopped";
         if ($retarr[$index]['complete']==1) $retarr[$index]['status_string']="Complete";
         if ($retarr[$index]['is_active']==1 && $retarr[$index]['connection_current']=="leech") $retarr[$index]['status_string']="Leeching";
         if ($retarr[$index]['is_active']==1 && $retarr[$index]['complete']==1) $retarr[$index]['status_string']="Seeding";
         if ($retarr[$index]['hashing']>0) {
            $retarr[$index]['status_string']="Hashing";
            $retarr[$index]['percent_complete']=@round(($retarr[$index]['chunks_hashed'])/($retarr[$index]['size_chunks'])*100);
         }
         $retarr[$index]['filemtime']=@filectime($retarr[$index]['base_path']);

         $index++;
      }
      if (isset($retarr)) {
         return $retarr;
      } else {
         return FALSE;
      }
   }
}
// Get list of files associated with a torrent...
function get_file_list($hash) {
   $cmdarray=array($hash,"","f.get_completed_chunks=","f.get_frozen_path=","f.is_created=","f.is_open=","f.get_last_touched=","f.get_match_depth_next=","f.get_match_depth_prev=","f.get_offset=","f.get_path=","f.get_path_components=","f.get_path_depth=","f.get_priority=","f.get_range_first=","f.get_range_second=","f.get_size_bytes=","f.get_size_chunks=");
   $request = xmlrpc_encode_request("f.multicall",$cmdarray);
   $response = do_xmlrpc($request);
   if (xmlrpc_is_fault($response)) {
       trigger_error("xmlrpc: $response[faultString] ($response[faultCode])");
   } else {
      $index=0;
      foreach($response AS $item) {
             $retarr[$index]['get_completed_chunks']=$item[0];
             $retarr[$index]['get_frozen_path']=$item[1];
             $retarr[$index]['get_is_created']=$item[2];
             $retarr[$index]['get_is_open']=$item[3];
             $retarr[$index]['get_last_touched']=$item[4];
             $retarr[$index]['get_match_depth_next']=$item[5];
             $retarr[$index]['get_match_depth_prev']=$item[6];
             $retarr[$index]['get_offset']=$item[7];
             $retarr[$index]['get_path']=$item[8];
             $retarr[$index]['get_path_components']=$item[9];
             $retarr[$index]['get_path_depth']=$item[10];
             $retarr[$index]['get_priority']=$item[11];
             $retarr[$index]['get_range_first']=$item[12];
             $retarr[$index]['get_range_second']=$item[13];
             $retarr[$index]['get_size_bytes']=$item[14];
             $retarr[$index]['get_size_chunks']=$item[15];
             $index++;
      }
   return $retarr;
   }
}
?>
