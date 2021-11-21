<?php
/**
* copyright            : (C) 2001-2011 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id$
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

function &rebuild_stats_cache($last_msg_id)
{
	$tm_expire = __request_timestamp__ - ($GLOBALS['LOGEDIN_TIMEOUT'] * 60);

	$obj = new stdClass();	// Initialize to prevent 'strict standards' notice.
	list($obj->last_user_id, $obj->user_count) = db_saq('SELECT MAX(id), count(*)-1 FROM fud30_users');

	$obj->online_users_anon	= q_singleval('SELECT count(*) FROM fud30_ses s WHERE time_sec>'. $tm_expire .' AND user_id>2000000000');
	$obj->online_users_hidden = q_singleval('SELECT count(*) FROM fud30_ses s INNER JOIN fud30_users u ON u.id=s.user_id WHERE s.time_sec>'. $tm_expire .' AND '. q_bitand('u.users_opt', 32768) .'>0');
	$obj->online_users_reg = q_singleval('SELECT count(*) FROM fud30_ses s INNER JOIN fud30_users u ON u.id=s.user_id WHERE s.time_sec>'. $tm_expire .' AND '. q_bitand('u.users_opt', 32768) .'=0');
	$c = uq(q_limit('SELECT u.id, u.alias, u.users_opt, u.custom_color FROM fud30_ses s INNER JOIN fud30_users u ON u.id=s.user_id WHERE s.time_sec>'. $tm_expire .' AND '. q_bitand('u.users_opt', 32768) .'=0 ORDER BY s.time_sec DESC', $GLOBALS['MAX_LOGGEDIN_USERS']));
	$obj->online_users_text = array();
	while ($r = db_rowarr($c)) {
		$obj->online_users_text[$r[0]] = draw_user_link($r[1], $r[2], $r[3]);
	}
	unset($c);

	q('UPDATE fud30_stats_cache SET
		cache_age='. __request_timestamp__ .',
		last_user_id='. (int)$obj->last_user_id .',
		user_count='. (int)$obj->user_count .',
		online_users_anon='. (int)$obj->online_users_anon .',
		online_users_hidden='. (int)$obj->online_users_hidden .',
		online_users_reg='. (int)$obj->online_users_reg .',
		online_users_text='. ssn(serialize($obj->online_users_text)));

	$obj->last_user_alias = q_singleval('SELECT alias FROM fud30_users WHERE id='. $obj->last_user_id);
	$obj->last_msg_subject = q_singleval('SELECT subject FROM fud30_msg WHERE id='. $last_msg_id);

	list($obj->most_online,$obj->most_online_time) = db_saq('SELECT most_online, most_online_time FROM fud30_stats_cache');
	/* Update most online users stats if needed. */
	if (($obj->online_users_reg + $obj->online_users_hidden + $obj->online_users_anon) > $obj->most_online) {
		$obj->most_online = $obj->online_users_reg + $obj->online_users_hidden + $obj->online_users_anon;
		$obj->most_online_time = __request_timestamp__;
		q('UPDATE fud30_stats_cache SET most_online='. $obj->most_online .', most_online_time='. $obj->most_online_time);
	} else if (!$obj->most_online_time) {
		$obj->most_online_time = __request_timestamp__;
	}

	return $obj;
}

$logedin = $forum_info = '';

if ($FUD_OPT_1 & 1073741824 || $FUD_OPT_2 & 16) {
	if (!($st_obj = db_sab('SELECT sc.*, m.subject AS last_msg_subject, u.alias AS last_user_alias FROM fud30_stats_cache sc INNER JOIN fud30_users u ON u.id=sc.last_user_id LEFT JOIN fud30_msg m ON m.id='. $last_msg_id .' WHERE sc.cache_age>'. (__request_timestamp__ - $STATS_CACHE_AGE)))) {
		$st_obj = rebuild_stats_cache($last_msg_id);
	} else if ($st_obj->online_users_text && (_uid || !($FUD_OPT_3 & 262144))) {
		$st_obj->online_users_text = unserialize($st_obj->online_users_text);
	}

	if (!$st_obj->most_online_time) {
		$st_obj->most_online_time = __request_timestamp__;
	}

	if ($FUD_OPT_1 & 1073741824 && (_uid || !($FUD_OPT_3 & 262144))) {
		if (!empty($st_obj->online_users_text)) {
			foreach($st_obj->online_users_text as $k => $v) {
				$logedin .= '<a href="/index.php?t=usrinfo&amp;id='.$k.'&amp;'._rsid.'">'.$v.'</a> ';
			}
		}
		$logedin = '<tr>
	<th class="wa">Lista de usuarios conectados '.(($FUD_OPT_1 & 536870912) ? ''.(_uid || !($FUD_OPT_3 & 131072) ? '[ <a href="/index.php?t=actions&amp;'._rsid.'" class="thLnk" rel="nofollow">Actividad del usuario</a> ]' : '' ) .'
'.(_uid || !($FUD_OPT_3 & 262144) ? '[ <a href="/index.php?t=online_today&amp;'._rsid.'" class="thLnk" rel="nofollow">Visitantes de hoy</a> ]' : '' ) .'' : '' ) .'</th>
</tr>
<tr>
	<td class="RowStyleA">
		<span class="SmallText">Hay <b>'.convertPlural($st_obj->online_users_reg, array(''.$st_obj->online_users_reg.' miembro',''.$st_obj->online_users_reg.' miembros')).'</b>, <b>'.convertPlural($st_obj->online_users_hidden, array(''.$st_obj->online_users_hidden.' miembro invisible',''.$st_obj->online_users_hidden.' miembros invisibles')).'</b> y <b>'.convertPlural($st_obj->online_users_anon, array(''.$st_obj->online_users_anon.' visitante',''.$st_obj->online_users_anon.' visitantes')).'</b> visitando este foro.&nbsp;&nbsp;&nbsp;
		<span class="adminColor">[Administrador]</span>&nbsp;&nbsp;
		<span class="modsColor">[Moderador]</span></span><br />
		'.$logedin.'
	</td>
</tr>';
	}
	if ($FUD_OPT_2 & 16) {
		$forum_info = '<tr>
	<td class="RowStyleB SmallText">
		Nuestros usuarios han publicado un total de <b>'.convertPlural($post_count, array(''.$post_count.' mensaje',''.$post_count.' mensajes')).'</b> dentro de <b>'.convertPlural($thread_count, array(''.$thread_count.' tema',''.$thread_count.' temas')).'</b>.<br />
		La mayor cantidad de usuarios en línea fue <b>'.$st_obj->most_online.'</b>, el <b>'.strftime('%a, %d %B %Y %H:%M', $st_obj->most_online_time).'</b><br />
		Tenemos <b>'.$st_obj->user_count.'</b> '.convertPlural($st_obj->user_count, array('usuario registrado','usuarios registrados')).'.<br />
		El último usuario registrado es <a href="/index.php?t=usrinfo&amp;id='.$st_obj->last_user_id.'&amp;'._rsid.'"><b>'.filter_var($st_obj->last_user_alias, FILTER_SANITIZE_STRING).'</b></a>
		'.($last_msg_id ? '<br />Último mensaje en el foro: <a href="/index.php?t='.d_thread_view.'&amp;goto='.$last_msg_id.'&amp;'._rsid.'#msg_'.$last_msg_id.'"><b>'.filter_var($st_obj->last_msg_subject, FILTER_SANITIZE_STRING).'</b></a>' : '' ) .'
	</td>
</tr>';
	}
}
?>
