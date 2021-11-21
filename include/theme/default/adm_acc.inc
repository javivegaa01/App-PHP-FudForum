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

	$login = $GLOBALS['r']->login;
	$GLOBALS['account_accepted'] = 'Su cuenta &#39;'.$login.'&#39; ha sido aprobada por el administracor, y ya está activa. Ahora puede acceder al foro '.$GLOBALS['FORUM_TITLE'].'&#39; como un usuario con el registro confirmado.';
	$GLOBALS['account_rejected'] = 'Su cuenta &#39;'.$login.'&#39; fue rechazada por el administrador. Si desea saber el motivo de la decisión, puede mandar un mensaje al administrador a la dirección &#39;'.$GLOBALS['ADMIN_EMAIL'].'&#39;.';

	$GLOBALS['account_accepted_s'] = 'Su solicitud para una cuenta en &#39;'.$GLOBALS['FORUM_TITLE'].'&#39; ha sido aceptada.';
	$GLOBALS['account_rejected_s'] = 'Su solicitud de cuenta en &#39;'.$GLOBALS['FORUM_TITLE'].'&#39; fue rechazada.';
?>
