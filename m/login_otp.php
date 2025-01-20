<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */
$area = "public";
include('./_include/core/pony_start.php');

$g['to_head'][] = '<link rel="stylesheet" href="'.$g['tmpl']['url_tmpl_mobile'].'css/main.css" type="text/css" media="all"/>';
$g['options']['no_loginform'] = 'Y';

class CLoginMobileOTP extends CHeader
{
	var $message = '';

	function parseBlock(&$html)
	{
		parent::parseBlock($html);
	}
}

$page = new CLoginMobileOTP("", $g['tmpl']['dir_tmpl_mobile'] . "login_otp.html");
$page->parseBanner = true;

$header = new CHeader("header", $g['tmpl']['dir_tmpl_mobile'] . "_header.html");
$page->add($header);
$footer = new CFooter("footer", $g['tmpl']['dir_tmpl_mobile'] . "_footer.html");
$page->add($footer);

loadPageContentAjax($page);

include("./_include/core/main_close.php");