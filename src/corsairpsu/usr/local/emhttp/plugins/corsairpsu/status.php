<?php
if (file_exists("/boot/config/plugins/corsairpsu/corsairpsu.cfg")) {
	$settings = parse_ini_file( "/boot/config/plugins/corsairpsu/corsairpsu.cfg" );
} else {
	$settings["TYPE"] = "corsairmi";
}

if ($settings["TYPE"] == "corsairmi") {
	$stdout = shell_exec('/usr/local/bin/corsairmi 2>&1');
} elseif ($settings["TYPE"] == "cpsumoncli") {
	$stdout = shell_exec('/usr/local/bin/cpsumon/cpsumoncli ' . $settings["TTY"] . ' 2>&1');
	//$stdout = file_get_contents("https://raw.githubusercontent.com/CyanLabs/corsairpsu-unraid/master/axoutput-example.txt"); //- Debug Testing	
} else {
	die("There is an error with your configuration!");
}

$re     = '/(?<key>[^:]+):\s+\'*(?<value>[^\n\']+)\'*\s*/';
preg_match_all($re, $stdout, $matches, PREG_SET_ORDER, 0);
foreach ($matches as $match)
    $data[$match['key']] = $match['value'];
if (!isset($data))
    exit(json_encode(array($stdout)));

if ($settings["TYPE"] == "corsairmi") {
	$capacity = filter_var($data['product'], FILTER_SANITIZE_NUMBER_INT);
	$load     = round($data['total watts'] / $capacity * 100);
	
	$output0_load     = round($data['output0 watts'] / $capacity * 100);
	$output1_load     = round($data['output1 watts'] / $capacity * 100);
	$output2_load     = round($data['output2 watts'] / $capacity * 100);
	
	// Sets keys for the 3 outputs to 12v, 5v and 3v
	$output0 = substr($data['output0 volts'], 0, strrpos($data['output0 volts'], '.'));
	$output1 = substr($data['output1 volts'], 0, strrpos($data['output1 volts'], '.'));
	$output2 = substr($data['output2 volts'], 0, strrpos($data['output2 volts'], '.'));

	// Removes raw timestamp on uptime / total hours leaving just the Xd, Xh text
	preg_match('/\((.*?)\)/', $data['uptime'], $uptime);
	preg_match('/\((.*?)\)/', $data['powered'], $poweredon);

	// Adds the keys and values to an array, named appropiately
	$json = array(
		'temp1' => $data['temp1'],
		'temp2' => $data['temp2'],
		'fan_rpm' => $data['fan rpm'],
		'capacity' => $capacity,
		"{$output0}v_watts" => $data['output0 watts'],
		"{$output1}v_watts" => $data['output1 watts'],
		"{$output2}v_watts" => $data['output2 watts'],
		'watts' => $data['total watts'],
		'load' => $load,
		"{$output0}v_load" => $output0_load,
		"{$output1}v_load" => $output1_load,
		"{$output2}v_load" => $output2_load,
		'vendor' => $data['vendor'],
		'product' => $data['product'],
		'uptime' => $uptime[1],
		'uptime_raw' => substr($data['uptime'], 0, strpos($data['uptime'], ' ')),
		'poweredon' => $poweredon[1],
		'poweredon_raw' => substr($data['powered'], 0, strpos($data['powered'], ' ')),
		'efficiency' => "Not Supported"
	);	
} else {
	$capacity = filter_var($data["PSU type"], FILTER_SANITIZE_NUMBER_INT);
	$input = floatval($data['Input power']);
	$load = round($input / $capacity * 100);

	$rail_5v = explode(",", $data['5V Rail']);
	$rail_3v = explode(",", $data['3.3V Rail']);

	$rail_5v_watts     = round(floatval($rail_5v[2]),2);
	$rail_3v_watts     = round(floatval($rail_3v[2]),2);
	$rail_12v = $input - $rail_5v_watts - $rail_3v_watts;
	
	$rail_12v_watts     = round(floatval($rail_12v),2);

	$rail_5v_load     = round(floatval($rail_5v[2]) / $capacity * 100);
	$rail_3v_load     = round(floatval($rail_3v[2]) / $capacity * 100);
	$rail_12v_load     = round(floatval($rail_12v) / $capacity * 100);

	$json = array(
		'temp1' => floatval($data["Temperature"]),
		'temp2' => floatval($data["Temperature"]),
		'fan_rpm' => floatval($data["Fan speed"]),
		'capacity' => $capacity,
		"12v_watts" => $rail_12v_watts,
		"5v_watts" => $rail_5v_watts,
		"3v_watts" => $rail_3v_watts,
		'watts' => $input,
		'load' => $load,
		"12v_load" => $rail_12v_load,
		"5v_load" => $rail_5v_load,
		"3v_load" => $rail_3v_load,
		'vendor' => "CORSAIR",
		'product' => str_replace("\r","",$data["PSU type"]),
		'uptime' => "Not Supported",
		'uptime_raw' => "Not Supported",
		'poweredon' => "Not Supported",
		'poweredon_raw' => "Not Supported",
		'efficiency' => floatval($data['Efficiency'])
	);
}
header('Content-Type: application/json');
echo json_encode($json);
?>