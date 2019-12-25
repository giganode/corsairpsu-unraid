<?php
$stdout = shell_exec("/usr/local/bin/corsairmi 2>&1");
$re = "/(?<key>[^:]+):\s+\'*(?<value>[^\n\']+)\'*\s*/";
preg_match_all($re, $stdout, $matches, PREG_SET_ORDER, 0);
foreach($matches as $match)
 $data[$match["key"]] = $match["value"];
if(!isset($data))
 exit(json_encode(array($stdout)));
$capacity = filter_var($data["product"], FILTER_SANITIZE_NUMBER_INT);
$load = round($data["total watts"] / $capacity * 100);

// Sets keys for the 3 outputs to 12v, 5v and 3v
$output0 = substr($data["output0 volts"], 0, strrpos($data["output0 volts"], '.'));
$output1 = substr($data["output1 volts"], 0, strrpos($data["output1 volts"], '.'));
$output2 = substr($data["output2 volts"], 0, strrpos($data["output2 volts"], '.'));

// Removes raw timestamp on uptime / total hours leaving just the Xd, Xh text
preg_match('/\((.*?)\)/', $data["uptime"], $uptime);
preg_match('/\((.*?)\)/', $data["powered"], $poweredon);

// Adds the keys and values to an array, named appropiately
$json = array("temp1" => "{$data['temp1']} °c",
 "temp2" => "{$data['temp2']} °c",
 "fan_rpm" => "{$data['fan rpm']} RPM" ,
 "capacity" => "{$capacity}W",
 "{$output0}v_watts" => "{$data["output0 watts"]}W", 
 "{$output1}v_watts" => "{$data["output1 watts"]}W", 
 "{$output2}v_watts" => "{$data["output2 watts"]}W",
 "watts" => "{$data["total watts"]}W",
 "load" => "{$load}%",
 "vendor" => $data["vendor"],
 "product" => $data["product"],
 "uptime" => $uptime[1],
 "poweredon" => $poweredon[1]
 );

echo json_encode($json);
?>
