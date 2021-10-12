<?php
// Direktzugriff auf die Datei aus Sicherheitsgründen sperren
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// HOOKS
// Bildungseinrichtungseite
$plugins->add_hook("misc_start", "education_misc");
// Teambenachrichtigung auf dem Index
$plugins->add_hook('global_start', 'education_global');
// Mod-CP
$plugins->add_hook('modcp_nav', 'education_modcp_nav');
$plugins->add_hook("modcp_start", "education_modcp");
// MyAlerts
if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
	$plugins->add_hook("global_start", "education_myalert_alerts");
}
// Profil
$plugins->add_hook("member_profile_end", "education_memberprofile");
 
// Die Informationen, die im Pluginmanager angezeigt werden
function education_info()
{
	return array(
		"name"		=> "Bildungseinrichtungen",
		"description"	=> "Dieses Plugin erweitert das Board um eine interaktive Liste von Bildungseinrichtungen. Ausgewählte Usergruppen können neue Bildungseinrichtungen hinzufügen und beitreten. Um eine Bildungseinrichtung hinzufügen zu können, wird ein aussagekräftiger Titel, eine Einordnung in eine Stadt, eine Beschreibung und eine Einordnung in eine Kategorie benötigt. Bildungseinrichtungen müssen vom Team erst freigeschaltet werden im Mod-CP. Eingereichte Bildungseinrichtungen vom Team werden automatisch freigeschaltet. Nach der Freischaltung können User sich mit ihren Accounts in eine Bildungseinrichtungen eintragen. Dabei können sie die Klasse bzw. Studiengang ihres Charakters angeben. Ersteller der Bildungseinrichtungen und das Team können diese bearbeiten und löschen. Zusätzlich werden die Eintragungen in eine Bildungseinrichtungen im Profil angezeigt.",
		"website"	=> "https://github.com/little-evil-genius/Bildungseinrichtungen",
		"author"	=> "little.evil.genius",
		"authorsite"	=> "https://storming-gates.de/member.php?action=profile&uid=1712",
		"version"	=> "1.0",
		"compatibility" => "18*"
	);
}
 
// Diese Funktion wird aufgerufen, wenn das Plugin installiert wird (optional).
function education_install(){
 
    global $db, $cache, $mybb;

    // Datenbank-Tabelle erstellen

    // BILDUNGSEINRICHTUNGEN - HIER WERDEN DIE INFOS ZU DEN SCHULEN GESPEICHERT
    $db->query("CREATE TABLE ".TABLE_PREFIX."educations(
        `eid` int(10) NOT NULL AUTO_INCREMENT,
        `type` VARCHAR(500) NOT NULL,
        `city` VARCHAR(500) COLLATE utf8_general_ci NOT NULL,
        `name` VARCHAR(1000) COLLATE utf8_general_ci NOT NULL,
        `description` VARCHAR(5000) COLLATE utf8_general_ci NOT NULL,
        `accepted` int(1) NOT NULL,
        `createdby` int(11) NOT NULL,
        PRIMARY KEY(`eid`),
        KEY `eid` (`eid`)
        )
        ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1    "
    );
        
        
    // SCHÜLER - HIER WERDEN DIE USER DER SCHULEN GESPEICHERT
	$db->query("CREATE TABLE ".TABLE_PREFIX."educations_user(
		`ueid` int(10) NOT NULL AUTO_INCREMENT,
		`eid` int(10) NOT NULL,
		`uid` int(10) NOT NULL,
		`class` VARCHAR(1000) COLLATE utf8_general_ci NOT NULL,
		PRIMARY KEY(`ueid`),
		KEY `ucid` (`ueid`)
		)
		ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1"
    );

    // EINSTELLUNGEN HINZUFÜGEN
    $setting_group = array(
        'name'          => 'education',
        'title'         => 'Bildungseinrichtungen',
        'description'   => 'Einstellungen für die Bildungseinrichtungen',
        'disporder'     => 1,
        'isdefault'     => 0
    );
        
     
    $gid = $db->insert_query("settinggroups", $setting_group);     
    
    
    $setting_array = array(
        'education_add_allow_groups' => array(
            'title' => 'Erlaubte Gruppen Hinzufügen',
            'description' => 'Welche Gruppen dürfen neue Bildungseinrichtungen erstellen?',
            'optionscode' => 'groupselect',
            'value' => '4', // Default
            'disporder' => 1
        ),

        'education_member_allow_groups' => array(
            'title' => 'Erlaubte Gruppen Beitreten',
            'description' => 'Welche Gruppen dürfen sich als Schüler/student eintragen?',
            'optionscode' => 'groupselect',
            'value' => '4', // Default
            'disporder' => 2
        ),

        'education_type' => array(
            'title' => 'Schulart',
            'description' => 'In welche Kategorien können die Bildungseinrichtitungen eingeordnet werden?',
            'optionscode' => 'text',
            'value' => 'Primary School, Community School, Vocational School, Voluntary Secondary School, University, College', // Default
            'disporder' => 3
        ),
          
        'education_city' => array(
            'title' => 'Städte',
            'description' => 'In welche Städte können die Bildungseinrichtitungen eingeordnet werden?',
            'optionscode' => 'text',
            'value' => 'Berlin, London, Paris', // Default
            'disporder' => 4
        ),

        'education_multipage' => array(
            'title' => 'Multipage-Navigation',
            'description' => 'Sollen die Bildungseinrichtigungen ab einer bestimmten Anzahl auf der Seite auf mehrere Seiten aufgeteilt werden?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 5        
        ),

        'education_multipage_show' => array(
            'title' => 'Anzahl der Bilungseinrichtigung (Multipage-Navigation)',
            'description' => 'Wie viele Bildungseinrichtigungen sollen auf einer Seite angezeigt werden?',
            'optionscode' => 'text',
            'value' => '10', // Default
            'disporder' => 6        
        ),

        'education_lists' => array(
            'title' => 'Listen PHP (Navigation Ergänzung)',
            'description' => 'Wie heißt die Hauptseite eurer Listen-Seite? Dies dient zur Ergänzung der Navigation. Falls nicht gewünscht einfach leer lassen.',
            'optionscode' => 'text',
            'value' => 'listen.php', // Default
            'disporder' => 7
        ),  
    );
    
    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid']  = $gid;
        $db->insert_query('settings', $setting);
    }
        
    rebuild_settings();

    // TEMPLATES EINFÜGEN
	// Übersichtsseite
	$insert_array = array(
		'title'        => 'education',
		'template'    => $db->escape_string('<html>
        <head>
            <title>{$mybb->settings[\\\'bbname\\\']} - {$lang->education}</title>
            {$headerinclude}
        </head>
        <body>
            {$header}
            <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
                <tr>
                    <td class="thead"><span class="smalltext"><strong>{$lang->education}</strong></span></td>
                </tr>
                {$education_filter}
                {$education_add}
                <tr>
                    <td class="trow2" width="100%">
                        {$multipage}<br />
                        {$education_bit}
                        {$multipage}<br />
                    </td>
                </tr>
                {$education_join}
            </table>
            {$footer}
        </body>
    </html>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	// Hinzufügen
	$insert_array = array(
		'title'        => 'education_add',
		'template'    => $db->escape_string('<tr>
        <td class="trow2" align="center">
            <form id="add_education" method="post" action="misc.php?action=add_education">
                <table border="0" cellspacing="0" cellpadding="5" class="tborder">        				
                    <tbody>
                        <tr>      
                            <td class="thead" colspan="4">{$lang->education_add}</td>
                        </tr>
                        <tr>
                            <td class="tcat" width="33%" align="center">{$lang->education_add_name}</td>
                            <td class="tcat" width="33%" align="center">{$lang->education_add_description}</td>
                        </tr>
                        <tr>
                            <td class="trow2" align="center">
                                <input type="text" name="name" id="name" class="textbox">
                            </td>
                            <td class="trow2" align="center">
                                <textarea name="description" id="desc" style="width: 200px; height: 50px;"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td class="tcat" width="33%" align="center">{$lang->education_add_city}</td>
                            <td class="tcat" width="33%" align="center">{$lang->education_add_city}</td>
                        </tr>
                        <tr>
                            <td class="trow2" align="center">
                                <select name="type">
                                    <option value="">{$lang->education_filter_option_type}</option>	
                                    {$type_select}
                                </select>
                            </td>
                            <td class="trow2" align="center">
                                <select name="city" id="city">
                                    <option value="">{$lang->education_filter_option_city}</option>	
                                    {$city_select}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="trow2" colspan="4" align="center">
                                <input type="hidden" name="action" value="add_education">	
                                <input type="submit" value="{$lang->education_add_send}" name="add_education" class="button">  
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </td>
    </tr>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
    
	// Einzelne Bildungseinrichtung
	$insert_array = array(
		'title'        => 'education_bit',
		'template'    => $db->escape_string('<table cellspacing="0" cellpadding="5">
        <tbody>
            <tr>
                <td colspan="2" class="thead">
                    {$name} » {$type} » {$city}
                </td>
            </tr>
            <tr>        
                <td class="tcat" width="55%">
                    {$lang->education_desc}
                </td>
                <td class="tcat" width="45%">
                    {$lang->education_member}
                </td>
            </tr>
            <tr>        
                <td class="trow2" align="justify" width="55%" valign="top">
                    <div style="max-height: 80px; overflow: auto; padding-right: 10px; scrollbar-width: none;">
                        {$description}
                    </div>
                </td>
                <td class="trow2" width="45%" valign="top">
                    <div style="max-height: 80px; overflow: auto;">
                        {$user_bit}
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    {$edit} {$delete}
                </td>
            </tr>
        </tbody>
    </table>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
    
	// Schüler/Studenten
	$insert_array = array(
		'title'        => 'education_bit_users',
		'template'    => $db->escape_string('» {$user} » {$class}<br>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
    
	// Bearbeitungsseite
	$insert_array = array(
		'title'        => 'education_edit',
		'template'    => $db->escape_string('<html>
        <head>
            <title>{$mybb->settings[\\\'bbname\\\']} - {$lang->education_edit}</title>
            {$headerinclude}
        </head>
        <body>
            {$header}       
            <form id="edit_education" method="post" action="misc.php?action=education_edit&eid={$eid}">
                <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
                    <tr>
                        <td colspan="2" class="trow2"><div class="thead">{$lang->education_edit}</div></td>
                    </tr>
                    <tr>
                        <td class="trow2" valign="top" width="50%"> 
                            <div class="tcat" style="margin-bottom: 5px;text-align: center;">{$lang->education_add_name}</div>
                            <input type="text" class="textbox" name="name" id="name" maxlength="500" value="{$name}" style="margin-bottom: 5px;width: 99%;" />
                            <div class="tcat" style="margin-bottom: 5px;text-align: center;">{$lang->clublist_add_type}</div>
                            <select name="type" class="input" style="margin-bottom: 5px;width: 100%;">	
                                <option value="{$type}">{$type}</option>	
                                {$type_select}
                            </select>
                            <div class="tcat" style="margin-bottom: 5px;text-align: center;">{$lang->education_add_city}</div>
                            <select name="city" class="input" style="margin-bottom: 5px;width: 100%;">	
                                <option value="{$city}">{$city}</option>	
                                {$city_select}
                            </select>
                        </td>
                        <td class="trow2" valign="top"> 
                            <div class="tcat" style="margin-bottom: 5px;text-align: center;">{$lang->education_add_description}</div>
                            <textarea class="textbox" name="description" id="description" style="width: 99%;height: 151px;" maxlength="5000">{$description}</textarea>
                        </td>
                    </tr>
                    <tr>	
                        <td class="trow2" colspan=2>
                            <center>  
                                <input type="hidden" name="eid" id="eid" value="{$eid}" class="textbox" />
                                <input type="submit" value="{$lang->education_edit_send}" name="edit_education"  id="submit" class="button"> 
                            </center>	
                        </td>
                    </tr>
                </table>
            </form>
            {$footer}
        </body>
    </html>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
    
	// Filter
	$insert_array = array(
		'title'        => 'education_filter',
		'template'    => $db->escape_string('<tr>
        <td class="trow2" align="center">
            <div class="float_left_" style="margin:auto" valign="middle">
                <form method="get" id="search_education" action="misc.php?action=education">
                    <input type="hidden" name="action" value="education" />
                    {$lang->education_filter}
                    <select name="type" id="type">
                        <option value="">{$lang->education_filter_option_type}</option>
                        {$filter_type}
                    </select>
                    <select name="city" id="city">
                        <option value="">{$lang->education_filter_option_city}</option>
                        {$filter_city}
                    </select>
                    <input type="submit" value="{$lang->education_filter_button}" class="button" />
                </form>
            </div>
        </td>
    </tr>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
    
	// Beitreten
	$insert_array = array(
		'title'        => 'education_join',
		'template'    => $db->escape_string('<tr>
        <td class="trow2" align="center">
            <form method="post" action="misc.php" id="join_education">
                <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
                    <tr>      
                        <td class="thead" colspan="4">{$lang->education_join}</td>	
                    </tr>
                    <tr>
                        <td class="tcat" width="33%" align="center">{$lang->education_join_class}</td>
                        <td class="tcat" width="33%" align="center">{$lang->education_join_type}</td>
                    </tr>
                    <tr>
                        <td class="trow2" align="center">
                            <input type="text" name="class" value="{$education_class}" class="textbox" />
                        </td>
                        <td class="trow2" align="center">
                            <select name="eid">
                                {$education_options_bit}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="trow2" colspan="2" align="center">
                            <input type="hidden" name="action" value="join_education" />
                            <input type="submit" value="{$lang->education_join_send}" name="join_educations" class="button" />
                        </td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
    
	// Profil
	$insert_array = array(
		'title'        => 'education_memberprofile',
		'template'    => $db->escape_string('<b>{$name} ({$type} in {$city}):</b> {$class}<br>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
    
	//Modcp
	$insert_array = array(
		'title'        => 'education_modcp',
		'template'    => $db->escape_string('<html>
        <head>
            <title>{$mybb->settings[\\\'bbname\\\']} -  {$lang->education_modcp}</title>
            {$headerinclude}
        </head>
        <body>
            {$header}
            <table width="100%" border="0" align="center">
                <tr>
                    {$modcp_nav}
                    <td valign="top">
                        <table border="0" cellspacing="{$theme[\\\'borderwidth\\\']}" cellpadding="{$theme[\\\'tablespace\\\']}" class="tborder">
                            <tr>
                                <td class="thead">
                                    <strong>{$lang->education_modcp}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="trow2">
                                    {$education_modcp_bit}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            {$footer}
        </body>
    </html>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
    
	// Modcp-Bit
	$insert_array = array(
		'title'        => 'education_modcp_bit',
		'template'    => $db->escape_string('<table width="100%" border="0">
        <tbody>
            <tr>
                <td class="thead" colspan="2">{$name} » {$type} » {$city}</td>
            </tr>
            <tr>
                <td align="center" colspan="2">» {$lang->education_modcp_createdby} <b>{$createdby}</b></td>
            </tr>
            <tr>
                <td class="trow2" colspan="2" align="justify">
                    {$description}
                </td> 
            </tr>
            <tr>
                <td class="trow2" align="center" width="50%">
                    <a href="modcp.php?action=education&accepted={$eid}" class="button">{$lang->education_modcp_accepted}</a>
                </td>
                
                <td class="trow2" align="center" width="50%">
                    <a href="modcp.php?action=education&declined={$eid}" class="button">{$lang->education_modcp_declined}</a> 
                </td>
            </tr>
        </tbody>
    </table>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
    
	// Modcp-Nav
	$insert_array = array(
		'title'        => 'education_modcp_nav',
		'template'    => $db->escape_string('<tr>
        <td class="trow1 smalltext">
            <a href="modcp.php?action=education" class="modcp_nav_item modcp_nav_reports">{$lang->education_modcp_nav}</a>			
        </td>
    </tr>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);
  
}
 
// Funktion zur Überprüfung des Installationsstatus; liefert true zurürck, wenn Plugin installiert, sonst false (optional).
function education_is_installed() {

    global $db, $mybb;

    if ($db->table_exists("education")) {
        return true;
    }
    return false;
} 
 
// Diese Funktion wird aufgerufen, wenn das Plugin deinstalliert wird (optional).
function education_uninstall() {
  
	global $db;

    //DATENBANK LÖSCHEN
    if($db->table_exists("education"))
    {
        $db->drop_table("education");
    }

	if($db->table_exists("education_user"))
    {
        $db->drop_table("education_user");
    }
    
    // EINSTELLUNGEN LÖSCHEN
    $db->delete_query('settings', "name LIKE 'education%'");
    $db->delete_query('settinggroups', "name = 'education'");

    rebuild_settings();

    // TEMPLATES LÖSCHEN
    $db->delete_query("templates", "title LIKE 'education%'");
}
 
// Diese Funktion wird aufgerufen, wenn das Plugin aktiviert wird.
function education_activate() {

    global $db, $cache;

    // MyALERTS STUFF
    if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		if (!$alertTypeManager) {
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}

        // Alert beim Annehmen
		$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
		$alertType->setCode('education_accepted'); // The codename for your alert type. Can be any unique string.
		$alertType->setEnabled(true);
		$alertType->setCanBeUserDisabled(true);

		$alertTypeManager->add($alertType);

        // Alert beim Ablehnen
        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
		$alertType->setCode('education_declined'); // The codename for your alert type. Can be any unique string.
		$alertType->setEnabled(true);
		$alertType->setCanBeUserDisabled(true);

		$alertTypeManager->add($alertType);
    }
    
    // VARIABLEN EINFÜGEN
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets('member_profile', '#'.preg_quote('{$awaybit}').'#', '{$awaybit} {$education_memberprofile}');
	find_replace_templatesets('header', '#'.preg_quote('{$bbclosedwarning}').'#', '{$new_education_alert} {$bbclosedwarning}');
	find_replace_templatesets('modcp_nav_users', '#'.preg_quote('{$nav_ipsearch}').'#', '{$nav_ipsearch} {$nav_education}');
}
 
// Diese Funktion wird aufgerufen, wenn das Plugin deaktiviert wird.
function education_deactivate() {
    
	global $db, $cache;

    // VARIABLEN ENTFERNEN
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("member_profile", "#".preg_quote('{$education_memberprofile}')."#i", '', 0);
    find_replace_templatesets("header", "#".preg_quote('{$new_education_alert}')."#i", '', 0);
    find_replace_templatesets("modcp_nav_users", "#".preg_quote('{$nav_education}')."#i", '', 0);

    // MyALERT STUFF
    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		if (!$alertTypeManager) {
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}

		$alertTypeManager->deleteByCode('education_declined');
        $alertTypeManager->deleteByCode('education_accepted');
	}
}

##############################
### FUNKTIONEN - THE MAGIC ###
##############################

// ONLINE ANZEIGE - WER IST WO
function education_online_activity($user_activity) {

    global $parameters;

    $split_loc = explode(".php", $user_activity['location']);
    if($split_loc[0] == $user['location']) {
        $filename = '';
    } else {
        $filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
    }
    
    switch ($filename) {
        case 'misc':
        if($parameters['action'] == "education" && empty($parameters['site'])) {
            $user_activity['activity'] = "education";
        }
        if($parameters['action'] == "education_edit" && empty($parameters['site'])) {
            $user_activity['activity'] = "education_edit";
        }
        break;
    }
      

    return $user_activity;
}

function education_online_location($plugin_array) {

    global $mybb, $theme, $lang;

	if($plugin_array['user_activity']['activity'] == "education") {
		$plugin_array['location_name'] = "Sieht sich die <a href=\"misc.php?action=education\">Bildungseinrichtungen</a> an.";
	}
    if($plugin_array['user_activity']['activity'] == "education_edit") {
		$plugin_array['location_name'] = "Bearbeitet gerade eine Bildungseinrichtung.";
	}


    return $plugin_array;
}

// TEAMHINWEIS ÜBER NEUE BILDUNGSEINRICHTUNGEN
function education_global() {
    global $db, $cache, $mybb, $templates, $new_education_alert;

    // NEUE BILDUNGSEINRICHTUNGEN
    $select_education = $db->query("SELECT * FROM ".TABLE_PREFIX."educations WHERE accepted = 0");

    $count_education = mysqli_num_rows($select_education);
      
    if ($mybb->usergroup['canmodcp'] == "1" && $count_education == "1") {   
        $new_education_alert = "<div class=\"red_alert\"><a href=\"modcp.php?action=education\">Eine neue Bildungseinrichtung muss freigeschaltet werden</a></div>";
    } elseif ($mybb->usergroup['canmodcp'] == "1" && $count_education > "1") {
        $new_education_alert = "<div class=\"red_alert\"><a href=\"modcp.php?action=education\">{$count_education} neue Bildungseinrichtungen müssen freigeschaltet werden</a></div>";
    }
}

// DIE SEITEN
function education_misc() {
    global $db, $cache, $mybb, $lang, $templates, $theme, $header, $headerinclude, $footer, $multipage, $education_add, $education_bit, $type_select, $filter_type, $city_select, $filter_city, $education_class, $education_options_bit;

    // SPRACHDATEI LADEN
    $lang->load('education');
    
    // USER-ID
    $user_id = $mybb->user['uid'];

    // ACTION-BAUM BAUEN
    $mybb->input['action'] = $mybb->get_input('action');

	// EINSTELLUNGEN HOLEN
    $education_add_allow_groups_setting = $mybb->settings['education_add_allow_groups'];
    $education_member_allow_groups_setting = $mybb->settings['education_member_allow_groups'];
    $education_type_setting = $mybb->settings['education_type'];
    $education_city_setting = $mybb->settings['education_city'];
    $education_multipage_setting = $mybb->settings['education_multipage']; 
    $education_multipage_show_setting = $mybb->settings['education_multipage_show']; 
    $education_lists_setting = $mybb->settings['education_lists'];

	// AUSWAHLMÖGLICHKEIT DROPBOX GENERIEREN
	// Kategorien
    $type_select = ""; 
	$education_type = explode (", ", $education_type_setting);
	foreach ($education_type as $type) {
		$type_select .= "<option value='{$type}'>{$type}</option>";
	}
    // Städte
    $city_select = "";
	$education_city = explode (", ", $education_city_setting);
	foreach ($education_city as $city) {
		$city_select .= "<option value='{$city}'>{$city}</option>";
	}

    // BILDUNGSEINRICHTUNGEN-SEITE
    if($mybb->input['action'] == "education") {

        // NAVIGATION
        if(!empty($education_lists_setting)){
            add_breadcrumb("Listen", "$education_lists_setting");
            add_breadcrumb($lang->education, "misc.php?action=education");
        } else{
            add_breadcrumb($lang->education, "misc.php?action=education");
        }
        
        // Nur den Gruppen, den es erlaubt ist, neue Bildungseinrichtungen hinzuzufügen, ist es erlaubt, den Link zu sehen.
        if(is_member($education_add_allow_groups_setting)) {
            eval("\$education_add = \"".$templates->get("education_add")."\";");
        }

        // Nur den Gruppen, den es erlaubt ist, Bildungseinrichtungen beizutreten, ist es erlaubt, den Link zu sehen.
        if(is_member($education_member_allow_groups_setting)) {
            eval("\$education_join = \"".$templates->get("education_join")."\";");
        }

         
        // BILDUNGSEINRICHTUNG BEITRETEN
        if(is_member($education_member_allow_groups_setting)) {
    
            $query = $db->query("SELECT eid, name FROM ".TABLE_PREFIX."educations ORDER by name ASC");
            while($names = $db->fetch_array($query)) {
                $checked = "";
                $education_eid = $db->fetch_field($db->simple_select("educations_user", "eid", "uid = '{$user_id}'"), "eid");
                if($names['eid'] == $education_eid) {
                    $checked = "selected=\"selected\"";
                }
                $education_options_bit .= "<option value=\"{$names['eid']}\" $checked>{$names['name']}</option>";
            }

            $education_class = $db->fetch_field($db->simple_select("educations_user", "class", "uid = '{$user_id}'"), "class");
            
            eval("\$education_join = \"".$templates->get("education_join")."\";");
        }

        // FILTER
        // Filter aus den DB Einträgen generieren - Kategorien        
        $type_query = $db->query("SELECT DISTINCT type FROM ".TABLE_PREFIX."educations"); 

        while($type_filter = $db->fetch_array($type_query)){
            $filter_type .= "<option value='{$type_filter['type']}'>{$type_filter['type']}</option>";    
        }
    
        $etype = $mybb->input['type'];
        if(empty($etype)) {
            $etype = "%";
        }

        // Filter aus den DB Einträgen generieren - Städte
        $city_query = $db->query("SELECT DISTINCT city FROM ".TABLE_PREFIX."educations"); 
        
        while($city_filter = $db->fetch_array($city_query)){
            $filter_city .= "<option value='{$city_filter['city']}'>{$city_filter['city']}</option>";    
        }
    
        $ecity = $mybb->input['city'];
        if(empty($ecity)) {
            $ecity = "%";
        }

        eval("\$education_filter = \"".$templates->get("education_filter")."\";");

        // MULTIPAGE
        $select_education = $db->query("SELECT * FROM ".TABLE_PREFIX . "educations
        WHERE accepted = 1
        AND type LIKE '$etype'
        AND city LIKE '$ecity'
        ");

        $type_url = htmlspecialchars_uni("misc.php?action=education&type={$etype}&city={$ecity}");

        $count = mysqli_num_rows($select_education);
        $perpage = $education_multipage_show_setting;
        $page = intval($mybb->input['page']);
	
        if($page) {
            $start = ($page-1) *$perpage;
        }
        else {
            $start = 0;
            $page = 1;
        }
        $end = $start + $perpage;
        $lower = $start+1;
        $upper = $end;
        if($upper > $count) {
            $upper = $count;
        }
	
        if ($education_multipage_setting == 1) {
            $multipage = multipage($count, $perpage, $page, $type_url);
        } else {
            $multipage = "";
        }

        // ABFRAGE ALLER BILDUNGSEINRICHTUNGEN- MULTIPAGE
        if ($education_multipage_setting == 1) {
            $query_education = $db->query("SELECT * FROM ".TABLE_PREFIX."educations
            WHERE accepted != '0'
            AND type LIKE '$etype'
            AND city LIKE '$ecity'
            ORDER by name ASC
            LIMIT $start, $perpage
            ");
        } 
        // ABFRAGE ALLER BILDUNGSEINRICHTUNGEN - OHNE MULTIPAGE
        else {
            $query_education = $db->query("SELECT * FROM ".TABLE_PREFIX."educations
            WHERE accepted != '0'
            AND type LIKE '$etype'
            AND city LIKE '$ecity'
            ORDER by name ASC
            ");
        }

        // AUSGABE ALLER BILDUNGSEINRICHTUNGEN
        while($education = $db->fetch_array($query_education)) {
	
            // ALLES LEER LAUFEN LASSEN
            $eid = "";
            $type = "";
            $city = "";
            $name = "";
            $description = "";
            $accepted = "";
            $createdby = "";
		
            // MIT INFOS FÜLLEN
            $eid = $education['eid'];
            $type = $education['type'];
            $city = $education['city'];
            $name = $education['name'];
            $description = $education['description'];
            $accepted = $education['accepted'];
            $createdby = $education['createdby'];

            // SCHÜLER DER BILDUNGSEINRICHTUNG
            // Abfrage
            $user_query = $db->query("SELECT * FROM ".TABLE_PREFIX."educations_user eu
            LEFT JOIN ".TABLE_PREFIX."users u
            ON (eu.uid = u.uid)
            WHERE eu.eid = '$eid'
            AND u.uid IN (SELECT uid FROM ".TABLE_PREFIX."users)
            ORDER BY u.username ASC
            ");
            
            // Leer laufen lassen
            $user_bit = "";
    
            // Auslese 
            while($users = $db->fetch_array($user_query)){

                $users['username'] = format_name($users['username'], $users['usergroup'], $users['displaygroup']);
                $user = build_profile_link($users['username'], $users['uid']);

                $class = $users['class'];


                eval("\$user_bit .= \"".$templates->get("education_bit_users")."\";");
            }

            // LÖSCH- & BEARBEITUNGSOPTIONEN - NUR TEAM
            if($mybb->usergroup['canmodcp'] == "1" || $createdby == $user_id){
                $edit = "» <a href=\"misc.php?action=education_edit&eid={$eid}\"><i class=\"fas fa-edit\" original-title=\"Bildungseinrichtung bearbeiten\"></i></a>";
                $delete = "» <a href=\"misc.php?action=education&delete={$eid}\"><i class=\"fas fa-trash\" original-title=\"Bildungseinrichtung löschen\"></i></a>";
            } else {
                $edit = "";
                $delete = "";
            }

            eval("\$education_bit .= \"".$templates->get("education_bit")."\";");
        }
    
        // BILDUNGSEINRICHTUNG LÖSCHEN
        $delete = $mybb->input['delete'];
        if($delete) {
            // in DB education löschen
            $db->delete_query("educations", "eid = '$delete'");
            // in DB education_user löschen
            $db->delete_query("educations_user", "eid = '$delete'");
            redirect("misc.php?action=education", "{$lang->education_delete_redirect}");
        }

        eval("\$page = \"".$templates->get("education")."\";");
        output_page($page);
        die();
    
    }

    // BILDUNGSEINRICHTUNG HINZUFÜGEN
    elseif($_POST['add_education']) {
        
        if($mybb->input['type'] == "")
        {
            error("Es muss eine Kategorie ausgewählt werden!");
        }
        elseif($mybb->input['city'] == "")
        {
            error("Es muss eine Stadt ausgewählt werden!");
        }
        elseif($mybb->input['name'] == "")
        {
            error("Es muss ein Name eingetragen werden!");
        }
        elseif($mybb->input['description'] == "")
        {
            error("Es muss eine Beschreibung eingetragen werden!");	
        } else{
  
            //Wenn das Team eine Bildungseinrichtung erstellt, dann werden diese sofort freigeschaltet
            if($mybb->usergroup['canmodcp'] == '1'){
                $accepted = 1;
            } else {
                $accepted = 0;
            }

            $type = $db->escape_string ($_POST['type']);
            $city = $db->escape_string ($_POST['city']);
            $name = $db->escape_string ($_POST['name']);
            $description = $db->escape_string ($_POST['description']);

            $new_education = array(
                "type" => $type,
                "city" => $city,
                "name" => $name,
                "description" => $description,
                "createdby" => (int)$mybb->user['uid'],
                "accepted" => $accepted
            );

            $db->insert_query("educations", $new_education);
            redirect("misc.php?action=education", "{$lang->education_add_redirect}");
        }  
    }

    // BILDUNGSEINRICHTUNG BEITRETEN
	elseif($mybb->input['action'] == "join_education") {
    
        // USER-ID
        $user_id = $mybb->user['uid'];

        $new_record = array(
            "eid" => (int)$mybb->get_input('eid'),
            "class" => $db->escape_string($mybb->get_input('class')),
            "uid" => (int)$mybb->user['uid']
        );

        // Zählen, ob der User schon eingetragen ist
        $education_update = $db->query("SELECT * FROM ".TABLE_PREFIX . "educations_user WHERE uid = '".$user_id."'");
        $count_user = mysqli_num_rows($education_update);

        // Wenn ein Eintrag schon vorhanden ist von dem User, wird nur seine Spalte geupdatet
        if($count_user == '1'){
            $db->update_query("educations_user", $new_record, "uid = '$user_id'");
        } // Neuer Eintrag 
        else {
            $db->insert_query("educations_user", $new_record);
        }

        redirect("misc.php?action=education", "{$lang->education_join_redirect}");
    }

    // BILDUNGSEINRICHTUNG BEARBEITEN
    elseif($mybb->input['action'] == "education_edit") {

        // NAVIGATION
        if(!empty($education_lists_setting)){
            add_breadcrumb("Listen", "$education_lists_setting");
            add_breadcrumb ($lang->education, "misc.php?action=education");
            add_breadcrumb ($lang->education_edit, "misc.php?action=education_edit");
        } else {
            add_breadcrumb ($lang->education, "misc.php?action=education");
            add_breadcrumb ($lang->education_edit, "misc.php?action=education_edit");
        }

        $eid =  $mybb->get_input('eid', MyBB::INPUT_INT);

        $edit_query = $db->query("SELECT * FROM ".TABLE_PREFIX."educations      
        WHERE eid = '".$eid."'
        ");

        $edit = $db->fetch_array($edit_query);

        // Alles leer laufen lassen
        $eid = "";
        $type = "";
        $city = "";
        $name = "";
        $description = "";
        $accepted = "";
        $createdby = "";
        
        // Füllen wir mal alles mit Informationen
        $eid = $edit['eid'];
        $type = $edit['type'];
        $city = $edit['city'];
        $name = $edit['name'];
        $description = $edit['description'];
        $accepted = $edit['accepted'];        
        $createdby = $edit['createdby'];

        //Der neue Inhalt wird nun in die Datenbank eingefügt bzw. die alten Daten überschrieben.        
        if($_POST['edit_education']){

            $eid = $mybb->input['eid'];
            $type = $db->escape_string ($_POST['type']);
            $city = $db->escape_string ($_POST['city']);
            $name = $db->escape_string ($_POST['name']);
            $description = $db->escape_string ($_POST['description']);
       
            $edit_education = array(
                "type" => $type,
                "city" => $city,
                "name" => $name,
                "description" => $description,
            );
            
            $db->update_query("educations", $edit_education, "eid = '".$eid."'");
            redirect("misc.php?action=education", "{$lang->education_edit_redirect}");
        }

        $createdby_uid = $db->fetch_field($db->simple_select("educations", "createdby", "eid = '{$eid}'"), "createdby");
        if ($createdby_uid == $user_id || $mybb->usergroup['canmodcp'] == "1"){ 
            // TEMPLATE FÜR DIE SEITE
            eval("\$page = \"".$templates->get("education_edit")."\";");
            output_page($page);
            die();
        } else {
            error_no_permission();
        }
    }
}

// MOD-CP - NAVIGATION
function education_modcp_nav() {

    global $db, $mybb, $templates, $theme, $header, $headerinclude, $footer, $lang, $modcp_nav, $nav_education;
    
    $lang->load('education');
    
    eval("\$nav_education = \"".$templates->get ("education_modcp_nav")."\";");
}

// MOD-CP - SEITE
function education_modcp() {
   
    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $db, $page, $modcp_nav, $education_modcp_bit;

    // SPRACHDATEI
	$lang->load('education');

    if($mybb->get_input('action') == 'education') {

        // Add a breadcrumb
        add_breadcrumb($lang->nav_modcp, "modcp.php");
        add_breadcrumb($lang->education_modcp, "modcp.php?action=education");

        // BILDUNGSEINRICHTUNG ABFRAGEN
        $modcp_query = $db->query("SELECT * FROM ".TABLE_PREFIX."educations
        WHERE accepted = '0'
        ORDER BY name ASC
        ");

        // BILDUNGSEINRICHTUNG AUSLESEN
        while($modcp = $db->fetch_array($modcp_query)) {
   
            // Alles leer laufen lassen
            $eid = "";
            $type = "";
            $city = "";
            $name = "";
            $description = "";
            $accepted = "";
            $createdby = "";
   
            // Füllen wir mal alles mit Informationen
            $eid = $modcp['eid'];
            $type = $modcp['type'];
            $city = $modcp['city'];
            $name = $modcp['name'];
            $description = $modcp['description'];
   
            // User der das eingesendet hat
            $modcp['createdby'] = htmlspecialchars_uni($modcp['createdby']);
            $user = get_user($modcp['createdby']);
            $user['username'] = htmlspecialchars_uni($user['username']);
            $createdby = build_profile_link($user['username'], $modcp['createdby']);
   
            eval("\$education_modcp_bit .= \"".$templates->get("education_modcp_bit")."\";");
        }
	  
        // Die Bildungseinrichtung wird vom Team abgelehnt 
        $dec = $mybb->input['declined'];
        if($dec){

            // MyALERTS STUFF
            $query_alert = $db->simple_select("educations", "*", "eid = '{$dec}'");
            while ($alert_del = $db->fetch_array ($query_alert)) {
                if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                    $user = get_user($alert['createdby']);
                    $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('education_declined');
                    if ($alertType != NULL && $alertType->getEnabled()) {
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$alert_del['createdby'], $alertType, (int)$dec);
                        $alert->setExtraDetails([
                            'username' => $user['username'],
                            'name' => $alert_del['name'],
                            'type' => $alert_del['type'],
                            'city' => $alert_del['city']
                        ]);
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                }  
            }

            $db->delete_query("educations", "eid = '$dec'");
            redirect("modcp.php?action=education", "{$lang->education_modcp_declined_redirect}");    
        }

    
        // Die Bildungseinrichtung wurde vom Team angenommen 
        if($acc = $mybb->input['accepted']){

            // MyALERTS STUFF
            $query_alert = $db->simple_select("educations", "*", "eid = '{$acc}'");
            while ($alert_acc = $db->fetch_array ($query_alert)) {
                if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                    $user = get_user($alert['createdby']);
                    $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('education_accepted');
                    if ($alertType != NULL && $alertType->getEnabled()) {
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$alert_acc['createdby'], $alertType, (int)$acc);
                        $alert->setExtraDetails([
                            'username' => $user['username'],
                            'name' => $alert_acc['name'],
                            'type' => $alert_acc['type'],
                            'city' => $alert_acc['city']
                        ]);
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                }
            }

            $db->query("UPDATE ".TABLE_PREFIX."educations SET accepted = 1 WHERE eid = '".$acc."'");
            redirect("modcp.php?action=education", "{$lang->education_modcp_accepted_redirect}");    
        }
		 
        // TEMPLATE FÜR DIE SEITE
        eval("\$page = \"".$templates->get("education_modcp")."\";");
        output_page($page);
        die();
    }
}

function education_myalert_alerts() {

	global $mybb, $lang;
	
    $lang->load('education');

    // BILDUNGSEINRICHTUNG ANNEHMEN
    /**
    * Alert formatter for my custom alert type.
    */
    class MybbStuff_MyAlerts_Formatter_EducationAcceptedFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
        * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
        *
        * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
        *
        * @return string The formatted alert string.
        */
	
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            global $db;
            $alertContent = $alert->getExtraDetails();
            $userid = $db->fetch_field($db->simple_select("users", "uid", "username = '{$alertContent['username']}'"), "uid");
            $user = get_user($userid);
            $alertContent['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
            return $this->lang->sprintf(
                $this->lang->education_accepted,
                $outputAlert['from_user'],
                $alertContent['username'],
                $outputAlert['dateline'],
                $alertContent['name'],
                $alertContent['type'],
                $alertContent['city']
            );	
        }

        /**
        * Init function called before running formatAlert(). Used to load language files and initialize other required
        * resources.
        *
        * @return void
        */
        public function init()
        {
            if (!$this->lang->education) {
                $this->lang->load('education');
            }	
        }

        /**
        * Build a link to an alert's content so that the system can redirect to it.
        *
        * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
        *
        * @return string The built alert, preferably an absolute link.
        */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {
            return $this->mybb->settings['bburl'] . '/misc.php?action=education';
        }	
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);		
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_EducationAcceptedFormatter($mybb, $lang, 'education_accepted')
        );
    }

	// BILDUNGSEINRICHTUNG ABLEHNEN
    /**
    * Alert formatter for my custom alert type.
    */
	class MybbStuff_MyAlerts_Formatter_EducationDeclinedFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
	{
	    /**
        * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
        *
        * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
        *
        * @return string The formatted alert string.
        */
	    public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
	    {
			global $db;
			$alertContent = $alert->getExtraDetails();
            $userid = $db->fetch_field($db->simple_select("users", "uid", "username = '{$alertContent['username']}'"), "uid");
            $user = get_user($userid);
            $alertContent['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
	        return $this->lang->sprintf(
	            $this->lang->education_declined,
				$outputAlert['from_user'],
				$alertContent['username'],
	            $outputAlert['dateline'],
				$alertContent['name'],
				$alertContent['type'],
				$alertContent['city']
	        );
	    }

	    /**
        * Init function called before running formatAlert(). Used to load language files and initialize other required
        * resources.
        *
        * @return void
        */
	    public function init()
	    {
	        if (!$this->lang->education) {
	            $this->lang->load('education');
	        }
	    }

	    /**
        * Build a link to an alert's content so that the system can redirect to it.
        *
        * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
        *
        * @return string The built alert, preferably an absolute link.
        */
	    public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
	    {
	        return $this->mybb->settings['bburl'] . '/misc.php?action=education';
	    }
	}

	if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
		$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

		if (!$formatterManager) {
			$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
		}

		$formatterManager->registerFormatter(
				new MybbStuff_MyAlerts_Formatter_EducationDeclinedFormatter($mybb, $lang, 'education_declined')
		);
	} 
}

// ANZEIGE IM PORFIL
function education_memberprofile() {
   
    global $db, $mybb, $lang, $templates, $theme, $memprofile, $education_memberprofile;

    // SPRACHDATEI LADEN
    $lang->load("education");

    $uid = $mybb->get_input('uid', MyBB::INPUT_INT);

    $profile_query = $db->query("SELECT * FROM ".TABLE_PREFIX."educations_user u
    LEFT JOIN ".TABLE_PREFIX."educations e
    ON (u.eid = e.eid) 
    WHERE u.uid = '".$uid."'
    ");

       
    while($prof = $db->fetch_array($profile_query)){

        // Alles leer laufen lassen
        $eid = "";
        $type = "";
        $city = "";
        $name = "";
        $description = "";
        $class = "";
       
        // Füllen wir mal alles mit Informationen
        $eid = $prof['eid'];
        $type = $prof['type'];
        $city = $prof['city'];
        $name = $prof['name'];
        $description = $prof['description'];
        $class = $prof['class'];
       
        eval("\$education_memberprofile .= \"".$templates->get("education_memberprofile")."\";");  
    }    
}
