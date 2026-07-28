<?php
$role = DB::table('roles')->where('nombre', 'admin')->first();
echo json_encode($role);
DB::table('roles')->where('nombre', 'admin')->update(['estatus' => 1]);
echo "\nRestored admin estatus to 1\n";
