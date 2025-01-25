<?php
/* (C) Websplosion LLC, 2001-2021

IMPORTANT: This is a commercial software product
and any kind of using it must agree to the Websplosion's license agreement.
It can be found at http://www.chameleonsocial.com/license.doc

This notice may not be removed from the source code. */

include("../_include/core/administration_start.php");
class CUsersResults extends CHtmlList
{
	function action()
	{
		global $g, $p;

		$del = get_param('delete');
        $banned = intval(get_param('ban'));
        $isRedirect = false;
		if ($del) {
            $user =  explode(',', $del);
            foreach ($user as $userId) {
                if (Common::isEnabledAutoMail('admin_delete')) {
                    DB::query('SELECT * FROM user WHERE user_id = ' . to_sql($userId, 'Number'));
                    $row = DB::fetch_row();
                    $vars = array(
                        'title' => $g['main']['title'],
                        'name' => $row['name'],
                    );
                    if($row['mail'] && Common::getOption('active', 'smtp') == "Y")
                    	Common::sendAutomail($row['lang'], $row['mail'], 'admin_delete', $vars);
                }
                delete_user($userId);
            }
			$isRedirect = true;
		} elseif ($banned) {
			$sql='UPDATE user SET ban_global=1-ban_global WHERE user_id='. to_sql($banned, 'Number');
			DB::execute($sql);
            $isRedirect = true;
		}
        if ($isRedirect) {
            $offset = intval(get_param('offset'));
            if ($offset) {
                $offset = "?offset={$offset}";
            } else {
                $offset = '';
            }
            redirect($p . $offset);
        }
	}

	function init()
	{
		global $g;

        $this->m_on_page = 100;
		$this->m_on_bar = 10;

		$this->m_sql_count = "SELECT COUNT(u.user_id) FROM user AS u " . $this->m_sql_from_add . "";
		$this->m_sql = "
			SELECT u.user_id, u.mail, u.type, u.orientation, u.password, u.gold_days, u.name, (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d'))
			) AS age, u.last_visit,
			u.is_photo,
			u.city_id, u.state_id, u.country_id, u.last_ip, u.register, u.ban_global, u.name_seo, u.phone,
			CASE 
		        WHEN u.role = 'group_admin' THEN 'Matchmaker'
		        WHEN u.under_admin IS NULL AND u.role = 'user' THEN 'User'
		        ELSE 'Candidate of '
		    END AS role,
		    b.profile_pdf, u.socialSignUp AS is_social_login, u.enabled_OTP_login,
		    CASE
		    	WHEN u.under_admin IS NOT NULL THEN (SELECT name FROM user WHERE user_id = u.under_admin)
		    	ELSE ''
		    END AS machmaker_name

			FROM user AS u
			INNER JOIN userinfo b ON (u.user_id = b.user_id)
			" . $this->m_sql_from_add . "
		";

		$this->m_field['user_id'] = array("user_id", null);
		$this->m_field['name_seo'] = array("name_seo", null);
		$this->m_field['phone'] = array("phone", null);
		$this->m_field['name'] = array("name", null);
		$this->m_field['age'] = array("age", null);
		$this->m_field['last_visit'] = array("last_visit", null);
		$this->m_field['city_title'] = array("city", null);
		$this->m_field['state_title'] = array("state", null);
		$this->m_field['country_title'] = array("country", null);
		$this->m_field['mail'] = array("mail", null);
		$this->m_field['type'] = array("type", null);
		$this->m_field['gold_days'] = array("gold_days", null);
		$this->m_field['password'] = array("password", null);
		$this->m_field['role'] = array("role", null);
		$this->m_field['orientation'] = array("orientation", null);
		$this->m_field['last_ip'] = array("last_ip", null);
		$this->m_field['register'] = array("register", null);
		$this->m_field['ban_action'] = array("ban_action", null);
		$this->m_field['profile_pdf'] = array("profile_pdf", null);
		$this->m_field['login_user'] = array("login_user", null);

		$this->m_field['is_social_login'] = array("is_social_login", null);
		$this->m_field['machmaker_name'] = array("machmaker_name", null);

		$where = "";
		// $this->m_debug = "Y";
		$user["p_orientation"] = (int) get_checks_param("p_orientation");
		if ($user["p_orientation"] != "0")
		{
			$where .= " AND " . $user["p_orientation"] . " & (1 << (cast(orientation AS signed) - 1))";
		}

		$user["p_relation"] = (int) get_checks_param("p_relation");
		if ($user["p_relation"] != "0")
		{
			$where .= " AND " . $user["p_relation"] . " & (1 << (cast(relation AS signed) - 1))";
		}

        $user["i_am_here_to"] = (int) get_checks_param("i_am_here_to");
		if ($user["i_am_here_to"] != "0")
		{
			$where .= " AND " . $user["i_am_here_to"] . " & (1 << (cast(i_am_here_to AS signed) - 1))";
		}

		$user['name'] = get_param("name", "");
		if ($user['name'] != "")
		{
			$where .= " AND name LIKE '%" . to_sql($user['name'], "Plain") . "%'";
		}

		$user['mail'] = get_param("mail", "");
		if ($user['mail'] != "")
		{
			$where .= " AND mail LIKE '%" . to_sql($user['mail'], "Plain") . "%'";
		}

		if (get_param("gold", "") == "1")
		{
			$where .= " AND gold_days>0";
		}
		if (get_param("gold", "") == "0")
		{
			$where .= " AND gold_days=0";
		}

        $r_from = get_param('r_from');
		$r_to = get_param('r_to');
        if ($r_from) {
            $date = new DateTime($r_from);
            $date->format('Y-m-d H:i:s');
            if ($date->format('Y-m-d H:i:s') ==  "{$r_from} 00:00:00") {
                $r_from .= ' 00:00:00';
            }
            $where .= " AND register >=" . to_sql($r_from);
        }
        if ($r_to) {
            $date = new DateTime($r_to);
            $date->format('Y-m-d H:i:s');
            if ($date->format('Y-m-d H:i:s') ==  "{$r_to} 00:00:00") {
                $r_to .= ' 23:59:59';
            }
            $where .= " AND register <=" . to_sql($r_to);
        }

		/*$r_from = get_param("r_from", "0000-00-00");
		$r_to = get_param("r_to", "0000-00-00");
		if ($r_from != "0000-00-00" or $r_to != "0000-00-00")
		{
			#$from = explode("-", $r_from);
			#$to = explode("-", $r_to);
			#$r_from = mktime(0, 0, 0, $from[1], $from[2], $from[0] == "0000" ? "2000" : $from[0]);
			#$r_to = mktime(0, 0, 0, $to[1], $to[2], $to[0] == "0000" ? "2000" : $to[0]);
			#if ($r_from < $r_to)
			{
				$where .= " AND register>" . to_sql($r_from) . " AND register<" . to_sql($r_to) . "";
			}
		}*/

		$user['p_age_from'] = (int) get_param("p_age_from", 0);
		$user['p_age_to'] = (int) get_param("p_age_to", 0);
        if ($user['p_age_to'] == $g['options']['users_age_max']) $user['p_age_to'] = 10000;

		if ($user['p_age_from'] != 0)
		{
			$where .= " AND (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d')) >= " . $user['p_age_from'] . ") ";
		}

		if ($user['p_age_to'] != 0)
		{
			$where .= " AND (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(birth, '00-%m-%d')) <= " . $user['p_age_to'] . ") ";
		}



		$user['country'] = (int) get_param("country", 0);
		if ($user['country'] != 0 and $user['country'] != "")
		{
			$where .= " AND u.country_id=" . $user['country'] . "";
		}
		$user['state'] = (int) get_param("state", 0);
		if ($user['state'] != 0 and $user['state'] != "")
		{
			$where .= " AND u.state_id=" . $user['state'] . "";
		}
		$user['city'] = (int) get_param("city", 0);
		if ($user['city'] != 0 and $user['city'] != "")
		{
			$where .= " AND u.city_id=" . $user['city'] . "";
		}

		if (get_param("photo", "") == "1")
		{
			$where .= " AND u.is_photo='Y'";
		}

		if (get_param("status", "") == "online")
		{
			$where .= " AND last_visit>" . (time() - $g['options']['online_time'] * 60) . "";
		}
		elseif (get_param("status", "") == "new")
		{
			$where .= " AND register>" . (time() - $g['options']['new_days'] * 3600 * 24) . "";
		}
		elseif (get_param("status", "") == "birthday")
		{
			$where .= " AND (DAYOFMONTH(birth)=DAYOFMONTH('" . date('Y-m-d H:i:s') . "') AND MONTH(birth)=MONTH('" . date('Y-m-d H:i:s') . "'))";
		}

		$keyword = get_param("keyword", "");
		if ($keyword != "")
		{
			$keyword = to_sql($keyword, "Plain");
			$where .= " AND (name LIKE '%" . $keyword . "%') ";
		}

        $useAsOnline = get_param('use_as_online');
        if($useAsOnline) {
            $where .= ' AND use_as_online = ' . to_sql($useAsOnline);
        }

		$this->m_sql_where = "1" . $where;
		$this->m_sql_order = "user_id";
		$this->m_sql_from_add = "";
	}
	function parseBlock(&$html)
	{
		parent::parseBlock($html);
	}
    function onPostParse(&$html)
	{
        if ($this->m_total != 0) {
            $html->parse('no_delete');
        }
	}
	function onItem(&$html, $row, $i, $last)
	{
		global $g;

        // $html->setvar('url_profile', User::url($row['user_id']));
        $html->setvar('url_profile', $row['name_seo']);
        $html->setvar('phone', $row['phone']);

		$this->m_field['city_title'][1] = DB::result("SELECT city_title FROM geo_city WHERE city_id=" . $row['city_id'] . "", 0, 2);
		if ($this->m_field['city_title'][1] == "") $this->m_field['city_title'][1] = "blank";
		$this->m_field['state_title'][1] = DB::result("SELECT state_title FROM geo_state WHERE state_id=" . $row['state_id'] . "", 0, 2);
		if ($this->m_field['state_title'][1] == "") $this->m_field['state_title'][1] = "blank";
		$this->m_field['country_title'][1] = DB::result("SELECT country_title FROM geo_country WHERE country_id=" . $row['country_id'] . "", 0, 2);
		if ($this->m_field['country_title'][1] == "") $this->m_field['country_title'][1] = "blank";

                if (Common::getOption('set', 'template_options') != 'urban')
                {
                    $html->setvar('user_id',  $row['user_id']);
                    $html->parse('blog',false);
                    if ($row['type'] == 'membership')
                    {
                        $this->m_field['type'][1] = l('platinum');
                    } else {
                        $this->m_field['type'][1] = l($row['type']);
                    }
                } else {
                    if ($row['type'] != 'none')
                    {
                        if ($row['gold_days'] > 0){
                            $this->m_field['type'][1] = l('Super Powers!');
                        } else {
                            $this->m_field['type'][1] = l('none');
                        }
                    } else {
                        $this->m_field['type'][1] = l($row['type']);
                    }
                }

		if($row['ban_global']){
			$this->m_field['ban_action'][1] = l('unban');
		} else {
			$this->m_field['ban_action'][1] = l('ban');
		}

		if($row['is_social_login'])
			$this->m_field['is_social_login'][1] = '<font color="blue">(Social)</font>';
		elseif($row['enabled_OTP_login'])
			$this->m_field['is_social_login'][1] = '<font color="blue">(Phone)</font>';
		else
			$this->m_field['is_social_login'][1] = '';

		if($row['profile_pdf'])
			$this->m_field['profile_pdf'][1] = '<a href="view_biodata.php?user_id='.$row['user_id'].'" target="_blank"><i class="fa fa-file-pdf-o"></i></a> &nbsp; ';
		else
			$this->m_field['profile_pdf'][1] = '';

		if($row['role'] == 'Candidate of ')
			$this->m_field['login_user'][1] = '';
		else
			$this->m_field['login_user'][1] = '<a class="d-none" onclick="return confirmCustom(\'Would you like to log in as: <h3>' . $row['name'] . '</h3>\', function() { window.open(\'user_login.php?user_id=' . $row['user_id'] . '\', \'_blank\'); }); return false;" target="_blank"><i class="fa fa-sign-in"></i></a>';



		$this->m_field['orientation'][1] = DB::result("SELECT title FROM const_orientation WHERE id=" . $row['orientation'] . "", 0, 2);
		if ($this->m_field['orientation'][1] == "")
		{
			$this->m_field['orientation'][1] = l("Invalid orientation");
		}
        $this->m_field['password'][1] = hard_trim($row['password'], 7);
		if (IS_DEMO) {
			$this->m_field['mail'][1] = 'disabled@ondemoadmin.cp';
			$this->m_field['password'][1] = 'not shown in the demo';
		}

        if ($i % 2 == 0) {
            $html->setvar("class", 'color');
            $html->setvar("decl", '_l');
            $html->setvar("decr", '_r');
        } else {
            $html->setvar("class", '');
            $html->setvar("decl", '');
            $html->setvar("decr", '');
        }
		parent::onItem($html, $row, $i, $last);
	}
}

$page = new CUsersResults("main", $g['tmpl']['dir_tmpl_administration'] . "users_results.html");
$header = new CAdminHeader("header", $g['tmpl']['dir_tmpl_administration'] . "_header.html");
$page->add($header);
$footer = new CAdminFooter("footer", $g['tmpl']['dir_tmpl_administration'] . "_footer.html");
$page->add($footer);

$page->add(new CAdminPageMenuUsers());

include("../_include/core/administration_close.php");