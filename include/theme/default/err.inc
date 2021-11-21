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

/** Log error and redirect to the error template. */
function error_dialog($title, $msg, $level='WARN', $ses=null)
{
	if (!$ses) {
		$ses = (int) $GLOBALS['usr']->sid;
	}

	// Log the error.
	if (defined('fud_logging') || $level !== 'INFO') {
		// Build error string.
		$error_msg  = '[Error] '. $title .'<br />';
		$error_msg .= '[Message to User] '. trim($msg) .'<br />';
		$error_msg .= '[User IP] '. get_ip() .'<br />';
		$error_msg .= '[Requested URL] http://';
		$error_msg .= isset($_SERVER['HTTP_HOST']) ? htmlspecialchars($_SERVER['HTTP_HOST']) : '';
		$error_msg .= isset($_SERVER['REQUEST_URI']) ? htmlspecialchars($_SERVER['REQUEST_URI']) : '';

		// Mask out sensitive data.
		unset($_POST['password']);
		unset($_POST['quick_password']);
		$error_msg .= !empty($_POST) ? '<br />[Post-Data] '. htmlspecialchars(serialize($_POST)) : '';
		$error_msg .= '<br />';

		if (isset($_SERVER['HTTP_REFERER'])) {
			$error_msg .= '[Referring URL] '. htmlspecialchars($_SERVER['HTTP_REFERER']) .'<br />';
		} else if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$error_msg .= '[User Agent] '. htmlspecialchars($_SERVER['HTTP_USER_AGENT']) .'<br />';
		}

		fud_logerror($error_msg, 'fud_errors');
	}

	// No need to redirect, we just want to log the error.
	if ($level == 'LOG&RETURN') {
		return;
	}

	// Store persistently.
	ses_putvar($ses, array('er_msg' => $msg, 'err_t' => $title));

	// Redirect to error template.
	if (is_int($ses)) {
		if ($GLOBALS['FUD_OPT_2'] & 32768) {
			header('Location: /index.php/e/'. _rsidl);
		} else {
			header('Location: /index.php?t=error&'. _rsidl);
		}
	} else {
		if ($GLOBALS['FUD_OPT_2'] & 32768) {
			header('Location: /index.php/e/0/'. $ses);
		} else {
			header('Location: /index.php?t=error&S='. $ses);
		}
	}
	exit;
}

/** Signal standard errors. */
function std_error($type)
{
	if (!isset($_SERVER['HTTP_REFERER'])) {
		$_SERVER['HTTP_REFERER'] = 'unknown';
	}

	$ses_id = s;
	$usr_d = new stdClass();
	$usr_d->email = $GLOBALS['usr']->email;

	if ($type == 'login') {
		if (__fud_real_user__) {
			$type = 'perms';
		} else {
			ses_anonuser_auth($GLOBALS['usr']->sid, '<fieldset><legend>ERROR: no ha iniciado sesión</legend>Para acceder a esta página debe ser un miembro de este foro.</fieldset><br />');
		}
	}

	$err_array = array(
'ERR_disabled'=>array('Función desactivada', 'Esta función ha sido desactivada por el administrador del sistema.'),
'ERR_access'=>array('ERROR: permisos insuficientes', 'No tiene los permisos necesarios para realizar esta acción.'),
'ERR_registration_disabled'=>array('Registro desactivado', 'El administrador ha desactivado el registro. Vuelva a intentarlo más tarde'),
'ERR_user'=>array('ERROR: no existe ese usuario', 'No existe ese usuario'),
'ERR_perms'=>array('¡No está autorizado!', 'Sus permisos no le permiten realizar esta acción.'),
'ERR_systemerr'=>array('ERROR: error de sistema', 'Ocurrió un error de sistema. Repórtelo al <a href="mailto:'.$GLOBALS['ADMIN_EMAIL'].'">webmaster</a>.'),
'ERR_emailconf'=>array('ERROR: su cuenta aún no ha sido confirmada', 'Aún no has confirmado tu cuenta por correo electrónico.<br /><table border="0"><tr><td><ol><li>Si no has recibido un correo de confirmación, <a href="/index.php?t=reset&amp;email='.$usr_d->email.'&amp;S='.$ses_id.'">haz clic aquí</a><li>Si '.$usr_d->email.' no es tu dirección de correo electrónico correcta, <a href="/index.php?t=register&amp;S='.$ses_id.'">indica tu dirección de correo electrónico</a>.</ol></td></tr></table>')
);

	if (isset($err_array['ERR_'. $type])) {
		$err = $err_array['ERR_'. $type];
		error_dialog($err[0], $err[1]);
	}
	error_dialog('Error crítico', 'Ocurrió un error. Desafortunadamente, también hay un problema con el sistema de informe de errores.');
}

/** Signal an invalid input error. */
function invl_inp_err()
{
	error_dialog('La entrada no es válida', 'Ha seguido un enlace que no es válido. Por favor, informe de ello al <a href="mailto:'.$GLOBALS['ADMIN_EMAIL'].'">webmaster</a>', 'INFO');
}
?>
