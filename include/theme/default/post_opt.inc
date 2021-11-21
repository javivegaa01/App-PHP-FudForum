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

function tmpl_post_options($arg, $perms=0)
{
	$post_opt_html		= 'El código <b>HTML</b> está <b>DESACTIVADO</b>';
	$post_opt_fud		= 'El <b>BBcode</b> está <b>DESACTIVADO</b>';
	$post_opt_images 	= 'Las <b>imágenes</b> están <b>DESACTIVADAS</b>';
	$post_opt_smilies	= 'Los <b>emoticonoss</b> están <b>DESACTIVADOS</b>';
	$edit_time_limit	= '';

	if (is_int($arg)) {
		if ($arg & 16) {
			$post_opt_fud = 'El <b><a href="/index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#style" target="_blank">BBcode</b> está <b>HABILITADO</a></b>';
		} else if (!($arg & 8)) {
			$post_opt_html = 'El código <b>HTML</b> está <b>ACTIVADO</b>';
		}
		if ($perms & 16384) {
			$post_opt_smilies = 'Los <a href="/index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#sml" target="_blank"><b>smilies</b> están <b>HABILITADOS</b></a>';
		}
		if ($perms & 32768) {
			$post_opt_images = 'Las <b>Imágenes</b> están <b>ACTIVADAS</b>';
		}
		if ($GLOBALS['EDIT_TIME_LIMIT'] >= 0) {	// Time limit enabled,
			$edit_time_limit = $GLOBALS['EDIT_TIME_LIMIT'] ? '<br /><b>Límite de tiempo de edición</b>: '.$GLOBALS['EDIT_TIME_LIMIT'].' minutos' : '<br /><b>Límite de tiempo de edición</b>: Ilimitado';
		}
	} else if ($arg == 'private') {
		$o =& $GLOBALS['FUD_OPT_1'];

		if ($o & 4096) {
			$post_opt_fud = 'El <b><a href="/index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#style" target="_blank">BBcode</b> está <b>HABILITADO</a></b>';
		} else if (!($o & 2048)) {
			$post_opt_html = 'El código <b>HTML</b> está <b>ACTIVADO</b>';
		}
		if ($o & 16384) {
			$post_opt_images = 'Las <b>Imágenes</b> están <b>ACTIVADAS</b>';
		}
		if ($o & 8192) {
			$post_opt_smilies = 'Los <a href="/index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#sml" target="_blank"><b>smilies</b> están <b>HABILITADOS</b></a>';
		}
	} else if ($arg == 'sig') {
		$o =& $GLOBALS['FUD_OPT_1'];

		if ($o & 131072) {
			$post_opt_fud = 'El <b><a href="/index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#style" target="_blank">BBcode</b> está <b>HABILITADO</a></b>';
		} else if (!($o & 65536)) {
			$post_opt_html = 'El código <b>HTML</b> está <b>ACTIVADO</b>';
		}
		if ($o & 524288) {
			$post_opt_images = 'Las <b>Imágenes</b> están <b>ACTIVADAS</b>';
		}
		if ($o & 262144) {
			$post_opt_smilies = 'Los <a href="/index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#sml" target="_blank"><b>smilies</b> están <b>HABILITADOS</b></a>';
		}
	}

	return 'Opciones del foro:<br /><span class="SmallText">
'.$post_opt_html.'<br />
'.$post_opt_fud.'<br />
'.$post_opt_images.'<br />
'.$post_opt_smilies.$edit_time_limit.'</span>';
}
?>
