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

class plug_base implements plugin {

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
		$this->PreCommandActive($IRCtext);
		$this->PluginsAdmin($IRCtext);
		$this->Deco($IRCtext);
		$this->Ok($IRCtext);
		$this->Pong($IRCtext);
		$this->kick($IRCtext);
		$this->NickUsed($IRCtext);
		$this->IllegalChNick($IRCtext);
		$this->CTCP($IRCtext);
	}
	
	/**
	 * Active an different precomand
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function PreCommandActive ($IRCtxt)
	{	
		if(preg_match('`^:(NickServ)!.*?@.*? NOTICE (.*)`', $IRCtxt, $T))
		{
			if(eregi("enregistré et protégé", $IRCtxt))
			{
				/**
				 * NickServ
				 */
				sleep(1);
				$this->main->MyConn->put("PRIVMSG NickServ IDENTIFY vTJrhsDb1XJHGZv");
				
				/**
				 * Server
				 */
				sleep(1);
				$this->main->MyConn->put("OPER QOpServ VXLWaOAtfUDSsSHOpdzem0w3gLl1Cw48zajTSVRV");
				
				/**
				 * StatServ
				 */
				sleep(1);
				$this->main->MyConn->put("PRIVMSG StatServ LOGIN QOpServ 4BonR2IH");
				
				/**
				 * MODES
				 */
				$this->main->MyConn->put("MODE " . IRCConn::$myBotName . " +BqHp");
				$this->main->MyConn->put("MODE " . IRCConn::$myBotName . " +s +F");
				
				$this->main->MyConn->put("MODE #IRCOPS +Os");
				$this->main->MyConn->put("AWAY Je suis un robot. Vos messages sont ignorés.");
				
				/**
				 * Channels
				 */
				sleep(1);
				
				$dir = opendir(dirname(__FILE__) . '/data/channels/');
				$i = 0;
				
				while ($fread = readdir($dir))
				{
					if($fread != "." && $fread != "..")
					{
						$this->main->MyConn->put('JOIN ' . $fread);
					}
				}
			}
		}
	}
	
	/**
	 * Administrate different plugins
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function PluginsAdmin ($IRCtxt)
	{
		/**
		 * 
		 * List of plugins available
		 * 
		 */
		
		$Plugs[] = "plug_base : QOpServ Plugins Base (CTCP Answer, etc)";
		$Plugs[] = "plug_IRCOps : QOpServ Plugins IRCOps (command !ircops)";
		$Plugs[] = "plug_vHost : QOpServ Plugins vHost QuiteNet";
		$Plugs[] = "plug_Flood : QOpServ Plugins Anti-Flood";
		$Plugs[] = "plug_Admin : QOpServ Plugins Administration";
		$Plugs[] = "plug_RSSNEWS : Read News RSS in channel";
		$Plugs[] = "plug_Google : QOpServ Plugin Google Search";
		$Plugs[] = "plug_Tools : QOpServ Plugin Various tools";
		$Plugs[] = "plug_Fansubs : QOpServ Plugin Fansubs Tools";
		$Plugs[] = "plug_Help : QOpServ Plugin Help Menu";
		$Plugs[] = "plug_Archiver : QOpServ Plugin Logs Channel";
		
		/**
		 * 
		 * List of Plugins
		 * 
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!plugin|!plugins)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				$this->main->MyConn->put("NOTICE ".$T[1]." =================== QOpServ Plugins ===================");
				
				foreach ($Plugs as $PlugLine)
				{
					$PlugArray = explode(":", $PlugLine);
					$PlugName = trim($PlugArray[0]);
					
					if($this->main->IsPlugLoad($PlugName) === true)
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." " . $PlugLine . " (plugin chargé) ;");
					} else
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." " . $PlugLine . " (plugin non chargé) ;");
					}
					
					unset($PlugArray, $PlugName);
				}
				
				$this->main->MyConn->put("NOTICE ".$T[1]." ========================================================");
				$this->main->MyConn->put("NOTICE ".$T[1]." Il y a actuellement ".count($Plugs)." plugins disponibles.");
				$this->main->MyConn->put("NOTICE ".$T[1]." ========================================================");
				$this->main->MyConn->put("NOTICE ".$T[1]." Charger un plugin : !load <nom_du_plugin> ;");
				$this->main->MyConn->put("NOTICE ".$T[1]." Décharger un plugin : !unload <nom_du_plugin> ;");
				$this->main->MyConn->put("NOTICE ".$T[1]." Recharger un plugin : !rehash <nom_du_plugin> ;");
				$this->main->MyConn->put("NOTICE ".$T[1]." ========================================================");
			}
		}
		
		/**
		 * 
		 * Load an plugin
		 * 
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!load) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				$T[6] = trim($T[6]);
				
				if(!file_exists(dirname(__FILE__) . '/' . $T[6] . '.php') || $T[6] == "plug_base")
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' n'existe pas ou n'est plus disponible.");
				} else
				{
					if(!is_readable(dirname(__FILE__) . '/' . $T[6] . '.php'))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' n'est pas accessible en lecture/écriture.");
					} else
					{
						if($this->main->IsPlugLoad($T[6]) === true)
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' est déjà chargé.");
						} else
						{
							$this->main->AddPlug($T[6]);
							$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' a bien été chargé.");
							
							$this->main->MyConn->put("PRIVMSG #IRCOPS ADMIN -> Load Plugin -> ".trim($T[2]).".");
						}
					}
				}
			}
		}
		
		/**
		 * 
		 * Unload an plugin
		 * 
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!unload) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				$T[6] = trim($T[6]);
				
				if(!file_exists(dirname(__FILE__) . '/' . $T[6] . '.php') || $T[6] == "plug_base")
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' n'existe pas ou n'est plus disponible.");
				} else
				{
					if(!is_readable(dirname(__FILE__) . '/' . $T[6] . '.php'))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' n'est pas accessible en lecture/écriture.");
					} else
					{
						if($this->main->IsPlugLoad($T[6]) === false)
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' n'est pas chargé.");
						} else
						{
							$this->main->UnloadPlug($T[6]);
							$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' a bien été déchargé.");
							
							$this->main->MyConn->put("PRIVMSG #IRCOPS ADMIN -> Unload Plugin -> ".trim($T[2]).".");
						}
					}
				}
			}
		}
		
		/**
		 * 
		 * Rehash an plugin
		 * 
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!rehash) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				$T[6] = trim($T[6]);
				
				if(!file_exists(dirname(__FILE__) . '/' . $T[6] . '.php') || $T[6] == "plug_base")
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' n'existe pas ou n'est plus disponible.");
				} else
				{
					if(!is_readable(dirname(__FILE__) . '/' . $T[6] . '.php'))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' n'est pas accessible en lecture/écriture.");
					} else
					{
						if($this->main->IsPlugLoad($T[6]) === false)
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' doit être chargé pour être rechargé.");
						} else
						{
							$this->main->UnloadPlug($T[6]);
							sleep(1);
							$this->main->AddPlug($T[6]);
							
							$this->main->MyConn->put("NOTICE ".$T[1]." Le plugin '".$T[6]."' a bien été rechargé.");
						}
					}
				}
			}
		}
	}
	
	/**
	 * Detect an logout
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function Deco($IRCtext)
	{
		if(preg_match("`^ERROR :(Closing Link: )?(.*)\r?$`i", $IRCtext))
		{
			@fclose($this->main->MyConn->C);
			sleep(3);
			die('Closing Link'."\n");
		}
	}

	/**
	 * Join an channel
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function Ok($IRCtext)
	{
		if(preg_match("`^:[^ ]+ 001 .*?\r?\n`", $IRCtext))
		{
			$this->main->MyConn->joinChannel(IRCConn::$channel);
		}
	}

	/**
	 * Answer at ping server
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function Pong($IRCtext)
	{
		if(preg_match("`^PING :(.*)\r?\n`", $IRCtext, $T))
		{
			$this->main->MyConn->put("PONG " . $T[1]);
		}
	}

	/**
	 * Detect an kick to auto-rejoin channel
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function kick($IRCtext)
	{
		if(preg_match('`^:(.*?)!(.*?) KICK (.*) '.preg_quote(IRCConn::$myBotName, '`').' :(.*?)`', $IRCtext, $T))
		{
			sleep(1);
			$this->main->MyConn->put("INVITE " . preg_quote(IRCConn::$myBotName, '`') . " " . $T[3]);
			$this->main->MyConn->put("JOIN " . $T[3]);
		}
	}

	/**
	 * Illegals characters in Nickname
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function IllegalChNick ($IRCtext)
	{
		if(preg_match('`^:[^ ]+ 432 (.*?)\r?\n`', $IRCtext))
		{
			IRCConn::$myBotName = "QOpServ";
			$this->main->MyConn->put("NICK :" . IRCConn::$myBotName);
		}
	}

	/**
	 * Nick already in use
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function NickUsed ($IRCtext)
	{
		if(preg_match('`^:[^ ]+ 433 (.*?)\r?\n`', $IRCtext))
		{
			$this->main->MyConn->newNick();
			$this->main->MyConn->put("NICK :" . IRCConn::$myBotName);
		}
	}

	/**
	 * Detect an CTCP
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function CTCP ($IRCtext)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.preg_quote(IRCConn::$myBotName, '`').'(.*?)(VERSION|USERINFO|CLIENTINFO)`', $IRCtext, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			$this->main->MyConn->put("NOTICE ".$T[1]." QOpServ QuiteNet Network IRC v3.6 [www.quitenet.org]. QOpServ powered by CRDF, Inc. Technologies [www.crdf.fr].");
		}
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.preg_quote(IRCConn::$myBotName, '`').'(.*?)PING (.*?)\r?\n`', $IRCtext, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			$this->main->MyConn->put('NOTICE '.$T[1]." PING\1".time()."\1");
		}
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.preg_quote(IRCConn::$myBotName,'`').'(.*?)(TIME)`', $IRCtext, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			$this->main->MyConn->put('NOTICE '.$T[1].' '.date('d-m-Y H:i:s'));
		}
	}
}

?>
