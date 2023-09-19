<?php
require_once('vendor/autoload.php');
use Liquid\Template;

$data = null;
$file = 'data.json';
if (file_exists($file))
	$data = file_get_contents($file);

if (!$data) {
	$data = file_get_contents('https://api.ambr.top/v2/en/avatar');
	file_put_contents($file, $data);
	foreach($json->data->items as $item) {
		if (!file_exists('img/'.$item->icon.'.png')) {
			$img = file_get_contents('https://api.ambr.top/assets/UI/'.$item->icon.'.png');
			file_put_contents('img/'.$item->icon.'.png', $img);
		}
	}
}

$month = (isset($_GET['m']) && intval($_GET['m']) && $_GET['m'] > 0 && $_GET['m'] <= 12) ? $_GET['m'] : date('n');
$month_name = date('F', strtotime(date('Y-'.$month.'-d')));
$link = 'http://'.$_SERVER['HTTP_HOST'];
$prev_month = $month > 1 ? $month - 1 : 12;
$next_month = $month >= 12 ? 1 : $month + 1;
$prev_link = $link.'?m='.$prev_month;
$next_link = $link.'?m='.$next_month;
$json = json_decode($data);

require_once('links.php');
// Get heroes for selected month
$heroes = [];
foreach($json->data->items as $hero) {
	if ($hero->birthday[0] == $month) {
		if (isset($links[$hero->name]))
			$hero->link = $links[$hero->name];
		$heroes[$hero->birthday[1]][] = $hero;
	}
}

// $dow - day of week
$day = 1;
$days = [];
$week = 1;
$weeks = [];
$bdays = [];
$dow = intval(date('w', strtotime(date('Y-'.$month.'-'.$day))));
while($day < date('t')) {
	$days[$day] = $dow;
	$weeks[$week][$dow] = $day;

	// Add Heroes to list
	if (isset($heroes[$day]))
		foreach ($heroes[$day] as $hero)
			$bdays[$day][] = $hero;

	$day++;
	$dow++;
	if ($dow > 7) {
		$dow = 1;
		$week++;
	}
}

// Highlight Today
$today = null;
if ($month == date('n'))
	$today = date('j');

$template = new Template();
echo $template->parse(file_get_contents('view.liquid'))->render(
	compact('weeks', 'bdays', 'month_name', 'month', 'prev_link', 'next_link', 'today')
);

?>
