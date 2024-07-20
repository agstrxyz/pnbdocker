<?php
register_menu("System Tools", true, "systool", 'AFTER_SETTINGS', 'ion ion-hammer');

function systool()
{
    global $ui;
    _admin();
    $ui->assign('_title', 'System Tools');
    $ui->assign('_system_menu', 'systool');
    $admin = Admin::_info();
    $ui->assign('_admin', $admin);


    $rootpass = getenv('ROOT_PASSWORD', true) ?: getenv('ROOT_PASSWORD');
    $server   = "localhost"; 
    $username = "root";
    $password = $rootpass; 

	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restart']) && $_POST['restart'] === 'true')
    {
        $command  = 'reboot';
        $connection = ssh2_connect($server, 22);
        ssh2_auth_password($connection, $username, $password);
        $stream = ssh2_exec($connection, $command);
        stream_set_blocking($stream, true);

        r2(U . 'plugin/systool', 's', 'Restart Success!');
        
    }

    $ui->display('systool.tpl');
}