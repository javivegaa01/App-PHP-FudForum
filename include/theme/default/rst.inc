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

/* Needed by admuser.php, so that password resets can be sent in the appropriate languge. */

$GLOBALS['register_conf_subject']       = 'Confirmación de registro';
$GLOBALS['reset_newpass_title']         = 'Restablecer contraseña';
$GLOBALS['reset_confirmation']          = 'Gracias por registrarse,\n\nPara activar su cuenta por favor vaya a la siguiente URL:\n\n'.$url.'\n\nUna vez que su cuenta haya sido activada, entrará al foro y\nserá redirigido a la página principal.\n\n\n\nSi recibió este mensaje por error, por favor, ignórelo. Si está recibiendo múltiples copias de este correo, y no las ha solicitado, por favor, póngase en contacto con el administrador del foro en '.$GLOBALS['ADMIN_EMAIL'].'\n\nEsta solicitud se realizó desde: '.$_SERVER['REMOTE_ADDR'].'.\n\n';
$GLOBALS['reset_reset']                 = 'Hola,\n\nHa solicitado actualizar su clave, para efectivamente ACTUALIZAR su contraseña\n.Por favor, vaya a esta URL\n\n'.$url.'\n\nNOTA: Este foro guarda las contraseñas con un mecanismo de cifrado de un sólo sentido, lo cual significa\n que una vez se introduce su contraseña, ésta es cifrada de manera que NO PUEDE SER RECUPERADA.\nEsto funciona simplemente cifrando la contraseña cada vez que se introduce, y comprobando si \nesta contraseña cifrada coincide con la versión guardada (si le interesa saber cómo funciona \neste mecanismo, lea acerca del algoritmo de hash MD5).\n\nSi recibió este mensaje por error, por favor, ignórelo. Si está recibiendo múltiples copias de este correo, y no las ha solicitado, por favor, póngase en contacto con el administrador del foro en '.$GLOBALS['ADMIN_EMAIL'].'\n\nEsta solicitud se realizó desde: '.$_SERVER['REMOTE_ADDR'].'.\n\n';
?>