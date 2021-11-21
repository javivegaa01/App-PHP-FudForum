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
	}

	// This form is for printing, therefore it lacks any advanced layout.
	if (!__fud_real_user__) {
		if ($FUD_OPT_2 & 32768) {	// USE_PATH_INFO
			header('Location: /index.php/i/'. _rsidl);
		} else {
			header('Location: /index.php?t=index&'. _rsidl);
		}
		exit;
	}
	
	// User's name to print on form.
	$name = q_singleval('SELECT name FROM fud30_users WHERE id='. __fud_real_user__);


?>
<!DOCTYPE html>
<html lang="es" dir="ltr">
<head>
<meta charset="utf-8">
<meta name=viewport content="width=device-width, initial-scale=1">
<title><?php echo $GLOBALS['FORUM_TITLE'].$TITLE_EXTRA; ?></title>
<script src="/js/lib.js"></script>
<script async src="/js/jquery.js"></script>
<script async src="/js/ui/jquery-ui.js"></script>
<link rel="stylesheet" href="/theme/default/forum.css" />
</head>
<body>
<div class="content">
<strong>Instrucciones para padres o tutores</strong><br /><br />
Imprima esta página, fírmela y envíenosla de vuelta por fax o correo.
<pre>
<?php echo @file_get_contents($FORUM_SETTINGS_PATH."coppa_maddress.msg"); ?>
</pre>
<table border="1" cellspacing="1" cellpadding="3">
<tr>
	<td colspan="2">Formulario de registro</td>
</tr>
<tr>
	<td>Nombre de usuario</td>
	<td><?php echo $usr->login; ?></td>
</tr>
<tr>
	<td>Contraseña</td>
	<td>&lt;HIDDEN&gt;</td>
</tr>
<tr>
	<td>Correo electrónico</td>
	<td><?php echo $usr->email; ?></td>
</tr>
<tr>
	<td>Nombre</td>
	<td><?php echo $name; ?></td>
</tr>
<tr>
	<td colspan="2">
		Firme el formulario siguiente y envíenoslo<br />
		He revisado la información que el menor ha suministrado y he leído la Normativa de privacidad del sitio web. Entiendo que la información de perfil puede cambiarse mediante una contraseña. Entiendo que puedo pedir que este perfil de registro se elimine completamente del foro.
	</td>
</tr>
<tr>
	<td>Firme aquí si autoriza</td>
	<td><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></td>
</tr>
<tr>
	<td>Firme aquí si desea que la cuenta sea eliminada</td>
	<td><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></td>
</tr>
<tr>
	<td>Nombre completo del padre o tutor:</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>Relación con el menor:</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>Teléfono:</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>Dirección de correo electrónico:</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>Fecha:</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td colspan="2">Póngase en contacto con <a href="mailto:<?php echo $GLOBALS['ADMIN_EMAIL']; ?>"><?php echo $GLOBALS['ADMIN_EMAIL']; ?></a> para cualquier duda</td>
</tr>
</table>
</div>
</body></html>
