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

class plug_Flood implements plugin {

	private $main;

	public function __construct($main) {
		$this->main = $main;
	}

	/**
	 * Déclaration des fonctions
	 *
	 * @param unknown_type $IRCtext
	 */
	
	public function start($IRCtext)
	{
		$this->AntiFlood__CTCP__PRIVMSG($IRCtext);
		$this->AntiFlood__NOTICE($IRCtext);
		$this->AutoTimeOutIgnore($IRCtext);
		$this->AntiFlood__URL($IRCtext);
		$this->AntiSpam__URL($IRCtext);
	}
	
	/**
	 * Function Antiflood for CTCP/PRIVMSG
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function AntiFlood__CTCP__PRIVMSG ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.preg_quote(IRCConn::$myBotName, '`').' :(.*?)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if($this->main->MyConn->GlobalAntiFlood(3, 5, "privmsg.ctcp." . $T[2]) === true && !eregi("services@services.quitenet.org", $T[2]))
			{				
				$fp = fopen(dirname(__FILE__) . '/data/ignore/' . $T[2], "w");
				fputs($fp, "AUTOIGNORE");
				fclose($fp);
				
				$this->main->MyConn->put("PRIVMSG #IRCOPS FLOOD PRIVMSG/CTCP -> ".trim($T[2])." added in ignore list on ". IRCConn::$myBotName .".");
			}
		}
	}
	
	/**
	 * Function AntiFlood for Notice
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function AntiFlood__NOTICE ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) NOTICE '.preg_quote(IRCConn::$myBotName, '`').' :(.*?)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if($this->main->MyConn->GlobalAntiFlood(8, 20, "notice." . $T[2]) === true && !eregi("services@services.quitenet.org", $T[2]))
			{				
				$fp = fopen(dirname(__FILE__) . '/data/ignore/' . $T[2], "w");
				fputs($fp, "AUTOIGNORE");
				fclose($fp);

				$this->main->MyConn->put("PRIVMSG #IRCOPS FLOOD NOTICE -> ".trim($T[2])." added in ignore list on ". IRCConn::$myBotName .".");
			}
		}
	}
	
	/**
	 * Remove ignore timeout
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function AutoTimeOutIgnore ($IRCtext)
	{
		/**
		 * TIME OUT
		 */
		
		if(file_exists(dirname(__FILE__) . '/data/.ignore'))
		{
			if(time() - filemtime(dirname(__FILE__) . '/data/.ignore') > ( 60 * 5 ))
			{
				unlink(dirname(__FILE__) . '/data/.ignore');
			}
		} else
		{
			$fp = fopen(dirname(__FILE__) . '/data/.ignore', "w");
			fputs($fp, time());
			fclose($fp);
			
			$Ignore['TimeOut'] = TRUE;
		}
		
		/**
		 * Executing command
		 */
		
		if($Ignore['TimeOut'] === TRUE)
		{
			$dir = opendir(dirname(__FILE__) . '/data/ignore/');
			
			while ($fread = readdir($dir))
			{
				if($fread != "." && $fread != "..")
				{
					$__OPEN__FILE__ = file(dirname(__FILE__) . '/data/ignore/' . $fread);
					$__INFO__IGNORE__ = trim($__OPEN__FILE__[0]);
					
					if($__INFO__IGNORE__ == "AUTOIGNORE")
					{						
						if(filemtime(dirname(__FILE__) . '/data/ignore/' . $fread) + ( 60 * 10 ) < time())
						{
							unlink(dirname(__FILE__) . '/data/ignore/' . $fread);
						}
					}
				}
			}
		}
	}
	
	/**
	 * Detect an flood advert in all channel
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function AntiFlood__URL ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])`', $IRCtxt, $T))
		{			
			if($this->main->MyConn->AntiFloodSpecial(5, 1, "advert.flood." . $T[2]) === TRUE)
			{
				$CreateAbuseReport = $this->main->MyConn->CreateAbuseRepport('Multiflood advertising channels detected', $T[1], $T[3], $T[2], "KILL");
				
				$this->main->MyConn->put("KILL ".$T[1]." You are violating network rules, ID: " . $CreateAbuseReport . " (please read /RULES for more information).");
				
				$this->main->MyConn->put("PRIVMSG #IRCOPS Flood -> Multiflood advertising channels detected -> ".trim($T[2])." (channel: ".$T[3].", abuse ID: " . $CreateAbuseReport . ").");
			}
		}
	}
	
	/**
	 * Anti-Spam Detector (for botnet)
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function AntiSpam__URL ($IRCtxt)
	{	
		/**
		 * Detect spam
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])`', $IRCtxt, $T))
		{
			if(time() - filemtime(dirname(__FILE__) . '/data/antiflood.join/' . $T[2] . $T[3]) < ( 15 ))
			{
				$CreateAbuseReport = $this->main->MyConn->CreateAbuseRepport('To send web addresses, you must wait a few seconds. Otherwise you will be identified as a spammer.', $T[1], $T[3], $T[2], "KILL");
				
				$this->main->MyConn->put("KILL ".$T[1]." You are violating network rules, ID: " . $CreateAbuseReport . " (please read /RULES for more information).");
				$this->main->MyConn->put("PRIVMSG #IRCOPS Spam -> Spam detected -> ".trim($T[2])." (channel: ".$T[3].", abuse ID: " . $CreateAbuseReport . ").");
			}
		}
		
		/**
		 * Join
		 */
	
		if(preg_match('`^:(.*?)!(.*?) JOIN :(.*)`', $IRCtxt, $T))
		{
			$T[3] = trim($T[3]);
			
			if(!file_exists(dirname(__FILE__) . '/data/antiflood.join/') || !is_readable(dirname(__FILE__) . '/data/antiflood.join/'))
			{
				$this->main->MyConn->put("PRIVMSG #IRCOPS Une erreur s'est produite : le dossier '".dirname(__FILE__) . '/data/antiflood.join/'."' n'existe pas ou n'est pas accessible en lecture/écriture.");
			} else
			{
				if(file_exists(dirname(__FILE__) . '/data/antiflood.join/' . $T[2] . $T[3])  && time() - filemtime(dirname(__FILE__) . '/data/antiflood.join/' . $T[2] . $T[3]) > ( 30 ))
				{
					unlink(dirname(__FILE__) . '/data/antiflood.join/' . $T[2] . $T[3]);
				}
				
				$fp = fopen(dirname(__FILE__) . '/data/antiflood.join/' . $T[2] . $T[3], "w");
				fputs($fp, time());
				fclose($fp);
			}
		}
		
		/**
		 * Timeout
		 */
		
		$dir = opendir(dirname(__FILE__) . '/data/antiflood.join/');
		
		while ($fread = readdir($dir))
		{
			if($fread != "." && $fread != "..")
			{
				if(filemtime(dirname(__FILE__) . '/data/antiflood.join/' . $fread) + ( 30 ) < time())
				{
					unlink(dirname(__FILE__) . '/data/antiflood.join/' . $fread);
				}
			}
		}
	}
}

?>
