<?php
require_once('SmrShip.class.inc');
require_once('SmrForce.class.inc');

abstract class AbstractSmrPlayer
{
	protected $db;
	
	const DEFAULT_MAX_TURNS = 450;
	const HOF_CHANGED = 1;
	const HOF_NEW = 2;
//	var $race_name;
//	var $level_id;
//	var $level_name;
//	var $alliance_description;
//	var $controlled;
//	var $old_dead;
//	var $bank;
//	var $newbie_warning;
//	var $relations_global;
//	var $relations_global_rev;
//	var $ignore_global;
//	var $last_news_update;
//	var $display_experience;
//	var $display_level_id;
//	var $display_level_name;
//	var $sector_change;
//	var $game_type;
//	var $game_speed;
//	var $validated;
//	var $out_of_game;
//    var $safe_exit;
//    var $detected;
	protected $accountID;
	protected $gameID;
	protected $playerName;
	protected $playerID;
	protected $sectorID;
	protected $lastSectorID;
	protected $newbieTurns;
	protected $dead;
	protected $startDead;
	protected $landedOnPlanet;
	protected $lastShieldUpdate;
	protected $lastActive;
	protected $raceID;
	protected $credits;
	protected $alignment;
	protected $experience;
	protected $allianceID;
	protected $shipID;
	protected $kills;
	protected $deaths;
	protected $allianceName;
	protected $goods;
	protected $goodsExpGained;
	protected $gadgets;
	protected $stats;
	protected $pastMaintBought;
	protected $pastMaintCredit;
	protected $pureRelations;
	protected $relations;
	protected $militaryPayment;
	protected $bounties;
	protected $turns;
	protected $lastCPLAction;
	
	protected $visitedSectors;
	
	protected $HOF;
	
	protected $hasChanged=false;
	protected $hasHOFChanged=false;
	protected $hasBountyChanged = array();
	
	protected function __construct()
	{
	}
	
	public function getAccountID()
	{
		return $this->accountID;
	}
	
	public function getGameID()
	{
		return $this->gameID;
	}
	
	public function getNewbieTurns()
	{
		return $this->newbieTurns;
	}
	
	public function hasNewbieTurns()
	{
		return $this->getNewbieTurns()>0;
	}
	public function setNewbieTurns($newbieTurns)
	{
		if($this->newbieTurns == $newbieTurns)
			return;
		$this->newbieTurns=$newbieTurns;
		$this->hasChanged=true;
	}
	
	public function getShipTypeID()
	{
		return $this->shipID;
	}
	
	public function setShipTypeID($shipID)
	{
		if($this->shipID == $shipID)
			return;
		$this->shipID=$shipID;
		$this->hasChanged=true;
	}
	
	public function getLastShieldUpdate()
	{
		return $this->lastShieldUpdate;
	}
	
	public function setLastShieldUpdate($time)
	{
		if($this->lastShieldUpdate == $time)
			return;
		$this->lastShieldUpdate=$time;
		$this->hasChanged=true;
	}
	
	public function getSectorID()
	{
		return $this->sectorID;
	}
	
	public function &getSector()
	{
		return SmrSector::getSector($this->getGameID(),$this->getSectorID());
	}
	
	public function setSectorID($sectorID)
	{
		$this->lastSectorID = $this->getSectorID();
		$this->sectorID = $sectorID;
		$this->updateLastCPLAction();
		$this->hasChanged=true;
	}
	
	public function getLastSectorID()
	{
		return $this->lastSectorID;
	}
	
	public function isDead()
	{
		return $this->dead;
	}
	
	public function hasFederalProtection()
	{
		$sector =& SmrSector::getSector($this->getGameID(),$this->getSectorID());
		if (!$sector->offersFederalProtection())
		{
			return false;
		}	

        $ship =& $this->getShip();
        $attackRating = $ship->getAttackRating();
		if ($attackRating > 8)
			return false;
		if ($ship->hasIllegalGoods())
			return false;
		if ($attackRating == 0)
			return true;
		if ($this->getAlignment() > ($attackRating - 4) * 150)
			return true;
		return false;
	}
	
	public function canFight()
	{
		return !$this->hasNewbieTurns() && !$this->isDead() && !$this->hasFederalProtection();
	}
	
	public function getStartDead()
	{
		return $this->startDead;
	}
	
	public function setDead($bool)
	{
		if($this->dead == $bool)
			return;
		$this->dead=$bool;
		$this->hasChanged=true;
	}
	
	public function getKills()
	{
		return $this->kills;
	}
	
	public function increaseKills($kills)
	{
		if($kills < 0)
			throw new Exception('Trying to increase negative kills.');
		$this->setKills($this->kills+$kills);
	}
	
	public function setKills($kills)
	{
		if($this->kills == $kills)
			return;
		$this->kills=$kills;
		$this->hasChanged=true;
	}
	
	public function getDeaths()
	{
		return $this->deaths;
	}
	
	public function increaseDeaths($deaths)
	{
		if($deaths < 0)
			throw new Exception('Trying to increase negative deaths.');
		$this->setDeaths($this->getDeaths()+$deaths);
	}
	
	public function setDeaths($deaths)
	{
		if($this->deaths == $deaths)
			return;
		$this->deaths = $deaths;
		$this->hasChanged=true;
	}
	
	public function getAlignment()
	{
		return $this->alignment;
	}
	
	public function increaseAlignment($align)
	{
		if($align < 0)
			throw new Exception('Trying to increase negative align.');
		if($align == 0)
			return;
		$align += $this->alignment;
		$this->setAlignment($align);
	}
	public function decreaseAlignment($align)
	{
		if($align < 0)
			throw new Exception('Trying to decrease negative align.');
		if($align == 0)
			return;
		$align = $this->alignment - $align;
		$this->setAlignment($align);
	}
	public function setAlignment($align)
	{
		if($this->alignment == $align)
			return;
		$this->alignment = $align;
		$this->hasChanged=true;
	}
	
	public function getCredits()
	{
		return $this->credits;
	}
	
	public function getExperience()
	{
		return $this->experience;
	}
	
	public function getNextLevelPercentAcquired()
	{
		if($this->getNextLevelExperience() == $this->getThisLevelExperience())
			return 100;
		return max(0,min(100,round(($this->getExperience() - $this->getThisLevelExperience()) / ($this->getNextLevelExperience() - $this->getThisLevelExperience())*100)));
	}
	
	public function getNextLevelPercentRemaining()
	{
		return 100 - $this->getNextLevelPercentAcquired();
	}
	
	public function getNextLevelExperience()
	{
		$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
		if(!isset($LEVELS_REQUIREMENTS[$this->getLevelID()+1]))
			return $this->getThisLevelExperience(); //Return current level experience if on last level.
		return $LEVELS_REQUIREMENTS[$this->getLevelID()+1]['Requirement'];
	}
	
	public function getThisLevelExperience()
	{
		$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
		return $LEVELS_REQUIREMENTS[$this->getLevelID()]['Requirement'];
	}
	
	public function setExperience($experience)
	{
		if($this->experience == $experience)
			return;
		if($experience<MIN_EXPERIENCE)
			$experience = MIN_EXPERIENCE;
		if($experience>MAX_EXPERIENCE)
			$experience = MAX_EXPERIENCE;
		$this->experience = $experience;
		$this->hasChanged=true;
	}

	public function increaseCredits($credits)
	{
		if($credits < 0)
			throw new Exception('Trying to increase negative credits.');
		if($credits == 0)
			return;
		$credits += $this->credits;
		$this->setCredits($credits);
	}
	public function decreaseCredits($credits)
	{
		if($credits < 0)
			throw new Exception('Trying to decrease negative credits.');
		if($credits == 0)
			return;
		$credits = $this->credits - $credits;
		$this->setCredits($credits);
	}
	public function setCredits($credits)
	{
		if($this->credits == $credits)
			return;
		if (has_privilege('Money Doesn\'t Matter',$this->accountID)) return;
		if($credits < 0)
			throw new Exception('Trying to set negative credits.');
		if($credits > MAX_MONEY)
			throw new Exception('Trying to set more than max credits.');
		$this->credits = $credits;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET credits = '.$this->credits.' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function increaseExperience($experience)
	{
		if($experience < 0)
			throw new Exception('Trying to increase negative experience.');
		if($experience == 0)
			return;
		$newExperience = $this->experience + $experience;
		$this->setExperience($newExperience);
		$this->increaseHOF($experience,array('Experience','Total','Gain'));
	}
	public function decreaseExperience($experience)
	{
		if($experience < 0)
			throw new Exception('Trying to decrease negative experience.');
		if($experience == 0)
			return;
		$newExperience = $this->experience - $experience;
		$this->setExperience($newExperience);
		$this->decreaseHOF($experience,array('Experience','Total','Loss'));
	}
	
	public function isLandedOnPlanet()
	{
		return $this->landedOnPlanet;
	}
	
	public function setLandedOnPlanet($bool)
	{
		if($this->landedOnPlanet == $bool)
			return;
		$this->landedOnPlanet=$bool;
		$this->hasChanged=true;
	}
	
	public function getLevelID()
	{
		$LEVELS_REQUIREMENTS =& Globals::getLevelRequirements();
		foreach ($LEVELS_REQUIREMENTS as $level_id => $require)
		{
			if ($this->getExperience() >= $require['Requirement']) continue;
			return $level_id - 1;
		}
		return max(array_keys($LEVELS_REQUIREMENTS));
	}
	
	public function getLevelName()
	{
		$LEVEL_REQUIREMENTS = Globals::getLevelRequirements();
		$RACES = Globals::getRaces();
		if ($this->getAlignment() > 150) $a_name = 'Federation';
		elseif ($this->getAlignment() < -150) $a_name = 'Underground';
		else $a_name = 'Sector';
		$level_name = $LEVEL_REQUIREMENTS[$this->getLevelID()]['Name'];
		$level_name = str_replace('Race',$RACES[$this->raceID]['Race Name'],$level_name);
		$level_name = str_replace('Alignment',$a_name,$level_name);
		return $level_name;
	}
	
	public function getPlayerID()
	{
		return $this->playerID;
	}
	
	public function getPlayerName()
	{
		return $this->playerName;
	}
	
	public function setPlayerName($name)
	{
		$this->playerName = $name;
		$this->hasChanged = true;
	}
	
	public function getName()
	{
		return $this->playerName;
	}

	public function getDisplayName()
	{
//NPC: get_colored_name($dead_guy->getPlayerName(), $dead_guy->getAlignment()) . ' <span class='npcColor'>[NPC]</span>';
			return get_colored_text($this->getAlignment(),$this->getName().' ('.$this->getPlayerID().')');
	}

	public function getLinkedDisplayName($includeAlliance=true)
	{
		$return = '<a href="'.$this->getTraderSearchHREF().'">'.$this->getDisplayName().'</a>';

		if($includeAlliance)
		{
			$return.= ' (';
			if($this->hasAlliance())
			{
				$return .= '<a href="'.$this->getAllianceRosterHREF().'">';
			}
			$return .= $this->getAllianceName();
			if($this->hasAlliance())
			{
				$return.'</a>';
			}
			$return.= ')';
		}
		return $return;
	}
	
	public function getRaceID()
	{
		return $this->raceID;
	}
	
	public function getRaceName()
	{
		return Globals::getRaceName($this->getRaceID());
	}
	
	public function getColouredRaceName($otherRaceID)
	{
		$races =& Globals::getRaces();
		
		$raceRelations =& Globals::getRaceRelations($this->getGameID(),$this->getRaceID());

		return get_colored_text($raceRelations[$otherRaceID], $races[$otherRaceID]['Race Name']);
	}
	
	public function setRaceID($raceID)
	{
		if($this->raceID == $raceID)
			return;
		$this->raceID=$raceID;
		$this->hasChanged=true;
	}
	
	public function getAllianceID()
	{
		return $this->allianceID;
	}
	
	public function hasAlliance()
	{
		return $this->getAllianceID()!=0;
	}
	
	public function setAllianceID($ID)
	{
		if($this->allianceID == $ID)
			return;
		$this->allianceID=$ID;
		$this->hasChanged=true;
	}
	
	public function getAllianceName()
	{
		if(!isset($this->allianceName))
		{
			//get alliance info
			if ($this->allianceID > 0)
			{
				$this->db->query('SELECT alliance_name FROM alliance WHERE alliance_id = ' . $this->getAllianceID() . ' AND game_id = ' . $this->getGameID() . ' LIMIT 1');
				$this->db->nextRecord();
				$this->allianceName = stripslashes($this->db->getField('alliance_name'));
			}
			else $this->allianceName = 'No Alliance';
		}
		return $this->allianceName;
	}
	
	public function getSpanColour()
	{
		return get_colored_name('NA', $this->alignment, 'Game', 'Color');
	}
	
	public function isCombatDronesKamikazeOnMines()
	{
		return $this->combatDronesKamikazeOnMines;
	}
	
	public function setCombatDronesKamikazeOnMines($bool)
	{
		if($this->combatDronesKamikazeOnMines == $bool)
			return;
		$this->combatDronesKamikazeOnMines = $bool;
		$this->hasChanged=true;
	}
	
	public abstract function getPastMaintBought();
	public abstract function getPastMaintCredit();
	public function getPastMaint()
	{
		$pastMaint = $this->getPastMaintBought()+$this->getPastMaintCredit();
		if ($pastMaint < 0) return 0;
		return $pastMaint;
	}
	
	protected abstract function getStatsData();
	public function getStats()
	{
		$this->getStatsData();
		return $this->stats;
	}
	
	public function getStat($name)
	{
		$stats = $this->getStats();
		return isset($stats[$name]) ? $stats[$name] : 0;
	}
	
	protected abstract function getGadgetsData();
	public function getGadgets()
	{
		$this->getGadgetsData();
		return $this->gadgets;
	}
	
	public function getGadget($gadgetID)
	{
		if(!is_numeric($gadgetID))
		{
			global $GADGETS;
			$gadgetID = $GADGETS[$gadgetID]['ID'];
		}
		$gadgets = $this->getGadgets();
		if(isset($gadgets[$gadgetID]))
			return $gadgets[$gadgetID];
		return false;
	}
	
	public function isGadgetEquipped($gadgetID)
	{
		$gadget = $this->getGadget($gadgetID);
		if($gadget===false)
			return false;
		return $gadget['Equipped'] > 0 && $gadget['Equipped'] < TIME && ($gadget['Expires'] == 0 || $gadget['Expires'] > TIME) && $gadget['Cooldown'] <= TIME;
	}
	
	protected abstract function getPureRelationsData();
	
	public function getPureRelations()
	{
		$this->getPureRelationsData();
		return $this->pureRelations;
	}
	
	public function getPureRelation($raceID)
	{
		$rels = $this->getPureRelations();
		return $rels[$raceID];
	}
	
	public function getRelations()
	{
		if(!isset($this->relations))
		{
			//get relations
			$RACES = Globals::getRaces();
			$pureRels = $this->getPureRelations(); // make sure they're initialised.
			$stats = $this->getStats(); // make sure they're initialised.
			$this->relations = array();
			foreach ($RACES as $race_id => $race_name)
			{
				$this->relations[$race_id] = round($pureRels[$race_id] + $stats['Relations'] / 2);
			}
		}
		return $this->relations;
	}
	
	public function getRelation($raceID)
	{
		$rels = $this->getRelations();
		return $rels[$raceID];
	}
	
	abstract public function &getShip();
	
	public function getDiminishingPortAttack()
	{
		return max(0,min(1, 1 - ($this->getPastMaint() / ($this->getShip()->getSpeed() * 25 + 450))));
	}
	
	public function getDiminishingPlanetAttack()
	{
		return max(0,min(1, 1 - ($this->getPastMaint() / ($this->getShip()->getSpeed() * 25 + 450))));
	}
	
	public function getDiminishingForceAttack()
	{
		return max(0,min(1, 1 - ($this->getPastMaint() / ($this->getShip()->getSpeed() * 25 + 900))));
	}
	
	public function getDiminishingTraderAttack()
	{
		return max(0,min(1, 1 - ($this->getPastMaint() / ($this->getShip()->getSpeed() * 25 + 475))));
	}
	
	public function &shootPlayer(AbstractSmrPlayer &$targetPlayer)
	{
		return $this->getShip()->shootPlayer($targetPlayer);
	}
	
	public function &shootForces(SmrForce &$forces)
	{
		return $this->getShip()->shootForces($forces);
	}
	
	public function &shootPort(SmrPort &$port)
	{
		return $this->getShip()->shootPort($port);
	}
	
	public function &shootPlanet(SmrPlanet &$planet)
	{
		return $this->getShip()->shootPlanet($planet);
	}
	
	public function &shootPlayers(array &$targetPlayers)
	{
		return $this->getShip()->shootPlayers($targetPlayers);
	}
	
	public function getMilitaryPayment()
	{
		return $this->militaryPayment;
	}
	
	public function hasMilitaryPayment()
	{
		return $this->getMilitaryPayment()>0;
	}
	
	public function setMilitaryPayment($amount)
	{
		if($this->militaryPayment == $amount)
			return;
		$this->militaryPayment = $amount;
		$this->hasChanged=true;
	}
	
	public function increaseMilitaryPayment($amount)
	{
		if($amount < 0)
			throw new Exception('Trying to increase negative military payment.');
		$this->setMilitaryPayment($this->getMilitaryPayment()+$amount);
	}
	
	public function decreaseMilitaryPayment($amount)
	{
		if($amount < 0)
			throw new Exception('Trying to decrease negative military payment.');
		$this->setMilitaryPayment($this->getMilitaryPayment()-$amount);
	}
	
	abstract protected function getBountiesData();
	
	public function getBounties()
	{
		$this->getBountiesData();
		return $this->bounties;
	}
	
	public function hasBounties()
	{
		return count($this->getBounties())>0;
	}
	
	public function getBounty($bountyID)
	{
		$bounties = $this->getBounties();
		return isset($bounties[$bountyID]) ? $bounties[$bountyID] : false;
	}
	
	public function hasBounty($bountyID)
	{
		return $this->getBounty($bountyID)!==false;
	}
	
	public function getBountyAmount($bountyID)
	{
		$bounty = $this->getBounty($bountyID);
		return $bounty['Amount'];
	}
	
	protected function createBounty($type)
	{
		$bounty = array('Amount' => 0,
						'SmrCredits' => 0,
						'Type' => $type,
						'Claimer' => 0,
						'Time' => TIME,
						'ID' => $this->getNextBountyID(),
						'New' => true);
		$this->setBounty($bounty);
	}
	
	public function getNextBountyID()
	{
		$keys = array_keys($this->getBounties());
		if(count($keys)>0)
			return max($keys)+1;
		else
			return 0;
	}
	
	public function setBounty(array $bounty)
	{
		$this->bounties[$bounty['ID']] = $bounty;
		$this->hasBountyChanged[$bounty['ID']]=true;
	}
	
	public function setBountyAmount($bountyID, $amount)
	{
		$bounty = $this->getBounty($bountyID);
		$bounty['Amount'] = $amount;
		$this->setBounty($bounty);
	}
	
	public function increaseBountyAmount($bountyID,$amount)
	{
		if($amount < 0)
			throw new Exception('Trying to increase negative bounty.');
		$this->setBountyAmount($this->getBountyAmount($bountyID)+$amount);
	}
	
	public function decreaseBountyAmount($bountyID,$amount)
	{
		if($amount < 0)
			throw new Exception('Trying to decrease negative bounty.');
		$this->setBountyAmount($this->getBountyAmount($bountyID)+$amount);
	}
	
	public function getCurrentBounty($type)
	{
		$bounties = $this->getBounties();
		foreach($bounties as $bounty)
		{
			if($bounty['Claimer'] == 0 && $bounty['Type']==$type)
				return $bounty;
		}
		return $this->createBounty($type);
	}
	
	public function hasCurrentBounty($type)
	{
		$bounties = $this->getBounties();
		foreach($bounties as $bounty)
		{
			if($bounty['Claimer'] == 0 && $bounty['Type']==$type)
				return true;
		}
		return false;
	}
	
	public function getCurrentBountyAmount($type)
	{
		$bounty = $this->getCurrentBounty($type);
		return $bounty['Amount'];
	}
	
	public function setCurrentBountyAmount($type, $amount)
	{
		$bounty = $this->getCurrentBounty($type);
		if($bounty['Amount'] == $amount)
			return;
		$bounty['Amount'] = $amount;
		$this->setBounty($bounty);
	}
	
	public function increaseCurrentBountyAmount($type,$amount)
	{
		if($amount < 0)
			throw new Exception('Trying to increase negative current bounty.');
		$this->setCurrentBountyAmount($type,$this->getCurrentBountyAmount($type)+$amount);
	}
	
	public function decreaseCurrentBountyAmount($type,$amount)
	{
		if($amount < 0)
			throw new Exception('Trying to decrease negative current bounty.');
		$this->setCurrentBountyAmount($type,$this->getCurrentBountyAmount($type)-$amount);
	}
	
	public function getCurrentBountySmrCredits($type)
	{
		$bounty = $this->getCurrentBounty($type);
		return $bounty['SmrCredits'];
	}
	
	public function setCurrentBountySmrCredits($type, $credits)
	{
		$bounty = $this->getCurrentBounty($type);
		if($bounty['SmrCredits'] == $credits)
			return;
		$bounty['SmrCredits'] = $credits;
		$this->setBounty($bounty);
	}
	
	public function increaseCurrentBountySmrCredits($type,$credits)
	{
		if($credits<0)
			throw new Exception('Trying to increase negative current bounty.');
		$this->setCurrentBountySmrCredits($type,$this->getCurrentBountySmrCredits($type)+$credits);
	}
	
	public function decreaseCurrentBountySmrCredits($type,$credits)
	{
		if($credits<0)
			throw new Exception('Trying to decrease negative current bounty.');
		$this->setCurrentBountySmrCredits($type,$this->getCurrentBountySmrCredits($type)-$credits);
	}
	
	public function setBountiesClaimable(AbstractSmrPlayer $claimer)
	{
		$bounties = $this->getBounties();
		if(is_array($bounties))
		{
			foreach($bounties as $bounty)
			{
				if($bounty['Claimer'] == 0)
				{
					$bounty['Claimer'] = $claimer->getAccountID();
					$this->setBounty($bounty);
				}
			}
		}
	}
	
	
	abstract protected function getHOFData();
	
	public function getHOF(array $typeList = null)
	{
		$this->getHOFData();
		if($typeList==null)
			return $this->HOF;
		$hof=$this->HOF;
		foreach($typeList as $type)
		{
			if(!isset($hof[$type]))
				return 0;
			$hof = $hof[$type];
		}
		return $hof;
	}
	
	public function increaseHOF($amount,array $typeList)
	{
		if($amount < 0)
			throw new Exception('Trying to increase negative HOF: '.implode(':',$typeList));
		if($amount == 0)
			return;
		$this->setHOF($this->getHOF($typeList)+$amount,$typeList);
	}
	
	public function decreaseHOF($amount,array $typeList)
	{
		if($amount < 0)
			throw new Exception('Trying to decrease negative HOF: '.implode(':',$typeList));
		if($amount == 0)
			return;
		$this->setHOF($this->getHOF($typeList)-$amount,$typeList);
	}
	
	public function setHOF($amount,array $typeList)
	{
		if(is_array($this->getHOF($typeList)))
			throw new Exception('Trying to overwrite a HOF type: '.implode(':',$typeList));
		if($this->getHOF($typeList)==$amount)
			return;
		if($amount < 0)
			$amount=0;
		$this->getHOF();
		
		$hof =& $this->HOF;
		$hofChanged =& $this->hasHOFChanged;
		$new = false;
		foreach($typeList as $type)
		{
			if(!isset($hofChanged[$type]))
				$hofChanged[$type] = array();
			if(!isset($hof[$type]))
			{
				$hof[$type] = array();
				$new = true;
			}
			$hof =& $hof[$type];
			$hofChanged =& $hofChanged[$type];
		}
		if($hofChanged==null)
		{
			$hofChanged = self::HOF_CHANGED;
			if($new)
				$hofChanged = self::HOF_NEW;
		}
		$hof = $amount;
	}
	
	abstract public function killPlayer($sectorID);
	abstract public function &killPlayerByPlayer(AbstractSmrPlayer &$killer);
	abstract public function &killPlayerByForces(SmrForce &$forces);
	abstract public function &killPlayerByPort(SmrPort &$port);
	abstract public function &killPlayerByPlanet(SmrPlanet &$planet);
	abstract public function getHome();
	
	
	public function getTurns()
	{
		return $this->turns;
	}
	
	public function hasTurns()
	{
		return $this->turns>0;
	}
	
	public function getMaxTurns()
	{
		return round(self::DEFAULT_MAX_TURNS * Globals::getGameSpeed($this->getGameID()));
	}

	public function setTurns($maint,$newNoob = false,$updateLastActive = false)
	{
		if(($maintSame=($this->turns == $maint)) & ($newbSame=($this->newbieTurns == $newNoob)) && !$updateLastActive)
			return;
		$this->turns = $maint;

		if(!has_beta_privilege('No SC Change'))
		{
			if ($this->turns < 0)
				$this->turns = 0;
			if ($this->turns > $this->getMaxTurns())
				$this->turns = $this->getMaxTurns();
		}
		
		if($newNoob !== false)
			$this->newbieTurns = $newNoob;
		if ($this->newbieTurns < 0)
			$this->newbieTurns = 0;
	
		if (has_privilege('Unlimited Maintenance'))
			$this->turns = $this->getMaxTurns();
		
		$this->hasChanged=true;
		if($updateLastActive === true)
		{
			$this->setLastActive(TIME);
			$this->setLastCPLAction(TIME);
		}
	}

	public function takeTurns($take, $noob = 0,$updateLastActive = true) // taking maint counts as activity by default
	{
		if($take < 0 || $noob < 0)
			throw new Exception('Trying to take negative turns.');
		$take = ceil($take);
		$new_maint = $this->getTurns() - $take;
		$new_noob = $this->getNewbieTurns() - $noob;
		
		if(!has_beta_privilege('No SC Loss'))
			$this->setTurns($new_maint,$new_noob,$updateLastActive);
		$this->increaseHOF($take,array('Movement','Turns Used','Since Last Death'));
		$this->increaseHOF($take,array('Movement','Turns Used','Total'));
		$this->increaseHOF($noob,array('Movement','Turns Used','Newbie'));
	}
	
	public function giveTurns($give, $noob = 0,$updateLastActive = false)
	{
		if($give < 0 || $noob < 0)
			throw new Exception('Trying to give negative turns.');
		$give = floor($give);
		
		if(!has_beta_privilege('No SC Gain'))
			$this->setTurns($this->getTurns() + $give,$this->getNewbieTurns() + $noob,$updateLastActive);
	}
	
	public function getLastActive()
	{
		return $this->lastActive;
	}
	
	public function setLastActive($lastActive)
	{
		if($this->lastActive == $lastActive)
			return;
		$this->lastActive=$lastActive;
		$this->hasChanged=true;
	}
	
	public function getLastCPLAction()
	{
		return $this->lastCPLAction;
	}
	
	public function setLastCPLAction($time)
	{
		if($this->lastCPLAction == $time)
			return;
		$this->lastCPLAction=$time;
		$this->hasChanged=true;
	}
	
	public function updateLastCPLAction()
	{
		$this->setLastCPLAction(TIME);
	}
	
	public function sameAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->equals($otherPlayer) || ($this->hasAlliance()>0&&$this->getAllianceID()==$otherPlayer->getAllianceID());
	}
	
	public function sharedForceAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function forceNAPAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function planetNAPAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function traderNAPAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function traderMAPAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->traderAttackTraderAlliance($otherPlayer) && $this->traderDefendTraderAlliance($otherPlayer);
	}
	
	public function traderAttackTraderAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function traderDefendTraderAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function traderAttackForceAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function traderAttackPortAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function traderAttackPlanetAlliance(AbstractSmrPlayer $otherPlayer)
	{
		return $this->sameAlliance($otherPlayer);
	}
	
	public function meetsAlignmentRestriction($restriction)
	{
		if($restriction<0)
			return $this->getAlignment()<=$restriction;
		if($restriction>0)
			return $this->getAlignment()>=$restriction;
		return true;
	}
	
	/**
	 * Will retrieve all visited sectors, use only when you are likely to check a large number of these
	 */
	public function hasVisitedSector($sectorID)
	{
		if(!isset($this->visitedSectors))
		{
			$this->visitedSectors = array();
			$this->db->query('SELECT sector_id FROM player_visited_sector WHERE account_id=' . $this->getAccountID() . ' AND game_id=' . $this->getGameID());
			while($this->db->nextRecord())
				$this->visitedSectors[$this->db->getField('sector_id')] = false;
		}
		return !isset($this->visitedSectors[$sectorID]);
	}
}
?>