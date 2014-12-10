<?php

/**
 * Facebook Connect - Finnish Language
 *
 * @package		Solspace:Facebook Connect
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2010-2014, Solspace, Inc.
 * @link		http://solspace.com/docs/facebook_connect
 * @license		http://www.solspace.com/license_agreement
 * @version		2.0.9
 * @filesource	fbc/language/finnish/lang.fbc.php
 *
 * Translated to Finnish by N/A
 */

$lang = $L = array(

//----------------------------------------
// Required for MODULES page - Vaaditaan MODUULIN sivulla
//----------------------------------------

"fbc_module_name" =>
"Facebook Connect",

"fbc_module_description" =>
"Yhdistä Facebook toimimaan sivustosi kanssa",

"fbc_module_version" =>
"Facebook Connect",

'modules' =>
"Moduulit",

'update_fbc_module' =>
"Päivitä Facebook Connect Moduuli",

'update_failure' =>
"Päivitys ei onnistunut.",

'update_successful' =>
"Päivitys onnistui",

//----------------------------------------
//  Main Menu - Päävalikko
//----------------------------------------

'online_documentation' =>
"Käyttöohjeet verkossa",

//----------------------------------------
//	Diagnostics - Diagnostiikka
//----------------------------------------

'diagnostics' =>
"Diagnostiikka",

'diagnostics_exp' =>
"Tarkista Facebook yhteys.",

'api_credentials_present' =>
"API tiedot saatavilla?",

'api_credentials_present_exp' =>
"Jotta yhteys Facebook palveluun onnistuu, sinun on luotava oma sovellus sivustoasi varten jolloin saat yhteydenmuodostukseen tarvittavat sovelluksesi IDn sekä salaisen tunnisteen.<br /><a href='http://www.facebook.com/developers/createapp.php' target='_blank'>Ohjeita oman Facebook sovelluksen luomiseen löydät täältä.</a>",

'api_credentials_are_present' =>
"API tiedot havaittu",

'api_credentials_are_not_present' =>
"API tietoja ei saatavilla",

'logged_in_to_facebook' =>
"Oletko jo kirjautunut Fabebook palveluun?",

'logged_in_to_facebook_exp' =>
"Kirjautuminen Facebook tilillesi tulee tehdä tätä nappia klikkaamalla. Kun se on tehty, yritämme avata API yhteyden",

'api_successful_connect' =>
"API yhteys onnistui?",

'api_successful_connect_exp' =>
"Yritämme kirjautua Facebook APIin heti kun olet kirjautunut tilillesi. Jos alla olevaa nappia painamalla ei saada yhteytä, varmista että sinun APP ID sekä API salainen tunniste ovat oikein tai saatavilla.",

'api_connect_was_successful' =>
"API yhteys on ONNISTUNEESTI luotu.",

'api_connect_was_not_successful' =>
"API yhteyttä EI SAATU luotua.",

'api_login_was_successful' =>
"Kirjautuminen ONNISTUI",

'api_login_was_not_successful' =>
"Kirjautuminen EPÄONNISTUI",

//----------------------------------------
//  Preferences - asetukset
//----------------------------------------

'preferences' =>
"Asetukset",

'select' =>
"Valitse",

'fbc_member_group_required' =>
"Ole hyvä ja valitse yksi käyttäjäryhmä.",

'preferences_exp' =>
"Nämä yleiset asetukset kontrollivat tapaa jolla Facebook kommunikoi sivustosi kanssa.",

'preferences_updated' =>
"Antamasi asetukset on päivitetty.",

'fbc_app_id' =>
"Facebook Sovelluksen ID",

'fbc_app_id_exp' =>
"Facebook sovelluksen ID luodaan sinulle kun olet rekisteröinyt sivustosi Facebookissa sovelluksena. <a href='http://www.facebook.com/developers/createapp.php' target='_blank'>Luo sivustoasi varten sovellus tästä.</a>",

'fbc_secret' =>
"Facebook sovelluksen salatunniste",

'fbc_secret_exp' =>
"Sovelluksen IDn lisäksi sille annetaan myös salainen tunniste. Se lisää tietoturvaa sivustosi sekä Facebookin välillä.",

'fbc_eligible_member_groups' =>
"Saatavilla olevat jäsenryhmät",

'fbc_eligible_member_groups_exp' =>
"Mikäli käyttäjä on jo kirjautuneena sivustollesi, hänen kirjautuessa Facebook palveluun, nämä kaksi käyttäjätiliä voidaan tuolloin synkronoida. Jotta tämä on mahdollista, käyttäjän tulee kuulua siihen ryhmään, jonka määrität tässä.",

'fbc_member_group' =>
"FBC Jäsenryhmä",

'fbc_member_group_exp' =>
"Kun Facebookin käyttäjä kirjautuu sivustollesi, mutta hänellä ei vielä ole omaa käyttäjätiliä, sellainen voidaan luoda hänelle automaattisesti. Jos he haluavat luoda tilin, tämä jäsenryhmä on se ryhmä, mihin käyttäjän tili liitetään.",

'fbc_require_member_account_activation' =>
"Vaadi käyttäjätilin aktivointi Facebookin käyttäjiltä?",

'fbc_require_member_account_activation_exp' =>
"Voit ohittaa sivustosi oletusrekisteröinasetukset tästä. Mikäli käyttäjä luo tunnuksen käyttämällä rekisteröintilomaketta joka tulee tämän moduulin mukana, näitä asetuksia käytetään. Huom! Mikäli käytössä on passiivinen rekisteröintitapa, näitä asetuksia ei huomioida.",

'fbc_no_activation' =>
"Aktivointi ei ole vaadittu",

'fbc_email_activation' =>
"Käyttäjäaktivointi sähköpostin kautta",

'fbc_admin_activation' =>
"Ylläpito aktivoi tunnuksen",

'fbc_confirm_account_sync' =>
"Vahvista ennen tilien synkronointia?",

'fbc_confirm_account_sync_exp' =>
"Jos asetuksena on Kyllä ja joku kirjautunut sivustollesi sen omaa kirjatumistapaa käyttäen, ja sen jälkeen yrittää kirjautua sivustolle käyttämällä Facebook-tiliään, heidän tulee ensin tilien synkronointilomake ennenkuin Facebook tili synkronoidaan jo olemassa olevan sivuston tilin kanssa.",

'fbc_passive_registration' =>
"Käytä passiivista rekisteröintitapaa?",

'fbc_passive_registration_exp' =>
"Helpoin tapa käyttää FBC moduulia on sallia passiivinen rekisteröinti. Kun tämä tapa on käytössä, ja joku kirjautuu sivustollesi käyttäen Facebook Login nappia, heille luodaan automaattisesti käyttäjätili myös sivustollesi ellei sitä ole vielä olemassa.",

//----------------------------------------
//  Buttons - nappulat
//----------------------------------------

'save' =>
"Tallenna",

//----------------------------------------
//  Errors - virheet
//----------------------------------------

'invalid_request' =>
"Viallinen pyyntö",

'invalid_url' =>
"Viallinen URL",

'invalid_url_exp' =>
"Facebook yhteyden URL tulee olla oikein ja osoittaa vain hakemistoon. Älä siis sisällytä xd_receiver.htm tiedoston nimeä.",

'fbc_module_disabled' =>
"FBC Moduuli on kytketty tällä hetkellä pois päältä.  Tarkista että se on asennettu ja että käytössäsi on uusin versio 
moduulista menemällä moduulien hallintasivulle kun olet kirjautunut ylläpidon hallintapaneeliin.",

'disable_module_to_disable_extension' =>
"Jotta tämä lisäosa voidaan poistaa käytöstä, tulee vastaava moduuli ensin <a href='%url%'>kytkeä pois käytöstä</a>.",

'enable_module_to_enable_extension' =>
"Jotta tämä lisäosa voidaan ottaa käyttöön, sinun tulee asentaa tämän lisäosan <a href='%url%'>moduuli</a>.",

'cp_jquery_requred' =>
"Lisäosa 'jQuery for the Control Panel' tulee olla <a href='%extensions_url%'>käytössä</a> jotta voit käyttää moduulia.",

//----------------------------------------
//  Update - Päivitys
//----------------------------------------

'update_fbc' =>
"Päivitä FBC Moduuli",

'fbc_update_message' =>
"Näyttää siltä, että olet ladannut uudemman version tästä moduulista. Ole hyvä ja suorita päivitys klikkaamalla 'Päivitä' alapuolelta.",

//----------------------------------------
//  API errors - virheet
//----------------------------------------

'could_not_connect_to_facebook' =>
"Ei saatu yhteyttä kohteeseen Facebook API.",

//----------------------------------------
//  Login errors - kirjautumisvirheet
//----------------------------------------

'not_authorized' =>
"Sinulla ei ole oikeutta katsella tätä sivustoa.",

'mbr_account_not_active' =>
"Sinulla on jo tili mutta sivuston ylläpito ei ole vielä aktivoinut sitä.",

'multi_login_warning' =>
"Olet jo kirjautunut tälle sivustolle käyttämällä toista selainta.",

'unable_to_login' =>
"Emme onnistuneet kirjaamaan sinua tälle sivustolle.",

'not_logged_in' =>
"Sinun tulee olla kirjautunut tälle sivustolle jotta voit lähettää tämän lomakkeen.",

'already_logged_in' =>
"Olet jo kirjautunut tälle sivustolle.",

//----------------------------------------
//  Sync errors - synkronointivirheet
//----------------------------------------

'not_logged_in' =>
"Sinun tulee olla kirjautuneena tälle sivustolle jotta voit lähettää tämän lomakkeen.",

'not_fb_synced' =>
"Käyttäjätilisi ei ole tällä hetkellä synkronoitu mihinkään Facebook-tiliin.",

'unsync_error' =>
"Tapahtui odottamaton virhe yritettäessa poistaa tilien synkronointi tämän sivuston ja Facebookn välillä.",

//----------------------------------------
//  Register errors - rekisteröintivirheet
//----------------------------------------

'registration_not_enabled' =>
"Tämä sivusto ei hyväksy uusien käyttäjätilien luontia tällä hetkellä.",

'facebook_member_group_missing' =>
"Jäsenryhmätiedot tarvitaan jotta rekisteröinti onnistuu. Ole hyvä ja ota yhteyttä sivuston ylläpitoon.",

'facebook_not_logged_in' =>
"Ole hyvä ja kirjaudu ensin Facebook-tilillesi ennen kuin käytät tätä rekisteröintilomaketta.",

'email_required_for_registration' =>
"Sähköpostiosoite vaaditaan.",

'username_required_for_registration' =>
"Käyttäjänimi vaaditaan.",

'blank_required_for_registration' =>
"%field_label% on pakollinen kenttä.",

'fb_user_already_exists' =>
"Sinun Facebook-tiliäsi on jo käytetty rekisteröidyttäessä tälle sivustolle. Ole hyvä ja yritä kirjautumista.",

'mbr_terms_of_service_required' =>
"Käyttöehdot on hyväksyttävä jotta rekisteröinti voidaan suorittaa onnistuneesti.",

'captcha_required' =>
"Kuvassa oleva teksti vaaditaan.",

'could_not_create_account' =>
"Käyttäjätiliä jota yritimme linkittää Facebook profiilin kanssa, ei voitu luoda.",

'member_group_not_eligible' =>
"Jäsenryhmä johon kuulut, ei salli kirjautumista käyttäen Facebook-tiliä.",

'account_created' =>
"Käyttäjätili luotu",

'back' =>
"Takaisin",

"mbr_admin_will_activate" =>
"Sivuston ylläpito aktivoi luomasi käyttäjätilin ja ilmoittaa sinulle sähköpostitse kun se on valmis käytettäväksi.",

"mbr_membership_instructions_email" =>
"Sinulle on juuri lähetetty sähköposti joka sisältää tietoa ja ohjeita käyttäjätilisi aktivoinnista.",

"mbr_activation_success" =>
"Käyttäjätilisi on nyt aktivoitu.",

"mbr_may_now_log_in" =>
"Voit nyt kirjautua ja alkaa käyttämään luomaasi tiliä.",

"passwords_do_not_match" =>
"Salasana sekä salasanan vahvistus eivät täsmänneet.",

"please_complete_field" =>
"Ole hyvä ja täytä tämä kenttä.",

"please_accept_terms" =>
"Sinun tulee hyväksyä sivuston käyttöehdot ennen jatkamista.",

"facebook_signed_request_failed" =>
"Tapahtui virhe kommunikoitaessa tämän sivuston ja Facebookin kanssa.",

"facebook_field_metadata_failed" =>
"Facebook rekisteröinti ei onnistunut.",

//----------------------------------------
//	Commenting - kommentointi
//----------------------------------------

'comment_on' =>
"{*actor*} kommentoi ",

'commented_on' =>
"{*actor*} kommentoi ",

/* END */
''=>''
);
?>