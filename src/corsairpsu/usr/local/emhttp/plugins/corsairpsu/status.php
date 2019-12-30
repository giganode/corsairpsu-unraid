<?php
$stdout = shell_exec('/usr/local/bin/corsairmi 2>&1');
$re     = '/(?<key>[^:]+):\s+\'*(?<value>[^\n\']+)\'*\s*/';
preg_match_all($re, $stdout, $matches, PREG_SET_ORDER, 0);
foreach ($matches as $match)
    $data[$match['key']] = $match['value'];
if (!isset($data))
    exit(json_encode(array($stdout)));
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
    'temp1' => "{$data['temp1']} °c",
    'temp1_raw' => $data['temp1'],
    'temp2' => "{$data['temp2']} °c",
    'temp2_raw' => $data['temp2'],
    'fan_rpm' => "{$data['fan rpm']} RPM",
    'fan_rpm_raw' => $data['fan rpm'],
    'capacity' => "{$capacity}W",
    'capacity_raw' => $capacity,
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
    'poweredon_raw' => substr($data['powered'], 0, strpos($data['powered'], ' '))
);

header('Content-Type: application/json');
echo json_encode($json);
?>