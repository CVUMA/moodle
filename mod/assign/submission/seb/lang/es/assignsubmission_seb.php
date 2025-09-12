<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'assignsubmission_seb', language 'es'
 *
 * @package    assignsubmission_seb
 * @copyright  2024 Universidad de Málaga
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allowedbrowserkeysdistinct'] = 'Las claves deben ser todas diferentes.';
$string['allowedbrowserkeyssyntax'] = 'Una clave debe ser una cadena hexadecimal de 64 caracteres.';
$string['checkingaccess'] = 'Comprobando acceso al Navegador de Examen Seguro...';
$string['default'] = 'Activado por defecto';
$string['default_help'] = 'Si se activa la opcion, este método de retroalimentación estará activo por defecto para todas las tareas nuevas.';
$string['enabled'] = 'Navegador de examen seguro';
$string['enabled_help'] = 'Si se activa, los estudiantes deben usar el Navegador de Examen Seguro para sus entregas.';
$string['error:ws:assignnotexists'] = 'No se encontró una tarea que coincida con el ID de actividad de la asignatura: {$a}';
$string['error:ws:nokeyprovided'] = 'Debe proporcionarse al menos una clave del Navegador de Examen Seguro.';
$string['event:accessprevented'] = 'Se impidió el acceso a la prueba de conocimiento.';
$string['filemanager_sebconfigfile'] = 'Cargar archivo de configuración de Safe Exam Browser';
$string['filemanager_sebconfigfile_help'] = 'Cargue su propio archivo de configuración de Safe Exam Browser para este cuestionario.';
$string['httplinkbutton'] = 'Descargar configuración';
$string['invalid_config_key'] = 'Clave de configuración SEB no válida';
$string['none'] = 'Ninguno';
$string['pluginname'] = 'Entregas utilizando el Navegador de examen seguro';
$string['seb'] = 'Navegador de examen seguro';
$string['seb:bypassseb'] = 'Omita el requisito de ver la tarea en Safe Exam Browser.';
$string['seb:manage_filemanager_sebconfigfile'] = 'Cambiar la configuración de la tarea SEB: seleccione el archivo de configuración SEB';
$string['seb:manage_seb_activateurlfiltering'] = 'Cambiar la configuración de la tarea SEB: activar el filtrado de URL';
$string['seb:manage_seb_allowedbrowserexamkeys'] = 'Cambiar la configuración de la tarea SEB: claves de examen del navegador permitidas';
$string['seb:manage_seb_allowreloadinexam'] = 'Cambiar la configuración de la tarea SEB: Permitir recargar';
$string['seb:manage_seb_allowspellchecking'] = 'Cambiar la configuración de la tarea SEB: habilite la revisión ortográfica';
$string['seb:manage_seb_allowuserquitseb'] = 'Cambiar la configuración de la tarea SEB: Permitir salir';
$string['seb:manage_seb_enableaudiocontrol'] = 'Cambiar la configuración de la tarea SEB: habilite el control de audio';
$string['seb:manage_seb_expressionsallowed'] = 'Cambiar la configuración de la tarea SEB: se permiten expresiones simples';
$string['seb:manage_seb_expressionsblocked'] = 'Cambiar la configuración de la tarea SEB: Expresiones simples bloqueadas';
$string['seb:manage_seb_filterembeddedcontent'] = 'Cambiar la configuración de la tarea SEB: filtrar contenido incrustado';
$string['seb:manage_seb_linkquitseb'] = 'Cambiar la configuración de la tarea SEB: Salir del enlace';
$string['seb:manage_seb_muteonstartup'] = 'Cambiar la configuración de la tarea SEB: Silenciar al inicio';
$string['seb:manage_seb_quitpassword'] = 'Cambiar la configuración de la tarea SEB: Salir de la contraseña';
$string['seb:manage_seb_regexallowed'] = 'Cambiar la configuración de la tarea SEB: se permiten expresiones Regex';
$string['seb:manage_seb_regexblocked'] = 'Cambiar la configuración de la tarea SEB: expresiones Regex bloqueadas';
$string['seb:manage_seb_requiresafeexambrowser'] = 'Cambiar la configuración de la tarea SEB: Requerir Safe Exam Browser';
$string['seb:manage_seb_showkeyboardlayout'] = 'Cambiar la configuración de la tarea SEB: Mostrar la distribución del teclado';
$string['seb:manage_seb_showreloadbutton'] = 'Cambiar la configuración de la tarea SEB: Mostrar botón de recarga';
$string['seb:manage_seb_showsebdownloadlink'] = 'Cambiar la configuración de la tarea SEB: Mostrar enlace de descarga';
$string['seb:manage_seb_showsebtaskbar'] = 'Cambiar la configuración de la tarea SEB: Mostrar barra de tareas';
$string['seb:manage_seb_showtime'] = 'Cambiar la configuración de la tarea SEB: Mostrar hora';
$string['seb:manage_seb_showwificontrol'] = 'Cambiar la configuración de la tarea SEB: Mostrar control de Wi-Fi';
$string['seb:manage_seb_templateid'] = 'Cambiar la configuración de la tarea SEB: seleccione la plantilla SEB';
$string['seb:manage_seb_userconfirmquit'] = 'Cambiar la configuración de la tarea SEB: confirmar al salir';
$string['seb:managetemplates'] = 'Administrar plantillas de configuración de SEB';
$string['seb_activateurlfiltering'] = 'Filtrar por URL';
$string['seb_activateurlfiltering_help'] = 'Si está habilitado, las URL se filtrarán al cargar páginas web. El conjunto de filtros debe definirse a continuación.';
$string['seb_allowedbrowserexamkeys'] = 'Claves de examen de navegador permitidas';
$string['seb_allowedbrowserexamkeys_help'] = 'En este campo puede introducir las claves de examen del navegador permitidas para las versiones de Safe Exam Browser que pueden acceder a este cuestionario. Si no se introducen claves, no se verifican las claves de examen del navegador.';
$string['seb_allowreloadinexam'] = 'Habilitar recargar en tarea';
$string['seb_allowreloadinexam_help'] = 'Si está habilitado, se permite la recarga de la página (botón de recarga en la barra de tareas SEB, barra de herramientas del navegador, menú deslizante lateral de iOS, atajo de teclado F5 / cmd + R). Tenga en cuenta que el almacenamiento en caché sin conexión puede romperse si un usuario intenta volver a cargar una página sin una conexión a Internet.';
$string['seb_allowspellchecking'] = 'Revisión ortográfica';
$string['seb_allowspellchecking_help'] = 'Si está habilitado, se permite la revisión ortográfica en el navegador SEB.';
$string['seb_allowuserquitseb'] = 'Habilitar salir de SEB';
$string['seb_allowuserquitseb_help'] = 'Si está habilitado, los usuarios pueden salir de SEB con el botón "Salir" en la barra de tareas de SEB o presionando las teclas Ctrl-Q o haciendo clic en el botón de cierre de la ventana principal del navegador.';
$string['seb_calendareventdescription'] = 'La descripción no está disponible porque el Navegador de Examen Seguro está habilitado en esta tarea.';
$string['seb_enableaudiocontrol'] = 'Habilitar controles de audio';
$string['seb_enableaudiocontrol_help'] = 'Si está habilitado, el icono de control de audio se muestra en la barra de tareas de SEB.';
$string['seb_expressionsallowed'] = 'URL permitidas';
$string['seb_expressionsallowed_help'] = '<b>Durante la realización de la tarea en SEB sólo se puede acceder a la página para entrega o subir la tarea.</b><br>
Para permitir consultar otras direcciones web tenemos que agregar cada una de ellas en una línea diferente de este campo. Se puede usar el carácter comodín \'\\ *\' en los enlaces.<br>
Además, dichos enlaces deben configurarse para abrirse en una nueva pestaña.<br>
Ejemplos:<br>
www.example.com<br>
www.example.com/manual/\\*';
$string['seb_expressionsblocked'] = 'URL no permitidas';
$string['seb_expressionsblocked_help'] = '<b>Durante la realización de la tarea en SEB sólo se puede acceder a la página para entrega o subir la tarea.</b><br>
Este campo se utiliza para no permitir el acceso a subdominios o páginas concretas de direcciones web agregadas en el campo "URL permitidas".<br>
Se puede usar el carácter comodín \'\\ *\' en los enlaces.<br>
Ejemplos:<br>
www.example.com/noacceso.php<br>';
$string['seb_filterembeddedcontent'] = 'Filtrar contenido incrustado';
$string['seb_filterembeddedcontent_help'] = 'Si está habilitado, los recursos incrustados también se filtrarán mediante el conjunto de filtros.';
$string['seb_help'] = 'Configura Tareas para utilizar el Navegador de Examen Seguro.';
$string['seb_linkquitseb'] = 'Habilitar salir de SEB a este enlace';
$string['seb_linkquitseb_help'] = 'En este campo puede introducir el enlace para salir de SEB. Se utilizará en un botón "Salir del navegador de examen seguro" en la página que aparece después de que se envía el examen. Al hacer clic en el botón o en el enlace ubicado donde desee colocarlo, es posible salir de SEB sin tener que introducir una contraseña para salir. Si no se introduce ningún vínculo, entonces el botón "Salir de Safe Exam Browser" no aparece y no hay ningún vínculo configurado para salir de SEB.';
$string['seb_managetemplates'] = 'Administrar plantillas de Safe Exam Browser';
$string['seb_muteonstartup'] = 'Silenciar al inicio';
$string['seb_quitpassword'] = 'Contraseña para salir de SEB';
$string['seb_quitpassword_help'] = 'Al rellenar "Contraseña para salir de SEB", el estudiante tras iniciar la entrega de la tarea en SEB no podrá volver al navegador normal (Firefox, Edge, Chrome,etc) hasta que el profesorado suministre esta contraseña.
<br><br>No establecer la contraseña permite volver al navegador normal (Firefox, Edge, Chrome,etc), tras iniciar la entrega de la tarea sin control por parte del profesorado, por ejemplo para consultar información/apuntes de la asignatura.
<br><br>Establecer está contraseña permite al estudiante realizar todas las acciones necesarias en la entrega de la tarea ( añadir, modificar o borrar entrega, confirmar que la entrega es definitiva, etc).
<br><br>Si necesita más información sobre SEB utilice el enlace contacta que aparece en la parte superior derecha de Campus Virtual.';
$string['seb_regexallowed'] = 'Regex permitido';
$string['seb_regexallowed_help'] = 'Un campo de texto que contiene las expresiones de filtrado para URL permitidas en un formato de expresión regular (Regex).';
$string['seb_regexblocked'] = 'Regex bloqueado';
$string['seb_regexblocked_help'] = 'Un campo de texto que contiene las expresiones de filtrado para URL bloqueadas en un formato de expresión regular (Regex).';
$string['seb_requiresafeexambrowser'] = 'Usar Safe Exam Browser (SEB)';
$string['seb_requiresafeexambrowser_help'] = 'Si está habilitado, los estudiantes sólo pueden entregar la tarea usando el Navegador de examen seguro.<br>Las opciones disponibles son:
<ul>
<li><b>Sólo permitir esta tarea y las aplicaciones seleccionadas</b><br/> Sólo permite al estudiante gestionar la entrega de la tarea y las aplicaciones seleccionadas.</li>
<li><b>Subir mi propia configuración</b><br/> Se configura la tarea para utilizar SEB mediante un archivo de configuración que se sube a Campus Virtual y está disponible para la entrega de la tarea por los estudiantes.</li>
</ul>';
$string['seb_showkeyboardlayout'] = 'Mostrar distribución de teclado';
$string['seb_showkeyboardlayout_help'] = 'Si está habilitado, la distribución actual del teclado se muestra en la barra de tareas de SEB. Le permite cambiar a otras distribuciones de teclado, que se han habilitado en el sistema operativo.';
$string['seb_showreloadbutton'] = 'Mostrar botón de recarga';
$string['seb_showreloadbutton_help'] = 'Si está habilitado, se muestra un botón de recarga en la barra de tareas de SEB, lo que permite recargar la página web actual.';
$string['seb_showsebdownloadlink'] = 'Mostrar el botón de descarga de SEB';
$string['seb_showsebdownloadlink_help'] = 'Si está habilitado, se mostrará un botón para descargar Safe Exam Browser en la página de la tarea. En las <b>Aulas TIC</b> de la Universidad ya esta instalado.';
$string['seb_showsebtaskbar'] = 'Mostrar barra de tareas SEB';
$string['seb_showsebtaskbar_help'] = 'Si está habilitado, aparece una barra de tareas en la parte inferior de la ventana del navegador SEB. La barra de tareas es necesaria para mostrar elementos como el control de Wi-Fi, el botón de recarga, la hora y la distribución del teclado.';
$string['seb_showtime'] = 'Mostrar hora actual';
$string['seb_showtime_help'] = 'Si está habilitado, la hora actual se muestra en la barra de tareas de SEB.';
$string['seb_showwificontrol'] = 'Mostrar control de Wi-Fi';
$string['seb_showwificontrol_help'] = 'Si está habilitado, aparece un botón de control de Wi-Fi en la barra de tareas de SEB. El botón permite a los usuarios volver a conectarse a redes Wi-Fi a las que se han conectado anteriormente.';
$string['seb_templateid'] = 'Plantilla de configuración de Safe Exam Browser';
$string['seb_templateid_help'] = 'La configuración de la plantilla de configuración seleccionada se utilizará para la configuración del navegador de examen seguro mientras se realiza la entrega de la tarea. Puede sobrescribir la configuración en la plantilla con su configuración manual.';
$string['seb_use_client'] = 'Usar configuración externa de SEB';
$string['seb_use_manually'] = 'Sólo permitir esta tarea';
$string['seb_use_template'] = 'Usar una plantilla existente';
$string['seb_use_upload'] = 'Subir mi propia configuración';
$string['seb_userconfirmquit'] = 'Pedir al usuario que confirme la salida';
$string['seb_userconfirmquit_help'] = 'Si está habilitado, los usuarios deben confirmar la salida de SEB cuando se detecta un enlace de salida.';
$string['sebbacktocoursebutton'] = 'Volver a la asignatura';
$string['sebdownloadbutton'] = 'Descargar Safe Exam Browser';
$string['sebkeysvalidationfailed'] = 'Error validando las claves de SEB';
$string['seblinkbutton'] = 'Iniciar navegador de examen seguro';
$string['sebrequired'] = 'Esta tarea ha sido configurada para que los estudiantes solo puedan realizarla utilizando Safe Exam Browser.';
$string['setting:assignpasswordrequired'] = 'Se requiere contraseña de tarea';
$string['setting:assignpasswordrequired_desc'] = 'Si está habilitado, todas las tareas que requieren Safe Exam Browser deben tener una contraseña de tarea establecida.';
$string['setting:autoreconfigureseb'] = 'Autoconfigurar SEB';
$string['setting:autoreconfigureseb_desc'] = 'Si está habilitado, los usuarios que accedan a la tarea mediante el navegador de examen seguro se verán automáticamente obligados a reconfigurar su navegador de examen seguro.';
$string['setting:displayblocksbeforestart'] = 'Mostrar bloques antes de entregar la tarea';
$string['setting:displayblocksbeforestart_desc'] = 'Si está habilitado, los bloques se mostrarán antes de que un usuario intente añadir la entrega.';
$string['setting:displayblockswhenfinished'] = 'Mostrar bloques después de entregar la tarea';
$string['setting:displayblockswhenfinished_desc'] = 'Si está habilitado, los bloques se mostrarán después de que un usuario haya añadido la entrega.';
$string['setting:downloadlink'] = 'Enlace de descarga de Safe Exam Browser';
$string['setting:downloadlink_desc'] = 'URL para descargar la aplicación Safe Exam Browser.';
$string['setting:showhttplink'] = 'Mostrar enlace http: //';
$string['setting:showseblink'] = 'Mostrar seb: // enlace';
$string['setting:showseblinks'] = 'Mostrar enlaces de configuración de Safe Exam Browser';
$string['setting:showseblinks_desc'] = 'Si mostrar enlaces para que un usuario acceda al archivo de configuración de Safe Exam Browser cuando se impide el acceso realizar entregas en la tarea. Tenga en cuenta que los enlaces seb: // pueden no funcionar en todos los navegadores.';
$string['setting:supportedversions'] = 'Tenga en cuenta que se requieren las siguientes versiones mínimas del cliente de Safe Exam Browser para usar la función de clave de configuración: macOS - 2.1.5pre2, Windows - 3.0, iOS - 2.1.14.';
$string['settingsfrozen'] = 'Debido a que hay al menos una entrega en la tarea, la configuración de Safe Exam Browser ya no se puede actualizar.';
$string['unknown_reason'] = 'Razón desconocida';
$string['used'] = 'En uso';
