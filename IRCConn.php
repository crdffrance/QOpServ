<?php

////////////////////////////////////////
// Encodage du fichier : UTF-8
// Utilisation des tabulations : Oui
// 1 tabulation = 4 caractères
// Fins de lignes = LF (Unix)
////////////////////////////////////////

///////////////////////////////
// LICENCE
/////////////////////////////// 
// 
// QOpServ is a PHP program with which you can publish any project
// or sources files of any type supported you want.
//
// International Copyright © 2000 - 2012 CRDF All Rights Reserved.
//
// Contact @ http://www.crdf.fr - clients@crdf.fr
// 
// QOpServ is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// 
// QOpServ is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with QOpServ; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
// 
///////////////////////////////

/**
 * QOpServ - Application PHP Bots PHP
 *
 * @author G. Jocelyn
 * @copyright CRDF France
 * @license GNU GPL (http://www.gnu.org/copyleft/gpl.html)
 * @link http://www.crdf.fr
 * @name QOpServ
 * @since 17/08/2008
 * @version 4.0.1
 */

Class IRCConn {

	// Timeout avant une reconnexion
	public $socketTimeout = 280;

	// Variables privées
	static public $instance = FALSE;
	protected $C;
	static $server;
	static $port;
	static $channel;
	static $myBotName;
	static $ip;
	static $domain;


	private function __construct() {
		$this->connect(self::$server, self::$port);
	}

	static function Init($server, $port, $channel, $myBotName='XboT', $ip='127.0.0.1', $domain='crdf.fr') {
		self::$server = $server;
		self::$port = $port;
		self::$channel = $channel;
		self::$myBotName = preg_replace('`[^_[:alnum:]\`\\\\[\]^-]`', '', $myBotName);
		self::$ip = $ip;
		self::$domain = $domain;
	}

	static function GetInstance() {
		if(!IRCConn::$instance) { 
			IRCConn::$instance = new IRCConn(); 
		}
		return IRCConn::$instance;
	}


	public function __destruct() {
		if(is_resource($this->C)) {
			@fclose($this->C);
		}
	}

	protected function connect() {
		$this->C = @fsockopen(self::$server, self::$port, $errno, $errstr, 30);
		if(!$this->C) {
			die('Impossible de se connecter au server IRC !'."\n");
		}
		// User
		$this->put('USER '.self::$myBotName.' '.self::$myBotName.'@'.self::$ip.' '.self::$domain.' :CRDF, Inc.');
		// Nick
		$this->put('NICK  '.self::$myBotName);
	}

	public function joinChannel($channel) {
		$this->put('JOIN '.$channel);
	}

	public function newNick() {
		self::$myBotName .= '_';
	}

	public function put($command) {
		echo '[-> ' . $command . "\n";
		fputs($this->C, $command . "\n");
	}

	public function get() {
		// Stream Timeout
		stream_set_timeout($this->C, $this->socketTimeout);
		$tmp1 = time();
		$content = fgets($this->C, 1024);
		//echo "<-]$content\n";
		if($content != ''){
			return $content;
		}
		// TIMEOUT
		if(time()-$tmp1 >= $this->socketTimeout) {
			die('TIMEOUT'."\n");
		}
	}
	
	/**
	 * 
	 * Create a repport abuse
	 * 
	 */
	
	public function CreateAbuseRepport ($Int, $UserNick, $UserChannel, $UserHost, $Action)
	{
		if(file_exists(dirname(__FILE__) . '/data/abuse.repport/') || is_readable(dirname(__FILE__) . '/data/abuse.repport/'))
		{
			if(!empty($Int) && !empty($UserNick) && !empty($UserChannel) && !empty($UserHost) && !empty($Action))
			{
				$ReportName = str_replace('-', '9', crc32($Int . $UserNick . $UserHost));
				
				if(!file_exists(dirname(__FILE__) . '/data/abuse.repport/' . $ReportName))
				{
					$GeneratorRepportText .= $ReportName . "\n";
					$GeneratorRepportText .= time() . "\n";
					$GeneratorRepportText .= $UserNick . "\n";
					$GeneratorRepportText .= $UserChannel . "\n";
					$GeneratorRepportText .= $UserHost . "\n";
					$GeneratorRepportText .= $Action . "\n";
					$GeneratorRepportText .= $Int;
					
					$fp = fopen(dirname(__FILE__) . '/data/abuse.repport/' . $ReportName, "w");
					fputs($fp, $GeneratorRepportText);
					fclose($fp);
					
					return $ReportName;
				} else
				{
					return $ReportName;
				}
			}
		}
	}
	
	/**
	 * 
	 * HL Admin or IRCOPS Function
	 * 
	 */
	
	public function HLStatement ()
	{
		$dir = opendir(dirname(__FILE__) . '/data/admin.account');
		$i = 0;
		
		while ($fread = readdir($dir))
		{
			if($fread != "." && $fread != "..")
			{
				$ResultAdmin[] = $fread;
			}
		}
		
		$n =  count($ResultAdmin);
		
		foreach ($ResultAdmin as $NameAdmin)
		{
			if(($n - 1) == $i)
			{
				$Result .= $NameAdmin;
			} else
			{
				$Result .= $NameAdmin . ", ";
			}
		
			$i++;
		}
		
		return $Result;
	}
	
	/**
	 * Antiflood Function
	 *
	 * @param unknown_type $credit
	 * @param unknown_type $timeout
	 * @param unknown_type $data
	 * @return unknown
	 */
	
	public function GlobalAntiFlood ($credit, $timeout, $data)
	{
		$rep = dirname(__FILE__) . '/data/antiflood';
		$f = "$rep/." . $data;
		
		$dir = opendir($rep);
		
		while ($fread = readdir($dir))
		{
			if($fread != "." && $fread != "..")
			{
				if(filemtime("$rep/$fread") + $timeout < time())
				{
					unlink("$rep/$fread");
				}
			}
		}
		
		if(file_exists($f))
		{
			$c_cnfl = file($f);
			$c_cnfl = $c_cnfl[0];
		} else
		{
			$fp = fopen($f, "w");
			fputs($fp, $credit);
			fclose($fp);
			$c_cnfl = $credit;
		}
		
		$c_cnfl--;
		
		$fp = fopen($f, "w");
		fputs($fp, $c_cnfl);
		fclose($fp);
		
		if(file_exists(dirname(__FILE__) . '/data/.DefConActive'))
		{
			return true;
		} else
		{
			if($c_cnfl < 0)
			{ 
				return true;
			}
		}
	}
	
	/**
	 * Anti-Flood for advert, etc... (special functions) uniq seconds
	 *
	 * @param unknown_type $credit
	 * @param unknown_type $timeout
	 * @param unknown_type $data
	 * @return unknown
	 */
	
	public function AntiFloodSpecial ($timeout, $data)
	{
		$rep = dirname(__FILE__) . '/data/antiflood.special';
		$f = "$rep/." . $data;
		
		$dir = opendir($rep);
		
		while ($fread = readdir($dir))
		{
			if($fread != "." && $fread != "..")
			{
				if(filemtime("$rep/$fread") + $timeout < time())
				{
					unlink("$rep/$fread");
				}
			}
		}
		
		if(file_exists($f))
		{
			$c_cnfl = file($f);
			$c_cnfl = $c_cnfl[0];
		} else
		{
			$fp = fopen($f, "w");
			fputs($fp, $credit);
			fclose($fp);
			$c_cnfl = $credit;
		}
		
		$c_cnfl--;
		
		$fp = fopen($f, "w");
		fputs($fp, $c_cnfl);
		fclose($fp);
		
		if(file_exists(dirname(__FILE__) . '/data/.DefConActive'))
		{
			return true;
		} else
		{
			if($c_cnfl < 0)
			{ 
				return true;
			}
		}
	}
	
	/**
	 * Open URL with CURL
	 * 
	 * @return data
	 * @param string
	 */
	
	public function OpenURL ($Url)
	{
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $Url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($curl, CURLOPT_USERAGENT, "QOpServ/3.6 QuiteNet.Org (compatible; MSIE 5.01; Windows NT 5.0)"); 
			
		$output = curl_exec($curl);
		
		curl_close($curl);
		
		return $output;
	}
}

?>