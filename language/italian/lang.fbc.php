<?php

/**
 * Facebook Connect - Language
 *
 * @package		Solspace:Facebook Connect
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2010-2014, Solspace, Inc.
 * @link		http://solspace.com/docs/facebook_connect
 * @license		http://www.solspace.com/license_agreement
 * @version		2.1.4
 * @filesource	fbc/language/italian/lang.fbc.php
 *
 * Translated to Italian by Gianni Martellosio / GMConsultant
 */

$lang = $L = array(

//----------------------------------------
// Required for MODULES page
//----------------------------------------

"fbc_module_name" =>
"Facebook Connect",

"fbc_module_description" =>
"Integra Facebook con il tuo sito web",

"fbc_module_version" =>
"Facebook Connect",

'modules' =>
"Moduli",

'update_fbc_module' =>
"Aggiorna il modulo Facebook Connect",

'update_failure' =>
"L'aggiornamento non ha avuto successo.",

'update_successful' =>
"L'aggiornamento ha avuto successo",

//----------------------------------------
//  Main Menu
//----------------------------------------

'online_documentation' =>
"Documentazione online",

//----------------------------------------
//	Diagnostics
//----------------------------------------

'diagnostics' =>
"Diagnostica",

'diagnostics_exp' =>
"Verificare impostazioni di connettività a Facebook.",

'api_credentials_present' =>
"Credenziali API presenti?",

'api_credentials_present_exp' =>
"Per connettersi a Facebook è necessario aver creato una App di Facebook per il tuo sito (su Facebook) e aver ricevuto una App ID e una stringa hash App segreta.<br /><a href='https://developers.facebook.com/apps' target='_blank'>Creare un App Facebook qui.</a>",

'api_credentials_are_present' =>
"Credenziali API SONO presente",

'api_credentials_are_not_present' =>
"Credenziali API NON SONO presente",

'logged_in_to_facebook' =>
"Già connesso a Facebook?",

'logged_in_to_facebook_exp' =>
"Assicurati di essere collegati al tuo account Facebook con questo tasto. In seguito potremo cercare di connetterci alle API.",

'api_successful_connect' =>
"Connessione API riuscita?",

'api_successful_connect_exp' =>
"Cerchiamo di collegarci alle API di Facebook, una volta effettuato il login con il tuo account. Se, dopo l'accesso a Facebook utilizzando il pulsante di cui sopra, questa connessione non viene stabilita, confermare l'ID della App l'impostazioni segreta dell' App sul sito di Facebook.",

'api_connect_was_successful' =>
"Una connessione API è stata stabilita.",

'api_connect_was_not_successful' =>
"Una connessione API NON è stata stabilita.",

'api_login_was_successful' =>
"L'accesso è avvenuto con successo",

'api_login_was_not_successful' =>
"L'accesso NON è avvenuto con successo",

//----------------------------------------
//  Preferences
//----------------------------------------

'preferences' =>
"Preferenze",

'select' =>
"Seleziona",

'fbc_member_group_required' =>
"Scegliere un gruppo membri per Facebook Connect.",

'preferences_exp' =>
"Queste preferenze generali controllano come Facebook interagisce con il tuo sito web.",

'preferences_updated' =>
"Le preferenze sono state aggiornate.",

'fbc_app_id' =>
"Facebook App ID",

'fbc_app_id_exp' =>
"La ID applicazione è fornita da Facebook quando si crea un App Facebook per il tuo sito. <a href='https://developers.facebook.com/apps' target='_blank'>Creare un app Facebook qui.</a>",

'fbc_secret' =>
"Facebook App Secret",

'fbc_secret_exp' =>
"Oltre a una ID App da Facebook, riceverete anche un codice segreto. Ciò fornisce un ulteriore livello di sicurezza per la vostra integrazione con Facebook.",

'fbc_eligible_member_groups' =>
"Gruppi utente ammissibili",

'fbc_eligible_member_groups_exp' =>
"Se qualcuno è già connesso al tuo sito EE e il login utilizzando il pulsante di accesso di Facebook, i loro due account possono essere sincronizzati. Affinché questo avvenga, devono appartenere a uno dei gruppi utenti indicati qui.",

'fbc_member_group' =>
"FBC Gruppo utente",

'fbc_member_group_exp' =>
"Se un utente di Facebook si unisce al vostro sito, ma non ha un account utente con voi, si può creare al volo un account utente. Quando questo avviene, questo gruppo utente verrà utilizzato come gruppo predefinito a cui verrà assegnato al momento della registrazione.",

'fbc_require_member_account_activation' =>
"Chiedere l'attivazione account per gli utenti Facebook?",

'fbc_require_member_account_activation_exp' =>
"È possibile ignorare le preferenze primarie di registrazione membro qui. Se qualcuno si registra sul tuo sito utilizzando il modulo di registrazione incluso in questo modulo, verrà rispettata questa impostazione. Si noti che se si utilizza l'opzione di registrazione passiva sul tuo sito, questa impostazione verrà ignorata.",

'fbc_no_activation' =>
"Nessuna attivazione richiesta",

'fbc_email_activation' =>
"Attivazione automatica via email",

'fbc_admin_activation' =>
"Attivazione manuale da un amministratore",

'fbc_confirm_account_sync' =>
"Confermare la sincronizzazione account?",

'fbc_confirm_account_sync_exp' =>
"Se impostato su sì, se qualcuno viene registrato in EE e si logga in Facebook sul vostro sito, deve inviare il FBC:Account_Sync_Form tag prima che l'account di Facebook venga sincronizzato con il proprio account EE.",

'fbc_passive_registration' =>
"Abilita la registrazione passiva?",

'fbc_passive_registration_exp' =>
"Il metodo più semplice per utilizzare FBC è quello di consentire la registrazione passiva. In questo modo, chiunque acceda al sito utilizzando il pulsante di login di Facebook verrà automaticamente registrato come membro EE se non sono già registrato.",

//----------------------------------------
//  Buttons
//----------------------------------------

'save' =>
"Salva",

//----------------------------------------
//  Errors
//----------------------------------------

'invalid_request' =>
"Richiesta NON Valida",

'invalid_url' =>
"URL NON valido",

'invalid_url_exp' =>
"Il Facebook Connect URL deve essere un URL valido e deve avere come riferimento la sola directory. Non includere il nome del file xd_receiver.htm.",

'fbc_module_disabled' =>
"Il modulo FBC è attualmente disabilitato. Si prega di assicurarsi che sia installato e aggiornato andando
al pannello di controllo del modulo nel Pannello di controllo di ExpressionEngine",

'disable_module_to_disable_extension' =>
"Per disabilitare questa estensione, è necessario disattivare il suo corrispondente <a href='%url%'>modulo</a>.",

'enable_module_to_enable_extension' =>
"Per abilitare questa estensione, è necessario installare il corrispondente <a href='%url%'>modulo</a>.",

'cp_jquery_requred' =>
"l'estensione 'jQuery per il pannello di controllo' deve essere <a href='%extensions_url%'>abilitata</a> per usare questo modulo.",

//----------------------------------------
//  Update
//----------------------------------------

'update_fbc' =>
"Aggiornare il modulo FBC",

'fbc_update_message' =>
"Sembra che tu hai caricato una nuova versione di FBC. Si prega di eseguire lo script di aggiornamento facendo clic su 'Aggiorna' qui sotto.",

//----------------------------------------
//  API errors
//----------------------------------------

'could_not_connect_to_facebook' =>
"Una connessione con le API di Facebook potrebbe NON essere attiva.",

//----------------------------------------
//  Login errors
//----------------------------------------

'not_authorized' =>
"Non si è autorizzati ad accedere a questo sito.",

'mbr_account_not_active' =>
"Si dispone di un account che non è ancora stato attivato dall'amministratore di questo sito web.",

'multi_login_warning' =>
"Hai già effettuato l'accesso al sito da un altro browser web.",

'unable_to_login' =>
"Non siamo riusciti a farti accedere a questo sito.",

'not_logged_in' =>
"Devi fare il login al sito per inviare questo modulo.",

'already_logged_in' =>
"Sei già connesso a questo sito.",

//----------------------------------------
//  Sync errors
//----------------------------------------

'not_logged_in' =>
"Devi fare il login al sito per inviare questo modulo.",

'not_fb_synced' =>
"Il tuo account non è attualmente sincronizzato a nessun account Facebook.",

'unsync_error' =>
"C'è stato un errore per eliminare la sincronizzazione tra l'account Facebook ed il tuo profilo su questo sito.",

//----------------------------------------
//  Register errors
//----------------------------------------

'registration_not_enabled' =>
"La registrazione non è attualmente consentita su questo sito.",

'facebook_member_group_missing' =>
"Un gruppo membri deve essere previsto per la procedura di registrazione. Si prega di contattare l'amministratore del sito.",

'facebook_not_logged_in' =>
"Effettua il login Facebook prima di utilizzare questo modulo di registrazione.",

'email_required_for_registration' =>
"Un indirizzo email è necessario per la registrazione.",

'username_required_for_registration' =>
"È richiesto un nome utente per la registrazione.",

'blank_required_for_registration' =>
"%field_label% è richiesto per la registrazione.",

'fb_user_already_exists' =>
"Il tuo account Facebook è già stato utilizzato per la registrazione su questo sito. Prova ad accedere.",

'mbr_terms_of_service_required' =>
"È necessario accettare i termini di servizio per la registrazione.",

'captcha_required' =>
"Il testo all'interno dell'immagine captcha deve essere inserita.",

'could_not_create_account' =>
"Un account collegato al tuo profilo di Facebook non può essere creato.",

'member_group_not_eligible' =>
"Il gruppo membri a cui si appartiene non consente login tramite Facebook.",

'account_created' =>
"account creato",

'back' =>
"Indietro",

"mbr_admin_will_activate" =>
"Un amministratore del sito attiverà il tuo account e ti invierà una notifica quando sarà pronto per l'uso.",

"mbr_membership_instructions_email" =>
"Vi è stata appena inviata una e-mail contenente le istruzioni di attivazione account.",

"mbr_activation_success" =>
"Il tuo account è stato attivato.",

"mbr_may_now_log_in" =>
"È ora possibile effettuare il login e iniziare a utilizzare il sito come utente registrato.",

"passwords_do_not_match" =>
"La password e la password di conferma non corrispondono.",

"please_complete_field" =>
"Completa questo campo.",

"please_accept_terms" =>
"È necessario accettare i termini di servizio di questo sito prima di continuare.",

"facebook_signed_request_failed" =>
"Si è verificato un errore nella comunicazione tra Facebook e questo sito web.",

"facebook_field_metadata_failed" =>
"La registrazione Facebook non è riuscita.",

// -------------------------------------
//	demo install (code pack)
// -------------------------------------

'demo_description' =>
'Questi template dimostrativi vi aiuteranno a capire meglio come funziona Solspace Facebook Connect Addon.',

'template_group_prefix' =>
'Prefisso Gruppo Template',

'template_group_prefix_desc' =>
'Ogni gruppo Template e variabile globale installata saranno preceduti da questa variabile al fine di evitare collisioni.',

'groups_and_templates' =>
"Gruppi e template da installare",

'groups_and_templates_desc' =>
"Questi Gruppi Template ed i loro template di riferimento saranno installati nella vostra installazione di ExpressionEngine.",

'screenshot' =>
'Schermata',

'install_demo_templates' =>
'Installare i Template Demo',

'prefix_error' =>
'I prefissi, che vengono utilizzati per i Gruppi Template, possono contenere solo caratteri alfanumerici, underscore, e trattini.',

'demo_templates' =>
'Template Demo',

//errors
'ee_not_running'				=>
'ExpressionEngine 2.x non sembra essere in esecuzione.',

'invalid_code_pack_path'		=>
'Percorso non valido per il Code Pack',

'invalid_code_pack_path_exp'	=>
'Nessun codepack valido trovato in \'%path%\'.',

'missing_code_pack'				=>
'Code Pack mancante',

'missing_code_pack_exp'			=>
'Non hai selezionato alcun code pack da installare.',

'missing_prefix'				=>
'Prefisso necessario',

'missing_prefix_exp'			=>
'Si prega di fornire un prefisso per i template di esempio e i dati che verranno creati.',

'invalid_prefix'				=>
'Prefisso non valido',

'invalid_prefix_exp'			=>
'Il prefisso fornito non era valido.',

'missing_theme_html'			=>
'Cartella mancante',

'missing_theme_html_exp'		=>
"Ci dovrebbe essere una cartella chiamata 'html' all'interno della cartella '/themes/solspace_themes/code_pack/%code_pack_name%'. Assicurarsi che sia a posto e che contenga altre cartelle che rappresentano il gruppi di template che verranno creati da questo code pack.",

'missing_codepack_legacy'		=>
'Manca la libreria CodePackLegacy necessario per installare questo code pack.',

//@deprecated
'missing_code_pack_theme'		=>
'Tema Code Pack mancante',

'missing_code_pack_theme_exp'	=>
"Ci dovrebbe essere almeno una cartella tema all'interno della cartella '%code_pack_name%\' localizzata all'interno di '/themes/code_pack/'. Una cartella tema è necessaria per procedere.",

//conflicts
'conflicting_group_names'		=>
'Conflitto nomi nei gruppi template',

'conflicting_group_names_exp'	=>
'Esistono già i seguenti nomi di gruppo template. Scegliere un prefisso diverso, al fine di evitare conflitti. %conflicting_groups%',

'conflicting_global_var_names'	=>
'Conflitto nomi delle variabili globali.',

'conflicting_global_var_names_exp' =>
'Ci sono stati conflitti tra le variabili globali sul suo sito e le variabili globali in questo code pack. Provare a modificare il prefisso per risolvere i seguenti conflitti. %conflicting_global_vars%',

//success messages
'global_vars_added'				=>
'Variabili globali aggiunte',

'global_vars_added_exp'			=>
'Le seguenti variabili globali sono stati aggiunti con successo. %global_vars%',

'templates_added'				=>
'I template sono stati aggiunti',

'templates_added_exp'			=>
'%template_count% template sono stati aggiunti con successo al tuo sito come parte di questo code pack.',

"home_page"						=>"Home Page",
"home_page_exp"					=> "Visualizza la home page di questo code pack qui: %link%",

//----------------------------------------
//	Commenting
//----------------------------------------

'comment_on' =>
"{*actor*} ha commentato ",

'commented_on' =>
"{*actor*} ha commentato ",

// END
''=>''
);
