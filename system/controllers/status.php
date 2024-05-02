<?php

_auth();
$action = $routes['1'];
$user = User::_info();
$ui->assign('_user', $user);
switch ($action) {
case 'success':
	unset($_SESSION['hostname']);
	r2(U . 'home', 's', 'Sukses Terhubung');
	break;
case 'fail':
	if(isset($_GET['msg']) && !empty($_GET['msg'])){
    $_SESSION['msg'] = $_GET['msg'];
}


    if ($_SESSION['msg'] == 'Your maximum never usage time has been reached') {
    $info .= 'Paket Sudah Habis' . '<br>';
	
	$u = ORM::for_table('tbl_user_recharges')->where('customer_id', $user['id'])->find_one();
	$u->status = 'off';
    $u->save();
	
     r2(U . 'order/package', 'd', $info);
	 }
	 
	 
	if ($_SESSION['msg'] == 'You are already logged in - access denied') {
    $info .= 'Akun sudah login di perangkat lain' . '<br>';
	
	Radius::disconnectCustomer($user['username']);
	 r2(U . 'home', 'd', $info);
	 
     }

	 if ($_SESSION['msg'] == 'Akun Anda tidak tersedia di jaringan ini') {
		$info .= 'Paket Expired' . '<br>';
		
		 r2(U . 'home', 'd', $info);
		 
		 }
	 
	
	break;
}