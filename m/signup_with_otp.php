<?php

$area = "public";
if(isset($_GET['login'])){
    unset($area);
}

include('./_include/core/pony_start.php');

if(guid()){
    User::logoutWoRedirect();
    redirect($_SERVER['REQUEST_URI']);
}
$area = "public";

$ajax = get_param('ajax');
if($ajax) {
    $login = new SignUpWithOTP('', '', '', '', true);
    $login->action(false);
    echo $login->message;
    die();
}

?>