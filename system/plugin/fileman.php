<?php
register_menu("File Manager", true, "fileman", 'SETTINGS', '');

function fileman()
{
    global $ui;
    _admin();
    $ui->assign('_title', 'Fileman');
    $ui->assign('_system_menu', 'settings');
    $admin = Admin::_info();
    $ui->assign('_admin', $admin);

   

    $ui->display('fileman.tpl');
}