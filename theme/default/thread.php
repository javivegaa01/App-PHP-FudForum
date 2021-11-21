<?php
/**
* copyright            : (C) 2001-2012 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id$
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
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

	return !$js_pager ? '<span class="SmallText fb">Páginas ('.$ttl_pg.'): ['.$page_pager_data.']</span>' : '<span class="SmallText fb">Páginas ('.$ttl_pg.'): ['.$page_pager_data.']</span>';
}function is_forum_notified($user_id, $forum_id)
{
	return q_singleval('SELECT 1 FROM fud30_forum_notify WHERE user_id='. $user_id .' AND forum_id='. $forum_id);
}

function forum_notify_add($user_id, $forum_id)
{
	db_li('INSERT INTO fud30_forum_notify (user_id, forum_id) VALUES ('. $user_id .', '. $forum_id .')', $ret);
}

function forum_notify_del($user_id, $forum_id)
{
	q('DELETE FROM fud30_forum_notify WHERE user_id='. $user_id .' AND forum_id='. $forum_id);
}/* Check moved topic permissions. */
function th_moved_perm_chk($frm_id)
{
	make_perms_query($fields, $join, $frm_id);
	$res = db_sab(q_limit('SELECT m.forum_id, '. $fields.
		' FROM fud30_forum f '. $join.
		' LEFT JOIN fud30_mod m ON m.user_id='._uid.' AND m.forum_id='. $frm_id .
		' WHERE f.id='. $frm_id, 1));
	if (!$res || (!($res->group_cache_opt & 2) && !$res->forum_id)) {
		return;
	}
	return 1;
}

/* Make sure that we have what appears to be a valid forum id. */
if (!isset($_GET['frm_id']) || (!($frm_id = (int)$_GET['frm_id']))) {
	invl_inp_err();
}

if (!isset($_GET['start']) || ($start = (int)$_GET['start']) < 1) {
	$start = 0;
}

/* This query creates frm object that contains info about the current
 * forum, category & user's subscription status & permissions to the
 * forum.
 */

make_perms_query($fields, $join, $frm_id);

$frm = db_sab(q_limit('SELECT	f.id, f.name, f.thread_count, f.cat_id,'.
			(_uid ? ' fn.forum_id AS subscribed, m.forum_id AS md, ' : ' 0 AS subscribed, 0 AS md, ').
			'a.ann_id AS is_ann, ms.post_stamp, '. $fields .'
		FROM fud30_forum f
		INNER JOIN fud30_cat c ON c.id=f.cat_id '.
		(_uid ? ' LEFT JOIN fud30_forum_notify fn ON fn.user_id='._uid.' AND fn.forum_id='. $frm_id .' LEFT JOIN fud30_mod m ON m.user_id='. _uid .' AND m.forum_id='. $frm_id : ' ')
		.$join.'
		LEFT JOIN fud30_ann_forums a ON a.forum_id='. $frm_id .'
		LEFT JOIN fud30_msg ms ON ms.id=f.last_post_id
		WHERE f.id='. $frm_id, 1));

if (!$frm) {
	invl_inp_err();
}
$frm->forum_id = $frm->id;
$MOD = ($is_a || $frm->md);
$lwi = q_singleval(q_limit('SELECT seq FROM fud30_tv_'. $frm_id .' ORDER BY seq DESC', 1));

/* Check that the user has permissions to access this forum. */
if (!($frm->group_cache_opt & 2) && !$MOD) {
	if (!isset($_GET['logoff'])) {
		std_error('login');
	}
	if ($FUD_OPT_2 & 32768) {
		header('Location: /index.php/i/'. _rsidl);
	} else {
		header('Location: /index.php?'. _rsidl);
	}
	exit;
}

if ($_GET['t'] == 'threadt') {
	$cur_frm_page = $start + 1;
} else {
	$cur_frm_page = floor($start / $THREADS_PER_PAGE) + 1;
}

/* Do various things for registered users. */
if (_uid) {
	if (isset($_GET['sub']) && sq_check(0, $usr->sq)) {
		forum_notify_add(_uid, $frm->id);
		$frm->subscribed = 1;
	} else if (isset($_GET['unsub']) && sq_check(0, $usr->sq)) {
		forum_notify_del(_uid, $frm->id);
		$frm->subscribed = 0;
	}
} else if (__fud_cache((int)$frm->post_stamp)) {
	return;
}

$ppg = $usr->posts_ppg ? $usr->posts_ppg : $POSTS_PER_PAGE;

/* Handling of forum level announcements (should be merged with non-forum announcements in index.php.t). */
$announcements = '';
if ($frm->is_ann) {
	$today = gmdate('Ymd', __request_timestamp__);
	$res = uq('SELECT a.subject, a.text, a.ann_opt FROM fud30_announce a INNER JOIN fud30_ann_forums af ON a.id=af.ann_id AND af.forum_id='. $frm->id .' WHERE a.date_started<='. $today .' AND a.date_ended>='. $today);
	while ($r = db_rowarr($res)) {
		if (!_uid && $r[2] & 2) {
			continue;	// Only for logged in users.
		}
		if (_uid && $r['2'] & 4) {
			continue;	// Only for anonomous users.
		}
		if (defined('plugins')) {
			list($r[0], $r[1]) = plugin_call_hook('ANNOUNCEMENT', array($r[0], $r[1]));
		}
		$announcements .= '<fieldset class="AnnText">
	<legend class="AnnSubjText">'.$r[0].'</legend>
	'.$r[1].'
</fieldset>';
	}
	unset($res);
}$collapse = $usr->cat_collapse_status ? unserialize($usr->cat_collapse_status) : array();
	$cat_id = !empty($_GET['cat'])    ? (int) $_GET['cat']    : 0;
	$frm_id = !empty($_GET['frm_id']) ? (int) $_GET['frm_id'] : 0;

	if ($cat_id && !empty($collapse[$cat_id])) {
		$collapse[$cat_id] = 0;
	}

	require $FORUM_SETTINGS_PATH .'idx.inc';
	if (!isset($cidxc[$cat_id])) {
		$cat_id = 0;
	}

	$cbuf = $forum_list_table_data = $cat_path = '';

	if ($cat_id) {
		$cid = $cat_id;
		while (($cid = $cidxc[$cid][4]) > 0) {
			$cat_path = '&nbsp;&raquo; <a href="/index.php?t=i&amp;cat='.$cid.'&amp;'._rsid.'">'.$cidxc[$cid][1].'</a>'. $cat_path;
		}
		$cat_path = '<br />
<a href="/index.php?t=i&amp;'._rsid.'">Inicio</a>
'.$cat_path.'&nbsp;&raquo; <b>'.$cidxc[$cat_id][1].'</b>';
	}

	/* List of fetched fields & their ids
	  0	msg.subject,
	  1	msg.id AS msg_id,
	  2	msg.post_stamp,
	  3	users.id AS user_id,
	  4	users.alias
	  5	forum.cat_id,
	  6	forum.forum_icon
	  7	forum.id
	  8	forum.last_post_id
	  9	forum.moderators
	  10	forum.name
	  11	forum.descr
	  12	forum.url_redirect
	  13	forum.post_count
	  14	forum.thread_count
	  15	forum_read.last_view
	  16	is_moderator
	  17	read perm
	  18	is the category using compact view
	*/
	$c = uq('SELECT
				m.subject, m.id, m.post_stamp,
				u.id, u.alias,
				f.cat_id, f.forum_icon, f.id, f.last_post_id, f.moderators, f.name, f.descr, f.url_redirect, f.post_count, f.thread_count,
				'. (_uid ? 'fr.last_view, mo.id, COALESCE(g2.group_cache_opt, g1.group_cache_opt) AS group_cache_opt' : '0,0,g1.group_cache_opt') .',
				c.cat_opt
			FROM fud30_fc_view v
			INNER JOIN fud30_cat c ON c.id=v.c
			INNER JOIN fud30_forum f ON f.id=v.f
			INNER JOIN fud30_group_cache g1 ON g1.user_id='. (_uid ? 2147483647 : 0) .' AND g1.resource_id=f.id
			LEFT JOIN fud30_msg m ON f.last_post_id=m.id
			LEFT JOIN fud30_users u ON u.id=m.poster_id '.
			(_uid ? ' LEFT JOIN fud30_forum_read fr ON fr.forum_id=f.id AND fr.user_id='. _uid .' LEFT JOIN fud30_mod mo ON mo.user_id='. _uid .' AND mo.forum_id=f.id LEFT JOIN fud30_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=f.id' : '').
			' WHERE f.parent = '. $frm_id .
			((!$is_a || $cat_id) ?  ' AND ' : '') .
			($is_a ? '' : (_uid ? ' (mo.id IS NOT NULL OR ('. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 1) .' > 0))' : ' ('. q_bitand('g1.group_cache_opt', 1) .' > 0)')) .
			($cat_id ? ($is_a ? '' : ' AND ') .' v.c IN('. implode(',', ($cf = $cidxc[$cat_id][5])) .') ' : '') .' ORDER BY v.id');

	$post_count = $thread_count = $last_msg_id = $cat = 0;
	while ($r = db_rowarr($c)) {
		/* Increase thread & post count. */
		$post_count += $r[13];
		$thread_count += $r[14];

		$cid = (int) $r[5];

		if ($cat != $cid && !$frm_id) {
			if ($cbuf) { /* If previous category was using compact view, print forum row. */
				if (empty($collapse[$i[4]])) { /* Only show if parent is not collapsed as well. */
					$forum_list_table_data .= '<tr class="row child-c'.$cat.'">
	<td class="RowStyleA wo hide2">&nbsp;</td>
	<td class="RowStyleB ac wo hide2">&nbsp;</td>
	<td  class="RowStyleA wa" colspan="4">Foros disponibles:'.$cbuf.'</td>
</tr>';
				}
				$cbuf = '';
			}

			foreach ($cidxc as $k => $i) {
				/* 2nd check ensures that we don't end up displaying categories without any children. */ 
				if (($cat_id && !isset($cf[$k])) || ($cid != $k && $i[4] >= $cidxc[$cid][4])) {
					continue;
				}

				/* If parent category is collapsed, hide child category. */
				if ($i[4] && !empty($collapse[$i[4]])) {
					$collapse[$k] = 1;
				}

				if ($k == $cid) {
					break;	// Got it!
				}
			}
			$cat = $cid;
			if ($i[3] & 1 && $k != $cat_id && !($i[3] & 4)) {
				if (!isset($collapse[$k])) {
					$collapse[$k] = !($i[3] & 2);
				}
				$forum_list_table_data .= '<tr id="c'.$r[5].'" style="display: table-row;">
	<td class="CatDesc '.(empty($collapse[$cid]) ? 'expanded' : 'collapsed' )  .'" colspan="5" style="padding-left: '.($i[0] ? $i[0] * 20 : '0').'px;">
		<a href="/index.php?t=index&amp;cat='.$k.'&amp;'._rsid.'" class="CatLink">'.$i[1].'</a> '.$i[2].'
	</td>
	<td class="CatDesc hide1">
	'.(key($cidxc) ? '<a href="javascript://" onclick=\'nextCat("c'.$k.'")\'><img src="/theme/default/images/down.png" alt="" width="16" height="11" border="0" style="vertical-align: top; float: right;" /></a>' : '' )  .'
	'.($cat ? '<a href="javascript://" onclick=\'prevCat("c'.$k.'")\'><img src="/theme/default/images/up.png" border="0" alt="" width="16" height="11" style="vertical-align: top; float: right;" /></a>' : '' )  .'
</td>
</tr>';
			} else {
				if ($i[3] & 4) {
					++$i[0];
				}
				$forum_list_table_data .= '<tr id="c'.$r[5].'" style="display: table-row;">
	<td class="CatDesc CatLockPad" colspan="5" style="padding-left: '.($i[0] ? $i[0] * 20 : '0').'px;">
		<span class="CatLockedName"><a href="/index.php?t=index&amp;cat='.$k.'&amp;'._rsid.'" class="CatLink">'.$i[1].'</a></span> '.$i[2].'
	</td>
	<td class="CatDesc hide1">
	'.(key($cidxc) ? '<a href="javascript://" onclick=\'nextCat("c'.$k.'")\'><img src="/theme/default/images/down.png" alt="" width="16" height="11" border="0" style="vertical-align: top; float: right;" /></a>' : '' )  .'
	'.($cat ? '<a href="javascript://" onclick=\'prevCat("c'.$k.'")\'><img src="/theme/default/images/up.png" border="0" alt="" width="16" height="11" style="vertical-align: top; float: right;" /></a>' : '' )  .'
</td>
</tr>';
			}
		}

		/* Compact category view (ignore when expanded). */
		if ($r[18] & 4 && $cat_id != $cid) {
			$cbuf .= '&nbsp; '.(_uid && $r[15] < $r[2] && $usr->last_read < $r[2] ? '**' : '' )  .'
<a href="'.(empty($r[12]) ? '/index.php?t='.t_thread_view.'&amp;frm_id='.$r[7].'&amp;'._rsid : $r[12] )  .'">'.$r[10].'</a>';
			continue;
		}

		/* Visible forum with no 'read' permission. */
		if (!($r[17] & 2) && !$is_a && !$r[16]) {
			$forum_list_table_data .= '<tr style="display: '.(empty($collapse[$cid]) ? 'table-row' : 'none' )  .'" class="child-c'.$r[5].'">
	<td class="RowStyleA" colspan="6">'.$r[10].($r[11] ? '<br />'.$r[11] : '').'</td>
</tr>';
			continue;
		}

		/* Code to determine the last post id for 'latest' forum message. */
		if ($r[8] > $last_msg_id) {
			$last_msg_id = $r[8];
		}

		if (!_uid) { /* Anon user. */
			$forum_read_indicator = '<img title="Sólo los miembros registrados del foro pueden buscar mensajes leídos y no leídos" src="/theme/default/images/existing_content.png" alt="Sólo los miembros registrados del foro pueden buscar mensajes leídos y no leídos" width="30" height="30" />';
		} else if ($r[15] < $r[2] && $usr->last_read < $r[2]) {
			$forum_read_indicator = '<img title="Mensajes nuevos" src="/theme/default/images/new_content.png" alt="Mensajes nuevos" width="30" height="30" />';
		} else {
			$forum_read_indicator = '<img title="No hay mensajes nuevos" src="/theme/default/images/existing_content.png" alt="No hay mensajes nuevos" width="30" height="30" />';
		}

		if ($r[9] && ($mods = unserialize($r[9]))) {
			$moderators = '';	// List of forum moderators.
			$modcount = 0;		// Use singular or plural message form.

			foreach($mods as $k => $v) {
				$moderators .= '<a href="/index.php?t=usrinfo&amp;id='.$k.'&amp;'._rsid.'">'.$v.'</a> &nbsp;';
				$modcount++;
			}
			$moderators = '<div class="TopBy"><b>'.convertPlural($modcount, array('Moderador','Moderadores')).':</b> '.$moderators.'</div>';
		} else {
			$moderators = '&nbsp;';
		}

		$forum_list_table_data .= '<tr style="display: '.(empty($collapse[$cid]) ? 'table-row' : 'none' )  .'" class="row child-c'.$r[5].'">
	<td class="RowStyleA wo hide2">'.($r[6] ? '<img src="/images/forum_icons/'.$r[6].'" alt="Icono del foro" />' : '&nbsp;' ) .'</td>
	<td class="RowStyleB ac wo hide2">'.(empty($r[12]) ? $forum_read_indicator : '<img title="Redirección" src="/theme/default/images/moved.png" alt="" />' )  .'</td>
	<td class="RowStyleA wa"><a href="'.(empty($r[12]) ? '/index.php?t='.t_thread_view.'&amp;frm_id='.$r[7].'&amp;'._rsid : $r[12] )  .'" class="big">'.$r[10].'</a>'.($r[11] ? '<br />'.$r[11] : '').$moderators.'</td>
	<td class="RowStyleB ac hide1">'.(empty($r[12]) ? $r[13] : '--' )  .'</td>
	<td class="RowStyleB ac hide1">'.(empty($r[12]) ? $r[14] : '--' )  .'</td>
	<td class="RowStyleA ac nw hide2">'.(empty($r[12]) ? ($r[8] ? '<span class="DateText">'.strftime('%a, %d %B %Y', $r[2]).'</span><br />De: '.($r[3] ? '<a href="/index.php?t=usrinfo&amp;id='.$r[3].'&amp;'._rsid.'">'.$r[4].'</a>' : $GLOBALS['ANON_NICK'] ) .' <a href="/index.php?t='.d_thread_view.'&amp;goto='.$r[8].'&amp;'._rsid.'#msg_'.$r[8].'"><img title="'.$r[0].'" src="/theme/default/images/goto.gif" alt="'.$r[0].'" width="9" height="9" /></a>' : 'n/d' )  : '--' )  .'</td>
</tr>';
	}
	unset($c);

	if ($cbuf) { /* If previous category was using compact view, print forum row. */
		$forum_list_table_data .= '<tr class="row child-c'.$cat.'">
	<td class="RowStyleA wo hide2">&nbsp;</td>
	<td class="RowStyleB ac wo hide2">&nbsp;</td>
	<td  class="RowStyleA wa" colspan="4">Foros disponibles:'.$cbuf.'</td>
</tr>';
	}function &get_all_read_perms($uid, $mod)
{
	$limit = array(0);

	$r = uq('SELECT resource_id, group_cache_opt FROM fud30_group_cache WHERE user_id='. _uid);
	while ($ent = db_rowarr($r)) {
		$limit[$ent[0]] = $ent[1] & 2;
	}
	unset($r);

	if (_uid) {
		if ($mod) {
			$r = uq('SELECT forum_id FROM fud30_mod WHERE user_id='. _uid);
			while ($ent = db_rowarr($r)) {
				$limit[$ent[0]] = 2;
			}
			unset($r);
		}

		$r = uq('SELECT resource_id FROM fud30_group_cache WHERE resource_id NOT IN ('. implode(',', array_keys($limit)) .') AND user_id=2147483647 AND '. q_bitand('group_cache_opt', 2) .' > 0');
		while ($ent = db_rowarr($r)) {
			if (!isset($limit[$ent[0]])) {
				$limit[$ent[0]] = 2;
			}
		}
		unset($r);
	}

	return $limit;
}

function perms_from_obj($obj, $adm)
{
	$perms = 1|2|4|8|16|32|64|128|256|512|1024|2048|4096|8192|16384|32768|262144;

	if ($adm || $obj->md) {
		return $perms;
	}

	return ($perms & $obj->group_cache_opt);
}

function make_perms_query(&$fields, &$join, $fid='')
{
	if (!$fid) {
		$fid = 'f.id';
	}

	if (_uid) {
		$join = ' INNER JOIN fud30_group_cache g1 ON g1.user_id=2147483647 AND g1.resource_id='. $fid .' LEFT JOIN fud30_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id='. $fid .' ';
		$fields = ' COALESCE(g2.group_cache_opt, g1.group_cache_opt) AS group_cache_opt ';
	} else {
		$join = ' INNER JOIN fud30_group_cache g1 ON g1.user_id=0 AND g1.resource_id='. $fid .' ';
		$fields = ' g1.group_cache_opt ';
	}
}

	ses_update_status($usr->sid, 'Explorando el foro <a href="/index.php?t=thread&amp;frm_id='.$frm->id.'">'.$frm->name.'</a>', $frm_id);
	$RSS = ($FUD_OPT_2 & 1048576 ? '<link rel="alternate" type="application/rss+xml" title="Leer este foro con lector un RSS (XML)" href="/feed.php?mode=m&amp;l=1&amp;basic=1&amp;frm='.$frm->id.'&amp;n=10" />
' : '' )  ;

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
				$thr_exch = '| <a href="/index.php?t=thr_exch&amp;'._rsid.'">Intercambio de hilos de discusión</a> <span class="GenTextRed">('.$thr_exchc.')</span>';
			}

			// All account approvals.
			if ($FUD_OPT_2 & 1024 && ($accounts_pending_approval = q_singleval('SELECT count(*) FROM fud30_users WHERE users_opt>=2097152 AND '. q_bitand('users_opt', 2097152) .' > 0 AND id > 0'))) {
				$accounts_pending_approval = '| <a href="/adm/admuserapr.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'">Cuentas pendientes de aprobación</a> <span class="GenTextRed">('.$accounts_pending_approval.')</span>';
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
				$thr_exch = '| <a href="/index.php?t=thr_exch&amp;'._rsid.'">Intercambio de hilos de discusión</a> <span class="GenTextRed">('.$thr_exchc.')</span>';
			}

			$q_limit = ' INNER JOIN fud30_mod mm ON f.id=mm.forum_id AND mm.user_id='. _uid;
		}

		// Messages requiring approval.
		if ($approve_count = q_singleval('SELECT count(*) FROM fud30_msg m INNER JOIN fud30_thread t ON m.thread_id=t.id INNER JOIN fud30_forum f ON t.forum_id=f.id '. $q_limit .' WHERE m.apr=0 AND f.forum_opt>=2')) {
			$mod_que = '<a href="/index.php?t=modque&amp;'._rsid.'">Cola de moderación</a> <span class="GenTextRed">('.$approve_count.')</span>';
		}
	} else if ($usr->users_opt & 268435456 && $FUD_OPT_2 & 1024 && ($accounts_pending_approval = q_singleval('SELECT count(*) FROM fud30_users WHERE users_opt>=2097152 AND '. q_bitand('users_opt', 2097152) .' > 0 AND id > 0'))) {
		$accounts_pending_approval = '| <a href="/adm/admuserapr.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'">Cuentas pendientes de aprobación</a> <span class="GenTextRed">('.$accounts_pending_approval.')</span>';
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
	}function tmpl_create_forum_select($frm_id, $mod)
{
	if (!isset($_GET['t']) || ($_GET['t'] != 'thread' && $_GET['t'] != 'threadt')) {
		$dest = t_thread_view;
	} else {
		$dest = $_GET['t'];
	}

	if ($mod) { /* Admin optimization. */
		$c = uq('SELECT f.id, f.name, c.id FROM fud30_fc_view v INNER JOIN fud30_forum f ON f.id=v.f INNER JOIN fud30_cat c ON f.cat_id=c.id WHERE f.url_redirect IS NULL ORDER BY v.id');
	} else {
		$c = uq('SELECT f.id, f.name, c.id
			FROM fud30_fc_view v
			INNER JOIN fud30_forum f ON f.id=v.f
			INNER JOIN fud30_cat c ON f.cat_id=c.id
			INNER JOIN fud30_group_cache g1 ON g1.user_id='. (_uid ? '2147483647' : '0') .' AND g1.resource_id=f.id '.
			(_uid ? ' LEFT JOIN fud30_mod mm ON mm.forum_id=f.id AND mm.user_id='. _uid .' LEFT JOIN fud30_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=f.id WHERE mm.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 1) .' > 0 '  : ' WHERE '. q_bitand('g1.group_cache_opt', 1) .' > 0 AND f.url_redirect IS NULL ').
			'ORDER BY v.id');
	}
	$f = array($frm_id => 1);

	$frmcount = 0;
	$oldc = $selection_options = '';
	while ($r = db_rowarr($c)) {
		if ($oldc != $r[2]) {
			foreach ($GLOBALS['cat_cache'] as $k => $i) {
				if ($r[2] != $k && $i[0] >= $GLOBALS['cat_cache'][$r[2]][0]) {
					continue;
				}
	
				$selection_options .= '<option disabled="disabled">- '.($tabw = ($i[0] ? str_repeat('&nbsp;&nbsp;&nbsp;', $i[0]) : '')).$i[1].'</option>';
				if ($k == $r[2]) {
					break;
				}
			}
			$oldc = $r[2];
		}
		$selection_options .= '<option value="'.$r[0].'"'.(isset($f[$r[0]]) ? ' selected="selected"' : '').'>'.$tabw.'&nbsp;&nbsp;'.$r[1].'</option>';
		$frmcount++;
	}
	unset($c);
	
	return ($frmcount > 1 ? '
<span class="SmallText fb">Ir al foro:</span>
<form action="/index.php" id="frmquicksel" method="get">
	<input type="hidden" name="t" value="'.$dest.'" />
	'._hs.'
	<select class="SmallText" name="frm_id">
		'.$selection_options.'
	</select>&nbsp;&nbsp;
	<input type="submit" class="button small" name="frm_goto" value="Ir" />
</form>
' : '' ) ;
}if (!isset($th)) {
	$th = 0;
}
if (!isset($frm->id)) {
	$frm = new stdClass();	// Initialize to prevent 'strict standards' notice.
	$frm->id = 0;
}require $GLOBALS['FORUM_SETTINGS_PATH'] .'cat_cache.inc';

function draw_forum_path($cid, $fn='', $fid=0, $tn='')
{
	global $cat_par, $cat_cache;

	$data = '';
	do {
		$data = '&nbsp;&raquo; <a href="/index.php?t=i&amp;cat='.$cid.'&amp;'._rsid.'">'.$cat_cache[$cid][1].'</a>'. $data;
	} while (($cid = $cat_par[$cid]) > 0);

	if ($fid) {
		$data .= '&nbsp;&raquo; <a href="/index.php?t='.t_thread_view.'&amp;frm_id='.$fid.'&amp;'._rsid.'">'.$fn.'</a>';
	} else if ($fn) {
		$data .= '&nbsp;&raquo; <strong>'.$fn.'</strong>';
	}

	return '<a href="/index.php?t=i&amp;'._rsid.'">Inicio</a>'.$data.($tn ? '&nbsp;&raquo; <strong>'.$tn.'</strong>' : '');
}

	$TITLE_EXTRA = ': '.$frm->name;

	$result = q('SELECT
		m.attach_cnt, m.poll_id, m.subject, m.icon, m.post_stamp,
		u.alias, u.id,
		u2.id, u2.alias,
		m2.id, m2.post_stamp,
		f.id, f.name,
		t.id, t.moved_to, t.root_msg_id, t.replies, t.rating, t.thread_opt, t.views, 
		r.last_view, t.n_rating, t.tdescr,
		m.foff, m.length, m.file_id
		FROM fud30_tv_'. $frm_id .' tv
			INNER JOIN fud30_thread	t	ON tv.thread_id=t.id
			INNER JOIN fud30_msg	m	ON t.root_msg_id=m.id
			INNER JOIN fud30_msg	m2	ON m2.id=t.last_post_id
			LEFT JOIN fud30_users	u	ON u.id=m.poster_id
			LEFT JOIN fud30_users	u2	ON u2.id=m2.poster_id
			LEFT JOIN fud30_forum	f	ON f.id=t.moved_to
			LEFT JOIN fud30_read 	r	ON t.id=r.thread_id AND r.user_id='. _uid .'
			WHERE tv.seq BETWEEN '. ($lwi - ($cur_frm_page * $THREADS_PER_PAGE) + 1) .' AND '. ($lwi - (($cur_frm_page - 1) * $THREADS_PER_PAGE)) .'
			ORDER BY tv.seq DESC');
	/* Field Defenitions
	 * 0 msg.attach_cnt
	 * 1 msg.poll_id
	 * 2 msg.subject
	 * 3 msg.icon
	 * 4 msg.post_stamp
	 * 5 users.alias
	 * 6 users.id
	 * 7 fud_users_2.id
	 * 8 fud_users_2.alias
	 * 9 fud_msg_2.id
	 * 10 fud_msg_2.post_stamp
	 * 11 forum.id
	 * 12 forum.name
	 * 13 thread.id
	 * 14 thread.moved_to
	 * 15 thread.root_msg_id
	 * 16 thread.replies
	 * 17 thread.thread_opt
	 * 18 thread.rating
	 * 19 thread.views
	 * 20 read.last_view
	 * 21 thread.n_rating
	 * 22 thread.tdescr
	 * 23 msg.foff
	 * 24 msg.length
	 * 25 msg.file_id
	 */

	if (!($r = db_rowarr($result))) {
		$thread_list_table_data = '<tr>
	<td class="RowStyleA ac" colspan="6"><span class="GenText">No hay mensajes en este foro.<br />Sé el primero en escribir un tema en este foro.</span></td>
</tr>';
		$threaded_view = $admin_heading_row = ''; $mo = 0;
	} else {
		$admin_heading_row = ($MOD || ($mo = $frm->group_cache_opt & 8224));
		$admin_control_row = $thread_list_table_data = '';

		do {
			$r[18] = (int) $r[18];

			if ($r[14]) {
				/* Additional security check for moved forums. */
				if (!$is_a && $r[11] && !th_moved_perm_chk($r[11])) {
					continue;
				}
				$thread_list_table_data .= '<tr>
	<td class="RowStyleB wo hide2"><img src="/theme/default/images/moved.png" title="Este hilo de discusión ha sido movido a otro foro" alt="" /></td>
	<td class="RowStyleB ac GenText" colspan="5"><a href="/index.php?t='.d_thread_view.'&amp;goto='.$r[15].'&amp;'._rsid.'#msg_'.$r[15].'">'.$r[2].'</a> se trasladó a <a href="/index.php?t='.t_thread_view.'&amp;frm_id='.$r[11].'&amp;'._rsid.'">'.$r[12].'</a> por el moderador.</td>
</tr>';
				continue;
			}
			$msg_count = $r[16] + 1;

			if ($msg_count > $ppg && $usr->users_opt & 256) {
				if ($THREAD_MSG_PAGER < ($pgcount = ceil($msg_count/$ppg))) {
					$i = $pgcount - $THREAD_MSG_PAGER;
					$mini_pager_data = '&nbsp;...';
				} else {
					$mini_pager_data = '';
					$i = 0;
				}

				while ($i < $pgcount) {
					$mini_pager_data .= '&nbsp;<a href="/index.php?t='.d_thread_view.'&amp;th='.$r[13].'&amp;start='.($i * $ppg).'&amp;'._rsid.'">'.++$i.'</a>';
				}

				$mini_thread_pager = $mini_pager_data ? '<span class="SmallText">(<img src="/theme/default/images/pager.gif" alt="" />'.$mini_pager_data.')</span>' : '';
			} else {
				$mini_thread_pager = '';
			}

			$thread_read_status = $first_unread_msg_link = '';
			if (_uid && $usr->last_read < $r[10] && $r[10] > $r[20]) {
				$thread_read_status = ($r[18] & 1) ? '<img src="/theme/default/images/unreadlocked.png" title="Hilo de discusión bloqueado con mensajes no leídos" alt="" width="22" height="22"  />' : '<img src="/theme/default/images/unread.png" title="Este hilo contiene mensajes que aún no ha leído" alt="" width="22" height="22" />';
				/* Do not show 1st unread message link if thread has no replies. */
				if ($r[16]) {
					$first_unread_msg_link = '<a href="/index.php?t='.d_thread_view.'&amp;th='.$r[13].'&amp;unread=1&amp;'._rsid.'"><img src="/theme/default/images/newposts.gif" title="Ir al primer mensaje no leído de este hilo de discusión" alt="" /></a>&nbsp;';
				}
			} else if ($r[18] & 1) {
				$thread_read_status = '<img src="/theme/default/images/readlocked.png" title="Este hilo de discusión se ha bloqueado" alt="" width="22" height="22" />';
			} else if (!_uid) {
				$thread_read_status = '<img src="/theme/default/images/read.png" title="Sólo los usuarios registrados pueden hacer seguimiento de mensajes leídos y no leídos" alt="" width="22" height="22" />';
			} else {
				$thread_read_status = '<img src="/theme/default/images/read.png" title="Este hilo de discusión no tiene mensajes no leídos" alt="" width="22" height="22" />';
			}

			if ($admin_heading_row) {
				if ($MOD || $mo == 8224) {
					$admin_control_row = '<div class="ModOpt">Opciones del moderador: <a href="javascript://" onclick="window_open(\'/index.php?t=mvthread&amp;'._rsid.'&amp;th='.$r[13].'\', \'th_move\', 300, 400);">Trasladar</a> | <a href="/index.php?t=mmod&amp;'._rsid.'&amp;th='.$r[13].'&amp;del='.$r[15].'">Borrar</a> | <a href="/index.php?t=mmod&amp;'._rsid.'&amp;th='.$r[13].'&amp;'.($r[18] & 1 ? 'unlock' : 'lock' )  .'=1&amp;SQ='.$GLOBALS['sq'].'">'.($r[18] & 1 ? 'Desbloquear el hilo de discusión' : 'Bloquear hilo de discusión' )  .'</a> <input type="checkbox" name="_sel[]" value="'.$r[13].'" /></div>';
				} else if ($mo & 32) {
					$admin_control_row = '<div class="ModOpt">Opciones del moderador: <a href="/index.php?t=mmod&amp;'._rsid.'&amp;th='.$r[13].'&amp;del='.$r[15].'">Borrar</a></div>';
				} else {
					$admin_control_row = '<div class="ModOpt">Opciones del moderador: <a href="javascript://" onclick="window_open(\'/index.php?t=mvthread&amp;'._rsid.'&amp;th='.$r[13].'\', \'th_move\', 300, 400);">Trasladar</a></div>';
				}
			}
			$thread_list_table_data .= '<tr class="row">
	<td class="RowStyleB wo hide2">'.$thread_read_status.'</td>
	<td class="RowStyleB wo ac hide2">'.($r[3] ? '<img src="/images/message_icons/'.$r[3].'" alt="'.$r[3].'" />' : '&nbsp;' ) .'</td>
	<td class="RowStyleA">'.(($r[18] > 1) ? ($r[18] & 4 ? '<span class="StClr">fijo:&nbsp;</span>' : '<span class="AnClr">Anuncio:&nbsp;</span>' )  : '' ) .$first_unread_msg_link.($r[1] ? 'Encuesta:&nbsp;' : '' ) .($r[0] ? '<img src="/theme/default/images/attachment.gif" alt="" />' : '' ) .'<a class="big" href="/index.php?t='.d_thread_view.'&amp;th='.$r[13].'&amp;start=0&amp;'._rsid.'">'.$r[2].'</a>'.($r[22] ? '<br /><span class="small">'.$r[22].'</span>' : '' )  .' '.((($FUD_OPT_2 & 4096) && $r[17]) ? ($MOD || $mo == 8224 ? '<a href="javascript://" onclick="window_open(\'/index.php?t=ratingtrack&amp;'._rsid.'&amp;th='.$r[13].'\', \'th_rating_track\', 300, 400);">' : '' ) .'<img src="/theme/default/images/'.$r[17].'stars.gif" title="'.$r[17].' de '.convertPlural($r[21], array(''.$r[21].' voto',''.$r[21].' votos')).'" alt="" />'.($MOD || $mo == 8224 ? '</a>' : '' ) : '' ) .' '.$mini_thread_pager.' <div class="TopBy">De: '.($r[5] ? '<a href="/index.php?t=usrinfo&amp;id='.$r[6].'&amp;'._rsid.'">'.$r[5].'</a>' : $GLOBALS['ANON_NICK'].'' ) .' el <span class="DateText">'.strftime('%a, %d %B %Y', $r[4]).'</span></div>'.$admin_control_row.'</td>
	<td class="RowStyleB ac hide1">'.$r[16].'</td>
	<td class="RowStyleB ac hide1">'.$r[19].'</td>
	<td class="RowStyleC nw hide2"><span class="DateText">'.strftime('%a, %d %B %Y %H:%M', $r[10]).'</span><br />De: '.($r[8] ? '<a href="/index.php?t=usrinfo&amp;id='.$r[7].'&amp;'._rsid.'">'.$r[8].'</a>' : $GLOBALS['ANON_NICK'].'' ) .' <a href="/index.php?t='.d_thread_view.'&amp;th='.$r[13].'&amp;goto='.$r[9].'&amp;'._rsid.'#msg_'.$r[9].'"><img src="/theme/default/images/goto.gif" title="Ir al último mensaje de este hilo de discusión" alt="" /></a></td>
</tr>';
		} while (($r = db_rowarr($result)));
	}

	if ($FUD_OPT_2 & 32768) {
		$page_pager = tmpl_create_pager($start, $THREADS_PER_PAGE, $frm->thread_count, '/index.php/sf/thread/'. $frm_id .'/1/', '/'. _rsid);
	} else {
		$page_pager = tmpl_create_pager($start, $THREADS_PER_PAGE, $frm->thread_count, '/index.php?t=thread&amp;frm_id='. $frm_id .'&amp;'. _rsid);
	}

if ($FUD_OPT_2 & 2 || $is_a) {	// PUBLIC_STATS is enabled or Admin user.
	$page_gen_time = number_format(microtime(true) - __request_timestamp_exact__, 5);
	$page_stats = $FUD_OPT_2 & 2 ? '<br /><div class="SmallText al">Tiempo total que tardó la generación de la página: '.convertPlural($page_gen_time, array(''.$page_gen_time.' segundo',''.$page_gen_time.' segundos')).'</div>' : '<br /><div class="SmallText al">Tiempo total que tardó la generación de la página: '.convertPlural($page_gen_time, array(''.$page_gen_time.' segundo',''.$page_gen_time.' segundos')).'</div>';
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
      <br /><label accesskey="f" title="Búsqueda en el foro">Búsqueda en el foro:<br />
      <input type="search" name="srch" value="" size="20" placeholder="Búsqueda en el foro" /></label>
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
	<?php echo ($FUD_OPT_4 & 8 ? '<li><a href="/index.php?t=page&amp;'._rsid.'" title="Páginas"><img src="/theme/default/images/pages.png" alt="" width="16" height="16" /> Páginas</a></li>' : ''); ?>
	<?php echo ($FUD_OPT_3 & 134217728 ? '<li><a href="/index.php?t=cal&amp;'._rsid.'" title="Calendario"><img src="/theme/default/images/calendar.png" alt="" width="16" height="16" /> Calendario</a></li>' : ''); ?>
	<?php echo ($FUD_OPT_1 & 16777216 ? ' <li><a href="/index.php?t=search'.(isset($frm->forum_id) ? '&amp;forum_limiter='.(int)$frm->forum_id.'' : '' )  .'&amp;'._rsid.'" title="Buscar"><img src="/theme/default/images/top_search.png" alt="" width="16" height="16" /> Buscar</a></li>' : ''); ?>
	<li><a accesskey="h" href="/index.php?t=help_index&amp;<?php echo _rsid; ?>" title="Preguntas"><img src="/theme/default/images/top_help.png" alt="" width="16" height="16" /> Preguntas</a></li>
	<?php echo (($FUD_OPT_1 & 8388608 || (_uid && $FUD_OPT_1 & 4194304) || $usr->users_opt & 1048576) ? '<li><a href="/index.php?t=finduser&amp;btn_submit=Find&amp;'._rsid.'" title="Miembros"><img src="/theme/default/images/top_members.png" alt="" width="16" height="16" /> Miembros</a></li>' : ''); ?>
	<?php echo (__fud_real_user__ ? '<li><a href="/index.php?t=uc&amp;'._rsid.'" title="Acceder al panel de control del usuario"><img src="/theme/default/images/top_profile.png" alt="" width="16" height="16" /> Panel de control</a></li>' : ($FUD_OPT_1 & 2 ? '<li><a href="/index.php?t=register&amp;'._rsid.'" title="Registrarse"><img src="/theme/default/images/top_register.png" alt="" width="16" height="18" /> Registrarse</a></li>' : '')).'
	'.(__fud_real_user__ ? '<li><a href="/index.php?t=login&amp;'._rsid.'&amp;logout=1&amp;SQ='.$GLOBALS['sq'].'" title="Salir"><img src="/theme/default/images/top_logout.png" alt="" width="16" height="16" /> Salir [ '.filter_var($usr->alias, FILTER_SANITIZE_STRING).' ]</a></li>' : '<li><a href="/index.php?t=login&amp;'._rsid.'" title="Acceder"><img src="/theme/default/images/top_login.png" alt="" width="16" height="16" /> Acceder</a></li>'); ?>
	<li><a href="/index.php?t=index&amp;<?php echo _rsid; ?>" title="Inicio"><img src="/theme/default/images/top_home.png" alt="" width="16" height="16" /> Inicio</a></li>
	<?php echo ($is_a || ($usr->users_opt & 268435456) ? '<li><a href="/adm/index.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'" title="Administración"><img src="/theme/default/images/top_admin.png" alt="" width="16" height="16" /> Administración</a></li>' : ''); ?>
</ul>
</div>
<?php echo $admin_cp; ?>
<?php echo draw_forum_path($frm->cat_id, $frm->name); ?>
<?php echo $announcements; ?>
<?php echo (!$frm_id || ($frm_id && !empty($forum_list_table_data)) ? '
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr>
	<th colspan="3" class="wa">Foro</th>
	<th class="nw hide1">Mensajes</th>
	<th class="nw hide1">Hilos de discusión</th>
	<th class="nw ac hide2">Último mensaje</th>
</tr>
	'.$forum_list_table_data.'
</table>
' : ''); ?>
<div class="wa">
	<div class="rel fl" style="left:0;">
		<span id="ShowLinks">
<span class="GenText fb">Mostrar:</span>
<a href="/index.php?t=selmsg&amp;date=today&amp;<?php echo _rsid; ?>&amp;frm_id=<?php echo (isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'&amp;th='.$th.'" title="Mostrar todos los mensajes publicados hoy" rel="nofollow">Mensajes de hoy</a>
'.(_uid ? '<b>::</b> <a href="/index.php?t=selmsg&amp;unread=1&amp;'._rsid.'&amp;frm_id='.(isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'" title="Mostrar todos los mensajes no leídos" rel="nofollow">Mensajes no leídos</a>&nbsp;' : ''); ?>
<?php echo (!$th ? '<b>::</b> <a href="/index.php?t=selmsg&amp;reply_count=0&amp;'._rsid.'&amp;frm_id='.(isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'" title="Mostrar todos los mensajes que no tienen respuestas" rel="nofollow">Mensajes no respondidos</a>&nbsp;' : ''); ?>
<b>::</b> <a href="/index.php?t=polllist&amp;<?php echo _rsid; ?>" rel="nofollow">Encuestas</a>
<b>::</b> <a href="/index.php?t=mnav&amp;<?php echo _rsid; ?>" rel="nofollow">Navegador de mensajes</a>
</span>
		<?php echo (_uid ? '
			<br />
			'.($frm->subscribed ? '
				<a href="/index.php?t='.$_GET['t'].'&amp;unsub=1&amp;frm_id='.$frm->id.'&amp;start='.$start.'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'" title="Dejar de recibir avisos acerca de nuevos hilos de discusión en el foro">Desuscribir</a>
			' : '
				<a href="/index.php?t='.$_GET['t'].'&amp;sub=1&amp;frm_id='.$frm->id.'&amp;start='.$start.'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'" title="Recibir una notificación cuando se inicie un nuevo hilo en este foro">Suscribirse</a>
			' )  .'
		' : ''); ?>
		<?php echo ((_uid && ($MOD || $frm->group_cache_opt & 2048)) ? '
			&nbsp;<a href="/index.php?t=merge_th&amp;frm_id='.$frm->id.'&amp;'._rsid.'">Juntar hilo</a>
		' : ''); ?>
	</div>
	<div class="rel GenText nw vt fr" style="right:0;">
		<?php echo ($FUD_OPT_2 & 512 ? '<a href="/index.php?t=threadt&amp;frm_id='.$frm->id.'&amp;'._rsid.'"><img title="Cambiar a la vista en árbol para este hilo" alt="Cambiar a la vista en árbol para este hilo" src="/theme/default/images/tree_view.gif" /></a>&nbsp;' : ''); ?>
		<a href="/index.php?t=post&amp;frm_id=<?php echo $frm->id; ?>&amp;<?php echo _rsid; ?>"><img src="/theme/default/images/new_thread.gif" alt="Crear un nuevo hilo de discusión" /></a>
	</div>
</div>
<br />
<!-- table class="wa" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="al wa"><span id="ShowLinks">
<span class="GenText fb">Mostrar:</span>
<a href="/index.php?t=selmsg&amp;date=today&amp;<?php echo _rsid; ?>&amp;frm_id=<?php echo (isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'&amp;th='.$th.'" title="Mostrar todos los mensajes publicados hoy" rel="nofollow">Mensajes de hoy</a>
'.(_uid ? '<b>::</b> <a href="/index.php?t=selmsg&amp;unread=1&amp;'._rsid.'&amp;frm_id='.(isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'" title="Mostrar todos los mensajes no leídos" rel="nofollow">Mensajes no leídos</a>&nbsp;' : ''); ?>
<?php echo (!$th ? '<b>::</b> <a href="/index.php?t=selmsg&amp;reply_count=0&amp;'._rsid.'&amp;frm_id='.(isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'" title="Mostrar todos los mensajes que no tienen respuestas" rel="nofollow">Mensajes no respondidos</a>&nbsp;' : ''); ?>
<b>::</b> <a href="/index.php?t=polllist&amp;<?php echo _rsid; ?>" rel="nofollow">Encuestas</a>
<b>::</b> <a href="/index.php?t=mnav&amp;<?php echo _rsid; ?>" rel="nofollow">Navegador de mensajes</a>
</span><?php echo (_uid ? '<br />'.($frm->subscribed ? '<a href="/index.php?t='.$_GET['t'].'&amp;unsub=1&amp;frm_id='.$frm->id.'&amp;start='.$start.'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'" title="Dejar de recibir avisos acerca de nuevos hilos de discusión en el foro">Desuscribir</a>' : '<a href="/index.php?t='.$_GET['t'].'&amp;sub=1&amp;frm_id='.$frm->id.'&amp;start='.$start.'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'" title="Recibir una notificación cuando se inicie un nuevo hilo en este foro">Suscribirse</a>' )  : '' ) .((_uid && ($MOD || $frm->group_cache_opt & 2048)) ? '&nbsp;<a href="/index.php?t=merge_th&amp;frm_id='.$frm->id.'&amp;'._rsid.'">Juntar hilo</a>' : ''); ?></td>
		<td class="GenText nw vb ar"><?php echo ($FUD_OPT_2 & 512 ? '<a href="/index.php?t=threadt&amp;frm_id='.$frm->id.'&amp;'._rsid.'"><img title="Cambiar a la vista en árbol para este hilo" alt="Cambiar a la vista en árbol para este hilo" src="/theme/default/images/tree_view.gif" /></a>&nbsp;' : ''); ?><a href="/index.php?t=post&amp;frm_id=<?php echo $frm->id; ?>&amp;<?php echo _rsid; ?>"><img src="/theme/default/images/new_thread.gif" alt="Crear un nuevo hilo de discusión" /></a></td>
	</tr>
</table-->
<?php echo ($MOD || $mo == 8224 ? '<form method="post" action="/index.php?t=mmd">' : ''); ?>
<table border="0" cellspacing="1" cellpadding="2" class="clear pad">
<tr>
	<th class="hide2">&nbsp;</th>
	<th class="hide2">&nbsp;</th>
	<th class="wa">Hilo de discusión</th>
	<th class="wo hide1">Respuestas</th>
	<th class="wo hide1">Vistas</th>
	<th class="nw hide2">Último mensaje</th>
</tr>
<?php echo ($MOD || $mo == 8224 ? '
<tr>
	<td colspan="3" class="RowStyleC ar">
		<input type="submit" class="button small" name="del_sel_all" value="Borrar todo lo seleccionado" />
		<input type="submit" class="button small" name="mov_sel_all" value="Trasladar todo lo seleccionado" />
		<input type="submit" class="button small" name="merge_sel_all" value="Combinar todo lo seleccionado" />
		<input type="submit" class="button small" name="loc_sel_all" value="Bloquear/desbloquear todo lo seleccionado" />
		<input type="checkbox" name="toggle" title="Select all/none" onclick="jQuery(\'input:checkbox\').prop(\'checked\', this.checked);" />
	</td>
	<td colspan="3" class="RowStyleC hide2"> </td>
</tr>
' : ''); ?>
<?php echo $thread_list_table_data; ?>
<?php echo ($MOD || $mo == 8224 ? '
<tr>
	<td colspan="3" class="RowStyleC ar">
		<input type="submit" class="button small" name="del_sel_all" value="Borrar todo lo seleccionado" />
		<input type="submit" class="button small" name="mov_sel_all" value="Trasladar todo lo seleccionado" />
		<input type="submit" class="button small" name="merge_sel_all" value="Combinar todo lo seleccionado" />
		<input type="submit" class="button small" name="loc_sel_all" value="Bloquear/desbloquear todo lo seleccionado" />
		<input type="checkbox" name="toggle" title="Select all/none" onclick="jQuery(\'input:checkbox\').prop(\'checked\', this.checked);" />
	</td>
	<td colspan="3" class="RowStyleC hide2"> </td>
</tr>
' : ''); ?>
</table><?php echo ($MOD || $mo == 8224 ? _hs.'</form>' : ''); ?>
<table border="0" cellspacing="0" cellpadding="0" class="wa">
<tr>
	<td class="vt"><?php echo $page_pager; ?>&nbsp;</td>
	<td class="GenText nw vb ar"><?php echo ($FUD_OPT_2 & 512 ? '<a href="/index.php?t=threadt&amp;frm_id='.$frm->id.'&amp;'._rsid.'"><img title="Cambiar a la vista en árbol para este hilo" alt="Cambiar a la vista en árbol para este hilo" src="/theme/default/images/tree_view.gif" /></a>&nbsp;' : ''); ?><a href="/index.php?t=post&amp;frm_id=<?php echo $frm->id; ?>&amp;<?php echo _rsid; ?>"><img src="/theme/default/images/new_thread.gif" alt="Crear un nuevo hilo de discusión" /></a></td>
</tr>
</table>
<?php echo tmpl_create_forum_select((isset($frm->forum_id) ? $frm->forum_id : $frm->id), $usr->users_opt & 1048576); ?>
<?php echo (_uid ? '
	<div class="ar SmallText">[ <a href="/index.php?t=markread&amp;'._rsid.'&amp;id='.$frm->id.'&amp;SQ='.$GLOBALS['sq'].'" title="Todos los mensajes sin leer dentro de este foro se marcarán como leídos">marcar todos los mensajes sin leer del foro como leídos</a> ]'.($FUD_OPT_2 & 1048576 ? '&nbsp;[ <a href="/index.php?t=help_index&amp;section=boardusage#syndicate">Leer este foro con lector un RSS (XML)</a> ]
[ <a href="/feed.php?mode=m&amp;l=1&amp;basic=1&amp;frm='.$frm->id.'&amp;n=10"><img src="/theme/default/images/rss.gif" title="Leer este foro con lector un RSS (XML)" alt="RSS" width="16" height="16" /></a> ]' : '' ) .(($FUD_OPT_2 & 270532608) == 270532608 ? '&nbsp;[ <a href="/pdf.php?frm='.$frm->id.'&amp;page='.$cur_frm_page.'&amp;'._rsid.'"><img src="/theme/default/images/pdf.gif" title="Generar PDF imprimible" alt="PDF" /></a> ]' : '' )  .'</div>
' : '
	<div class="ar SmallText">'.(($FUD_OPT_2 & 270532608) == 270532608 ? '&nbsp;[ <a href="/pdf.php?frm='.$frm->id.'&amp;page='.$cur_frm_page.'&amp;'._rsid.'"><img src="/theme/default/images/pdf.gif" title="Generar PDF imprimible" alt="PDF" /></a> ]' : '' ) .($FUD_OPT_2 & 1048576 ? '&nbsp;[ <a href="/index.php?t=help_index&amp;section=boardusage#syndicate">Leer este foro con lector un RSS (XML)</a> ]
[ <a href="/feed.php?mode=m&amp;l=1&amp;basic=1&amp;frm='.$frm->id.'&amp;n=10"><img src="/theme/default/images/rss.gif" title="Leer este foro con lector un RSS (XML)" alt="RSS" width="16" height="16" /></a> ]' : '' )  .'</div>
'); ?>
<fieldset>
	<legend>Leyenda:</legend>
	<img src="/theme/default/images/unread.png" alt="Mensajes nuevos" />&nbsp;Mensajes nuevos&nbsp;&nbsp;
	<img src="/theme/default/images/read.png" alt="No hay mensajes nuevos" />&nbsp;No hay mensajes nuevos&nbsp;&nbsp;
	<img src="/theme/default/images/unreadlocked.png" alt="Bloqueado (con mensajes no leídos)" />&nbsp;Bloqueado (con mensajes no leídos)&nbsp;&nbsp;
	<img src="/theme/default/images/readlocked.png" alt="Bloqueado" />&nbsp;Bloqueado&nbsp;&nbsp;
	<img src="/theme/default/images/moved.png" alt="Se trasladó a otro foro" />&nbsp;Se trasladó a otro foro
</fieldset>
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
	<p class="SmallText">Propulsado por: FUDforum <?php echo $GLOBALS['FORUM_VERSION']; ?>.<br />Derechos de autor &copy; 2001-2021 <a href="http://fudforum.org/">FUDforum Bulletin Board Software</a></p>
</div>

</body></html>
