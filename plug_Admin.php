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

class plug_Admin implements plugin {

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
		$this->AdminCMD($IRCtext);
		$this->CommandBot($IRCtext);
	}
	
	/**
	 * Admin commands
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function AdminCMD ($IRCtxt)
	{
		/**
		 * Identification d'un administrateur
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(identify|IDENTIFY) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			$T[4] = trim($T[4]);
			
			if(!file_exists(dirname(__FILE__) . '/data/admin.account/' . $T[1]))
			{
				$this->main->MyConn->put("NOTICE ".$T[1]." Le nom du compte '".$T[1]."' n'est pas trouvé dans la base de données : accès refusé.");
				$this->main->MyConn->put("PRIVMSG #IRCOPS ADMIN ERROR -> Invalid login -> ".trim($T[2]).".");
				
				if($this->main->MyConn->GlobalAntiFlood(3, 120, "login.admin." . $T[2]) === true)
				{
					$fp = fopen(dirname(__FILE__) . '/data/ignore/' . $T[2], "w");
					fputs($fp, "AUTOIGNORE");
					fclose($fp);
					
					$this->main->MyConn->put("PRIVMSG #IRCOPS Cracking/Hack Password -> ".trim($T[2])." added in ignore list on ". IRCConn::$myBotName .".");
				}
			} else
			{
				$FILE__TO__OBTAINPASSWORD = file(dirname(__FILE__) . '/data/admin.account/' . $T[1]);
				$__PASSWORD__ACTUAL__ = trim($FILE__TO__OBTAINPASSWORD[0]);
				
				if($__PASSWORD__ACTUAL__ != sha1($T[4]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Le mot de passe du compte '".$T[1]."' est invalide : accès refusé.");
					$this->main->MyConn->put("PRIVMSG #IRCOPS ADMIN ERROR -> Invalid password -> ".trim($T[2]).".");
					
					if($this->main->MyConn->GlobalAntiFlood(3, 120, "login.admin." . $T[2]) === true)
					{
						$fp = fopen(dirname(__FILE__) . '/data/ignore/' . $T[2], "w");
						fputs($fp, "AUTOIGNORE");
						fclose($fp);
						
						$this->main->MyConn->put("PRIVMSG #IRCOPS Cracking/Hack Password -> ".trim($T[2])." added in ignore list on ". IRCConn::$myBotName .".");
					}
				} else
				{
					if(!file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Identification acceptée. Vous êtes connecté en tant qu'administrateur du robot ".IRCConn::$myBotName." sous l'host '".$T[2]."'. N'oubliez pas de vous déconnecter en tapant '/MSG ".IRCConn::$myBotName." LOGOUT'.");
						
						$fp = fopen(dirname(__FILE__) . '/data/admin.host/' . $T[2], "w");
						fputs($fp, time());
						fclose($fp);
						
						$this->main->MyConn->put("PRIVMSG #IRCOPS ADMIN -> Connect -> ".trim($T[2]).".");
					} else
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Vous êtes déjà connecté sous l'host '".$T[2]."'. Pour vous déconnecter, tapez '/MSG ".IRCConn::$myBotName." LOGOUT'.");
					}
				}
			}
		}
		
		/**
		 * Déconnexion d'un utilisateur
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(logout|LOGOUT)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if(!file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				$this->main->MyConn->put("NOTICE ".$T[1]." Vous n'êtes pas connecté en tant qu'administrateur du robot ".IRCConn::$myBotName.".");
			} else
			{
				unlink(dirname(__FILE__) . '/data/admin.host/' . $T[2]);
				
				$this->main->MyConn->put("NOTICE ".$T[1]." Vous avez été déconnecté avec succès. Vous n'êtes plus administrateur de ".IRCConn::$myBotName.".");
				$this->main->MyConn->put("PRIVMSG #IRCOPS ADMIN -> Logout -> ".trim($T[2]).".");
			}
		}
		
		/**
		 * Modifier son mot de passe
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(modpassword|MODPASSWORD) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			$T[4] = trim($T[4]);
			
			if(!file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				$this->main->MyConn->put("NOTICE ".$T[1]." Pour exécuter cette commande, vous devez être connecté en tant qu'administrateur du robot ".IRCConn::$myBotName.". Pour vous identifier, tapez '/MSG ".IRCConn::$myBotName." IDENTIFY <password>'.");
			} else
			{
				if(!file_exists(dirname(__FILE__) . '/data/admin.account/' . $T[1]) || !is_readable(dirname(__FILE__) . '/data/admin.account/' . $T[1]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Une erreur s'est produite : le fichier de la base de données '".dirname(__FILE__) . '/data/admin.account/' . $T[1]."' n'existe pas ou n'est pas accessible en lecture/écriture.");
				} else
				{
					if(empty($T[4]) || strlen($T[4]) < 6 || !eregi("^[0-9_a-z\+.\+-]*$", $T[4]))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Votre nouveau mot de passe est invalide : il doit se composer de plus de 6 caractères et se composer uniquement de caractères alphanumériques.");
					} else
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Votre mot de passe a bien été modifié.");
						
						$fp = fopen(dirname(__FILE__) . '/data/admin.account/' . $T[1], "w");
						fputs($fp, sha1($T[4]));
						fclose($fp);
					}
				}
			}
		}
	}
	
	/**
	 * Command for ADMINS
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function CommandBot ($IRCtxt)
	{
		preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' (.*)`', $IRCtxt, $T);
		
		if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]) || !is_array($T))
		{
			/**
			 * SAY IN CHANNEL
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(say|SAY) (.*) (.*)`', $IRCtxt, $T))
			{
				$T[4] = trim($T[4]);
				$T[5] = trim($T[5]);
				
				if(empty($T[4]) || empty($T[5]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." SAY <channel> <phrase>'.");
				} else
				{
					$this->main->MyConn->put("PRIVMSG " . $T[4] . " " . $T[5]);
				}
			}
			
			/**
			 * NOTICE USER
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(notice|NOTICE) (.*) (.*)`', $IRCtxt, $T))
			{
				$T[4] = trim($T[4]);
				$T[5] = trim($T[5]);
				
				if(empty($T[4]) || empty($T[5]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." NOTICE <user> <phrase>'.");
				} else
				{
					$this->main->MyConn->put("NOTICE " . $T[4] . " " . $T[5]);
				}
			}
			
			/**
			 * RESTART BOT
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(restart|RESTART)`', $IRCtxt, $T))
			{
				$this->main->MyConn->put("QUIT Bot restarting (".$T[1].")");
			}
			
			/**
			 * KILL FROM SERVER
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(kill|KILL) (.*) (.*)`', $IRCtxt, $T))
			{
				$T[4] = trim($T[4]);
				$T[5] = trim($T[5]);
				
				if(empty($T[4]) || empty($T[5]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." KILL <nick> <reason>'.");
				} else
				{
					$this->main->MyConn->put("KILL " . $T[4] . " " . $T[5]);
				}
			}

			/**
			 * JOIN CHANNEL
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(join|JOIN) (.*)`', $IRCtxt, $T))
			{
				$T[4] = trim($T[4]);
				
				if(empty($T[4]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." JOIN <channel>'.");
				} else
				{
					$this->main->MyConn->put("JOIN " . $T[4]);
				}
			}
			
			/**
			 * PART CHANNEL
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(part|PART) (.*)`', $IRCtxt, $T))
			{
				$T[4] = trim($T[4]);
				
				if(empty($T[4]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." PART <channel>'.");
				} else
				{
					$this->main->MyConn->put("PART " . $T[4]);
				}
			}
			
			/**
			 * AUTOJOIN CHANNEL
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(autojoin|AUTOJOIN) (.*)`', $IRCtxt, $T))
			{
				$T[4] = trim($T[4]);
				
				if(empty($T[4]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." AUTOJOIN <channel>'.");
				} else
				{
					if(file_exists(dirname(__FILE__) . '/data/channels/' . $T[4]))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Le channel '".$T[4]."' a déjà été ajouté dans la liste des auto-join au démarrage du robot.");
					} else
					{
						$fp = fopen(dirname(__FILE__) . '/data/channels/' . $T[4], "w");
						fputs($fp, time());
						fclose($fp);
						
						$this->main->MyConn->put("JOIN " . $T[4]);
						$this->main->MyConn->put("PRIVMSG StatServ CHANSTATS ADD " . $T[4]);
						sleep(1);
						
						$this->main->MyConn->put("NOTICE ".$T[1]." Le channel '".$T[4]."' a été ajouté dans la liste des auto-join au démarrage du robot.");
					}
				}
			}
			
			/**
			 * AUTOPART CHANNEL
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(autopart|AUTOPART) (.*)`', $IRCtxt, $T))
			{
				$T[4] = trim($T[4]);
				
				if(empty($T[4]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." AUTOPART <channel>'.");
				} else
				{
					if(file_exists(dirname(__FILE__) . '/data/channels/' . $T[4]))
					{
						unlink(dirname(__FILE__) . '/data/channels/' . $T[4]);
						
						$this->main->MyConn->put("PART " . $T[4]);
						$this->main->MyConn->put("PRIVMSG StatServ CHANSTATS DEL " . $T[4]);
						sleep(1);
						
						/**
						 * Delete all informations about the channel
						 */
						
						if(file_exists(dirname(__FILE__) . '/data/channels.logs/' . strtolower($T[4])))
						{
							unlink(dirname(__FILE__) . '/data/channels.logs/' . strtolower($T[4]));
							
							$this->main->MyConn->put("NOTICE ".$T[1]." * L'archivage des données de ce channnel a été désactivé.");
						}
						
						if(file_exists(dirname(__FILE__) . '/data/channel.function.disabled/' . strtolower($T[4])))
						{
							unlink(dirname(__FILE__) . '/data/channel.function.disabled/' . strtolower($T[4]));
							
							$this->main->MyConn->put("NOTICE ".$T[1]." * Les principales commandes du robot étaient désactivées. Les données ont été supprimées avec succès.");
						}
						
						if(file_exists(dirname(__FILE__) . '/data/fansubs.event/' . strtolower($T[4])))
						{
							unlink(dirname(__FILE__) . '/data/fansubs.event/' . strtolower($T[4]));
							
							$this->main->MyConn->put("NOTICE ".$T[1]." * Le système Fansub Avancement a été supprimé avec succès pour ce channel.");
						}
						
						/**
						 * Send a message
						 */
						
						$this->main->MyConn->put("NOTICE ".$T[1]." Le channel '".$T[4]."' a été supprimé de la liste des auto-join au démarrage du robot.");
					} else
					{						
						$this->main->MyConn->put("NOTICE ".$T[1]." Le channel '".$T[4]."' n'a pas été ajouté dans la liste des auto-join au démarrage du robot.");
					}
				}
			}
			
			/**
			 * CONSULT ABUSE REPORT
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!abuse) (.*)`', $IRCtxt, $T))
			{
				$T[6] = trim($T[6]);
				
				if(!empty($T[6]))
				{
					if(!file_exists(dirname(__FILE__) . '/data/abuse.repport/' . $T[6]) || !is_readable(dirname(__FILE__) . '/data/abuse.repport/' . $T[6]))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Le rapport numéro ".$T[6]." n'existe pas ou plus dans la base de données.");
					} else
					{
						$__OpenLogRepportAbuse = file(dirname(__FILE__) . '/data/abuse.repport/' . $T[6]);
						
						$ReportID = trim($__OpenLogRepportAbuse[0]);
						$TimeStam = trim($__OpenLogRepportAbuse[1]);
						$NickName = trim($__OpenLogRepportAbuse[2]);
						$Channel_ = trim($__OpenLogRepportAbuse[3]);
						$Hostname = trim($__OpenLogRepportAbuse[4]);
						$Actions_ = trim($__OpenLogRepportAbuse[5]);
						$Reasons_ = trim($__OpenLogRepportAbuse[6]);
						
						$this->main->MyConn->put("NOTICE ".$T[1]." =================== Rapport d'abuse ===================");

						$this->main->MyConn->put("NOTICE ".$T[1]." Date      : le " . strftime("%A %d %B %Y à %T", $TimeStam));
						
						if($NickName != 1 || !empty($NickName))
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." NickName  : " . $NickName);
						}
						if($Hostname == 1 || !empty($Hostname))
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." HostName  : " . $Hostname);
						}
						if($Channel_ == 1 || !empty($Channel_))
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Channel   : " . $Channel_);
						}
						if($Actions_ == 1 || !empty($Actions_))
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Action    : " . $Actions_);
						}
						if($Reasons_ == 1 || !empty($Reasons_))
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Reason    : " . $Reasons_);
						}
						
						$this->main->MyConn->put("NOTICE ".$T[1]." =======================================================");
					}
				} else
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : !abuse <numéro de l'abuse>.");
				}
			}
			
			/**
			 * AUTOCHANLIST
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(AUTOCHANLIST|autochanlist)`', $IRCtxt, $T))
			{
				$dir = opendir(dirname(__FILE__) . '/data/channels/');
				$i = 0;
				
				while ($fread = readdir($dir))
				{
					if($fread != "." && $fread != "..")
					{
						$AutoJoinChannels[] = $fread;
						$i++;
					}
				}
				
				if($i == 0)
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." La liste des channels en auto-join ne contient aucune entrée.");
				} else
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." =================== Liste d'auto-join channel ===================");
					
					foreach ($AutoJoinChannels as $ChannelAuto)
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Channel: " . $ChannelAuto);
					}
					
					$this->main->MyConn->put("NOTICE ".$T[1]." =========================================================");
					$this->main->MyConn->put("NOTICE ".$T[1]." Il y a ".$i." channels en auto-join dans la liste.");
					$this->main->MyConn->put("NOTICE ".$T[1]." =========================================================");
				}
			}
			
			/**
			 * IGNORE
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(IGNORE|ignore) (.*)`', $IRCtxt, $T))
			{
				$T[4] = trim($T[4]);
				
				if(empty($T[4]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." IGNORE <host>'.");
				} else
				{
					if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[4]))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." L'host '".$T[4]."' appartient à un administrateur du robot ".IRCConn::$myBotName.".");
					} else
					{
						if(file_exists(dirname(__FILE__) . '/data/ignore/' . $T[4]))
						{						
							$this->main->MyConn->put("NOTICE ".$T[1]." Le robot ignore déjà l'host '".$T[4]."'.");
						} else
						{
							$fp = fopen(dirname(__FILE__) . '/data/ignore/' . $T[4], "w");
							fputs($fp, time());
							fclose($fp);
							
							$this->main->MyConn->put("NOTICE ".$T[1]." Le robot va maintenant ignorer toutes les actions venant de l'host '".$T[4]."'.");
						}
					}
				}
			}
			
			/**
			 * UNIGNORE
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(UNIGNORE|unignore) (.*)`', $IRCtxt, $T))
			{
				$T[4] = trim($T[4]);
				
				if(empty($T[4]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." UNIGNORE <host>'.");
				} else
				{
					if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[4]))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." L'host '".$T[4]."' appartient à un administrateur du robot ".IRCConn::$myBotName.".");
					} else
					{
						if(file_exists(dirname(__FILE__) . '/data/ignore/' . $T[4]))
						{
							unlink(dirname(__FILE__) . '/data/ignore/' . $T[4]);
							
							$this->main->MyConn->put("NOTICE ".$T[1]." Le robot n'ignore plus toutes les actions venant de l'host '".$T[4]."'.");
						} else
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Le robot n'ignore pas l'host '".$T[4]."'.");
						}
					}
				}
			}
			
			/**
			 * LIST IGNORE
			 */
			
			if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(IGNORELIST|ignorelist)`', $IRCtxt, $T))
			{
				$dir = opendir(dirname(__FILE__) . '/data/ignore/');
				$i = 0;
				
				while ($fread = readdir($dir))
				{
					if($fread != "." && $fread != "..")
					{
						$IgnoreList[] = $fread;
						$i++;
					}
				}
				
				if($i == 0)
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." La liste d'ignorance ne contient aucune entrée.");
				} else
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." =================== Liste d'ignorance ===================");
					
					foreach ($IgnoreList as $IgnoreHost)
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Host: " . $IgnoreHost);
					}
					
					$this->main->MyConn->put("NOTICE ".$T[1]." =========================================================");
					$this->main->MyConn->put("NOTICE ".$T[1]." Il y a ".$i." hosts dans la liste.");
					$this->main->MyConn->put("NOTICE ".$T[1]." =========================================================");
				}
			}
		}
		
		/**
		 * List of Administrators
		 * 
		 * Warning: this element require SuperAdmin account
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(LISTADMIN|listadmin)`', $IRCtxt, $T))
		{
			if(!file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				$this->main->MyConn->put("NOTICE ".$T[1]." Pour exécuter cette commande, vous devez être connecté en tant qu'administrateur du robot ".IRCConn::$myBotName.". Pour vous identifier, tapez '/MSG ".IRCConn::$myBotName." IDENTIFY <password>'.");
			} else
			{
				if(!file_exists(dirname(__FILE__) . '/data/admin.account/') || !is_readable(dirname(__FILE__) . '/data/admin.account/'))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Une erreur s'est produite : le dossier '".dirname(__FILE__) . '/data/admin.account/'." n'existe pas ou n'est pas accessible en lecture/écriture.");
				} else
				{
					$dir = opendir(dirname(__FILE__) . '/data/admin.account/');
					$i = 0;
					
					while ($fread = readdir($dir))
					{
						if($fread != "." && $fread != "..")
						{
							$ListAdmins[] = $fread;
							$i++;
						}
					}
					
					if($i == 0)
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." La liste des administrateurs ne contient aucune entrée.");
					} else
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." =========================================================");
						
						foreach ($ListAdmins as $AdminLine)
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Username: " . $AdminLine);
						}
						
						$this->main->MyConn->put("NOTICE ".$T[1]." =========================================================");
						$this->main->MyConn->put("NOTICE ".$T[1]." Il y a ".$i." entrées dans la liste.");
						$this->main->MyConn->put("NOTICE ".$T[1]." =========================================================");
					}
				}
			}
		}
		
		/**
		 * Add user from the Administrator
		 * 
		 * Warning: this element require SuperAdmin account
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(ADDADMIN|addadmin) (.*)`', $IRCtxt, $T))
		{
			if(!file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				$this->main->MyConn->put("NOTICE ".$T[1]." Pour exécuter cette commande, vous devez être connecté en tant qu'administrateur du robot ".IRCConn::$myBotName.". Pour vous identifier, tapez '/MSG ".IRCConn::$myBotName." IDENTIFY <password>'.");
			} else
			{
				if(!file_exists(dirname(__FILE__) . '/data/admin.superadmin/' . $T[1]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Pour exécuter cette commande, vous devez être Super Administrateur du robot ".IRCConn::$myBotName.".");
				} else
				{
					$T[4] = trim($T[4]);
					
					if(empty($T[4]))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." ADDADMIN <username>'.");
					} else
					{
						if(!file_exists(dirname(__FILE__) . '/data/admin.account/') || !is_readable(dirname(__FILE__) . '/data/admin.account/'))
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Une erreur s'est produite : le dossier '".dirname(__FILE__) . '/data/admin.account/'." n'existe pas ou n'est pas accessible en lecture/écriture.");
						} else
						{
							if(file_exists(dirname(__FILE__) . '/data/admin.account/' . $T[4]))
							{
								$this->main->MyConn->put("NOTICE ".$T[1]." Une erreur s'est produite : le nom d'utilisateur '".$T[4]."' existe déjà dans la base de données des administrateurs.");
							} else
							{
								$__GENERATE__PASSWORD__ = uniqid();
								
								$fp = fopen(dirname(__FILE__) . '/data/admin.account/' . $T[4], "w");
								fputs($fp, sha1($__GENERATE__PASSWORD__));
								fclose($fp);
								
								$this->main->MyConn->put("NOTICE ".$T[1]." Le nom d'utilisateur '".$T[4]."' a bien été ajouté à la liste des adminsitrateurs avec succès (mot de passe : '".$__GENERATE__PASSWORD__."').");
								
								/**
								 * Send information to user
								 */
								
								$this->main->MyConn->put("NOTICE ".$T[4]." **************************************************************************");
								$this->main->MyConn->put("NOTICE ".$T[4]." *** Nom d'utilisateur (votre NickName) : " . $T[4]);
								$this->main->MyConn->put("NOTICE ".$T[4]." *** Mot de passe : " . $__GENERATE__PASSWORD__);
								$this->main->MyConn->put("NOTICE ".$T[4]." * Pour vous identifier en tant qu'administrateur du robot ".IRCConn::$myBotName.", vous devez vous connecter avec la commande '/MSG ".IRCConn::$myBotName." IDENTIFY ".$__GENERATE__PASSWORD__."'.");
								$this->main->MyConn->put("NOTICE ".$T[4]." * Nous vous conseillons fortement de modifier ce mot de passe en tapant la commande '/MSG ".IRCConn::$myBotName." MODPASSWORD <password>'.");
								$this->main->MyConn->put("NOTICE ".$T[4]." **************************************************************************");
							}
						}
					}
				}
			}
		}
		
		/**
		 * Delete user from the Administrator
		 * 
		 * Warning: this element require SuperAdmin account
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(DELADMIN|deladmin) (.*)`', $IRCtxt, $T))
		{
			if(!file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				$this->main->MyConn->put("NOTICE ".$T[1]." Pour exécuter cette commande, vous devez être connecté en tant qu'administrateur du robot ".IRCConn::$myBotName.". Pour vous identifier, tapez '/MSG ".IRCConn::$myBotName." IDENTIFY <password>'.");
			} else
			{
				if(!file_exists(dirname(__FILE__) . '/data/admin.superadmin/' . $T[1]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Pour exécuter cette commande, vous devez être Super Administrateur du robot ".IRCConn::$myBotName.".");
				} else
				{
					$T[4] = trim($T[4]);
					
					if(empty($T[4]))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." DELADMIN <username>'.");
					} else
					{
						if(!file_exists(dirname(__FILE__) . '/data/admin.account/' . $T[4]))
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Une erreur s'est produite : le nom d'utilisateur '".$T[4]."' n'a pas été trouvé dans la base de données des administrateurs.");
						} else
						{
							unlink(dirname(__FILE__) . '/data/admin.account/' . $T[4]);
							
							$this->main->MyConn->put("NOTICE ".$T[1]." Le nom d'utilisateur '".$T[4]."' a été bien été supprimé des administrateurs du robot ".IRCConn::$myBotName.".");
						}
					}
				}
			}
		}
		
		/**
		 * Modpassword user from the Administrator
		 * 
		 * Warning: this element require SuperAdmin account
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(MODADMINPASSWORD|modadminpassword) (.*) (.*)`', $IRCtxt, $T))
		{
			if(!file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				$this->main->MyConn->put("NOTICE ".$T[1]." Pour exécuter cette commande, vous devez être connecté en tant qu'administrateur du robot ".IRCConn::$myBotName.". Pour vous identifier, tapez '/MSG ".IRCConn::$myBotName." IDENTIFY <password>'.");
			} else
			{
				if(!file_exists(dirname(__FILE__) . '/data/admin.superadmin/' . $T[1]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Pour exécuter cette commande, vous devez être Super Administrateur du robot ".IRCConn::$myBotName.".");
				} else
				{
					$T[4] = trim($T[4]);
					$T[5] = trim($T[5]);
					
					if(empty($T[4]) || empty($T[5]))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Syntaxe de la commande : '/MSG ".IRCConn::$myBotName." MODADMINPASSWORD <username> <newpassword>'.");
					} else
					{
						if(!file_exists(dirname(__FILE__) . '/data/admin.account/' . $T[4]) || !is_readable(dirname(__FILE__) . '/data/admin.account/' . $T[4]))
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Une erreur s'est produite : le nom d'utilisateur '".$T[4]."' n'a pas été trouvé dans la base de données des administrateurs.");
						} else
						{
							if(empty($T[5]) || strlen($T[5]) < 6 || !eregi("^[0-9_a-z\+.\+-]*$", $T[5]))
							{
								$this->main->MyConn->put("NOTICE ".$T[1]." Le mot de passe est invalide : il doit se composer de plus de 6 caractères et se composer uniquement de caractères alphanumériques.");
							} else
							{
								$this->main->MyConn->put("NOTICE ".$T[1]." Le mot de passe pour l'administrateur '".$T[4]."' a bien été modifié avec succès.");
								
								$fp = fopen(dirname(__FILE__) . '/data/admin.account/' . $T[4], "w");
								fputs($fp, sha1($T[5]));
								fclose($fp);
							}
						}
					}
				}
			}
		}
		
		/**
		 * Enabled or Disabled QOpServ Archiver in a channel
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!log)`', $IRCtxt, $T))
		{
			if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				if(file_exists(dirname(__FILE__) . '/data/channels.logs/' . strtolower($T[3])))
				{
					unlink(dirname(__FILE__) . '/data/channels.logs/' . strtolower($T[3]));
					
					$this->main->MyConn->put("NOTICE ".$T[1]." Le channel '".strtolower($T[3])."' n'est plus loggué [OFF].");
				} else
				{
					$fp = fopen(dirname(__FILE__) . '/data/channels.logs/' . strtolower($T[3]), "w");
					fputs($fp, time());
					fclose($fp);
					
					$this->main->MyConn->put("NOTICE ".$T[1]." Le channel '".strtolower($T[3])."' est loggué [ON].");
				}
			}
		}
		
		/**
		 * Enabled or Disabled QOpServ Vhost command
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!advhost)`', $IRCtxt, $T))
		{
			if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				if(file_exists(dirname(__FILE__) . '/data/.LockVHost'))
				{
					unlink(dirname(__FILE__) . '/data/.LockVHost');
					
					$this->main->MyConn->put("NOTICE ".$T[1]." La commande !vhost est activée.");
				} else
				{
					$fp = fopen(dirname(__FILE__) . '/data/.LockVHost', "w");
					fputs($fp, time());
					fclose($fp);
					
					$this->main->MyConn->put("NOTICE ".$T[1]." La commande !vhost est désactivée.");
				}
			}
		}
		
		/**
		 * Enabled or Disabled QOpServ commands in a channel
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!triggers)`', $IRCtxt, $T))
		{
			if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				if(file_exists(dirname(__FILE__) . '/data/channel.function.disabled/' . strtolower($T[3])))
				{
					unlink(dirname(__FILE__) . '/data/channel.function.disabled/' . strtolower($T[3]));
					
					$this->main->MyConn->put("NOTICE ".$T[1]." Les commandes principales du robot sur le channel '".strtolower($T[3])."' ont été activées [ON].");
				} else
				{
					$fp = fopen(dirname(__FILE__) . '/data/channel.function.disabled/' . strtolower($T[3]), "w");
					fputs($fp, time());
					fclose($fp);
					
					$this->main->MyConn->put("NOTICE ".$T[1]." Les commandes principales du robot sur le channel '".strtolower($T[3])."' ont été désactivées [OFF].");
				}
			}
		}
		
		/**
		 * Enabled or Disabled QOpServ DEFCON
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!defcon)`', $IRCtxt, $T))
		{
			if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
			{
				if(file_exists(dirname(__FILE__) . '/data/.DefConActive'))
				{
					/**
					 * Del level
					 */
					unlink(dirname(__FILE__) . '/data/.DefConActive');
					
					/**
					 * Join channel
					 */
					
					sleep(1);
					
					$dir = opendir(dirname(__FILE__) . '/data/channels/');
					$i = 0;
				
					while ($fread = readdir($dir))
					{
						if($fread != "." && $fread != "..")
						{
							if(strtolower($fread) != "#ircops")
							{
								$this->main->MyConn->put("JOIN " . $fread);
							}
						}
					}
					
					/**
					 * Change bot mode
					 */
					
					sleep(1);
					$this->main->MyConn->put("MODE " . IRCConn::$myBotName . " -dRT");
					
					/**
					 * Send notification
					 */
					
					$this->main->MyConn->put("NOTICE ".$T[1]." Le DEFCON est sur le niveau 2 [NIVEAU NORMAL]. Les protections au niveau du robot ont été désactivées.");
				} else
				{
					/**
					 * Register level
					 */
					
					$fp = fopen(dirname(__FILE__) . '/data/.DefConActive', "w");
					fputs($fp, time());
					fclose($fp);
					
					/**
					 * Part from all channels
					 */
					
					sleep(1);
					
					$dir = opendir(dirname(__FILE__) . '/data/channels/');
					$i = 0;
				
					while ($fread = readdir($dir))
					{
						if($fread != "." && $fread != "..")
						{
							if(strtolower($fread) != "#ircops")
							{
								$this->main->MyConn->put("PART " . $fread . " DEFCON Enabled (".$T[1].")");
							}
						}
					}
					
					/**
					 * Change bot mode
					 */
					
					sleep(1);
					$this->main->MyConn->put("MODE " . IRCConn::$myBotName . " +dRT");
					
					/**
					 * Send notification
					 */
					
					sleep(1);
					$this->main->MyConn->put("NOTICE ".$T[1]." Le DEFCON est sur le niveau 1 [/!\ NIVEAU D'ALERTE MAXIMALE /!\]. Les protections au niveau du robot ont été activées.");
					
					/**
					 * Send notification into a ircops channel
					 */
					
					$this->main->MyConn->put("PRIVMSG #IRCOPS /!\ DEFCON ENABLED /!\ -> Defcon has been enabled by " . $T[2] . ".");			
				}
			}
		}
	}
}

?>
