#!/usr/bin/env php
<?php
	chdir('/home/ethos/');
	$hour = date('H', time());
	$COINS = FALSE;
	$COINS['BCN'] = array('hash_rate' => 2000.0, 'config' => 'minergate-bcn.conf'); // CryptoNight
	$COINS['HUSH'] = array('hash_rate' => 1720.0, 'config' => 'suprnova-hush.conf'); // Equihash
	//$COINS['HUSH'] = array('hash_rate' => 1720.0, 'config' => 'miningspeed-hush.conf'); // Equihash
	$COINS['ETH'] = array('hash_rate' => 104.0, 'config' => 'nanopool-eth.conf'); // Ethash
	// $COINS['MUSIC'] = array('hash_rate' => 104.0, 'config' => 'nomnom-music.conf'); // Ethash // Switch to exchange!! Too annoying syncing the wallet.
	$COINS['ORB'] = array('hash_rate' => 4200.0, 'config' => 'theblocksfactory-orb.conf'); // Neoscrypt
	// $COINS['SC'] = array('hash_rate' => 6400.0, 'config' => 'siamining-sia.conf'); // Blake (2b) // Switch to exchange!!
	$COINS['LBC'] = array('hash_rate' => 1080.0, 'config' => 'suprnova-lbc.conf'); // LBRY
	//$COINS['SIGT'] = array('hash_rate' => 106.0, 'config' => 'suprnova-sigt.conf'); // Skunk // Closed -> POW ended
	$COINS['ZEC'] = array('hash_rate' => 1720.0, 'config' => 'suprnova-zec.conf'); // Equihash
	$COINS['ZCL'] = array('hash_rate' => 1720.0, 'config' => 'suprnova-zcl.conf'); // Equihash
	$COINS['VTC'] = array('hash_rate' => 142000.0, 'config' => 'alwayshashing-vtc.conf'); // Equihash
	$COINS['XMR'] = array('hash_rate' => 2000.0, 'config' => 'nanopool-xmr.conf'); // CryptoNight
	$COINS['DGB'] = array('hash_rate' => 142.0, 'config' => 'suprnova-dgb.conf'); // Myriad-Groestl
	$COINS['DCR'] = array('hash_rate' => 10000.0, 'config' => 'suprnova-dcr.conf'); // Decred
	$COINS['PXC'] = array('hash_rate' => 4200.0, 'config' => 'theblocksfactory-pxc.conf'); // Neoscrypt
	$COINS['SIB'] = array('hash_rate' => 46.0, 'config' => 'suprnova-sib.conf'); // Neoscrypt
	$COINS['KMD'] = array('hash_rate' => 46.0, 'config' => 'suprnova-kmd.conf'); // Equihash
	$COINS['ZEN'] = array('hash_rate' => 46.0, 'config' => 'suprnova-zen.conf'); // Equihash
	$COINS['FTC'] = array('hash_rate' => 4200.0, 'config' => 'miningpoolhub-ftc.conf'); // Neoscrypt
	$COINS['UBIQ'] = array('hash_rate' => 104.0, 'config' => 'ubiqpool-ubiq.conf'); // Ethash
	$COINS['MONA'] = array('hash_rate' => 104.0, 'config' => 'suprnova-mona.conf'); // Lyra2REv2
	//$COINS['SOIL'] = array('hash_rate' => 104.0, 'config' => 'miners-zone-soil.conf'); // Ethash // Where's the wallet??
	//$COINS['SUMO'] = array('hash_rate' => 2000.0, 'config' => 'sumokoin-sumo.conf'); // CryptoNight // Switch to exchange!!
	$COINS['VTC'] = array('hash_rate' => 104.0, 'config' => 'miningpoolhub-vtc.conf'); // Lyra2REv2
	//$COINS['AEON'] = array('hash_rate' => 104.0, 'config' => 'sumominer-aeon.conf'); // Lyra2REv2 // Switch to exchange!!

	$SMALL_COLLECT = array('ZEN'); // 'PXC', 'ZCL', 'ZEN', 'SIB'

	if($hour >= 22 || $hour < 10)
	{
		// Sleeping.. Switch to ETH.
		if(!file_exists("in_active.conf"))
		{
			copy("local.conf", "in_active.conf");
			copy("configs/nanopool-eth.conf", "local.conf");
			sleep(5);
			shell_exec('/opt/ethos/bin/minestop');
			sleep(5);
			$output = shell_exec('/opt/ethos/bin/restart-proxy 2>&1');
			file_put_contents('scripts/log', date('m/d/Y H:i:s')." - Switching to sleep: $output.\r\n", FILE_APPEND | LOCK_EX);
		}
	}
	else if($hour >= 12 && $hour < 18)
	{
		if(!file_exists('scripts/no-autoswitch'))
		{
			$current_coin = file_get_contents('scripts/current_coin.txt');
			$new_coin = $SMALL_COLLECT[array_rand($SMALL_COLLECT)];
			if($new_coin == $current_coin)
				$new_coin = $SMALL_COLLECT[array_rand($SMALL_COLLECT)];
			if($new_coin == $current_coin)
				return;

			// Switch coin
			file_put_contents('scripts/current_coin.txt', $new_coin, LOCK_EX);
			$config_file = $COINS[$new_coin]['config'];
			copy("configs/".$config_file, "local.conf");
			sleep(5);
			shell_exec('/opt/ethos/bin/minestop');
			sleep(5);
			$output = shell_exec('/opt/ethos/bin/restart-proxy 2>&1');
			file_put_contents('scripts/log', date('m/d/Y H:i:s')." - Switching to $new_coin (Dust Collect): $output.\r\n", FILE_APPEND | LOCK_EX);
		}
	}
	else
	{
		// Switch back from sleep..
		if(file_exists("in_active.conf"))
		{
			copy("in_active.conf", "local.conf");
			unlink("in_active.conf");
			sleep(5);
			shell_exec('/opt/ethos/bin/minestop');
			sleep(5);
			$output = shell_exec('/opt/ethos/bin/restart-proxy 2>&1');
			file_put_contents('scripts/log', date('m/d/Y H:i:s')." - Switching back from sleep: $output.\r\n", FILE_APPEND | LOCK_EX);
		}
		else if(!file_exists('scripts/no-autoswitch'))
		{
			$json_coins = file_get_contents('http://whattomine.com/coins.json?adapt_q_280x=0&adapt_q_380=0&adapt_q_fury=0&adapt_q_470=0&adapt_q_480=0&adapt_q_570=0&adapt_q_580=0&adapt_q_750Ti=0&adapt_q_10606=0&adapt_q_1070=6&adapt_1070=true&adapt_q_1080=0&adapt_q_1080Ti=0&eth=true&factor%5Beth_hr%5D=180.0&factor%5Beth_p%5D=720.0&grof=true&factor%5Bgro_hr%5D=213.0&factor%5Bgro_p%5D=780.0&x11gf=true&factor%5Bx11g_hr%5D=69.0&factor%5Bx11g_p%5D=720.0&cn=true&factor%5Bcn_hr%5D=3000.0&factor%5Bcn_p%5D=600.0&eq=true&factor%5Beq_hr%5D=2580.0&factor%5Beq_p%5D=720.0&lre=true&factor%5Blrev2_hr%5D=14700.0&factor%5Blrev2_p%5D=390.0&ns=true&factor%5Bns_hr%5D=1950.0&factor%5Bns_p%5D=450.0&lbry=true&factor%5Blbry_hr%5D=315.0&factor%5Blbry_p%5D=525.0&bk2bf=true&factor%5Bbk2b_hr%5D=3450.0&factor%5Bbk2b_p%5D=630.0&bk14=true&factor%5Bbk14_hr%5D=5910.0&factor%5Bbk14_p%5D=570.0&pas=true&factor%5Bpas_hr%5D=2100.0&factor%5Bpas_p%5D=405.0&skh=true&factor%5Bskh_hr%5D=54.0&factor%5Bskh_p%5D=345.0&factor%5Bl2z_hr%5D=420.0&factor%5Bl2z_p%5D=300.0&factor%5Bcost%5D=0.1&sort=Revenue&volume=0&revenue=current&factor%5Bexchanges%5D%5B%5D=&factor%5Bexchanges%5D%5B%5D=bittrex&factor%5Bexchanges%5D%5B%5D=bleutrade&factor%5Bexchanges%5D%5B%5D=bter&factor%5Bexchanges%5D%5B%5D=c_cex&factor%5Bexchanges%5D%5B%5D=cryptopia&factor%5Bexchanges%5D%5B%5D=hitbtc&factor%5Bexchanges%5D%5B%5D=poloniex&factor%5Bexchanges%5D%5B%5D=yobit&dataset=Main&commit=Calculate');
			$data_coins = json_decode($json_coins, true);
			$profits = FALSE;

			if(isset($data_coins['coins']) && count($data_coins['coins']) > 0)
			{
				foreach($data_coins['coins'] as $label => $coin)
				{
					if(!isset($COINS[$coin['tag']]))
						continue; // Skip unsupported coins.
					if($coin['lagging'])
						continue; // Skip lagging coins.

					$tag = $coin['tag'];
					$hash_rate = $COINS[$tag]['hash_rate'];
					$coin_id = $coin['id'];

					$profits[$tag] = floatval($coin['profitability']);

					/// BEFORE ---
					//$json_coin = file_get_contents("http://whattomine.com/coins/$coin_id.json?hr=$hash_rate&p=0&fee=0.0&cost=0&hcost=0.0");
					//$data_coin = json_decode($json_coin, true);
					//$profits[$tag] = floatval($data_coin['btc_revenue']); // str_replace('$', '', $data_coin['revenue'])
				}
			}
			// Output list
			var_dump($profits);

			if($profits && count($profits) > 0)
			{
				// Sort by profit (reverse)
				uasort($profits, 'float_rsort');
				$new_coin = key($profits);
				$new_profit = current($profits);

				// Get current active coin
				$current_coin = file_get_contents('scripts/current_coin.txt');
				if($new_coin == $current_coin)
					return;

				// Switch coin
				file_put_contents('scripts/current_coin.txt', $new_coin, LOCK_EX);
				$config_file = $COINS[$new_coin]['config'];
				copy("configs/".$config_file, "local.conf");
				sleep(5);
				shell_exec('/opt/ethos/bin/minestop');
				sleep(5);
				$output = shell_exec('/opt/ethos/bin/restart-proxy 2>&1');
				file_put_contents('scripts/log', date('m/d/Y H:i:s')." - Switching to $new_coin ($new_profit): $output.\r\n", FILE_APPEND | LOCK_EX);
			}
		}
	}

	function float_rsort($a, $b) {
		if ($a == $b) {
			return 0;
		}
		return ($a > $b) ? -1 : 1;
	}

?>
