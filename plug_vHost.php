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

class plug_vHost implements plugin {

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
		$this->ManagevHost($IRCtext);
		$this->PreventsvHost($IRCtext);
	}
	
	/**
	 * Function to manage vHost
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function ManagevHost ($IRCtxt)
	{
		/**
		 * Depend count for two hours
		 */
		
		if(file_exists(dirname(__FILE__) . '/data/.announcevHost'))
		{
			if(time() - filemtime(dirname(__FILE__) . '/data/.announcevHost') > ( 3600 * 2 ))
			{
				unlink(dirname(__FILE__) . '/data/.announcevHost');
			}
		} else
		{
			$fp = fopen(dirname(__FILE__) . '/data/.announcevHost', "w");
			fputs($fp, time());
			fclose($fp);
			
			$vHost['TimeOut'] = true;
		}
		
		if($vHost['TimeOut'] === true)
		{
			$dir = opendir(dirname(__FILE__) . '/data/vhost/');
			$i = 0;
			
			while ($fread = readdir($dir))
			{
				if($fread != "." && $fread != "..")
				{
					$i++;
				}
			}
			
			if($i != 0)
			{
				$this->main->MyConn->put("PRIVMSG #IRCOPS --- vHost --- : il y a actuellement ".$i." vHosts en attente de traitement (tapez !list pour voir les vHosts en attente).");
			}
		}
		
		/**
		 * List and vHost Wait List
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!list)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if(strtolower($T[3]) == "#ircops")
			{
				$dir = opendir(dirname(__FILE__) . '/data/vhost/');
				$i = 0;
				
				while ($fread = readdir($dir))
				{
					if($fread != "." && $fread != "..")
					{
						$vHostListWait[] = $fread;
						$i++;
					}
				}
				
				if($i == 0)
				{
					$this->main->MyConn->put("PRIVMSG #IrcOps Il n'y actuellement aucune vHosts en attente de validation.");
				} else
				{
					$this->main->MyConn->put("PRIVMSG #IRCOPS =================== vHosts en attente ===================");
					
					foreach ($vHostListWait as $IDvHost)
					{
						$__OPENFILE__ = file(dirname(__FILE__) . '/data/vhost/' . $IDvHost);
						
						if(file_exists(dirname(__FILE__) . '/data/vhost/' . $IDvHost))
						{
							$this->main->MyConn->put("PRIVMSG #IRCOPS [ID : ".trim($IDvHost)."] [NickName: ".trim($__OPENFILE__[0])."] [vHost: ".trim($__OPENFILE__[1])."] : Accepter : !on ".trim($IDvHost)." / Refuser : !off ".trim($IDvHost)."");
						} else
						{
							$this->main->MyConn->put("PRIVMSG #IRCOPS Une erreur s'est produite : file not found.");
						}
					}
					
					$this->main->MyConn->put("PRIVMSG #IRCOPS =========================================================");
					$this->main->MyConn->put("PRIVMSG #IRCOPS Il y a ".$i." vHosts en attente de validation.");
					$this->main->MyConn->put("PRIVMSG #IRCOPS =========================================================");
				}
			}
		}
		
		/**
		 * 
		 * Refuse an vHost
		 * 
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!off) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if(strtolower($T[3]) == "#ircops")
			{
				$__ID__ = trim($T[6]);
				
				if(!file_exists(dirname(__FILE__) . '/data/vhost/' . $__ID__) || empty($__ID__))
				{
					$this->main->MyConn->put("PRIVMSG #IRCOPS L'ID n'existe pas ou plus dans la base de données.");
				} else
				{
					$__OPEN__FILE__ = file(dirname(__FILE__) . '/data/vhost/' . $__ID__);
					
					$this->main->MyConn->put("PRIVMSG #IRCOPS La vHost '".trim($__OPEN__FILE__[1])."' pour le NickName '".trim($__OPEN__FILE__[0])."' a bien été refusée.");
					
					/**
					 * Del
					 */
					
					$this->main->MyConn->put("PRIVMSG MemoServ SEND ".trim($__OPEN__FILE__[0])." Votre demande de vHost '".trim($__OPEN__FILE__[1])."' pour votre NickName a été refusée par un IRCOps (".$T[1]."). Si vous souhaitez obtenir plus d'informations concernant ce refus, merci de bien vouloir contacter l'IRCOps qui a effectué cette action");
					
					unlink(dirname(__FILE__) . '/data/vhost/' . $__ID__);
				}
			}
		}
		
		/**
		 * Agree an vHost
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!on) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if(strtolower($T[3]) == "#ircops")
			{
				$__ID__ = trim($T[6]);
				
				if(!file_exists(dirname(__FILE__) . '/data/vhost/' . $__ID__) || empty($__ID__))
				{
					$this->main->MyConn->put("PRIVMSG #IRCOPS L'ID n'existe pas ou plus dans la base de données.");
				} else
				{
					$__OPEN__FILE__ = file(dirname(__FILE__) . '/data/vhost/' . $__ID__);
					
					$this->main->MyConn->put("PRIVMSG #IRCOPS La vHost '".trim($__OPEN__FILE__[1])."' pour le NickName '".trim($__OPEN__FILE__[0])."' a bien été acceptée.");
					
					/**
					 * Setup vHost in a NickName
					 */
					
					$this->main->MyConn->put("PRIVMSG HostServ SET ".trim($__OPEN__FILE__[0])." ".trim($__OPEN__FILE__[1])."");
					
					/**
					 * Del
					 */
					
					$this->main->MyConn->put("PRIVMSG MemoServ SEND ".trim($__OPEN__FILE__[0])." Votre demande de vHost '".trim($__OPEN__FILE__[1])."' pour votre NickName a été accetpée par un IRCOps (".$T[1]."). Pour activer votre vHost, merci de bien vouloir taper '/MSG HostServ ON' ou de vous reconnecter au serveur.");
					unlink(dirname(__FILE__) . '/data/vhost/' . $__ID__);
				}
			}
		}
	}
	
	/**
	 * Command type !vhost
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function PreventsvHost ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*) :(.*?)(!hs|!host|!vhost) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			$T[6] = trim($T[6]);
			
			if($this->main->MyConn->GlobalAntiFlood(4, 300, "vhost." . $T[2]) != true)
			{
				if(file_exists(dirname(__FILE__) . '/data/.LockVHost'))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." La commande !vhost a été temporairement désactivée par un IRCOps.");
				} else
				{
					$__OPENURL__NICKR__ = $this->main->MyConn->OpenURL('http://stats.quitenet.org/?m=u&p=ustats&type=3&user=' . $T[1]);
					
					if($__OPENURL__NICKR__ === FALSE)
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." La commande !vhost rencontre actuellement un problème technique. Nous ne pouvons pas traiter votre demande, merci de réessayer dans quelques instants...");
					} else
					{
						if(eregi('aucune donnée statistique collectée', $__OPENURL__NICKR__))
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Pour pouvoir demander une Vhost, vous devez être enregistré auprès de nos services (NickServ).");
						} else
						{					
							if(!preg_match("/\.\b/i", $T[6]) || !eregi("^[0-9_a-z\+.\+-]*$", $T[6]))
							{
								$this->main->MyConn->put("NOTICE ".$T[1]." Le vHost indiquée est invalide : une vhost peut seulement contenir les caratères A-Z, a-z, 0-9, '.' et '-'. (exemple de vHost : !vhost la.vhost.souhaitee).");
							} else
							{
								if(preg_match("/\service\b/i", $T[6]) || preg_match("/\ircop\b/i", $T[6]) || preg_match("/\netadmin\b/i", $T[6]) || preg_match("/\quitenet\b/i", $T[6]) || preg_match("/\quitenet\b/i", $T[6]) || preg_match("/\coadmin\b/i", $T[6]) || preg_match("/\fuck\b/i", $T[6]))
								{
									$this->main->MyConn->put("NOTICE ".$T[1]." Le vHost indiquée est invalide : elle comporte un mot interdit. (exemple de vHost : !vhost la.vhost.souhaitee).");
								} else
								{
									$dir = opendir(dirname(__FILE__) . '/data/vhost/');
									
									while ($fread = readdir($dir))
									{
										if($fread != "." && $fread != "..")
										{
											$__OPENFILE__ = file(dirname(__FILE__) . '/data/vhost/' . $fread);
											
											if(trim($__OPENFILE__[0]) == $T[1])
											{
												$vHost['CreateAuth'] = true;
											}
										}
									}
									
									if($vHost['CreateAuth'] != true)
									{
										$__ID__N__ = rand(1, 999);
										
										$this->main->MyConn->put("PRIVMSG #IRCOPS --- vHost --- : un usager demande la vHost '".$T[6]."' pour son NickName '".$T[1]."' (demandé sur le channel '".$T[3]."') (HL System : ".$this->main->MyConn->HLStatement().").");
										$this->main->MyConn->put("PRIVMSG #IRCOPS --- vHost --- : Accepter : !on ".trim($__ID__N__)." / Refuser : !off ".trim($__ID__N__)."");
										
										$this->main->MyConn->put("NOTICE ".$T[1]." Votre demande a été placé en file d'attente et sera traité dans les plus brefs delais. Une fois la demande accepté il vous suffira de taper '/MSG HostServ ON' pour activer la vHost.");
										$this->main->MyConn->put("NOTICE ".$T[1]." Votre numéro de suivi pour cette demande est le : '".$__ID__N__."'. Merci.");
												
										$__DATA__IN__FILE = $T[1] . "\n" . $T[6] . "\n" . time();
												
										$fp = fopen(dirname(__FILE__) . '/data/vhost/' . $__ID__N__, "w");
										fputs($fp, $__DATA__IN__FILE);
										fclose($fp);
									} else
									{
										$this->main->MyConn->put("NOTICE ".$T[1]." Une demande de vHost est en cours de traitement sur votre NickName. Merci de bien vouloir attendre la validation de votre vHost par un IRCOps.");
									}
								}
							}
						}
					}
				}
			}
		}
	}
}

?>
