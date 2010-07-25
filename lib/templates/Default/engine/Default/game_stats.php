<div align="center">
	<table class="standard">
		<tr>
			<td align="center">General Info</td>
			<td align="center">Other Info</td>
		</tr>
		<tr>
			<td valign="top" align="center">
				<table class="nobord">
					<tr>
						<td align="right">Name</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo Globals::getGameName($GameID); ?></td></tr>
					<tr>
						<td align="right">Description</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo bbifyMessage(Globals::getGameDescription($GameID)); ?></td>
					</tr>
					<tr>
						<td align="right">Start Date</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo date(DATE_DATE_SHORT,Globals::getGameStartDate($GameID)); ?></td>
					</tr>
					<tr>
						<td align="right">End Date</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo date(DATE_DATE_SHORT,Globals::getGameEndDate($GameID)); ?></td>
					</tr>
					<tr>
						<td align="right">Current Players</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format(count($CurrentPlayers)); ?></td></tr>
					<tr>
						<td align="right">Max Turns</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format(Globals::getGameMaxTurns($GameID)); ?></td>
					</tr>
					<tr>
						<td align="right">Max Players</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format(Globals::getGameMaxPlayers($GameID)); ?></td>
					</tr>
					<tr>
						<td align="right">Alliance Max Players</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format(Globals::getAllianceMaxPlayers($GameID)); ?></td>
					</tr>
					<tr>
						<td align="right">Alliance Max Vets</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format(Globals::getAllianceMaxVets($GameID)); ?></td>
					</tr>
					<tr>
						<td align="right">Game Type</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo Globals::getGameType($GameID); ?></td>
					</tr>
					<tr>
						<td align="right">Game Speed</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format(Globals::getGameSpeed($GameID)); ?></td>
					</tr>
					<tr>
						<td align="right">Credits Needed</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format(Globals::getGameCreditsRequired($GameID)); ?></td>
					</tr>
					<tr>
						<td align="right">Stats Ignored</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo Globals::getGameIgnoreStats($GameID)?'Yes':'No'; ?></td>
					</tr>
					<tr>
						<td align="right">Starting Credits</td>
						<td>&nbsp;</td>
						<td align="left"><?php echo number_format(Globals::getStartingCredits($GameID)); ?></td>
					</tr>
				</table>
			</td>
			<td valign="top" align="center">
				<table class="nobord">
					<tr>
						<td align="right">Total Players</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($TotalPlayers); ?></td>
					</tr>
					<tr>
						<td align="right">Alliances</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($TotalAlliances); ?></td>
					</tr>
					<tr>
						<td align="right">Highest Experience</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($HighestExp); ?></td>
					</tr>
					<tr>
						<td align="right">Highest Alignment</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($HighestAlign); ?></td>
					</tr>
					<tr>
						<td align="right">Lowest Alignment</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($LowestAlign); ?></td>
					</tr>
					<tr>
						<td align="right">Highest Kills</td>
						<td>&nbsp;</td><td align="left"><?php echo number_format($HighestKills); ?></td>
					</tr>
				</table>
			</td>
		</tr>
	</table><br />
	<table class="standard">
		<tr>
			<td align="center">Top 10 Players in Experience</td>
			<td align="center">Top 10 Players in Kills</td>
		</tr>
		<tr>
			<td class="center" style="border:none"><?php
				if(isset($ExperienceRankings))
				{ ?>
					<table class="nobord">
						<tr>
							<th align="center">Rank</th>
							<th align="center">Player</th>
							<th align="center">Experience</th>
						</tr><?php
						foreach($ExperienceRankings as $Rank => &$RankedPlayer)
						{ ?>
							<tr>
								<td align="center"><?php echo $Rank; ?></td>
								<td align="center"><?php echo $RankedPlayer->getPlayerName(); ?></td>
								<td align="center"><?php echo number_format($RankedPlayer->getExperience()); ?></td>
							</tr><?php
						} unset($RankedPlayer); ?>
					</table><?php
				} ?>
			</td>
			<td align="center"><?php
				if(isset($KillRankings))
				{ ?>
					<table class="nobord">
						<tr>
							<th align="center">Rank</th>
							<th align="center">Player</th>
							<th align="center">Kills</th>
						</tr><?php
						foreach($KillRankings as $Rank => &$RankedPlayer)
						{ ?>
							<tr>
								<td align="center"><?php echo $Rank; ?></td>
								<td align="center"><?php echo $RankedPlayer->getPlayerName(); ?></td>
								<td align="center"><?php echo number_format($RankedPlayer->getKills()); ?></td>
							</tr><?php
						} unset($RankedPlayer); ?>
					</table><?php
				} ?>
			</td>
		</tr>
	</table>

	<h1>Current Players</h1>
	<p>There <?php
	if ($PlayersAccessed != 1)
	{
		?>are <?php echo $PlayersAccessed; ?> players who have<?php
	}
	else
	{
		?>is 1 player who has<?php
	} ?>
	accessed the server in the last 10 minutes.<br /><?php
	
	if (count($CurrentPlayers) == 0)
	{
		?>Noone was moving so your ship computer couldn't intercept any transmissions.<br /><?php
	}
	else
	{
		if (count($CurrentPlayers) == $PlayersAccessed)
		{
			?>All of them<?php
		}
		else
		{
			?>A few of them<?php
		} ?>
		were moving so your ship computer was able to intercept <?php echo count($CurrentPlayers); ?> transmission<?php if (count($CurrentPlayers) > 1){ ?>s<?php } ?>.<br /><?php
	} ?>

	The traders listed in <span class="italic">italics</span> are still ranked as Newbie or Beginner.</p><?php

	if (count($CurrentPlayers) > 0)
	{ ?>
		<table class="standard" width="95%">
			<tr>
				<th>Player</th>
				<th>Race</th>
				<th>Alliance</th>
				<th>Experience</th>
			</tr><?php
			foreach($CurrentPlayers as &$CurrentPlayer)
			{
				$Style = '';
				if ($CurrentPlayer->getAccount()->isNewbie())
				{
					$Style = 'italic';
				}
				if ($CurrentPlayer->getAccountID() == $ThisPlayer->getAccountID())
				{
					$Style .= ' bold';
				}
				if (!empty($Style))
				{
					$Style = ' class="'.trim($Style).'"';
				}
				?>
				<tr>
					<td valign="top"<?php echo $Style; ?>><?php echo $CurrentPlayer->getLevelName();?> <?php echo $CurrentPlayer->getDisplayName(); ?></td>
					<td align="center"<?php echo $Style; ?>><?php echo $player->getColouredRaceName($CurrentPlayer->getRaceID()); ?></td>
					<td<?php echo $Style; ?>><?php echo $CurrentPlayer->getAllianceName(); ?></td>
					<td align="right"<?php echo $Style; ?>><?php echo number_format($CurrentPlayer->getExperience()); ?></td>
				</tr><?php
			} unset($CurrentPlayer); ?>
		</table><?php
	} ?>
</div>