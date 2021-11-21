<?php
/**
* copyright            : (C) 2001-2018 Advanced Internet Designs Inc.
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

if (isset($_GET['id'])) {
	// Show a page.
	if (is_numeric($_GET['id'])) {
	      $page = db_sab('SELECT id, slug, title, foff, length, page_opt FROM fud30_pages WHERE id='. (int)$_GET['id'] . ($is_a ? '' : ' AND '. q_bitand('page_opt', 1) .' = 1'));
	} else {
	      $page = db_sab('SELECT id, slug, title, foff, length, page_opt FROM fud30_pages WHERE slug='. _esc($_GET['id']) . ($is_a ? '' : ' AND '. q_bitand('page_opt', 1) .' = 1'));
	}

	$TITLE_EXTRA = ': '. $page->title;

	fud_use('page_adm.inc', true);
	$page->body = fud_page::read_page_body($page->foff, $page->length, ($page->page_opt & 4));
} else {
	if (!($FUD_OPT_4 & 8)) {
		std_error('disabled');	// No pages to list.
	}

	// Show a list of pages.
	$page_list = '';
	$i = 0;
	$c = q('SELECT id, slug, title FROM fud30_pages WHERE '. q_bitand('page_opt', 1) .' = 1 AND '. q_bitand('page_opt', 2) .' = 2');
	while ($r = db_rowobj($c)) {
		$page_list .= '<li><a href="/index.php?t=page&id='.$r->slug.'&amp;'._rsid.'">'.$r->title.'</a>';
		$i++;
	}
}

/* Print number of unread private messages in User Control Panel. */
	if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud30_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$ucp_private_msg = $c ? '<li><a href="/index.php?t=pmsg&amp;'._rsid.'" title="Mensajes privados"><img src="/theme/default/images/top_pm.png" alt="" width="16" height="16" /> Tienes <span class="GenTextRed">('.$c.')</span> '.convertPlural($c, array('mensaje privado','mensajes privados')).' sin leer</a></li>' : '<li><a href="/index.php?t=pmsg&amp;'._rsid.'" title="Mensajes privados"><img src="/theme/default/images/top_pm.png" alt="" width="15" height="11" /> Mensajes privados</a></li>';
	} else {
		$ucp_private_msg = '';
	}

ses_update_status($usr->sid, 'Buscar página');

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
<div class="breadcrumbs">
	<a href="/index.php?t=i&amp;<?php echo _rsid; ?>">Inicio</a> &nbsp;&raquo;
	<?php echo (empty($page) ? '
		Páginas disponibles
	' : '
		'.($page->page_opt&2 ? '
			<a href="/index.php?t=page&amp;'._rsid.'">Páginas disponibles</a> &nbsp;&raquo; '.$page->title.'
		' : '
			'.$page->title.'
		' )  .'
	'); ?>
<div>
<div class="ContentTable">
<?php echo (!empty($page->title) ? '
	<h2>'.$page->title.'</h2>
	<div class="content page-content">
		'.$page->body.'
	</div>
' : '
	<h2>Páginas disponibles</h2>
	<ul>
		'.$page_list.'
	</ul>
	<i>'.convertPlural($i, array(''.$i.' págna',''.$i.' páginas')).' en la lista.</i>
'); ?>
</div>
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
