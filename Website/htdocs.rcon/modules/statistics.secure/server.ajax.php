<?php
if ($_SERVER['PHP_SELF'] != '/index.php') {
	header("Location: /index.php");
}

if (isset($_SESSION['{$adb->prefix}user']))
{
	$server = $_SESSION['server-id'];
	$user = $_SESSION['{$adb->prefix}user'];
	
	if (!$user->HasAccess($server, MODE_STATISTICS))
		die();
	
	$start_url = "javascript:ShowStatistics('";
	$end_url = "');";
	
	$title = '<b>Player Statistics</b>';
	$order = 'player_stats_max_score';
	
	if (isset($_REQUEST['order']))
		$order = $_REQUEST['order'];
	
	if (isset($_REQUEST['letter']))
	{
		$letter = $_REQUEST['letter'];
		$title .= " starting with <b>{$letter}</b>";
		
		$end_url = "', '{$letter}');";
	
		if ($letter == '#')
			$query = "SELECT *, (player_stats_kills / player_stats_deaths) AS player_stats_kdr FROM {$adb->prefix}player_stats INNER JOIN {$adb->prefix}players ON {$adb->prefix}player_stats.player_id = {$adb->prefix}players.player_id WHERE (player_name LIKE '0%' OR player_name LIKE '1%' OR player_name LIKE '2%' OR player_name LIKE '3%' OR player_name LIKE '4%' OR player_name LIKE '5%' OR player_name LIKE '6%' OR player_name LIKE '7%' OR player_name LIKE '8%' OR player_name LIKE '9%') AND server_id = {$server} ORDER BY {$order} DESC, player_name";
		else
			$query = "SELECT *, (player_stats_kills / player_stats_deaths) AS player_stats_kdr FROM {$adb->prefix}player_stats INNER JOIN {$adb->prefix}players ON {$adb->prefix}player_stats.player_id = {$adb->prefix}players.player_id WHERE player_name LIKE '{$letter}%' AND server_id = {$server} ORDER BY {$order} DESC, player_name";
	}
	else
	{
		$title = "<b>Top 20</b>";
		$query = "SELECT *, (player_stats_kills / player_stats_deaths) AS player_stats_kdr FROM {$adb->prefix}player_stats INNER JOIN {$adb->prefix}players ON {$adb->prefix}player_stats.player_id = {$adb->prefix}players.player_id WHERE server_id = {$server} ORDER BY {$order} DESC, player_name LIMIT 20";
	}
	
	$title .= ' ordered by <b>' . ucwords(str_replace('_', ' ', substr($order, 13))) . '</b>';
	$result = $adb->query($query, false);

	if (!empty($result))
	{
		$total_players = $adb->num_rows($result);

		if ($total_players > 0)
		{
			echo "<table class=\"standard\" style=\"width: 820px\">\n";
			echo "<thead>\n";
			echo "<tr><th colspan=\"10\" class=\"border-top left center right\"><b>{$title}</b></th></tr>\n";
			echo "<tr>\n";
			echo "<th width=\"113\" class=\"left\">Player Name</th>\n";
			echo "<th width=\"78\" class=\"center\"><a href=\"{$start_url}player_stats_max_score{$end_url}\" style=\"color: white;\">Max Score</a></th>\n";
			echo "<th width=\"78\" class=\"center\"><a href=\"{$start_url}player_stats_kills{$end_url}\" style=\"color: white;\">Kills</a></th>\n";
			echo "<th width=\"78\" class=\"center\"><a href=\"{$start_url}player_stats_kdr{$end_url}\" style=\"color: white;\">KDR</a></th>\n";
			echo "<th width=\"78\" class=\"center\"><a href=\"{$start_url}player_stats_assists{$end_url}\" style=\"color: white;\">Assists</a></th>\n";
			echo "<th width=\"78\" class=\"center\"><a href=\"{$start_url}player_stats_captures{$end_url}\" style=\"color: white;\">Captures</a></th>\n";
			echo "<th width=\"78\" class=\"center\"><a href=\"{$start_url}player_stats_returns{$end_url}\" style=\"color: white;\">Returns</a></th>\n";
			echo "<th width=\"78\" class=\"center\"><a href=\"{$start_url}player_stats_deaths{$end_url}\" style=\"color: white;\">Deaths</a></th>\n";
			echo "<th width=\"78\" class=\"center\"><a href=\"{$start_url}player_stats_suicides{$end_url}\" style=\"color: white;\">Suicides</a></th>\n";
			echo "<th width=\"78\" class=\"center right\"><a href=\"{$start_url}player_stats_carrier_kills{$end_url}\" style=\"color: white;\">Carrier Kills</a></th>\n";
			echo "</tr>\n";
			echo "</thead>\n";
			
			echo "<tfoot>\n";
			echo "<tr><td colspan=\"10\" class=\"border-bottom left right\"></td></tr>\n";
			echo "</tfoot>\n";
			
			for ($count = 0; $count < $total_players; $count++)
			{
				$player_name = $adb->query_result($result, $count, 'player_name');
				$player_stats_max_score = $adb->query_result($result, $count, 'player_stats_max_score');
				$player_stats_kills = $adb->query_result($result, $count, 'player_stats_kills');
				$player_stats_kdr = sprintf('%0.2f', $adb->query_result($result, $count, 'player_stats_kdr'));
				$player_stats_assists = $adb->query_result($result, $count, 'player_stats_assists');
				$player_stats_captures = $adb->query_result($result, $count, 'player_stats_captures');
				$player_stats_returns = $adb->query_result($result, $count, 'player_stats_returns');
				$player_stats_deaths = $adb->query_result($result, $count, 'player_stats_deaths');
				$player_stats_suicides = $adb->query_result($result, $count, 'player_stats_suicides');
				$player_stats_carrier_kills = $adb->query_result($result, $count, 'player_stats_carrier_kills');
	
				echo "<tr><td style=\"text-align: left\" class=\"left\">{$player_name}</td>";
				echo "<td style=\"text-align: center\">{$player_stats_max_score}</td>";
				echo "<td style=\"text-align: center\">{$player_stats_kills}</td>";
				echo "<td style=\"text-align: center\">{$player_stats_kdr}</td>";
				echo "<td style=\"text-align: center\">{$player_stats_assists}</td>";
				echo "<td style=\"text-align: center\">{$player_stats_captures}</td>";
				echo "<td style=\"text-align: center\">{$player_stats_returns}</td>";
				echo "<td style=\"text-align: center\">{$player_stats_deaths}</td>";
				echo "<td style=\"text-align: center\">{$player_stats_suicides}</td>";
				echo "<td style=\"text-align: center\" class=\"right\" >{$player_stats_carrier_kills}</td></tr>\n";
			}	
			
			echo "</tbody>\n</table>\n";
		}
		else
		{
			if ($letter == '#')
				echo "There are no statistics for players starting with a numeric";
			else
				echo "There are no statistics for players starting with '{$letter}'";
		}
	}
	else
	{
		if ($letter == '#')
			echo "There are no statistics for players starting with a numeric";
		else
			echo "There are no statistics for players starting with '{$letter}'";
	}
}
else
{
	echo "Your session is invalid\n";
}
?>
