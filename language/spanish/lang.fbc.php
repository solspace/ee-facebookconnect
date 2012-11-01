<?php

 /**
 * Solspace - FBC
 *
 * @package 	Solspace:FBC
 * @author		Solspace DevTeam
 * @copyright	Copyright (c) 2010-2012, Solspace, Inc.
 * @link		http://www.solspace.com/docs/addon/c/Facebook_Connect/
 * @version		2.0.9
 * @translated to spanish by Coterfield
 * @filesource 	./system/expressionengine/third_party/fbc/language/spanish/
 */

$lang = $L = array(

//----------------------------------------
// Required for MODULES page
//----------------------------------------

"fbc_module_name" =>
"FBC",

"fbc_module_description" =>
"Integra Facebook con su sitio web",

"fbc_module_version" =>
"Facebook Connect",

'modules' =>
"Módulos",

'update_fbc_module' =>
"Actualiza el Módulo Facebook Connect",

'update_failure' =>
"La actualización no se realizó con éxito.",

'update_successful' =>
"La actualización se realizó con éxito",

//----------------------------------------
//  Main Menu
//----------------------------------------

'online_documentation' =>
"Documentación Online",

//----------------------------------------
//	Diagnostics
//----------------------------------------

'diagnostics' =>
"Diagnóstico",

'diagnostics_exp' =>
"Verifique las configuraciones de conectividad de Facebook.",

'api_credentials_present' =>
"¿Están presentes las credenciales de API?",

'api_credentials_present_exp' =>
"Para conectarse a Facebook es necesario haber registrado su sitio como una aplicación de Facebook y haber recibido una ID de aplicación y una cadena hash secreta.<br /><a href='http://www.facebook.com/developers/createapp.php' target='_blank'>Establezca su sitio como una aplicación de Facebook aquí.</a>",

'api_credentials_are_present' =>
"Las credenciales API ESTÁN presentes",

'api_credentials_are_not_present' =>
"Las credenciales API NO ESTÁN presentes",

'logged_in_to_facebook' =>
"¿Ya inició sesión en Facebook?",

'logged_in_to_facebook_exp' =>
"Asegúrese de que ha iniciado sesión en su cuenta de Facebook usando este botón. Luego intentaremos conectar a la API.",

'api_successful_connect' =>
"¿Conexión API exitosa?",

'api_successful_connect_exp' =>
"Intentaremos conectar a la API de Facebook una vez que hayas ingresado con tu cuenta. Si después de acceder a Facebook usando el botón de arriba esta conexión no se ha establecido, confirme la ID de su aplicación y las configuraciones de API secretas en el sitio de Facebook.",

'api_connect_was_successful' =>
"La conexión API FUE establecida".,

'api_connect_was_not_successful' =>
"La conexión API NO FUE establecido.",

'api_login_was_successful' =>
"Inicio de sesión FUE exitoso",

'api_login_was_not_successful' =>
"Inicio de sesión NO FUE exitoso",

//----------------------------------------
//  Preferences
//----------------------------------------

'preferences' =>
"Preferencias",

'select' =>
"Seleccionar",

'fbc_member_group_required' =>
"Por favor, elija un grupo de miembros Facebook Connect.",

'preferences_exp' =>
"Estas preferencias generales controlan cómo Facebook interactúa con su sitio web.",

'preferences_updated' =>
"Sus preferencias han sido actualizadas.",

'fbc_app_id' =>
"ID de Aplicación Facebook",

'fbc_app_id_exp' =>
"El ID de aplicación es proporcionada por Facebook al registrar su sitio web como una aplicación de Facebook. <a href='http://www.facebook.com/developers/createapp.php' target='_blank'>Establezca su sitio como un aplicación de Facebook aquí.</a>",

'fbc_secret' =>
"Secreto de la Aplicación Facebook",

'fbc_secret_exp' =>
"Además de un ID de aplicación de Facebook, usted también recibirá una cadena de texto secreta. Esto proporciona una capa adicional de seguridad para su integración con Facebook.",

'fbc_eligible_member_groups' =>
"Grupos de Miembros elegibles",

'fbc_eligible_member_groups_exp' =>
"Si alguien inició sesión en su sitio EE y se conectan con el botón de inicio de sesión de Facebook, sus dos cuentas pueden ser sincronizadas. Para que esto suceda, deben pertenecer a uno de los Grupos de Miembros que indiques aquí.",

'fbc_member_group' =>
"Grupo de Miembros FBC",

'fbc_member_group_exp' =>
"Si un usuario de Facebook se une a su sitio, pero aún no tiene una cuenta de miembro con usted, puede crear una cuenta de usuario sencilla sobre la marcha. Cuando lo hagan, este grupo de miembros será utilizada como grupo por defecto al que serán asignados en el momento de inscripción.",

'fbc_require_member_account_activation' =>
"¿Requerir activación de Cuenta de Miembro para usuarios de Facebook?",

'fbc_require_member_account_activation_exp' =>
"Usted puede sobrescribir las preferencias primarias de registro de miembro aquí. Si una persona se registra en su sitio web utilizando el formulario de inscripción incluido en este módulo, esta configuración será respetada. Tenga en cuenta que si utiliza la opción de registro pasivo en su sitio, este ajuste se ignorará.",

'fbc_no_activation' =>
"No requiere activación",

'fbc_email_activation' =>
"Auto-activación mediante correo electrónico",

'fbc_admin_activation' =>
"Activación manual por un administrador",

'fbc_confirm_account_sync' =>
"¿Confirmar antes de Sincronizar Cuentas?",

'fbc_confirm_account_sync_exp' =>
"Cuando se establece en si, si alguien inicia sesión en EE e inicia sesión en Facebook en su sitio, primero debe enviar a través de account_sync_form antes de que su cuenta de Facebook sea sincronizada con su cuenta EE.",

'fbc_passive_registration' =>
"¿Habilitar el Registro Pasivo?",

'fbc_passive_registration_exp' =>
"El método más fácil de usar FBC es permitir el registro pasivo. De este modo, cuando alguien incia sesión en un sitio mediante el botón de inicio de sesión de Facebook será automáticamente registrado como miembro de EE si no estuvieras registrado aún.",

//----------------------------------------
//  Buttons
//----------------------------------------

'save' =>
"Guardar",

//----------------------------------------
//  Errors
//----------------------------------------

'invalid_request' =>
"Solicitud inválida",

'invalid_url' =>
"URL no válida",

'invalid_url_exp' =>
"La URL Facebook Connect debe ser una URL válida y una referencia a un único directorio. No incluya el nombre del archivo xd_receiver.htm.",

'fbc_module_disabled' =>
"El módulo FBC está actualmente desactivado. Por favor, asegúrese que esté instalado y actualizado, vaya
al panel de control del módulo en el Panel de Control de ExpressionEngine",

'disable_module_to_disable_extension' =>
"Para desactivar esta extensión, debe desactivar su <a href='%url%'>módulo</a> correspondiente.",

'enable_module_to_enable_extension' =>
"Para habilitar esta extensión, usted debe instalar su <a href='%url%'>módulo</a> correspondiente.",

'cp_jquery_requred' =>
"La extensión 'jQuery para el Panel de Control' debe estar <a href='%extensions_url%'>ativada</a> para usar este módulo.",

//----------------------------------------
//  Update
//----------------------------------------

'update_fbc' =>
"Actualizar el módulo FBC",

'fbc_update_message' =>
"Parece que has subido una nueva versión del FBC. Por favor ejecute el script de actualización, haga clic en el botón 'Actualizar' de abajo".,

//----------------------------------------
//  API errors
//----------------------------------------

'could_not_connect_to_facebook' =>
"Una conexión no puede realizarse con la API de Facebook.",

//----------------------------------------
//  Login errors
//----------------------------------------

'not_authorized' =>
"No tiene permisos para acceder a este sitio web.",

'mbr_account_not_active' =>
"Tienes una cuenta que aún no ha sido activada por el webmaster de este sitio web.",

'multi_login_warning' =>
"Ya ha accedido a este sitio desde otro navegador web.",

'unable_to_login' =>
"No se pudo iniciar sesión en este sitio.",

'not_logged_in' =>
"Tienes que estar registrado en este sitio para enviar este formulario.",

'already_logged_in' =>
"Usted ya está registrado en este sitio web.",

//----------------------------------------
//  Sync errors
//----------------------------------------

'not_logged_in' =>
"Tienes que estar registrado en este sitio para enviar este formulario.",

'not_fb_synced' =>
"Su cuenta no está sincronizado con alguna cuenta de Facebook.",

'unsync_error' =>
"Se produjo un error al desincronizar una cuenta Facebook de su perfil en el sitio.",

//----------------------------------------
//  Register errors
//----------------------------------------

'registration_not_enabled' =>
"El registro no está permitido actualmente en este sitio.",

'facebook_member_group_missing' =>
"Un grupo de miembros debe ser proporcionada para este proceso de registro. Póngase en contacto con el administrador del sitio.",

'facebook_not_logged_in' =>
"Por favor, inicie sesión en Facebook antes de utilizar este formulario de registro.",

'email_required_for_registration' =>
"Una dirección de correo electrónico es necesaria para el registro.",

'username_required_for_registration' =>
"El nombre de usuario es necesario para el registro.",

'blank_required_for_registration' =>
"%field_label% es necessario para el registro.",

'fb_user_already_exists' =>
"Tu Facebook ya se ha utilizado para registrarse en este sitio. Por favor  intente iniciar sesión",

'mbr_terms_of_service_required' =>
"Usted debe aceptar los términos del servicio para poder registrarse.",

'captcha_required' =>
"El texto de la imagen captcha debe ser ingresada.",

'could_not_create_account' =>
"Una cuenta asociada a su perfil de Facebook no pudo ser creado.",

'member_group_not_eligible' =>
"El grupo de miembros al que pertenece no permite inicio de sesión a través de Facebook.",

'account_created' =>
"Cuenta creada",

'back' =>
"Volver",

"mbr_admin_will_activate" =>
"El administrador del sitio activará su cuenta y le notificará cuando esté listo para su uso.",

"mbr_membership_instructions_email" =>
"Se le ha enviado un correo electrónico con instrucciones de activación de su membresía.",

"mbr_activation_success" =>
"Su cuenta ha sido activada.",

"mbr_may_now_log_in" =>
"Ahora puede iniciar sesión y comenzar a usar el sitio web.",

"passwords_do_not_match" =>
"La contraseña y la confirmación de la contraseña no coinciden.",

"please_complete_field" =>
"Por favor, complete este campo.",

"please_accept_terms" =>
"Usted debe aceptar los términos de servicio de este sitio web antes de continuar.",

"facebook_signed_request_failed" =>
"Se produjo un error en la comunicación entre Facebook y este sitio web.",

"facebook_field_metadata_failed" =>
"El registro de Facebook no se realizó correctamente.",

//----------------------------------------
//	Commenting
//----------------------------------------

'comment_on' =>
"{*actor*} comentó en ",

'Commented_on' =>
"{*actor*} comentó en ",

/* FIN */
''=>''
);
?>