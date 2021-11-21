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

$folders = array(1=>'Bandeja de entrada', 2=>'Guardados', 4=>'Borrador', 3=>'Enviados', 5=>'Basura');

function tmpl_cur_ppage($folder_id, $folders, $msg_subject='')
{
	if (!$folder_id || (!$msg_subject && $_GET['t'] == 'ppost')) {
		$user_action = 'Escribiendo un mensaje privado';
	} else {
		$user_action = $msg_subject ? '<a href="/index.php?t=pmsg&amp;folder_id='.$folder_id.'&amp;'._rsid.'">'.$folders[$folder_id].'</a> &raquo; '.$msg_subject.'' : 'Recorriendo la carpeta <b>'.$folders[$folder_id].'</b>';
	}

	return '<span class="GenText"><a href="/index.php?t=pmsg&amp;'._rsid.'">Mensajes privados</a>&nbsp;&raquo;&nbsp;'.$user_action.'</span><br /><img src="/blank.gif" alt="" height="4" width="1" /><br />';
}
?>