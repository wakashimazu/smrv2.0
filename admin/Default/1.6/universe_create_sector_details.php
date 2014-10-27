<?php

$sector =& SmrSector::getSector($var['game_id'],$var['sector_id']);
$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';
$PHP_OUTPUT.= create_echo_form($container);
$PHP_OUTPUT.= '<table class="create"><tr><td class="center';
if (!$sector->hasLinkUp()) $PHP_OUTPUT.= ' border_top';
if (!$sector->hasLinkDown()) $PHP_OUTPUT.= ' border_bottom';
if (!$sector->hasLinkLeft()) $PHP_OUTPUT.= ' border_left';
if (!$sector->hasLinkRight()) $PHP_OUTPUT.= ' border_right';
$PHP_OUTPUT.= '"><table><tr><td width="5%">&nbsp;</td><td width="90%" class="center"><input type="checkbox" name="up" value="up"';
if ($sector->hasLinkUp()) $PHP_OUTPUT.= ' checked';
$PHP_OUTPUT.= '></td><td width="5%">&nbsp;</td></tr>';
$PHP_OUTPUT.= '<tr><td width="5%" class="center"><input type="checkbox" name="left" value="left"';
if ($sector->hasLinkLeft()) $PHP_OUTPUT.= ' checked';
$PHP_OUTPUT.= '></td><td width="90%" class="center">';
$PHP_OUTPUT.= 'Sector: ' . $sector->getSectorID() . '<br /><br />';
$PHP_OUTPUT.= 'Planet Type: <select name="plan_type" onChange = "planetSelect(this)" >';
$PHP_OUTPUT.= '<option value="0">No Planet</option>';

$selectedType = 0;
$planet = null;
if ($sector->hasPlanet()) {
	$selectedType = $sector->getPlanet()->getTypeID();
	$planet = $sector->getPlanet();
}

$db->query('SELECT * FROM planet_type');
while ($db->nextRecord()) {
	$type = $db->getInt('planet_type_id');
	$PHP_OUTPUT.= '<option value="'.$type.'"'.($type == $selectedType ? ' selected' : '').'>'.$db->getField('planet_type_name').'</option>';

}
//$PHP_OUTPUT.= '<option value="Uninhab"' . ($sector->hasPlanet() ? ' selected' : '') . '>Uninhabitable Planet</option>';
//$PHP_OUTPUT.= '<option value="NPC"' . ($planet_type == 'NPC' ? ' selected' : '') . '>NPC Planet</option>';
$PHP_OUTPUT.= '</select>';
//loading all pictures from the images folder
$planet_pics;
$dir = WWW."images/planets";
if (is_dir($dir)) {
	$planet_pics = scandir($dir);
	foreach ($planet_pics as $key => $value){
		if(!is_file($dir.'/'.$value)){
			unset($planet_pics[$key]);
		}
	}
}

if (!is_null($planet)) {
	$PHP_OUTPUT.='<div id="planetEdit" >';
	$PHP_OUTPUT.='<br>Planet Image: <input type="hidden" name="image" value="'.$planet->getImage().'"></input>';
	$PHP_OUTPUT.='<br>Planet Size: <input name="size" value="'.$planet->getSize().'"></input> </div>';
	$PHP_OUTPUT.='<div id="planet_selector" class="noselect" >';
	foreach($planet_pics as $index => $pic){
				
		$PHP_OUTPUT.= '<div class="thumbnail selectable" style="background-image:url(\'images/planets/thumb/'.$pic.'\')" data-name="'.$pic.'" > </div>';
	}
	$PHP_OUTPUT.='</div> ';
	$PHP_OUTPUT.='<div id="planet_preview"> ';
	$PHP_OUTPUT.='<div id="preview" style="background-image:url(\''.$planet->getImage().'\')"> </div>';
	$PHP_OUTPUT.='<div id="slider"> </div>';
	$PHP_OUTPUT.='</div> ';
	$PHP_OUTPUT.='</div> ';
}
$PHP_OUTPUT.='<br><br>';

$PHP_OUTPUT.= 'Port: <select name="port_level">';
$PHP_OUTPUT.= '<option value="0">No Port</option>';
for ($i=1;$i<=9;$i++) {
	$PHP_OUTPUT.= '<option value="' . $i . '"';
	if ($sector->hasPort() && $sector->getPort()->getLevel() == $i) $PHP_OUTPUT.= 'selected';
	$PHP_OUTPUT.= '>Level ' . $i . '</option>';
}
$PHP_OUTPUT.= '</select>';
$PHP_OUTPUT.= '<select name="port_race">';
$races =& Globals::getRaces();
foreach ($races as &$race) {
	$PHP_OUTPUT.= '<option value="' . $race['Race ID'] . '"';
	if ($sector->hasPort() && $sector->getPort()->getRaceID() == $race['Race ID']) $PHP_OUTPUT.= 'selected';
	$PHP_OUTPUT.= '>' . $race['Race Name'] . '</option>';
} unset($race);
$PHP_OUTPUT.= '</select>';
//goods determined randomly to sway admin abuse
$PHP_OUTPUT.= '<br /><br />';
$locations =& SmrLocation::getAllLocations();
$sectorLocations =& $sector->getLocations();
for ($i=0;$i<UNI_GEN_LOCATION_SLOTS;$i++) {
	$PHP_OUTPUT.= 'Location ' . ($i + 1) . ': <select name="loc_type' . $i . '">';
	$PHP_OUTPUT.= '<option value="0">No Location</option>';
	foreach ($locations as &$location) {
		$PHP_OUTPUT.= '<option value="' . $location->getTypeID() . '"';
		if (isset($sectorLocations[$i]) && $sectorLocations[$i]->equals($location)) {
			$PHP_OUTPUT.= 'selected';
		}
		$PHP_OUTPUT.= '>' . $location->getName() . '</option>';
	} unset($location);
	$PHP_OUTPUT.= '</select><br />';
}

$PHP_OUTPUT.= '</td><td width="5%" class="center"><input type="checkbox" name="right" value="right"';
if ($sector->hasLinkRight()) $PHP_OUTPUT.= ' checked';
$PHP_OUTPUT.= '></td></tr>';
$PHP_OUTPUT.= '<tr><td width="5%" class="center">&nbsp;</td><td width="90%" class="center"><input type="checkbox" name="down" value="down"';
if ($sector->hasLinkDown()) $PHP_OUTPUT.= ' checked';
$PHP_OUTPUT.= '></td><td width="5%" class="center">Warp:<br /><input type="number" size="5" name="warp" value="';
if ($sector->hasWarp()) {
	$warpSector=& $sector->getWarpSector();
	$PHP_OUTPUT.= $warpSector->getSectorID();
	$warpGal = $warpSector->getGalaxyName();
}
else {
	$PHP_OUTPUT.= 0;
	$warpGal = 'No Warp';
}
$PHP_OUTPUT.= '"><br>' . $warpGal . '</td></tr></table></td></tr></table>';
$PHP_OUTPUT.= '<br><br>';

$PHP_OUTPUT.= '<input type="submit" name="submit" value="Edit Sector"><br />';
$container = $var;
$container['body'] = '1.6/universe_create_sectors.php';
$PHP_OUTPUT.= '<br><a href="'.SmrSession::getNewHREF($container).'" class="submitStyle">Cancel</a>';
$PHP_OUTPUT.= '</form>';
?>