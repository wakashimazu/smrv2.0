<?php
require_once(get_file_loc('ChessGame.class.inc'));

$chessGames =& ChessGame::getOngoingAccountGames($player->getAccountID());
$template->assign('ChessGames', $chessGames);

$playersChallenged = array($player->getAccountID() => true);
foreach($chessGames as $chessGame) {
	$playersChallenged[$chessGame->getWhiteID()] = true;
	$playersChallenged[$chessGame->getBlackID()] = true;
}

$players = array();
$db->query('SELECT player_id, player.player_name FROM player JOIN account USING(account_id) LEFT OUTER JOIN npc_logins USING(login) WHERE working IS NULL AND validated = ' . $db->escapeBoolean(true) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id NOT IN (' . $db->escapeArray(array_keys($playersChallenged)) . ') ORDER BY player_name');
while ($db->nextRecord()) {
	$players[$db->getInt('player_id')] = $db->getField('player_name');
}
$template->assignByRef('PlayerList',$players);

if(ENABLE_NPCS_CHESS) {
	$npcs = array();
	$db->query('SELECT player_id, player.player_name FROM player JOIN account USING(account_id) JOIN npc_logins USING(login) WHERE validated = ' . $db->escapeBoolean(true) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id NOT IN (' . $db->escapeArray(array_keys($playersChallenged)) . ') ORDER BY player_name');
	while ($db->nextRecord()) {
		$npcs[$db->getInt('player_id')] = $db->getField('player_name');
	}
	$template->assignByRef('NPCList',$npcs);
}

?>