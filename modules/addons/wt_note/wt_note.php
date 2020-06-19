<?php
if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function wt_note_config() {
	$configarray = array(
	"name" => "Notification module via telegram",
	"description" => "Notification module via telegram",
	"version" => "1.0",
	"author" => "",
	"language" => "english",
	"fields" => array(
	"key" => array ("FriendlyName" => "Bot Token", "Type" => "text", "Size" => "50", "Description" => "Send to @BotFather: /newbot to create your telegram bot", "Default" => "", ),
	"chatid" => array ("FriendlyName" => "Chat ID", "Type" => "text", "Size" => "50", "Description" => "ChatID to send messages to. If a group, bot must be added to group manually.", "Default" => "", ),
	));
	return $configarray;
}

function wt_note_activate() {
	$query = "CREATE TABLE IF NOT EXISTS `wikitelegramnote` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`adminid` int(11) NOT NULL,
	`access_token` varchar(255) NOT NULL,
	`permissions` text NOT NULL,
	PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;";
	$result = mysql_query($query);
}

function wt_note_deactivate() {
	$query = "DROP TABLE `wikitelegramnote`";
	$result = mysql_query($query);
}

function wt_note_output($vars) {
	global $customadminpath, $CONFIG;

	$access_token = select_query('wikitelegramnote', '', array('adminid' => $_SESSION['adminid']));

	if ( $_GET['return'] == '1' && $_SESSION['request_token'] ) {
		
		insert_query("wikitelegramnote", array("adminid" => $_SESSION['adminid'], "access_token" => $result['access_token']));
		$_SESSION['request_token'] = "";
		header("Location: addonmodules.php?module=wt_note");
		
	} elseif($_GET['setup'] == '1' && !mysql_num_rows($access_token)) {

		$_SESSION['request_token'] = $vars['key'];
		header("Location: ". $CONFIG['SystemURL']."/".$customadminpath."/addonmodules.php?module=wt_note&return=1");

	} elseif( $_GET['disable'] == '1' && mysql_num_rows($access_token) ) {
		full_query("DELETE FROM `wikitelegramnote` WHERE `adminid` = '".$_SESSION['adminid']."'");
		echo "<div class='infobox'><strong>Notification plugin successfully disabled</strong><br>Update successfully removed and plug-in disabled</div>";
	} elseif( mysql_num_rows($access_token) && $_POST ){
		update_query('wikitelegramnote',array('permissions' => serialize($_POST['wt_notefication'])), array('adminid' => $_SESSION['adminid']));
		echo "<div class='infobox'><strong>Changes saved</strong><br>Changes saved successfully</div>";    
	}

	$access_token = select_query('wikitelegramnote', '', array('adminid' => $_SESSION['adminid']));
	$result = mysql_fetch_array($access_token, MYSQL_ASSOC);
	$permissions = unserialize($result['permissions']);   

	if ( !mysql_num_rows($access_token)) {
		echo "<p><a href='addonmodules.php?module=wt_note&setup=1'>Activate</a></p>";
	} else {
		echo "<p><a href='addonmodules.php?module=wt_note&disable=1'>Disable</a></p>";
		echo '<form method="POST"><table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
		<tr>
		<td class="fieldlabel" width="200px">Send message when:</td>
		<td class="fieldarea">
		<table width="100%">
		<tr>
		<td valign="top">
		<input type="checkbox" name="wt_notefication[new_client]" value="1" id="wt_notefications_new_client" '.($permissions['new_client'] == "1" ? "checked" : "").'> <label for="wt_notefications_new_client">New client</label><br>
		<input type="checkbox" name="wt_notefication[new_invoice]" value="1" id="wt_notefications_new_invoice" '.($permissions['new_invoice'] == "1" ? "checked" : "").'> <label for="wt_notefications_new_invoice">New invoice</label><br>
		<input type="checkbox" name="wt_notefication[new_update]" value="1" id="wt_notefications_new_update" '.($permissions['new_update'] == "1" ? "checked" : "").'> <label for="wt_notefications_new_update">New update</label><br>
		<input type="checkbox" name="wt_notefication[new_ticket]" value="1" id="wt_notefications_new_ticket" '.($permissions['new_ticket'] == "1" ? "checked" : "").'> <label for="wt_notefications_new_ticket">New Ticket</label><br>
		</td>
		</tr>
		</table>
		</table>
		<p align="center"><input type="submit" value="Save changes" class="button"></p></form>';
	}
}
