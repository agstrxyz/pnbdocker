<?php
register_menu("PHPmyAdmin", true, "phpmyadmin", 'SETTINGS', '');

function phpmyadmin()
{
    global $ui;
    _admin();
    $ui->assign('_title', 'PhpMyAdmin');
    $ui->assign('_system_menu', 'settings');
    $admin = Admin::_info();
    $ui->assign('_admin', $admin);

   

    $ui->display('phpmyadmin.tpl');
}