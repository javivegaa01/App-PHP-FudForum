<?php
/**
* copyright            : (C) 2001-2017 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id$
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}function alt_var($key)
{
	if (!isset($GLOBALS['_ALTERNATOR_'][$key])) {
		$args = func_get_args(); unset($args[0]);
		$GLOBALS['_ALTERNATOR_'][$key] = array('p' => 2, 't' => func_num_args(), 'v' => $args);
		return $args[1];
	}
	$k =& $GLOBALS['_ALTERNATOR_'][$key];
	if ($k['p'] == $k['t']) {
		$k['p'] = 1;
	}
	return $k['v'][$k['p']++];
}function pager_replace(&$str, $s, $c)
{
	$str = str_replace(array('%s', '%c'), array($s, $c), $str);
}

function tmpl_create_pager($start, $count, $total, $arg, $suf='', $append=1, $js_pager=0, $no_append=0)
{
	if (!$count) {
		$count =& $GLOBALS['POSTS_PER_PAGE'];
	}
	if ($total <= $count) {
		return;
	}

	$upfx = '';
	if ($GLOBALS['FUD_OPT_2'] & 32768 && (!empty($_SERVER['PATH_INFO']) || strpos($arg, '?') === false)) {
		if (!$suf) {
			$suf = '/';
		} else if (strpos($suf, '//') !== false) {
			$suf = preg_replace('!/+!', '/', $suf);
		}
	} else if (!$no_append) {
		$upfx = '&amp;start=';
	}

	$cur_pg = ceil($start / $count);
	$ttl_pg = ceil($total / $count);

	$page_pager_data = '';

	if (($page_start = $start - $count) > -1) {
		if ($append) {
			$page_first_url = $arg . $upfx . $suf;
			$page_prev_url = $arg . $upfx . $page_start . $suf;
		} else {
			$page_first_url = $page_prev_url = $arg;
			pager_replace($page_first_url, 0, $count);
			pager_replace($page_prev_url, $page_start, $count);
		}

		$page_pager_data .= !$js_pager ? '&nbsp;<a href="'.$page_first_url.'" class="PagerLink">&laquo;</a>&nbsp;&nbsp;<a href="'.$page_prev_url.'" accesskey="p" class="PagerLink">&lsaquo;</a>&nbsp;&nbsp;' : '&nbsp;<a href="javascript://" onclick="'.$page_first_url.'" class="PagerLink">&laquo;</a>&nbsp;&nbsp;<a href="javascript://" onclick="'.$page_prev_url.'" class="PagerLink">&lsaquo;</a>&nbsp;&nbsp;';
	}

	$mid = ceil($GLOBALS['GENERAL_PAGER_COUNT'] / 2);

	if ($ttl_pg > $GLOBALS['GENERAL_PAGER_COUNT']) {
		if (($mid + $cur_pg) >= $ttl_pg) {
			$end = $ttl_pg;
			$mid += $mid + $cur_pg - $ttl_pg;
			$st = $cur_pg - $mid;
		} else if (($cur_pg - $mid) <= 0) {
			$st = 0;
			$mid += $mid - $cur_pg;
			$end = $mid + $cur_pg;
		} else {
			$st = $cur_pg - $mid;
			$end = $mid + $cur_pg;
		}

		if ($st < 0) {
			$start = 0;
		}
		if ($end > $ttl_pg) {
			$end = $ttl_pg;
		}
		if ($end - $start > $GLOBALS['GENERAL_PAGER_COUNT']) {
			$end = $start + $GLOBALS['GENERAL_PAGER_COUNT'];
		}
	} else {
		$end = $ttl_pg;
		$st = 0;
	}

	while ($st < $end) {
		if ($st != $cur_pg) {
			$page_start = $st * $count;
			if ($append) {
				$page_page_url = $arg . $upfx . $page_start . $suf;
			} else {
				$page_page_url = $arg;
				pager_replace($page_page_url, $page_start, $count);
			}
			$st++;
			$page_pager_data .= !$js_pager ? '<a href="'.$page_page_url.'" class="PagerLink">'.$st.'</a>&nbsp;&nbsp;' : '<a href="javascript://" onclick="'.$page_page_url.'" class="PagerLink">'.$st.'</a>&nbsp;&nbsp;';
		} else {
			$st++;
			$page_pager_data .= !$js_pager ? $st.'&nbsp;&nbsp;' : $st.'&nbsp;&nbsp;';
		}
	}

	$page_pager_data = substr($page_pager_data, 0 , strlen((!$js_pager ? '&nbsp;&nbsp;' : '&nbsp;&nbsp;')) * -1);

	if (($page_start = $start + $count) < $total) {
		$page_start_2 = ($st - 1) * $count;
		if ($append) {
			$page_next_url = $arg . $upfx . $page_start . $suf;
			// $page_last_url = $arg . $upfx . $page_start_2 . $suf;
			$page_last_url = $arg . $upfx . floor($total-1/$count)*$count . $suf;
		} else {
			$page_next_url = $page_last_url = $arg;
			pager_replace($page_next_url, $upfx . $page_start, $count);
			pager_replace($page_last_url, $upfx . $page_start_2, $count);
		}
		$page_pager_data .= !$js_pager ? '&nbsp;&nbsp;<a href="'.$page_next_url.'" accesskey="n" class="PagerLink">&rsaquo;</a>&nbsp;&nbsp;<a href="'.$page_last_url.'" class="PagerLink">&raquo;</a>' : '&nbsp;&nbsp;<a href="javascript://" onclick="'.$page_next_url.'" class="PagerLink">&rsaquo;</a>&nbsp;&nbsp;<a href="javascript://" onclick="'.$page_last_url.'" class="PagerLink">&raquo;</a>';
	}

	return !$js_pager ? '<span class="SmallText fb">P??ginas ('.$ttl_pg.'): ['.$page_pager_data.']</span>' : '<span class="SmallText fb">P??ginas ('.$ttl_pg.'): ['.$page_pager_data.']</span>';
}

	if (!empty($_GET['goto'])) {
		$pl_view = empty($_GET['vote']) ? 0 : (int)$_GET['goto'];
		$mid = q_singleval('SELECT id FROM fud30_msg WHERE poll_id='. (int)$_GET['goto']);
		/* PATH_INFO is handled via /pv/ */
		header('Location: /index.php?t='. d_thread_view .'&goto='. $mid .'&pl_view='. $pl_view .'&'._rsidl .'#msg_'. $mid);
		return;
	}

	ses_update_status($usr->sid, '<a href="/index.php?t=polllist">Revisando las encuestas disponibles</a>');

if (_uid) {
	$admin_cp = $accounts_pending_approval = $group_mgr = $reported_msgs = $custom_avatar_queue = $mod_que = $thr_exch = '';

	if ($usr->users_opt & 524288 || $is_a) {	// is_mod or admin.
		if ($is_a) {
			// Approval of custom Avatars.
			if ($FUD_OPT_1 & 32 && ($avatar_count = q_singleval('SELECT count(*) FROM fud30_users WHERE users_opt>=16777216 AND '. q_bitand('users_opt', 16777216) .' > 0'))) {
				$custom_avatar_queue = '| <a href="/adm/admavatarapr.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'">Cola avatares personalizados</a> <span class="GenTextRed">('.$avatar_count.')</span>';
			}

			// All reported messages.
			if ($report_count = q_singleval('SELECT count(*) FROM fud30_msg_report')) {
				$reported_msgs = '| <a href="/index.php?t=reported&amp;'._rsid.'" rel="nofollow">Mensajes reportados</a> <span class="GenTextRed">('.$report_count.')</span>';
			}

			// All thread exchange requests.
			if ($thr_exchc = q_singleval('SELECT count(*) FROM fud30_thr_exchange')) {
				$thr_exch = '| <a href="/index.php?t=thr_exch&amp;'._rsid.'">Intercambio de hilos de discusi??n</a> <span class="GenTextRed">('.$thr_exchc.')</span>';
			}

			// All account approvals.
			if ($FUD_OPT_2 & 1024 && ($accounts_pending_approval = q_singleval('SELECT count(*) FROM fud30_users WHERE users_opt>=2097152 AND '. q_bitand('users_opt', 2097152) .' > 0 AND id > 0'))) {
				$accounts_pending_approval = '| <a href="/adm/admuserapr.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'">Cuentas pendientes de aprobaci??n</a> <span class="GenTextRed">('.$accounts_pending_approval.')</span>';
			} else {
				$accounts_pending_approval = '';
			}

			$q_limit = '';
		} else {
			// Messages reported in moderated forums.
			if ($report_count = q_singleval('SELECT count(*) FROM fud30_msg_report mr INNER JOIN fud30_msg m ON mr.msg_id=m.id INNER JOIN fud30_thread t ON m.thread_id=t.id INNER JOIN fud30_mod mm ON t.forum_id=mm.forum_id AND mm.user_id='. _uid)) {
				$reported_msgs = '| <a href="/index.php?t=reported&amp;'._rsid.'" rel="nofollow">Mensajes reportados</a> <span class="GenTextRed">('.$report_count.')</span>';
			}

			// Thread move requests in moderated forums.
			if ($thr_exchc = q_singleval('SELECT count(*) FROM fud30_thr_exchange te INNER JOIN fud30_mod m ON m.user_id='. _uid .' AND te.frm=m.forum_id')) {
				$thr_exch = '| <a href="/index.php?t=thr_exch&amp;'._rsid.'">Intercambio de hilos de discusi??n</a> <span class="GenTextRed">('.$thr_exchc.')</span>';
			}

			$q_limit = ' INNER JOIN fud30_mod mm ON f.id=mm.forum_id AND mm.user_id='. _uid;
		}

		// Messages requiring approval.
		if ($approve_count = q_singleval('SELECT count(*) FROM fud30_msg m INNER JOIN fud30_thread t ON m.thread_id=t.id INNER JOIN fud30_forum f ON t.forum_id=f.id '. $q_limit .' WHERE m.apr=0 AND f.forum_opt>=2')) {
			$mod_que = '<a href="/index.php?t=modque&amp;'._rsid.'">Cola de moderaci??n</a> <span class="GenTextRed">('.$approve_count.')</span>';
		}
	} else if ($usr->users_opt & 268435456 && $FUD_OPT_2 & 1024 && ($accounts_pending_approval = q_singleval('SELECT count(*) FROM fud30_users WHERE users_opt>=2097152 AND '. q_bitand('users_opt', 2097152) .' > 0 AND id > 0'))) {
		$accounts_pending_approval = '| <a href="/adm/admuserapr.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'">Cuentas pendientes de aprobaci??n</a> <span class="GenTextRed">('.$accounts_pending_approval.')</span>';
	} else {
		$accounts_pending_approval = '';
	}
	if ($is_a || $usr->group_leader_list) {
		$group_mgr = '| <a href="/index.php?t=groupmgr&amp;'._rsid.'">Administrador de grupo</a>';
	}

	if ($thr_exch || $accounts_pending_approval || $group_mgr || $reported_msgs || $custom_avatar_queue || $mod_que) {
		$admin_cp = '<br /><span class="GenText fb">Admin:</span> '.$mod_que.' '.$reported_msgs.' '.$thr_exch.' '.$custom_avatar_queue.' '.$group_mgr.' '.$accounts_pending_approval.'<br />';
	}
} else {
	$admin_cp = '';
}/* Print number of unread private messages in User Control Panel. */
	if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud30_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$ucp_private_msg = $c ? '<li><a href="/index.php?t=pmsg&amp;'._rsid.'" title="Mensajes privados"><img src="/theme/default/images/top_pm.png" alt="" width="16" height="16" /> Tienes <span class="GenTextRed">('.$c.')</span> '.convertPlural($c, array('mensaje privado','mensajes privados')).' sin leer</a></li>' : '<li><a href="/index.php?t=pmsg&amp;'._rsid.'" title="Mensajes privados"><img src="/theme/default/images/top_pm.png" alt="" width="15" height="11" /> Mensajes privados</a></li>';
	} else {
		$ucp_private_msg = '';
	}

	if (!isset($_GET['oby'])) {
		$_GET['oby'] = 'DESC';
	}
	if (!isset($_GET['start']) || !($start = (int)$_GET['start'])) {
		$start = 0;
	}
	if (isset($_GET['uid']) && ($uid = (int)$_GET['uid'])) {
		$usr_lmt = ' WHERE p.owner='. $uid;
	} else {
		$uid = $usr_lmt = '';
	}

	if (!$is_a) {
		$usr_lmt = $usr_lmt . ($usr_lmt ? ' AND ' : ' WHERE ') .' (mm.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 2) .' > 0)';
	}

	if ($_GET['oby'] == 'ASC') {
		$oby = 'ASC';
		$oby_rev_val = 'DESC';
	} else {
                $oby = 'DESC';
		$oby_rev_val = 'ASC';
	}

	$poll_entries = '';
	$c = uq(q_limit('SELECT /*!40000 SQL_CALC_FOUND_ROWS */
			p.owner, p.name, p.creation_date, p.id, p.max_votes, p.total_votes,
			u.alias, u.alias AS login, (u.last_visit + '. ($LOGEDIN_TIMEOUT * 60) .') AS last_visit, u.users_opt,
			'. ($is_a ? '1' : 'mm.id') .' AS md,
			COALESCE(g2.group_cache_opt, g1.group_cache_opt) AS gco
			FROM fud30_poll p
			INNER JOIN fud30_group_cache g1 ON g1.user_id='. (_uid ? '2147483647' : '0') .' AND g1.resource_id=p.forum_id
			INNER JOIN fud30_forum f ON p.forum_id=f.id
			INNER JOIN fud30_cat c ON c.id=f.cat_id
			LEFT JOIN fud30_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=p.forum_id
			LEFT JOIN fud30_mod mm ON mm.forum_id=p.forum_id AND mm.user_id='. _uid .'
			LEFT JOIN fud30_users u ON u.id=p.owner
			LEFT JOIN fud30_poll_opt_track pot ON pot.poll_id=p.id AND pot.user_id='. _uid .
			$usr_lmt .' ORDER BY p.creation_date '. $oby,
			$POLLS_PER_PAGE, $start));

	while ($obj = db_rowobj($c)) {
		$view_res_lnk = $obj->total_votes ? '<b>::</b> <a href="/index.php?t=polllist&amp;goto='.$obj->id.'&amp;vote=1&amp;'._rsid.'#msg_'.$obj->id.'">Ver resultados</a>' : '';
		if (!$obj->total_votes) {
			$obj->total_votes = '0';
		}
		if ($obj->owner && (!($obj->users_opt & 32768) || $is_a) && $FUD_OPT_2 & 32) {
			$online_indicator = $obj->last_visit > __request_timestamp__ ? '<img src="/theme/default/images/online.png" title="'.$obj->login.' est?? actualmente en el foro" alt="'.$obj->login.' est?? actualmente en el foro" />&nbsp;' : '<img src="/theme/default/images/offline.png" title="'.$obj->login.' no est?? actualmente en el foro" alt="'.$obj->login.' no est?? actualmente en el foro" />&nbsp;';
		} else {
			$online_indicator = '';
		}
		$poll_entries .= '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'">
	<td class="wa">'.$obj->name.'</td>
	<td class="nw">'.strftime('%a, %d %B %Y %H:%M', $obj->creation_date).'</td>
	<td class="nw">'.$online_indicator.'<a href="/index.php?t=usrinfo&amp;id='.$obj->owner.'&amp;'._rsid.'">'.filter_var($obj->alias, FILTER_SANITIZE_STRING).'</a></td>
	<td class="nw ac">'.$obj->total_votes.'</td>
	<td class="nw ac"> <b>::</b> <a href="/index.php?t=polllist&amp;goto='.$obj->id.'&amp;'._rsid.'#msg_'.$obj->id.'">Votar</a> '.$view_res_lnk.' <b>::</b></td>
</tr>';
	}
	unset($c);

	if (($ttl = (int) q_singleval('SELECT /*!40000 FOUND_ROWS(), */ -1')) < 0) {
		$ttl = (int) q_singleval('SELECT count(*)
				FROM fud30_poll p
				INNER JOIN fud30_forum f ON p.forum_id=f.id
				INNER JOIN fud30_cat c ON c.id=f.cat_id
				LEFT JOIN fud30_mod mm ON mm.forum_id=p.forum_id AND mm.user_id='. _uid .'
				INNER JOIN fud30_group_cache g1 ON g1.user_id='. (_uid ? '2147483647' : '0') .' AND g1.resource_id=p.forum_id
				LEFT JOIN fud30_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=p.forum_id'. $usr_lmt);
	}

	$pager = '';
	if ($ttl > $POLLS_PER_PAGE) {
		if ($FUD_OPT_2 & 32768) {
			$pager = tmpl_create_pager($start, $POLLS_PER_PAGE, $ttl, '/index.php/pl/'. $uid .'/', '/'. $oby .'/'. _rsid);
		} else {
			$pager = tmpl_create_pager($start, $POLLS_PER_PAGE, $ttl, '/index.php?t=polllist&amp;oby='. $oby .'&amp;uid='. $uid);
		}
	} else if (!$ttl) {
		$poll_entries = '<tr>
	<td colspan="5" class="ac">No hay encuestas accesibles</td>
</tr>';
	}

if ($FUD_OPT_2 & 2 || $is_a) {	// PUBLIC_STATS is enabled or Admin user.
	$page_gen_time = number_format(microtime(true) - __request_timestamp_exact__, 5);
	$page_stats = $FUD_OPT_2 & 2 ? '<br /><div class="SmallText al">Tiempo total que tard?? la generaci??n de la p??gina: '.convertPlural($page_gen_time, array(''.$page_gen_time.' segundo',''.$page_gen_time.' segundos')).'</div>' : '<br /><div class="SmallText al">Tiempo total que tard?? la generaci??n de la p??gina: '.convertPlural($page_gen_time, array(''.$page_gen_time.' segundo',''.$page_gen_time.' segundos')).'</div>';
} else {
	$page_stats = '';
}
?>
<!DOCTYPE html>
<html lang="es" dir="ltr">
<head>
	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="description" content="<?php echo (!empty($META_DESCR) ? $META_DESCR.'' : $GLOBALS['FORUM_DESCR'].''); ?>" />
	<title><?php echo $GLOBALS['FORUM_TITLE'].$TITLE_EXTRA; ?></title>
	<link rel="search" type="application/opensearchdescription+xml" title="<?php echo $GLOBALS['FORUM_TITLE']; ?> Search" href="/open_search.php" />
	<?php echo $RSS; ?>
	<link rel="stylesheet" href="/theme/default/forum.css" media="screen" title="Default Forum Theme" />
	<link rel="stylesheet" href="/js/ui/jquery-ui.css" media="screen" />
	<script src="/js/jquery.js"></script>
	<script async src="/js/ui/jquery-ui.js"></script>
	<script src="/js/lib.js"></script>
</head>
<body>
<!--  -->
<div class="header">
  <?php echo ($GLOBALS['FUD_OPT_1'] & 1 && $GLOBALS['FUD_OPT_1'] & 16777216 ? '
  <div class="headsearch">
    <form id="headsearch" method="get" action="/index.php">'._hs.'
      <input type="hidden" name="t" value="search" />
      <br /><label accesskey="f" title="B??squeda en el foro">B??squeda en el foro:<br />
      <input type="search" name="srch" value="" size="20" placeholder="B??squeda en el foro" /></label>
      <input type="image" src="/theme/default/images/search.png" title="Buscar" name="btn_submit">&nbsp;
    </form>
  </div>
  ' : ''); ?>
  <a href="/" title="Inicio">
    <img class="headimg" src="/theme/default/images/header.gif" alt="" align="left" height="80" />
    <span class="headtitle"><?php echo $GLOBALS['FORUM_TITLE']; ?></span>
  </a><br />
  <span class="headdescr"><?php echo $GLOBALS['FORUM_DESCR']; ?><br /><br /></span>
</div>
<div class="content">

<!-- Table for sidebars. -->
<table width="100%"><tr><td>
<div id="UserControlPanel">
<ul>
	<?php echo $ucp_private_msg; ?>
	<?php echo ($FUD_OPT_4 & 16 ? '<li><a href="/index.php?t=blog&amp;'._rsid.'" title="Blog"><img src="/theme/default/images/blog.png" alt="" width="16" height="16" /> Blog</a></li>' : ''); ?>
	<?php echo ($FUD_OPT_4 & 8 ? '<li><a href="/index.php?t=page&amp;'._rsid.'" title="P??ginas"><img src="/theme/default/images/pages.png" alt="" width="16" height="16" /> P??ginas</a></li>' : ''); ?>
	<?php echo ($FUD_OPT_3 & 134217728 ? '<li><a href="/index.php?t=cal&amp;'._rsid.'" title="Calendario"><img src="/theme/default/images/calendar.png" alt="" width="16" height="16" /> Calendario</a></li>' : ''); ?>
	<?php echo ($FUD_OPT_1 & 16777216 ? ' <li><a href="/index.php?t=search'.(isset($frm->forum_id) ? '&amp;forum_limiter='.(int)$frm->forum_id.'' : '' )  .'&amp;'._rsid.'" title="Buscar"><img src="/theme/default/images/top_search.png" alt="" width="16" height="16" /> Buscar</a></li>' : ''); ?>
	<li><a accesskey="h" href="/index.php?t=help_index&amp;<?php echo _rsid; ?>" title="Preguntas"><img src="/theme/default/images/top_help.png" alt="" width="16" height="16" /> Preguntas</a></li>
	<?php echo (($FUD_OPT_1 & 8388608 || (_uid && $FUD_OPT_1 & 4194304) || $usr->users_opt & 1048576) ? '<li><a href="/index.php?t=finduser&amp;btn_submit=Find&amp;'._rsid.'" title="Miembros"><img src="/theme/default/images/top_members.png" alt="" width="16" height="16" /> Miembros</a></li>' : ''); ?>
	<?php echo (__fud_real_user__ ? '<li><a href="/index.php?t=uc&amp;'._rsid.'" title="Acceder al panel de control del usuario"><img src="/theme/default/images/top_profile.png" alt="" width="16" height="16" /> Panel de control</a></li>' : ($FUD_OPT_1 & 2 ? '<li><a href="/index.php?t=register&amp;'._rsid.'" title="Registrarse"><img src="/theme/default/images/top_register.png" alt="" width="16" height="18" /> Registrarse</a></li>' : '')).'
	'.(__fud_real_user__ ? '<li><a href="/index.php?t=login&amp;'._rsid.'&amp;logout=1&amp;SQ='.$GLOBALS['sq'].'" title="Salir"><img src="/theme/default/images/top_logout.png" alt="" width="16" height="16" /> Salir [ '.filter_var($usr->alias, FILTER_SANITIZE_STRING).' ]</a></li>' : '<li><a href="/index.php?t=login&amp;'._rsid.'" title="Acceder"><img src="/theme/default/images/top_login.png" alt="" width="16" height="16" /> Acceder</a></li>'); ?>
	<li><a href="/index.php?t=index&amp;<?php echo _rsid; ?>" title="Inicio"><img src="/theme/default/images/top_home.png" alt="" width="16" height="16" /> Inicio</a></li>
	<?php echo ($is_a || ($usr->users_opt & 268435456) ? '<li><a href="/adm/index.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'" title="Administraci??n"><img src="/theme/default/images/top_admin.png" alt="" width="16" height="16" /> Administraci??n</a></li>' : ''); ?>
</ul>
</div>
<br /><?php echo $admin_cp; ?><br /><br />
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr>
	<th>Nombre de la encuesta</th>
	<th class="nw"><a class="thLnk" href="/index.php?t=polllist&amp;start=<?php echo $start; ?>&amp;oby=<?php echo $oby_rev_val; ?>&amp;<?php echo _rsid; ?>">Creada el</a></th>
	<th class="nw">Creada por</th>
	<th class="nw ac">Votos totales</th>
	<th class="nw ac">Acci??n</th>
</tr>
<?php echo $poll_entries; ?>
</table>
<br /><br />
<?php echo $pager; ?>
<br /><div class="ac"><span class="curtime"><b>Fecha y hora actual:</b> <?php echo strftime('%a %b %d %H:%M:%S %Z %Y', __request_timestamp__); ?></span></div>
<?php echo $page_stats; ?>
<?php echo (!empty($RIGHT_SIDEBAR) ? '
</td><td width="200px" align-"right" valign="top" class="sidebar-right">
	'.$RIGHT_SIDEBAR.'
' : ''); ?>
</td></tr></table>

</div>
<div class="footer ac">
	<b>.::</b>
	<a href="mailto:<?php echo $GLOBALS['ADMIN_EMAIL']; ?>">Contacto</a>
	<b>::</b>
	<a href="/index.php?t=index&amp;<?php echo _rsid; ?>">Inicio</a>
	<b>::.</b>
	<p class="SmallText">Propulsado por: FUDforum <?php echo $GLOBALS['FORUM_VERSION']; ?>.<br />Derechos de autor &copy;??2001-2021 <a href="http://fudforum.org/">FUDforum Bulletin Board Software</a></p>
</div>

</body></html>
