<?php

/**
 * Facebook Connect - Brazilian Portugese Language
 *
 * @package		Solspace:Facebook Connect
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2010-2014, Solspace, Inc.
 * @link		http://solspace.com/docs/facebook_connect
 * @license		http://www.solspace.com/license_agreement
 * @version		2.0.9
 * @filesource	fbc/language/brazilian/lang.fbc.php
 *
 *Translated to Brazilian Portugese by MarchiMedia
 */

$lang = $L = array(

//----------------------------------------
// Required for MODULES page
//----------------------------------------

"fbc_module_name" =>
"Facebook Connect",

"fbc_module_description" =>
"Integra o Facebook com o seu site",

"fbc_module_version" =>
"Facebook Connect",

'modules' =>
"Módulos",

'update_fbc_module' =>
"Atualiza o módulo do Facebook Connect",

'update_failure' =>
"A atualização não foi efetuada com sucesso.",

'update_successful' =>
"A atualização foi um sucesso",

//----------------------------------------
//  Menu Principal
//----------------------------------------

'online_documentation' =>
"Documentação Online",

//----------------------------------------
//	Diagnósticos
//----------------------------------------

'diagnostics' =>
"Diagnostics",

'diagnostics_exp' =>
"Verifica as configurações de conectividade do Facebook.",

'api_credentials_present' =>
"As credenciais da API estão presentes?",

'api_credentials_present_exp' =>
"Para poder conectar ao Facebook você precisa ter registrado seu site como uma aplicação do Facebook e ter recebido um ID da aplicação e um segredo de hash string.<br /><a href='http://www.facebook.com/developers/createapp.php' target='_blank'>Defina seu site como uma aplicação do Facebook aqui.</a>",

'api_credentials_are_present' =>
"As credenciais de API ESTÃO presentes",

'api_credentials_are_not_present' =>
"As API credenciais NÃO ESTÃO presentes",

'logged_in_to_facebook' =>
"Já logado no Facebook?",

'logged_in_to_facebook_exp' =>
"Tenha certeza que você já está conectado na sua conta Facebook utilizando este botão. Nós iremos tentar conectar à API.",

'api_successful_connect' =>
"Conexão à API com sucesso?",

'api_successful_connect_exp' =>
"Nós tentaremos conectar à API do Facebook assim que você efetuar seu login com sua conta. Se, após o login no Facebook usando o botão acima, esta conexão não for estabelecida, confirme o ID do seu aplicativo e as configurações do segredo da sua API no site do Facebook.",

'api_connect_was_successful' =>
"A conexão da API FOI estabelecida.",

'api_connect_was_not_successful' =>
"Uma conexão API NÃO FOI estabelecida.",

'api_login_was_successful' =>
"O Login FOI um SUCESSO",

'api_login_was_not_successful' =>
"O Login NÃO FOI um sucesso",

//----------------------------------------
//  Preferências
//----------------------------------------

'preferences' =>
"Preferências",

'select' =>
"Selecione",

'fbc_member_group_required' =>
"Por favor escolha um membro de grupo para a Conexão do Facebook.",

'preferences_exp' =>
"Estas preferências gerais controlam a forma como o Facebook interage com o seu site.",

'preferences_updated' =>
"Suas preferências foram atualizadas.",

'fbc_app_id' =>
"ID da Aplicação Facebook",

'fbc_app_id_exp' =>
"O ID da aplicação é fornecido pelo Facebook quando você registra seu site como uma aplicação Facebook. <a href='http://www.facebook.com/developers/createapp.php' target='_blank'>Defina seu site como uma aplicação do Facebook aqui.</a>",

'fbc_secret' =>
"Segredo da Aplicação Facebook",

'fbc_secret_exp' =>
"Para adicionar um ID da aplicação do Facebook, você recebará uma string secreta. Isto fornece uma camada extra de segurança para sua integração ao Facebook.",

'fbc_eligible_member_groups' =>
"Grupos de Membro Elegíveis",

'fbc_eligible_member_groups_exp' =>
"Se alguém já estiver logado no seu site e eles efetuarem o login utilizando o botão de login no Facebook, então duas contas podem ser sincronizadas. Para que isto aconteça, estas contas devem estar em um dos grupos que você indicar aqui.",

'fbc_member_group' =>
"Grupo de Membro FBC",

'fbc_member_group_exp' =>
"Se um usuário do Facebook ingressa no seu site, mas não possui uma conta de membro com você, eles podem criar uma conta de membro  simples, imediatamente. Quando eles fazem isto, este grupo de membro será utilizado como um grupo padrão para que eles sejam inseridos dentro deste grupo na hora do registro.",

'fbc_require_member_account_activation' =>
"Obrigar ativação de Conta de Membro para Usuários do Facebook?",

'fbc_require_member_account_activation_exp' =>
"Você pode sobregravar suas preferências primárias de registro de membro aqui. Se alguém registrar no seu site utilizando o o formulário de registro incluído neste módulo, esta configuração será respeitada. Note que se você utilizar a opção de registro passivo no seu site, esta configuração será ignorada.",

'fbc_no_activation' =>
"Nenhuma ativação necessária",

'fbc_email_activation' =>
"Ativação automática via e-mail",

'fbc_admin_activation' =>
"Ativação Manual efetuada por um Administrador",

'fbc_confirm_account_sync' =>
"Confirmar antes de Sincronizar as Contas?",

'fbc_confirm_account_sync_exp' =>
"Quando definido para sim, se alguém estiver logado no EE e logar no Facebook no seu site, eles devem primeiro enviar via account_sync_form antes das contas do Facebook deles e serão sincronizados com sua conta no EE.",

'fbc_passive_registration' =>
"Ativar Registro Passive?",

'fbc_passive_registration_exp' =>
"O método mais fácil de usar o FBCé ativar o registro passivo. Neste modo, alguém logado no seu site usando o botão de login do Facebook irá automaticamente ser registrado como um membro EE se ele já não estiver registrado.",

//----------------------------------------
//  Botões
//----------------------------------------

'save' =>
"Salvar",

//----------------------------------------
//  Erros
//----------------------------------------

'invalid_request' =>
"Solicitação Inválida",

'invalid_url' =>
"URL Inválida",

'invalid_url_exp' =>
"A URL do Facebook Connect deve ser uma URL válida e uma referência apenas para uma pasta. Não inclua o nome do arquivo xd_receiver.htm.",

'fbc_module_disabled' =>
"O módulo FBC está desativado no momento.  Por favor tenha certeza que ele está instalado e atualizado, indo no painel de controle do módulo do ExpressionEngine",

'disable_module_to_disable_extension' =>
"Para desativar esta extensão, você deve desativar seu <a href='%url%'>módulo</a>.",

'enable_module_to_enable_extension' =>
"Para ativar esta extensão, você deve instalar seu <a href='%url%'>módule</a> correspondente.",

'cp_jquery_requred' =>
"A extensão 'jQuery para o Painel de Controle' deve estar <a href='%extensions_url%'>ativada</a> para usar este módulo.",

//----------------------------------------
//  Atualizar
//----------------------------------------

'update_fbc' =>
"Atualizar o módulo FBC",

'fbc_update_message' =>
"Parece que você subiu uma nova versão do FBC. Por favor efetue o script de atualização, clicando no botão 'Update'.",

//----------------------------------------
//  Erros API
//----------------------------------------

'could_not_connect_to_facebook' =>
"Uma conexão não pode ser efetuada com a API do Facebook.",

//----------------------------------------
//  Erros de Login
//----------------------------------------

'not_authorized' =>
"Você não possui autorização para acessar este site.",

'mbr_account_not_active' =>
"Você possui uma conta que ainda não foi ativada pelo webmaster do site.",

'multi_login_warning' =>
"Você já está logado neste site com outro navegador.",

'unable_to_login' =>
"Nós não fomos capazes de conectar você neste site.",

'not_logged_in' =>
"Você deve estar logado neste site para poder enviar este formulário.",

'already_logged_in' =>
"Você já está logado neste site.",

//----------------------------------------
//  Erros de sincronia
//----------------------------------------

'not_logged_in' =>
"Você deve estar logado neste site para enviar este formulário.",

'not_fb_synced' =>
"Sua conta atualmente não está sincornizada com uma conta do Facebook.",

'unsync_error' =>
"Ocorreu um erro ao dessincronizar qualquer conta do Facebook do seu perfil do site.",

//----------------------------------------
//  Erros de Registro
//----------------------------------------

'registration_not_enabled' =>
"O registro não é permitido atualmente neste site.",

'facebook_member_group_missing' =>
"Um grupo de membro deve ser fornecido para este processo de registro. Por favor entre com contato com o administrador do site.",

'facebook_not_logged_in' =>
"Por favor faça o login no Facebook antes de usar este formulário de registro.",

'email_required_for_registration' =>
"Um endereço de e-mail é necessário para o registro.",

'username_required_for_registration' =>
"Um nome de usuário é necessário para o registro.",

'blank_required_for_registration' =>
"%field_label% é necessário para o registro.",

'fb_user_already_exists' =>
"Sua conta do Facebook já foi utilizada para efetuar o registro neste site. Por favor tente efetuar seu login.",

'mbr_terms_of_service_required' =>
"Você deve aceitar os termos do serviço para poder efetuar o registro.",

'captcha_required' =>
"O texto com o código de verificação de imagem deve ser fornecido.",

'could_not_create_account' =>
"Uma conta linkada no seu perfil do Facebook não pode ser criada.",

'member_group_not_eligible' =>
"O grupo de membro que você se encontra, não permite que você efetue seu login através do Facebook.",

'account_created' =>
"Conta Criada",

'back' =>
"Voltar",

"mbr_admin_will_activate" =>
"Um administrador do site irá ativar sua conta e avisar você quando estiver pronta para uso.",

"mbr_membership_instructions_email" =>
"Foi enviado um e-mail para você contendo as instruções de ativação de membro.",

"mbr_activation_success" =>
"Sua conta foi ativada.",

"mbr_may_now_log_in" =>
"Você ´pode agora logar e começar a utilizar o site.",

"passwords_do_not_match" =>
"A senha e a confirmação de senha não combinam.",

"please_complete_field" =>
"Por favor preencha este campo.",

"please_accept_terms" =>
"Você deve aceitar os termos de serviço deste site antes de continuar.",

"facebook_signed_request_failed" =>
"Um erro ocorreu na comunicação entre o Facebook e este site.",

"facebook_field_metadata_failed" =>
"O registro no Facebook não foi um sucesso.",

//----------------------------------------
//	Comentando
//----------------------------------------

'comment_on' =>
"{*actor*} comentado em ",

'commented_on' =>
"{*actor*} comentado em ",

/* FIM */
''=>''
);
?>