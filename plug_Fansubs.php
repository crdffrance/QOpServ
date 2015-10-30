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

class plug_Fansubs implements plugin {

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
		$this->EventFansubETAT($IRCtext);
	}
	
	/**
	 * Fonction d'affichage pour fansub de l'état d'avancement de ces releases
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function EventFansubETAT ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!avancement|!planning)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if($this->main->MyConn->GlobalAntiFlood(1, 60, "privmsg.fansubevent." . $T[2]) != true)
			{				
				if(file_exists(dirname(__FILE__) . '/data/fansubs.event/' . strtolower($T[3])))
				{
					$__OPEN__FILE__ = file(dirname(__FILE__) . '/data/fansubs.event/' . strtolower($T[3]));
					$__URL__ACCESS__API__ = trim($__OPEN__FILE__[0]);
					
					if(empty($__URL__ACCESS__API__))
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." Une erreur s'est produite : le fichier ne contient aucune adresse pour la lecture des données.");
					} else
					{						
						if(!file_exists(dirname(__FILE__) . '/data/fansub.event.cache/' . strtolower($T[3])))
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." Hey ! Une erreur s'est produite pendant le chargement de l'avancement des releases pour ce channel car il n'y a aucunes données dans le cache du système. Merci d'attendre quelques minutes et de réessayer.");
						} else
						{
							$Handle_FileAvancement = file_get_contents(dirname(__FILE__) . '/data/fansub.event.cache/' . strtolower($T[3]));
							
							if(empty($Handle_FileAvancement))
							{
								$this->main->MyConn->put("NOTICE ".$T[1]." Hey ! Une erreur s'est produite pendant le chargement de l'avancement des releases pour ce channel car il n'y a aucunes données dans le cache du système. Merci d'attendre quelques minutes et de réessayer.");
							} else
							{
								if(eregi("<item>(.*)</item>", $Handle_FileAvancement, $rawitems))
								{
									$items = explode("<item>", $rawitems[0]);
									$nb = count($items);
									$maximum = ( ( $nb - 1 ) < 10) ? ( $nb - 1 ) : 10;
									
									$this->main->MyConn->put("NOTICE ".$T[1]." ---------------- Avancement des releases ----------------");
									$this->main->MyConn->put("NOTICE ".$T[1]." \t");
									
									if($nb == 0)
									{
										$this->main->MyConn->put("NOTICE ".$T[1]." Il n'y a aucune données sur l'état d'avancement de nos releases.");
									} else
									{
										for ($i = 0 ; $i < $maximum ; $i++)
										{
											eregi("<title>(.*)</title>", $items[ $i + 1 ], $title);
											eregi("<num_episode>(.*)</num_episode>", $items[ $i + 1 ], $num_episode);
											eregi("<avancement>(.*)</avancement>", $items[ $i + 1 ], $avancement);
											eregi("<info>(.*)</info>", $items[ $i + 1 ], $info);
											
											/**
											 * Secure DATA
											 */
											
											$tite[1] = trim(strip_tags($tite[1]));
											$num_episode[1] = trim(strip_tags($num_episode[1]));
											$avancement[1] = trim(strip_tags($avancement[1]));
											$info[1] = trim(strip_tags($info[1]));
											
											/**
											 * END
											 */
											
											$this->main->MyConn->put("NOTICE ".$T[1]." ".$title[1]." ".$num_episode[1]."");
											$this->main->MyConn->put("NOTICE ".$T[1]." 				4".$avancement[1]."");
											
											if(!empty($info[1]))
											{
												$this->main->MyConn->put("NOTICE ".$T[1]." " . $info[1]);
											}
											
											$this->main->MyConn->put("NOTICE ".$T[1]." \t");
										}
									}
									
									$this->main->MyConn->put("NOTICE ".$T[1]." ---------------------------------------------------------");
								} else
								{
									$this->main->MyConn->put("NOTICE ".$T[1]." Hey ! Une erreur s'est produite pendant la lecture du fichier XML du channel '".$T[3]."'. Le système de lecture du fichier XML ne correspond pas à un fichier du système CRDF Get Fansub.");
								}
							}
						}
					}
				}
			}
		}
		
		/**
		 * Seek an update avancement
		 */
		
		if(file_exists(dirname(__FILE__) . '/data/.eventfansub'))
		{
			if(time() - filemtime(dirname(__FILE__) . '/data/.eventfansub') > ( 300 ))
			{
				unlink(dirname(__FILE__) . '/data/.eventfansub');
			}
		} else
		{
			$fp = fopen(dirname(__FILE__) . '/data/.eventfansub', "w");
			fputs($fp, time());
			fclose($fp);
			
			$Update['TimeOut'] = true;
		}
		
		if($Update['TimeOut'] === TRUE)
		{
			$dir = opendir(dirname(__FILE__) . '/data/fansubs.event/');
				
			while ($fread = readdir($dir))
			{
				if($fread != "." && $fread != "..")
				{
					$__URL__FILE__SEEKING__ = file(dirname(__FILE__) . '/data/fansubs.event/' . strtolower($fread));
					
					$__GET__SEEK__UPDATE__ = $this->main->MyConn->OpenURL(trim($__URL__FILE__SEEKING__[0]));
					
					if($__GET__SEEK__UPDATE__ === FALSE)
					{
						//$this->main->MyConn->put("PRIVMSG ".$fread." Une erreur s'est produite pendant l'ouverture de la page Web.");
					} else
					{
						if(file_exists(dirname(__FILE__) . '/data/fansub.event.update/' . strtolower($fread)))
						{
							$__SHA1__FILE__OBTAIN__ = file(dirname(__FILE__) . '/data/fansub.event.update/' . strtolower($fread));
							
							if(trim($__SHA1__FILE__OBTAIN__[0]) != sha1($__GET__SEEK__UPDATE__))
							{
								$this->main->MyConn->put("PRIVMSG ".strtolower($fread)." [02Fansub Avancement] Des mises à jour ont été trouvées au niveau de l'avancement des releases. Pour voir ces avancements, tapez la commande !avancement (il y a peut-être une nouvelle release).");
								
								$fp = fopen(dirname(__FILE__) . '/data/fansub.event.update/' . strtolower($fread), "w");
								fputs($fp, sha1($__GET__SEEK__UPDATE__));
								fclose($fp);
								
								/**
								 * Enregistrement du cache pour les mises à jour
								 */
								
								$fp = fopen(dirname(__FILE__) . '/data/fansub.event.cache/' . strtolower($fread), "w");
								fputs($fp, $__GET__SEEK__UPDATE__);
								fclose($fp);
							}
						} else
						{
							$fp = fopen(dirname(__FILE__) . '/data/fansub.event.update/' . strtolower($fread), "w");
							fputs($fp, sha1($__GET__SEEK__UPDATE__));
							fclose($fp);
						}
					}
				}
			}
		}
	}
}

?>
