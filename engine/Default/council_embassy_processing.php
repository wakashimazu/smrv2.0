<?php

if(!$player->isPresident()) {
	create_error('Only the president can view the embassy.');
}

$race_id = $var['race_id'];
$type = strtoupper($_REQUEST['action']);
$time = TIME + TIME_FOR_COUNCIL_VOTE;// / Globals::getGameSpeed($player->getGameID());

$db->query('SELECT count(*) FROM race_has_voting
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND race_id_1 = ' . $db->escapeNumber($player->getRaceID()));
if ($db->nextRecord() && $db->getInt('count(*)') > 2) {
	create_error('You can\'t initiate more than 3 votes at a time!');
}

if ($type == 'PEACE') {
	$db->query('SELECT 1 FROM race_has_voting
				WHERE race_id_1='.$db->escapeNumber($race_id).' AND race_id_2='.$db->escapeNumber($player->getRaceID()).' AND game_id = '.$db->escapeNumber($player->getGameID()));
	if($db->nextRecord()) {
		create_error('You cannot start a vote with that race.');
	}
}

$db->query('REPLACE INTO race_has_voting
			(game_id, race_id_1, race_id_2, type, end_time)
			VALUES(' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getRaceID()) . ', '.$db->escapeNumber($race_id).', '.$db->escapeString($type).', '.$db->escapeNumber($time).')');

if ($type == 'PEACE') {
	$db->query('REPLACE INTO race_has_voting
				(game_id, race_id_1, race_id_2, type, end_time)
				VALUES(' . $db->escapeNumber($player->getGameID()) . ', '.$race_id.', ' . $db->escapeNumber($player->getRaceID()) . ', '.$db->escapeString($type).', '.$time.')');
}

forward(create_container('skeleton.php', 'council_embassy.php'));

?>