<?php
require_once(get_file_loc('hof.functions.inc'));
$game_id =null;
if (isset($var['game_id'])) $game_id = $var['game_id'];
$base = array();

if (empty($game_id))
{
	$topic = 'All Time Hall of Fame';
}
else
{
	$topic = Globals::getGameName($game_id).' Hall of Fame';
}
$template->assign('PageTopic',$topic);
$PHP_OUTPUT.=('<div class="center">');

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'hall_of_fame_player_detail.php';
if (isset($var['game_id'])) $container['game_id'] = $var['game_id'];
$PHP_OUTPUT.='Welcome to the Hall of Fame ' . $account->getHofName() . '!<br />The Hall of Fame is a comprehensive '.
			'list of player accomplishments.  Here you can view how players rank in many different '.
			'aspects of the game rather than just kills, deaths, and experience with the rankings system.<br />'.
				create_link($container,'You can also view your Personal Hall of Fame here.').'<br /><br />';

$db->query('SELECT DISTINCT type FROM player_hof' . (isset($var['game_id']) ? ' WHERE game_id='.$var['game_id'] : '').' ORDER BY type');
define('DONATION_NAME','Money Donated To SMR');
define('USER_SCORE_NAME','User Score');
$hofTypes = array(DONATION_NAME=>true, USER_SCORE_NAME=>true);
while($db->nextRecord())
{
	$hof =& $hofTypes;
	$typeList = explode(':',$db->getField('type'));
	foreach($typeList as $type)
	{
		if(!isset($hof[$type]))
		{
			$hof[$type] = array();
		}
		$hof =& $hof[$type];
	}
	$hof = true;
}
$container = create_container('skeleton.php','hall_of_fame_new.php');
if (isset($var['game_id'])) $container['game_id'] = $var['game_id'];
$viewing= '<span style="font-weight:bold;">Currently viewing: </span>'.create_link($container,isset($var['game_id'])?'Current HoF':'Global HoF');
$typeList = array();
if(isset($var['type']))
{
	foreach($var['type'] as $type)
	{
		if(!is_array($hofTypes[$type]))
		{
			$var['type'] = $typeList;
			$var['view'] = $type;
			break;
		}
		else
			$typeList[] = $type;
		$viewing .= ' -&gt; ';
		$container = $var;
		$container['type'] = $typeList;
		unset($container['view']);
		$viewing.= create_link($container,$type);
		
		$hofTypes =& $hofTypes[$type];
	}
}
if(isset($var['view']))
{
	$viewing .= ' -&gt; ';
	if(is_array($hofTypes[$var['view']]))
	{
		$typeList[] = $var['view'];
		$var['type'] = $typeList;
	}
	$container = $var;
	$viewing .= create_link($container,$var['view']);
	
	if(is_array($hofTypes[$var['view']]))
	{
		$hofTypes =& $hofTypes[$var['view']];
		unset($var['view']);
	}
}
$viewing.= '<br /><br />';

$PHP_OUTPUT.= $viewing;
$PHP_OUTPUT.= '<table class="standard" align="center">';


if(!isset($var['view']))
{
	$PHP_OUTPUT.=('<tr><th>Category</th><th width="60%">Subcategory</th></tr>');
	
	foreach($hofTypes as $type => $value)
	{
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td>'.$type.'</td>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'hall_of_fame_new.php';
		if (isset($var['type']))
			$container['type'] = $var['type'];
		else
			$container['type'] = array();
		$container['type'][] = $type;
		if (isset($var['game_id']))
			$container['game_id'] = $var['game_id'];
		$PHP_OUTPUT.=('<td valign="middle">');
		$i=0;
		if(is_array($value))
		{
			foreach($value as $subType => $subTypeValue)
			{
				++$i;
				$container['view'] = $subType;
				
				$rankType = $container['type'];
				$rankType[] = $subType;
				$rank = getHofRank($subType,$rankType,$account->account_id,$game_id,$db);
				$rankMsg='';
				if($rank['Rank']!=0)
					$rankMsg = ' (#' . $rank['Rank'] .')';
				
				$PHP_OUTPUT.=create_submit_link($container,$subType.$rankMsg);
				$PHP_OUTPUT.=('&nbsp;');
				if ($i % 3 == 0) $PHP_OUTPUT.=('<br />');
			}
		}
		else
		{
			unset($container['view']);
			$rank = getHofRank($type,$container['type'],$account->account_id,$game_id,$db);
			$PHP_OUTPUT.=create_submit_link($container,'View (#' . $rank['Rank'] .')');
		}
		$PHP_OUTPUT.=('</td></tr>');
	}
}
else
{
	$PHP_OUTPUT.=('<tr><th>Rank</th><th>Player</th><th>Total</th></tr>');
	
	$rank=1;
	$foundMe=false;
	$viewType = $var['type'];
	$viewType[] = $var['view'];
	if($var['view'] == DONATION_NAME)
		$db->query('SELECT account_id, SUM(amount) as amount FROM account_donated ' .
				'GROUP BY account_id ORDER BY amount DESC LIMIT 25');
	else if($var['view'] == USER_SCORE_NAME)
	{
		$statements = SmrAccount::getUserScoreCaseStatement($db);
		$query = 'SELECT account_id, '.$statements['CASE'].' amount FROM (SELECT account_id, type, SUM(amount) amount FROM player_hof WHERE type IN ('.$statements['IN'].')'.(isset($var['game_id']) ? ' AND game_id=' . $var['game_id'] : '') .' GROUP BY account_id,type) x GROUP BY account_id ORDER BY amount DESC LIMIT 25';
		$db->query($query);
	}
	else
		$db->query('SELECT account_id,SUM(amount) amount FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false).(isset($var['game_id']) ? ' AND game_id=' . $var['game_id'] : '') .' GROUP BY account_id ORDER BY amount DESC LIMIT 25');
	while($db->nextRecord())
	{
		if($db->getField('account_id') == $account->account_id)
			$foundMe = true;
		$PHP_OUTPUT .= displayHOFRow($rank++,$db->getField('account_id'),$db->getField('amount') );
	}
	if(!$foundMe)
	{
		$rank = getHofRank($var['view'],$viewType,$account->getAccountID(),$var['game_id'],$db);
		$PHP_OUTPUT .= displayHOFRow($rank['Rank'],$account->getAccountID(),$rank['Amount']);
	}
}

$PHP_OUTPUT.=('</table></div>');

?>