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
}$GLOBALS['__error__'] = 0;
$GLOBALS['__err_msg__'] = array();

function set_err($err, $msg)
{
	$GLOBALS['__err_msg__'][$err] = $msg;
	$GLOBALS['__error__'] = 1;
}

function is_post_error()
{
	return $GLOBALS['__error__'];
}

function get_err($err, $br=0)
{
	if (isset($err, $GLOBALS['__err_msg__'][$err])) {
		return ($br ? '<span class="ErrorText">'.$GLOBALS['__err_msg__'][$err].'</span><br />' : '<br /><span class="ErrorText">'.$GLOBALS['__err_msg__'][$err].'</span>');
	}
}

function post_check_images()
{
	if (!empty($_POST['msg_body']) && $GLOBALS['MAX_IMAGE_COUNT'] && $GLOBALS['MAX_IMAGE_COUNT'] < count_images((string)$_POST['msg_body'])) {
		return -1;
	}

	return 0;
}

function check_post_form()
{
	/* Make sure we got a valid subject. */
	if (!strlen(trim((string)$_POST['msg_subject']))) {
		set_err('msg_subject', 'Se necesita asunto');
	}

	/* Make sure the number of images [img] inside the body do not exceed the allowed limit. */
	if (post_check_images()) {
		set_err('msg_body', 'Se permite un máximo de '.$GLOBALS['MAX_IMAGE_COUNT'].' imágenes por mensaje. Reduzca el número de imágenes.');
	}

	/* Captcha check for anon users. */
	if (!_uid && $GLOBALS['FUD_OPT_3'] & 8192 ) {
		if (empty($_POST['turing_test']) || empty($_POST['turing_res']) || !test_turing_answer($_POST['turing_test'], $_POST['turing_res'])) {
			set_err('reg_turing', 'Invalid validation code.');
		}
	}

	if (defined('fud_bad_sq')) {
		unset($_POST['submitted']);
		set_err('msg_session', '<h4 class="ErrorText ac">Su sesión había expirado. Por favor, vuelva a enviar el formulario. Perdón por las molestias.</h4>');
	}

	/* Check for duplicate topics (exclude replies and edits). */
	if (($GLOBALS['FUD_OPT_3'] & 67108864) && $_POST['reply_to'] == 0 && $_POST['msg_id'] == 0) {
		$c = q_singleval('SELECT count(*) FROM fud30_msg WHERE subject='. _esc($_POST['msg_subject']) .' AND reply_to=0 AND poster_id='. _uid .' AND post_stamp >= '. (__request_timestamp__ - 86400));
		if ( $c > 0 ) {
			set_err('msg_body', 'No crees temas duplicados.');
		}
	}

	/* Check against minimum post length. */
	if ($GLOBALS['POST_MIN_LEN']) {
		$body_without_bbcode = preg_replace('/\[(.*?)\]|\s+/', '', $_POST['msg_body']);	// Remove tags and whitespace.
		if (strlen($body_without_bbcode) < $GLOBALS['POST_MIN_LEN']) {
			$post_min_len = $GLOBALS['POST_MIN_LEN'];
			set_err('msg_body', 'Tu mensaje es demasiado corto. El tamaño mínimo es de '.convertPlural($post_min_len, array(''.$post_min_len.' carácter',''.$post_min_len.' caracteres')).'.');
		}
		unset($body_without_bbcode);
	}

	/* Check if user is allowed to post links. */
	if ($GLOBALS['POSTS_BEFORE_LINKS'] && !empty($_POST['msg_body'])) {
		if (preg_match('?(\[url)|(http://)|(https://)?i', $_POST['msg_body'])) {
			$c = q_singleval('SELECT posted_msg_count FROM fud30_users WHERE id='. _uid);
			if ( $GLOBALS['POSTS_BEFORE_LINKS'] > $c ) {
				$posts_before_links = $GLOBALS['POSTS_BEFORE_LINKS'];
				set_err('msg_body', 'No se pueden usar los enlaces hasta que se hayan publicado más de '.convertPlural($posts_before_links, array(''.$posts_before_links.' mensaje',''.$posts_before_links.' mensajes')).'.');
			}
		}
	}

	return $GLOBALS['__error__'];
}

function check_ppost_form($msg_subject)
{
	if (!strlen(trim($msg_subject))) {
		set_err('msg_subject', 'Se necesita asunto');
	}

	if (post_check_images()) {
		set_err('msg_body', 'Se permite un máximo de '.$GLOBALS['MAX_IMAGE_COUNT'].' imágenes por mensaje. Reduzca el número de imágenes.');
	}

	if (empty($_POST['msg_to_list'])) {
		set_err('msg_to_list', 'No se puede enviar mensaje, falta el destinatario');
	} else {
		$GLOBALS['recv_user_id'] = array();
		/* Hack for login names containing HTML entities ex. &#123; */
		if (($hack = strpos($_POST['msg_to_list'], '&#')) !== false) {
			$hack_str = preg_replace('!&#([0-9]+);!', '&#\1#', $_POST['msg_to_list']);
		} else {
			$hack_str = $_POST['msg_to_list'];
		}
		foreach(explode(';', $hack_str) as $v) {
			$v = trim($v);
			if (strlen($v)) {
				if ($hack !== false) {
					$v = preg_replace('!&#([0-9]+)#!', '&#\1;', $v);
				}
				if (!($obj = db_sab('SELECT u.users_opt, u.id, ui.ignore_id FROM fud30_users u LEFT JOIN fud30_user_ignore ui ON ui.user_id=u.id AND ui.ignore_id='. _uid .' WHERE u.alias='. ssn(char_fix(htmlspecialchars($v)))))) {
					set_err('msg_to_list', 'No hay un usuario llamado "'.char_fix(htmlspecialchars($v)).'" en este foro');
					break;
				}
				if (!empty($obj->ignore_id)) {
					set_err('msg_to_list', 'No puede enviar un mensaje privado a "'.char_fix(htmlspecialchars($v)).'", porque este usuario le está ignorando.');
					break;
				} else if (!($obj->users_opt & 32) && !$GLOBALS['is_a']) {
					set_err('msg_to_list', 'No puede enviar un mensaje privado a "'.filter_var($v, FILTER_SANITIZE_STRING).'", porque esta persona no está aceptando mensajes privados.');
					break;
				} else {
					$GLOBALS['recv_user_id'][] = $obj->id;
				}
			}
		}
	}

	if (defined('fud_bad_sq')) {
		unset($_POST['btn_action']);
		set_err('msg_session', '<h4 class="ErrorText ac">Su sesión había expirado. Por favor, vuelva a enviar el formulario. Perdón por las molestias.</h4>');
	}

	return $GLOBALS['__error__'];
}

function check_femail_form()
{
	if (empty($_POST['femail']) || validate_email($_POST['femail'])) {
		set_err('femail', 'Escriba una dirección de correo electrónico válida para su amigo.');
	}
	if (empty($_POST['subj'])) {
		set_err('subj', 'No puede enviar un correo sin asunto.');
	}
	if (empty($_POST['body'])) {
		set_err('body', 'No puede enviar un correo sin el cuerpo del mensaje.');
	}
	if (defined('fud_bad_sq')) {
		unset($_POST['posted']);
		set_err('msg_session', '<h4 class="ErrorText ac">Su sesión había expirado. Por favor, vuelva a enviar el formulario. Perdón por las molestias.</h4>');
	}

	return $GLOBALS['__error__'];
}

function count_images($text)
{
	$text = strtolower($text);
	$a = substr_count($text, '[img]');
	$b = substr_count($text, '[/img]');

	return (($a > $b) ? $b : $a);
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
			error_dialog('ERROR: Ha sido expulsado.', 'Su cuenta tenía el acceso restringido '.($ban_expiry ? 'temporalmente hasta '.strftime('%a, %d %B %Y %H:%M', $ban_expiry) : 'permanentemente' )  .' al sitio, debido a una violación de las reglas del foro.
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
		error_dialog('ERROR: Su cuenta aún no ha sido confirmada', 'No hemos recibido una confirmación de su padre y/o tutor legal, la cual te permitiría publicar mensajes. Si perdiste tu formulario COPPA, <a href="/index.php?t=coppa_fax&amp;'._rsid.'">míralo de nuevo</a>.');
	}

	if ($GLOBALS['FUD_OPT_2'] & 1 && !($usr->users_opt & 131072)) {
		std_error('emailconf');
	}

	if ($GLOBALS['FUD_OPT_2'] & 1024 && $usr->users_opt & 2097152) {
		error_dialog('La cuenta no está verificada', 'El administrator ha elegido revisar todas las cuentas manualmente antes de su activación. Hasta que el administrador valide la cuenta, no podrá usar todas las posibilidades de su cuenta.');
	}
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
}function read_msg_body($off, $len, $id)
{
	if ($off == -1) {	// Fetch from DB and return.
		return q_singleval('SELECT data FROM fud30_msg_store WHERE id='. $id);
	}

	if (!$len) {	// Empty message.
		return;
	}

	// Open file if it's not already open.
	if (!isset($GLOBALS['__MSG_FP__'][$id])) {
		$GLOBALS['__MSG_FP__'][$id] = fopen($GLOBALS['MSG_STORE_DIR'] .'msg_'. $id, 'rb');
	}

	// Read from file.
	fseek($GLOBALS['__MSG_FP__'][$id], $off);
	return fread($GLOBALS['__MSG_FP__'][$id], $len);
}

	if (isset($_POST['done'])) {
		check_return($usr->returnto);
	}

	if (__fud_real_user__) {
		is_allowed_user($usr);
	} else if (is_ip_blocked(get_ip())) {
		invl_inp_err();
	}

	if ((isset($_GET['th']) && ($th = (int)$_GET['th'])) || (isset($_POST['th']) && ($th = (int)$_POST['th']))) {
		$data = db_sab('SELECT m.subject, t.id, mm.id AS md, COALESCE(g2.group_cache_opt, g1.group_cache_opt) AS gco
				FROM fud30_thread t
				INNER JOIN fud30_msg m ON t.root_msg_id=m.id
				LEFT JOIN fud30_mod mm ON mm.forum_id=t.forum_id AND mm.user_id='. _uid .'
				INNER JOIN fud30_group_cache g1 ON g1.user_id='. (_uid ? '2147483647' : '0') .' AND g1.resource_id=t.forum_id
				LEFT JOIN fud30_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=t.forum_id
				WHERE t.id='. $th);
		if (!$data) {
			invl_inp_err();
		}
	} else {
		invl_inp_err();
	}

	if (!$is_a && !$data->md && !($data->gco & 2)) {
		std_error('access');
	}

/* Print number of unread private messages in User Control Panel. */
	if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud30_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$ucp_private_msg = $c ? '<li><a href="/index.php?t=pmsg&amp;'._rsid.'" title="Mensajes privados"><img src="/theme/default/images/top_pm.png" alt="" width="16" height="16" /> Tienes <span class="GenTextRed">('.$c.')</span> '.convertPlural($c, array('mensaje privado','mensajes privados')).' sin leer</a></li>' : '<li><a href="/index.php?t=pmsg&amp;'._rsid.'" title="Mensajes privados"><img src="/theme/default/images/top_pm.png" alt="" width="15" height="11" /> Mensajes privados</a></li>';
	} else {
		$ucp_private_msg = '';
	}

	if (isset($_POST['posted']) && _uid && !check_femail_form()) {
		$to = empty($POST['fname']) ? $_POST['femail'] : $_POST['fname'] .' <'. $_POST['femail'] .'>';
		$from = $usr->alias .'<'. $usr->email .'>';
		send_email($from, $to, $_POST['subj'], $_POST['body']);

		error_dialog('Se ha enviado el mensaje de correo', 'El correo electrónico a tu amigo en '.filter_var($_POST['femail'], FILTER_SANITIZE_STRING).' acerca del tema '.$data->subject.' se ha enviado correctamente.');
	} else if (!isset($_POST['posted'])) {
		$def_thread_view = $FUD_OPT_2 & 4 ? 'msg' : 'tree';
	}

	$form_data = _uid ? '<tr class="'.alt_var('page_alt','RowStyleA','RowStyleB').'">
	<td class="GenText nw">Su nombre:</td>
	<td width="100%">'.filter_var($usr->alias, FILTER_SANITIZE_STRING).'</td>
</tr>
<tr class="'.alt_var('page_alt','RowStyleA','RowStyleB').'">
	<td class="GenText nw">Su correo electrónico:</td>
	<td width="100%">'.$usr->email.'</td>
</tr>
<tr class="'.alt_var('page_alt','RowStyleA','RowStyleB').'">
	<td class="GenText nw">Nombre del amigo:</td>
	<td width="100%"><input type="text" name="fname" value="'.(isset($_POST['fname']) ? filter_var($_POST['fname'], FILTER_SANITIZE_STRING).'' : '' )  .'" /></td>
</tr>
<tr class="'.alt_var('page_alt','RowStyleA','RowStyleB').'">
	<td class="GenText nw vt SmallText">Correo electrónico del amigo:<br /><i>requerido</i></td>
	<td valign="top"><input type="text" name="femail" value="'.(isset($_POST['femail']) ? filter_var($_POST['femail'], FILTER_SANITIZE_STRING).'' : '' )  .'">'.get_err('femail').'</td>
</tr>
<tr class="'.alt_var('page_alt','RowStyleA','RowStyleB').'">
	<td class="GenText nw vt SmallText">Asunto:<br /><i>requerido</i></td>
	<td valign="top" class="nw"><input type="text" spellcheck="true" name="subj" value="'.(isset($_POST['subject']) ? filter_var($_POST['subject'], FILTER_SANITIZE_STRING).'' : $data->subject.'' )  .'" size="60" />'.get_err('subj').'</td>
</tr>
<tr class="'.alt_var('page_alt','RowStyleA','RowStyleB').'">
	<td class="GenText nw vt">Mensaje:<span class="SmallText"><br /><i>requerido</i></span></td>
	<td valign="top" class="nw"><textarea name="body" rows="19" cols="78" wrap="PHYSICAL">'.(isset($_POST['body']) ? filter_var($_POST['body'], FILTER_SANITIZE_STRING).'' : 'Hola,\n\nHay un tema interesante sobre "'.$data->subject.'" en el foro '.$GLOBALS['FORUM_TITLE'].' que puedes desear leer. Puedes ver el tema en:\n  http://www.javiervega-fudforum.org/index.php/index.php?t=rview&amp;th='.$data->id.'&amp;rid='._uid.'\n\nTu amigo,\n\n'.$usr->alias.'\n' ) .'</textarea>'.get_err('body').'</td>
</tr>
<tr class="'.alt_var('page_alt','RowStyleA','RowStyleB').'">
	<td class="GenText ar" colspan="2"><input type="submit" class="button" name="submit" value="Enviar correo electrónico ahora" /></td>
</tr>' : '<tr class="'.alt_var('page_alt','RowStyleA','RowStyleB').'">
	<td class="GenText ac SmallText">Copiar este mensaje a un programa de correo de su elección, para enviarlo a su(s) amigo(s).</td>
</tr>
<tr class="'.alt_var('page_alt','RowStyleA','RowStyleB').'">
	<td class="GenText"><textarea name="body" rows="19" cols="78">'.(isset($_POST['body']) ? filter_var($_POST['body'], FILTER_SANITIZE_STRING).'' : 'Hola,\n\nHay un tema interesante sobre "'.$data->subject.'" en el foro '.$GLOBALS['FORUM_TITLE'].' que puedes desear leer. Puedes ver el tema en:\n  http://www.javiervega-fudforum.org/index.php/index.php?t=rview&amp;th='.$data->id.'&amp;rid='._uid.'\n\nTu amigo,\n\n'.$usr->alias.'\n' ) .'</textarea></td>
</tr>
<tr class="'.alt_var('page_alt','RowStyleA','RowStyleB').'">
	<td class="GenText ar"><input type="submit" class="button" name="done" value="Hecho" /></td>
</tr>';


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
<div class="ctb">
<?php echo (is_post_error() ? '<h4 class="ac ErrorText">Hubo un error</h4>' : ''); ?>
<form action="/index.php?t=remail" id="remail" method="post">
<input type="hidden" name="posted" value="1" />
<table cellspacing="1" cellpadding="2" class="MiniTable">
<tr>
	<th colspan="2">Enviar un correo electrónico sobre este hilo a un amigo</th>
</tr>
<?php echo str_replace('\n', "\n", $form_data); ?>
</table>
<?php echo _hs; ?>
<input type="hidden" name="th" value="<?php echo $th; ?>" />
</form>
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
	<p class="SmallText">Propulsado por: FUDforum <?php echo $GLOBALS['FORUM_VERSION']; ?>.<br />Derechos de autor &copy; 2001-2021 <a href="http://fudforum.org/">FUDforum Bulletin Board Software</a></p>
</div>

</body></html>