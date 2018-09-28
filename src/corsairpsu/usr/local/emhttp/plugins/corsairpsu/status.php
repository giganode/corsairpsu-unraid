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
echo json_encode(array($data["uptime"],
"{$data["temp1"]} / {$data["temp2"]}",
$data["fan rpm"],
$data["supply volts"],
$capacity,
"{$data["output0 watts"]} Watts",
"{$data["total watts"]} Watts",
$load));
?>
