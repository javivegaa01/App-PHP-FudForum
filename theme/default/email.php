<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id$
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}function validate_email($email)
{
	$bits = explode('@', $email);
	if (count($bits) != 2) {
		return 1;
	}
	$doms = explode('.', $bits[1]);
	$last = array_pop($doms);

	// Validate domain extension 2-4 characters A-Z
	if (!preg_match('!^[A-Za-z]{2,4}$!', $last)) {
		return 1;
	}

	// (Sub)domain name 63 chars long max A-Za-z0-9_
	foreach ($doms as $v) {
		if (!$v || strlen($v) > 63 || !preg_match('!^[A-Za-z0-9_-]+$!', $v)) {
			return 1;
		}
	}

	// Now the hard part, validate the e-mail address itself.
	if (!$bits[0] || strlen($bits[0]) > 255 || !preg_match('!^[-A-Za-z0-9_.+{}~\']+$!', $bits[0])) {
		return 1;
	}
}

function encode_subject($text)
{
	if (preg_match('![\x7f-\xff]!', $text)) {
		$text = '=?utf-8?B?'. base64_encode($text) .'?=';
	}

	return $text;
}

function send_email($from, $to, $subj, $body, $header='', $munge_newlines=1)
{
	if (empty($to)) {
		return 0;
	}

	/* HTML entities check. */
	if (strpos($subj, '&') !== false) {
		$subj = html_entity_decode($subj);
	}

	if ($header) {
		$header = "\n" . str_replace("\r", '', $header);
	}
	$extra_header = '';
	if (strpos($header, 'MIME-Version') === false) {
		$extra_header = "\nMIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding: 8bit". $header;
	}
	$header = 'From: '. $from ."\nErrors-To: ". $from ."\nReturn-Path: ". $from ."\nX-Mailer: FUDforum v". $GLOBALS['FORUM_VERSION']. $extra_header. $header;

	$body = str_replace("\r", '', $body);
	if ($munge_newlines) {
		$body = str_replace('\n', "\n", $body);
	}
	$subj = encode_subject($subj);

	// Call PRE mail plugins.
	if (defined('plugins')) {
		list($to, $subj, $body, $header) = plugin_call_hook('PRE_MAIL', array($to, $subj, $body, $header));
	}

	if (defined('fud_logging')) {
		if (!function_exists('logaction')) {
			fud_use('logaction.inc');
		}
		logaction(_uid, 'SEND EMAIL', 0, 'To=['. implode(',', (array)$to) .']<br />Subject=['. $subj .']<br />Headers=['. str_replace("\n", '<br />', htmlentities($header)) .']<br />Message=['. $body .']');
	}

	if ($GLOBALS['FUD_OPT_1'] & 512) {
		if (!class_exists('fud_smtp')) {
			fud_use('smtp.inc');
		}
		$smtp = new fud_smtp;
		$smtp->msg = str_replace(array('\n', "\n."), array("\n", "\n.."), $body);
		$smtp->subject = encode_subject($subj);
		$smtp->to = $to;
		$smtp->from = $from;
		$smtp->headers = $header;
		$smtp->send_smtp_email();
		return 1;
	}

	foreach ((array)$to as $email) {
		if (!@mail($email, $subj, $body, $header)) {
			fud_logerror('Your system didn\'t accept E-mail ['. $subj .'] to ['. $email .'] for delivery.', 'fud_errors', $header ."\n\n". $body);
			return -1;
		}
	}
	
	return 1;
}$GLOBALS['__revfs'] = array('&quot;', '&lt;', '&gt;', '&amp;');
$GLOBALS['__revfd'] = array('"', '<', '>', '&');

function reverse_fmt($data)
{
	$s = $d = array();
	foreach ($GLOBALS['__revfs'] as $k => $v) {
		if (strpos($data, $v) !== false) {
			$s[] = $v;
			$d[] = $GLOBALS['__revfd'][$k];
		}
	}

	return $s ? str_replace($s, $d, $data) : $data;
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
}include $GLOBALS['FORUM_SETTINGS_PATH'] .'ip_filter_cache';
	include $GLOBALS['FORUM_SETTINGS_PATH'] .'login_filter_cache';
	include $GLOBALS['FORUM_SETTINGS_PATH'] .'email_filter_cache';

function is_ip_blocked($ip)
{
	if (empty($GLOBALS['__FUD_IP_FILTER__'])) {
		return;
	}
	$block =& $GLOBALS['__FUD_IP_FILTER__'];
	list($a,$b,$c,$d) = explode('.', $ip);

	if (!isset($block[$a])) {
		return;
	}
	if (isset($block[$a][$b][$c][$d])) {
		return 1;
	}

	if (isset($block[$a][256])) {
		$t = $block[$a][256];
	} else if (isset($block[$a][$b])) {
		$t = $block[$a][$b];
	} else {
		return;
	}

	if (isset($t[$c])) {
		$t = $t[$c];
	} else if (isset($t[256])) {
		$t = $t[256];
	} else {
		return;
	}

	if (isset($t[$d]) || isset($t[256])) {
		return 1;
	}
}

function is_login_blocked($l)
{
	foreach ($GLOBALS['__FUD_LGN_FILTER__'] as $v) {
		if (preg_match($v, $l)) {
			return 1;
		}
	}
	return;
}

function is_email_blocked($addr)
{
	if (empty($GLOBALS['__FUD_EMAIL_FILTER__'])) {
		return;
	}
	$addr = strtolower($addr);
	foreach ($GLOBALS['__FUD_EMAIL_FILTER__'] as $k => $v) {
		if (($v && (strpos($addr, $k) !== false)) || (!$v && preg_match($k, $addr))) {
			return 1;
		}
	}
	return;
}

function is_allowed_user(&$usr, $simple=0)
{
	/* Check if the ban expired. */
	if (($banned = $usr->users_opt & 65536) && $usr->ban_expiry && $usr->ban_expiry < __request_timestamp__) {
		q('UPDATE fud30_users SET users_opt = '. q_bitand('users_opt', ~65536) .' WHERE id='. $usr->id);
		$usr->users_opt ^= 65536;
		$banned = 0;
	} 

	if ($banned || is_email_blocked($usr->email) || is_login_blocked($usr->login) || is_ip_blocked(get_ip())) {
		$ban_expiry = (int) $usr->ban_expiry;
		$ban_reason = $usr->ban_reason;
		if (!$simple) { // On login page we already have anon session.
			ses_delete($usr->sid);
			$usr = ses_anon_make();
		}
		setcookie($GLOBALS['COOKIE_NAME'].'1', 'd34db33fd34db33fd34db33fd34db33f', ($ban_expiry ? $ban_expiry : (__request_timestamp__ + 63072000)), $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN']);
		if ($banned) {
			error_dialog('ERROR: Ha sido expulsado.', 'Su cuenta ten??a el acceso restringido '.($ban_expiry ? 'temporalmente hasta '.strftime('%a, %d %B %Y %H:%M', $ban_expiry) : 'permanentemente' )  .' al sitio, debido a una violaci??n de las reglas del foro.
<br />
<br />
<span class="GenTextRed">'.$ban_reason.'</span>');
		} else {
			error_dialog('ERROR: Su cuenta ha sido filtrada.', 'Su cuenta ha sido bloqueada por uno de los filtros de usuario instalados.');
		}
	}

	if ($simple) {
		return;
	}

	if ($GLOBALS['FUD_OPT_1'] & 1048576 && $usr->users_opt & 262144) {
		error_dialog('ERROR: Su cuenta a??n no ha sido confirmada', 'No hemos recibido una confirmaci??n de su padre y/o tutor legal, la cual te permitir??a publicar mensajes. Si perdiste tu formulario COPPA, <a href="/index.php?t=coppa_fax&amp;'._rsid.'">m??ralo de nuevo</a>.');
	}

	if ($GLOBALS['FUD_OPT_2'] & 1 && !($usr->users_opt & 131072)) {
		std_error('emailconf');
	}

	if ($GLOBALS['FUD_OPT_2'] & 1024 && $usr->users_opt & 2097152) {
		error_dialog('La cuenta no est?? verificada', 'El administrator ha elegido revisar todas las cuentas manualmente antes de su activaci??n. Hasta que el administrador valide la cuenta, no podr?? usar todas las posibilidades de su cuenta.');
	}
}class fud_smtp
{
	var $fs, $last_ret, $msg, $subject, $to, $from, $headers;

	function get_return_code($cmp_code='250')
	{
		if (!($this->last_ret = @fgets($this->fs, 515))) {
			return;
		}
		if ((int)$this->last_ret == $cmp_code) {
			return 1;
		}
		return;
	}

	function wts($string)
	{
		/* Write to stream. */
		fwrite($this->fs, $string ."\r\n");
	}

	function open_smtp_connex()
	{
		if( !($this->fs = @fsockopen($GLOBALS['FUD_SMTP_SERVER'], $GLOBALS['FUD_SMTP_PORT'], $errno, $errstr, $GLOBALS['FUD_SMTP_TIMEOUT'])) ) {
			fud_logerror('ERROR: SMTP server at '. $GLOBALS['FUD_SMTP_SERVER'] ." is not available<br />\n". ($errno ? "Additional Problem Info: $errno -> $errstr <br />\n" : ''), 'fud_errors');
			return;
		}
		if (!$this->get_return_code(220)) {	// 220 == Ready to speak SMTP.
			return;
		}

		$es = strpos($this->last_ret, 'ESMTP') !== false;
		$smtp_srv = $_SERVER['SERVER_NAME'];
		if ($smtp_srv == 'localhost' || $smtp_srv == '127.0.0.1' || $smtp_srv == '::1') {
			$smtp_srv = 'FUDforum SMTP server';
		}

		$this->wts(($es ? 'EHLO ' : 'HELO ') . $smtp_srv);
		if (!$this->get_return_code()) {
			return;
		}

		/* Scan all lines and look for TLS support. */
		$tls = false;
		if ($es) {
			while($str = @fgets($this->fs, 515)) {
				if (substr($str, 0, 12) == '250-STARTTLS') $tls = true;
				if (substr($str, 3,  1) == ' ') break;	// Done reading if 4th char is a space.

			}
		}

		/* Do SMTP Auth if needed. */
		if ($GLOBALS['FUD_SMTP_LOGIN']) {
			if ($tls) {
				/*  Initiate TSL communication with server. */
				$this->wts('STARTTLS');
				if (!$this->get_return_code(220)) {
					return;
				}
				/* Encrypt the connection. */
				if (!stream_socket_enable_crypto($this->fs, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
					return false;
				} 
				/* Say hi again. */
				$this->wts(($es ? 'EHLO ' : 'HELO ').$smtp_srv);
				if (!$this->get_return_code()) {
					return;
				}
				/* we need to scan all other lines */
				while($str = @fgets($this->fs, 515)) {
					if (substr($str, 3, 1) == ' ') break;
				}
			}

			$this->wts('AUTH LOGIN');
			if (!$this->get_return_code(334)) {
				return;
			}
			$this->wts(base64_encode($GLOBALS['FUD_SMTP_LOGIN']));
			if (!$this->get_return_code(334)) {
				return;
			}
			$this->wts(base64_encode($GLOBALS['FUD_SMTP_PASS']));
			if (!$this->get_return_code(235)) {
				return;
			}
		}

		return 1;
	}

	function send_from_hdr()
	{
		$this->wts('MAIL FROM: <'. $GLOBALS['NOTIFY_FROM'] .'>');
		return $this->get_return_code();
	}

	function send_to_hdr()
	{
		$this->to = (array) $this->to;

		foreach ($this->to as $to_addr) {
			$this->wts('RCPT TO: <'. $to_addr .'>');
			if (!$this->get_return_code()) {
				return;
			}
		}
		return 1;
	}

	function send_data()
	{
		$this->wts('DATA');
		if (!$this->get_return_code(354)) {
			return;
		}

		/* This is done to ensure what we comply with RFC requiring each line to end with \r\n */
		$this->msg = preg_replace('!(\r)?\n!si', "\r\n", $this->msg);

		if( empty($this->from) ) $this->from = $GLOBALS['NOTIFY_FROM'];

		$this->wts('Subject: '. $this->subject);
		$this->wts('Date: '. date('r'));
		$this->wts('To: '. (count($this->to) == 1 ? $this->to[0] : $GLOBALS['NOTIFY_FROM']));
		$this->wts($this->headers ."\r\n");
		$this->wts($this->msg);
		$this->wts('.');

		return $this->get_return_code();
	}

	function close_connex()
	{
		$this->wts('QUIT');
		fclose($this->fs);
	}

	function send_smtp_email()
	{
		if (!$this->open_smtp_connex()) {
			if ($this->last_ret) {
				fud_logerror('Open SMTP connection - invalid return code: '. $this->last_ret, 'fud_errors');
			}
			return false;
		}
		if (!$this->send_from_hdr()) {
			fud_logerror('Send "From:" header - invalid SMTP return code: '. $this->last_ret, 'fud_errors');
			$this->close_connex();
			return false;
		}
		if (!$this->send_to_hdr()) {
			fud_logerror('Send "To:" header - invalid SMTP return code: '. $this->last_ret, 'fud_errors');
			$this->close_connex();
			return false;
		}
		if (!$this->send_data()) {
			fud_logerror('Send data - invalid SMTP return code: '. $this->last_ret, 'fud_errors');
			$this->close_connex();
			return false;
		}

		$this->close_connex();
		return true;
	}
}

	if (!_uid) {
		std_error('login');
	}

	if (!($FUD_OPT_2 & 1073741824)) {
		error_dialog('ERROR: No fue posible enviar el correo', 'El sistema de correo electr??nico ha sido desactivado por el administrador.');
	}

	is_allowed_user($usr);

function set_err($type, $msg)
{
	$GLOBALS['_ERROR_'][$type] = $msg;
	$GLOBALS['error'] = 1;
}

function get_err($type)
{
	if (!isset($GLOBALS['_ERROR_'][$type])) {
		return;
	}
	return '<br /><span class="ErrorText">'.$GLOBALS['_ERROR_'][$type].'</span>';
}

function mail_check()
{
	if (!strlen(trim($_POST['tx_body']))) {
		set_err('tx_body', 'Se necesita un cuerpo del mensaje');
	}

	if (!strlen(trim($_POST['tx_subject']))) {
		set_err('tx_subject', 'Se necesita un asunto');
	}

	if (!strlen(trim($_POST['tx_name']))) {
		set_err('tx_name', 'Se necesita un nombre');
	} else if (!q_singleval('SELECT id FROM fud30_users WHERE alias='. _esc(htmlspecialchars($_POST['tx_name'])))) {
		set_err('tx_name', 'El usuario no es v??lido');
	}

	return $GLOBALS['error'];
}

	$_ERROR_ = array();
	$error = $tx_name = $tx_subject = $tx_body = '';

	if (isset($_GET['toi']) && (int)$_GET['toi']) {
		$tx_name = q_singleval('SELECT alias FROM fud30_users WHERE id='. (int)$_GET['toi']);
	}
	if (isset($_POST['btn_submit'])) {
		if (!mail_check()) {
			if (!($email = q_singleval('SELECT email FROM fud30_users WHERE alias='. _esc(htmlspecialchars($_POST['tx_name'])) .' AND '. q_bitand('users_opt', 16) .' > 0'))) {
				error_dialog('ERROR: No fue posible enviar el correo', 'El sistema no pudo enviar un correo a '.filter_var($_POST['tx_name'], FILTER_SANITIZE_STRING).' porque no fue capaz de encontrar su direcci??n de correo electr??nico');
			}
			send_email($usr->email, $email, $_POST['tx_subject'], $_POST['tx_body']);
			check_return($usr->returnto);
		}

		foreach (array('tx_name', 'tx_subject', 'tx_body') as $v) {
			$tx_name = isset($_POST[$v]) ? $_POST[$v] : '';
		}
	}

	/* start page */
	$TITLE_EXTRA = ': Formulario de correo electr??nico';

/* Print number of unread private messages in User Control Panel. */
	if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud30_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$ucp_private_msg = $c ? '<li><a href="/index.php?t=pmsg&amp;'._rsid.'" title="Mensajes privados"><img src="/theme/default/images/top_pm.png" alt="" width="16" height="16" /> Tienes <span class="GenTextRed">('.$c.')</span> '.convertPlural($c, array('mensaje privado','mensajes privados')).' sin leer</a></li>' : '<li><a href="/index.php?t=pmsg&amp;'._rsid.'" title="Mensajes privados"><img src="/theme/default/images/top_pm.png" alt="" width="15" height="11" /> Mensajes privados</a></li>';
	} else {
		$ucp_private_msg = '';
	}if (_uid) {
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
<form method="post" id="frme" action="/index.php?t=email">
<div class="ctb"><table cellspacing="1" cellpadding="2" class="MiniTable">
<tr>
	<th colspan="2">Correo electr??nico</th>
</tr>
<tr class="RowStyleB">
	<td class="GenText nw">Para: <?php echo get_err('tx_name'); ?></td>
	<td class="RowStyleA wa"><input type="text" name="tx_name" value="<?php echo filter_var($tx_name, FILTER_SANITIZE_STRING); ?>" /></td>
</tr>
<tr class="RowStyleB">
	<td class="GenText nw">Asunto: <?php echo get_err('tx_subject'); ?></td>
	<td class="RowStyleA wa"><input spellcheck="true" type="text" tabindex="1" name="tx_subject" value="<?php echo (isset($_POST['tx_subject']) ? filter_var($_POST['tx_subject'], FILTER_SANITIZE_STRING).'' : ''); ?>" /></td>
</tr>
<tr class="RowStyleB">
	<td class="GenText nw vt">Cuerpo: <?php echo get_err('tx_body'); ?></td>
	<td class="RowStyleA wa"><textarea tabindex="2" name="tx_body" cols="40" rows="20"><?php echo (isset($_POST['tx_body']) ? filter_var($_POST['tx_body'], FILTER_SANITIZE_STRING).'' : ''); ?></textarea></td>
</tr>
<tr class="RowStyleC">
	<td class="ar" colspan="2"><input type="submit" class="button" name="btn_submit" tabindex="3" value="Correo electr??nico" /></td>
</tr>
</table></div><?php echo _hs; ?></form>
<br /><div class="ac"><span class="curtime"><b>Fecha y hora actual:</b> <?php echo strftime('%a %b %d %H:%M:%S %Z %Y', __request_timestamp__); ?></span></div>
<?php echo $page_stats; ?>
<script>
	document.forms['frme'].tx_subject.focus();
</script>
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
