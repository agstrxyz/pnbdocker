<?php
 use Radius;

 register_menu("Online Users", true, "online_users", 'AFTER_SERVICES',  'ion ion-wifi');

function online_users()
{
    global $ui;
    _admin();
    $ui->assign('_title', 'Online Users');
    $ui->assign('_system_menu', 'online_users');
    $admin = Admin::_info();
    $ui->assign('_admin', $admin);
    
    
	$search = _post('search');
	if ($search != '') {
		$paginator = Paginator::build(ORM::for_table('radacct')->where_raw("acctstoptime IS NULL"));
		$useron = ORM::for_table('radacct')
			->where_raw("acctstoptime IS NULL")
			->where_like('username', '%' . $search . '%')
			->offset($paginator['startpoint'])
			->limit($paginator['limit'])
			->order_by_asc('username')
			->find_many();
	} else {
		$paginator = Paginator::build(ORM::for_table('radacct')->where_raw("acctstoptime IS NULL"));
		$useron = ORM::for_table('radacct')
			->where_raw("acctstoptime IS NULL")
			->offset($paginator['startpoint'])
			->limit($paginator['limit'])
			->order_by_asc('acctsessiontime')
			->find_many();
	}
	
	$totalCount = ORM::for_table('radacct')
		->where_raw("acctstoptime IS NULL")
		->count();	
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kill']) && $_POST['kill'] === 'true') {
    $output = array();
    $retcode = 0;
	$coaport = 3799;
	$d = _post('d');
	$dd = _post('dd');
	$dddd = _post('dddd');
	$ddd = ORM::for_table('nas')->where_like('nasname', '%' . $dd . '%')->find_one();
	$sharedsecret = $ddd['secret'];
    $ds = $ddd['server'];
    $os = strtoupper(PHP_OS);

    if (strpos($os, 'WIN') === 0) {
        // Windows OS
        exec("echo 'User-Name=$d'|radclient $dd:$coaport disconnect '$sharedsecret'", $output, $retcode);
    } else {
        // Linux OS
        exec("echo 'User-Name=$d,Framed-IP-Address=$dddd'|radclient $dd:$coaport disconnect '$sharedsecret'", $output, $retcode);
    }
    $ui->assign('output', $output);
    $ui->assign('returnCode', $retcode);
	$ui->assign('d', $d);
	$ui->assign('dd', $dd);

	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sync']) && $_POST['sync'] === 'true') {
		$date_now = date("Y-m-d H:i:s");
		$dbact = ORM::for_table('radacct')
			//->raw_execute("UPDATE radacct SET acctstoptime = '$date_now', acctterminatecause = 'Admin-Reset' WHERE acctstoptime IS NULL");
			->raw_execute("UPDATE radacct SET acctstoptime = NOW(), acctterminatecause = 'Stale-Session'
                     WHERE ((UNIX_TIMESTAMP(NOW()) - (UNIX_TIMESTAMP(acctstarttime) + acctsessiontime)) > (acctinterval * 2))
                       AND (acctstoptime='0000-00-00 00:00:00' OR acctstoptime IS NULL)");
	}
	
	$ui->assign('paginator', $paginator);
	$ui->assign('useron', $useron);
	$ui->assign('totalCount', $totalCount);
	$ui->assign('search', $search);
    $ui->display('onlineusers.tpl');
	
}



// Function to format bytes into KB, MB, GB or TB
function useron_formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Convert seconds into months, days, hours, minutes, and seconds.
function useron_secondsToTimeFull($ss)
{
    $s = $ss%60;
    $m = floor(($ss%3600)/60);
    $h = floor(($ss%86400)/3600);
    $d = floor(($ss%2592000)/86400);
    $M = floor($ss/2592000);

    return "$M months, $d days, $h hours, $m minutes, $s seconds";
}

function useron_secondsToTime($inputSeconds)
{
    $secondsInAMinute = 60;
    $secondsInAnHour = 60 * $secondsInAMinute;
    $secondsInADay = 24 * $secondsInAnHour;

    // Extract days
    $days = floor($inputSeconds / $secondsInADay);

    // Extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // Extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // Extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // Format and return
    $timeParts = [];
    $sections = [
        'day' => (int)$days,
        'hour' => (int)$hours,
        'minute' => (int)$minutes,
        'second' => (int)$seconds,
    ];

    foreach ($sections as $name => $value){
        if ($value > 0){
            $timeParts[] = $value. ' '.$name.($value == 1 ? '' : 's');
        }
    }

    return implode(', ', $timeParts);
}