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
	}function check_return($returnto)
{
	if ($GLOBALS['FUD_OPT_2'] & 32768 && !empty($_SERVER['PATH_INFO'])) {
		if (!$returnto || !strncmp($returnto, '/er/', 4)) {
			header('Location: /index.php/i/'. _rsidl);
		} else if ($returnto[0] == '/') { /* Unusual situation, path_info & normal themes are active. */
			header('Location: /index.php'. $returnto);
		} else {
			header('Location: /index.php?'. $returnto);
		}
	} else if (!$returnto || !strncmp($returnto, 't=error', 7)) {
		header('Location: /index.php?t=index&'. _rsidl);
	} else if (strpos($returnto, 'S=') === false && $GLOBALS['FUD_OPT_1'] & 128) {
		header('Location: /index.php?'. $returnto .'&S='. s);
	} else {
		header('Location: /index.php?'. $returnto);
	}
	exit;
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
}

	/* Permissions check, this form is only allowed for moderators & admins unless public.
	 * Check if IP display is allowed.
	 */
	if (!($usr->users_opt & (524288|1048576)) && !($FUD_OPT_1 & 134217728)) {
		invl_inp_err();
	}

function __fud_whois($ip, $whois_server='')
{
	if (!$whois_server) {
		$whois_server = $GLOBALS['FUD_WHOIS_SERVER'];
	}

	if (!$sock = @fsockopen($whois_server, 43, $errno, $errstr, 20)) {
		$errstr = preg_match('/WIN/', PHP_OS) ? utf8_encode($errstr) : $errstr;	// Windows silliness.
		return 'No se pudo conectar con el servidor WHOIS ('.$whois_server.'): '.$errstr;
	}
	fputs($sock, $ip ."\n");
	$buffer = '';
	do {
		$buffer .= fread($sock, 10240);
	} while (!feof($sock));
	fclose($sock);

	return $buffer;
}

function fud_whois($ip)
{
	$result = __fud_whois($ip);

	/* Check if ARIN can handle the request or if we need to
	 * request information from another server.
	 */
	if (($p = strpos($result, 'ReferralServer: whois://')) !== false) {
		$p += strlen('ReferralServer: whois://');
		$e = strpos($result, "\n", $p);
		$whois = substr($result, $p, ($e - $p));
		if ($whois) {
			$result = __fud_whois($ip, $whois);
		}
	}

	return ($result ? $result : 'La informaci??n de whois para <b>'.$ip.'</b> no est?? disponible.');
}

/* Print number of unread private messages in User Control Panel. */
	if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud30_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$ucp_private_msg = $c ? '<li><a href="/index.php?t=pmsg&amp;'._rsid.'" title="Mensajes privados"><img src="/theme/default/images/top_pm.png" alt="" width="16" height="16" /> Tienes <span class="GenTextRed">('.$c.')</span> '.convertPlural($c, array('mensaje privado','mensajes privados')).' sin leer</a></li>' : '<li><a href="/index.php?t=pmsg&amp;'._rsid.'" title="Mensajes privados"><img src="/theme/default/images/top_pm.png" alt="" width="15" height="11" /> Mensajes privados</a></li>';
	} else {
		$ucp_private_msg = '';
	}

	if (isset($_POST['ip'])) {
		$_GET['ip'] = $_POST['ip'];
	}
	$ip = isset($_GET['ip']) ? filter_var($_GET['ip'], FILTER_VALIDATE_IP) : '';

	if (isset($_POST['user'])) {
		$_GET['user'] = $_POST['user'];
	}
	if (isset($_GET['user'])) {
		if (($user_id = (int) $_GET['user'])) {
			$user = q_singleval('SELECT alias FROM fud30_users WHERE id='. $user_id);
		} else {
			list($user_id, $user) = db_saq('SELECT id, alias FROM fud30_users WHERE alias='. _esc(char_fix(htmlspecialchars($_GET['user']))));
		}
	} else {
		$user = '';
	}

	$TITLE_EXTRA = ': Examinador de IP';

	if ($ip) {
		if (substr_count($ip, '.') == 3) {
			$cond = 'm.ip_addr=\''. $ip .'\'';
		} else {
			$cond = 'm.ip_addr LIKE \''. $ip .'%\'';
		}

		$o = uq('SELECT DISTINCT(m.poster_id), u.alias FROM fud30_msg m INNER JOIN fud30_users u ON m.poster_id=u.id WHERE '. $cond);
		$user_list = '';
		$i = 0;
		while ($r = db_rowarr($o)) {
			$user_list .= '<tr><td class="'.alt_var('ip_alt','RowStyleA','RowStyleB').'">'.++$i.'. <a href="/index.php?t=usrinfo&amp;id='.$r[0].'&amp;'._rsid.'">'.$r[1].'</a></td></tr>';
		}
		unset($o);
		$o = uq('SELECT id, alias FROM fud30_users WHERE registration_ip='. _esc($ip));
		while ($r = db_rowarr($o)) {
			$user_list .= '<tr><td class="'.alt_var('ip_alt','RowStyleA','RowStyleB').'">'.++$i.'. <a href="/index.php?t=usrinfo&amp;id='.$r[0].'&amp;'._rsid.'">'.$r[1].'</a></td></tr>';
		}
		unset($o);
		$page_data = '<table cellspacing="2" cellpadding="2" class="MiniTable">
<tr>
	<td class="vt">
		<table cellspacing="0" cellpadding="2" class="ContentTable">
		<tr><th>Usuarios que usan la direcci??n IP &#39;'.$ip.'&#39;</th></tr>'.$user_list.'
		</table>
	</td>
	<td width="50"> </td>
	<td class="vt"><b>Informaci??n del ISP</b><br /><div class="ip"><pre>'.fud_whois($ip).'</pre></div></td>
</tr>
</table>';
	} else if ($user) {
		$o = uq('SELECT DISTINCT(ip_addr) FROM fud30_msg WHERE poster_id='. $user_id);
		$ip_list = '';
		$i = 0;
		while ($r = db_rowarr($o)) {
			$ip_list .= '<tr>
	<td class="'.alt_var('ip_alt','RowStyleA','RowStyleB').'">'.++$i.'. <a href="/index.php?t=ip&amp;ip='.$r[0].'&amp;'._rsid.'">'.$r[0].'</a></td>
</tr>';
		}
		unset($o);
		
		$o = uq('SELECT registration_ip FROM fud30_users WHERE id='. $user_id);
		while ($r = db_rowarr($o)) {
			$ip_list .= '<tr>
	<td class="'.alt_var('ip_alt','RowStyleA','RowStyleB').'">'.++$i.'. <a href="/index.php?t=ip&amp;ip='.$r[0].'&amp;'._rsid.'">'.$r[0].'</a></td>
</tr>';
		}
		unset($o);

		$page_data = '<table cellspacing="2" cellpadding="2" class="MiniTable">
<tr>
	<th>Todas las IPs usadas por &#39;'.$user.'&#39;</th>
</tr>
'.$ip_list.'
</table>';
	} else {
		$page_data = '';
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

<div class="ctb">
<table cellspacing="0" cellpadding="0" class="MiniTable">
<tr>
	<td>
		<fieldset>
		<legend>Buscar usuarios por IP</legend>
		<form method="post" action="/index.php?t=ip"><?php echo _hs; ?>
		<span class="SmallText">Sintaxis admitida: 1.2.3.4, 1.2.3, 1.2, 1<br /></span>
		<input type="text" name="ip" value="<?php echo $ip; ?>" size="20" maxlength="15" />
		<input type="submit" value="Buscar" />
		</form>
		</fieldset>
	</td>
	<td width="50"> </td>
	<td>
		<fieldset>
		<legend>Analizar el uso de la IP</legend>
		<form method="post" action="/index.php?t=ip"><?php echo _hs; ?>
		<span class="SmallText">Por favor, indique el nombre de usuario exacto.<br /></span>
		<input type="text" name="user" value="<?php echo $user; ?>" size="20" />
		<input type="submit" value="Buscar" />
		</form>
		</fieldset>
	</td>
</tr>
</table>
<br /><br />
<?php echo $page_data; ?>
</div>
<br /><div class="ac"><span class="curtime"><b>Fecha y hora actual:</b> <?php echo strftime('%a %b %d %H:%M:%S %Z %Y', __request_timestamp__); ?></span></div>
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
