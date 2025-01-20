<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

$area = "public";
include("./_include/core/pony_start.php");

$cmd = get_param('cmd');
$signup_as = get_param('signup_as');

// dd($_GET, $_POST);
if($cmd == 'sent')
    redirect('index');

// if($signup_as == '')
//     redirect('index');


$g['to_head'][] = '<link rel="stylesheet" href="'.$g['tmpl']['url_tmpl_mobile'].'css/main.css" type="text/css" media="all"/>';
$g['options']['no_loginform'] = 'Y';


$tmplRegister = Common::getOption('register_page_template', 'template_options');
/*$tmplLogin = Common::getOption('login_page_template', 'template_options');
if($cmd == '' && $tmplRegister) {
    $tmpl = $tmplRegister;
} else if ($cmd == 'please_login' && $tmplLogin) {
    $tmpl = $tmplLogin;
}*/
$tmpl = $tmplRegister;

if(IS_DEMO) {
    // for demo login by url
    $pageTemplate = get_param('page_template', '');
    if($pageTemplate) {
        $tmpl = $pageTemplate;
    }
}

$responseAjax = get_param('ajax');
if($responseAjax) {
    $responseData = false;
    if (in_array($cmd, array('states', 'cities'))) {
        $id = get_param('select_id');
        $method = 'list' . $cmd;
        $responseData['list'] = Common::$method($id);
    } elseif ($cmd == 'register') {
        $page = new CJoinForm('join', null);
        $page->init();
        $responseData = $page->responseData;
    } else {
        $page = new CJoinPage('login', null);
        $page->action(false);
        $responseData = $page->message;
    }
    die(get_json_encode($responseData));
}


// TEMPLATE DEFINE
$signup_as = get_param("signup_as");
$by = get_param("by");

$template = '';
if($signup_as && $by) {
    if($signup_as == "matchmaker")
        $template = ($by == 'phone') ? "register_phone.html" : "register.html";
    elseif($signup_as == "candidate") 
        $template = ($by == 'phone') ? "register_candidate_phone.html" : "register_candidate.html";
}
// TEMPLATE DEFINE END

if(!$template)
    redirect('index');

$page = new CJoinPage("", $g['tmpl']['dir_tmpl_mobile'] . $template);
$page->parseBanner = true;

$header = new CHeader("header", $g['tmpl']['dir_tmpl_mobile'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_mobile'] . "_footer.html");
$page->add($footer);

$register = new CJoinForm("join", null);
$page->add($register);

loadPageContentAjax($page);

include("./_include/core/main_close.php");