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

class plug_Tools implements plugin {

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
		$this->CRDFBlacklist($IRCtext);
		$this->ProgrammeTV($IRCtext);
		$this->VDM($IRCtext);
		$this->MiniLink($IRCtext);
	}
	
	/**
	 * Seek an ip adress in blacklist
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function CRDFBlacklist ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!crdfrbl|!blacklistcrdf|!blcrdf) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]) && !file_exists(dirname(__FILE__) . '/data/channel.function.disabled/' . strtolower($T[3])))
		{
			if($this->main->MyConn->GlobalAntiFlood(1, 20, "privmsg.crdfblacklist." . $T[2]) != true)
			{
				$T[6] = trim($T[6]);
				
				if(empty($T[6]))
				{
					$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! J'suis pas devin tête d'ampoule ! Essai de taper une adresse IP pour voir...");
				} else
				{
					$ctx = stream_context_create(array('http' => array('timeout' => 5)));
					
					$__OPEN__URL__CRDF__ = file_get_contents("http://www.crdf.fr/product/phpantispammers/blacklist-status/?IP=" . urlencode($T[6]), 0, $ctx);
					
					if($__OPEN__URL__CRDF__ === FALSE)
					{
						$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! Une erreur s'est produite pendant la connexion à CRDF Blacklist Community. Essai à nouveau pour voir...");
					} else
					{
						if(eregi("pas recensée", $__OPEN__URL__CRDF__) || eregi("invalide", $__OPEN__URL__CRDF__))
						{
							$this->main->MyConn->put("PRIVMSG ".$T[3]." [02CRDF Blacklist] L'adresse IP '".$T[6]."' n'est pas recensée dans notre base de données. Elle n'est donc pas intégrée dans les bases anti-spam de PHP Anti Spammers.");
						} else
						{
							$this->main->MyConn->put("PRIVMSG ".$T[3]." [02CRDF Blacklist] L'adresse IP '".$T[6]."' est recensée dans notre base de données. - http://www.crdf.fr/product/phpantispammers/blacklist-status/?IP=" . $T[6]);
						}
					}
				}
			}
		}
	}
	
	/**
	 * Programme TV
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function ProgrammeTV ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!programmetv|!cesoir|!tv)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]) && !file_exists(dirname(__FILE__) . '/data/channel.function.disabled/' . strtolower($T[3])))
		{
			if($this->main->MyConn->GlobalAntiFlood(1, 300, "privmsg.progtv." . $T[2]) != true)
			{
				$ctx = stream_context_create(array('http' => array('timeout' => 5)));
				
				$__RAW__TV__RSS__ = file_get_contents("http://feeds.feedburner.com/programme-television", 0, $ctx);
				
				if($__RAW__TV__RSS__ === FALSE)
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Hey ! Une erreur s'est produite pendant la connexion au serveur de récupération du programme tv. Essai à nouveau pour voir...");
				} else
				{
					if(eregi("<item>(.*)</item>", $__RAW__TV__RSS__, $rawitems))
					{
						$items = explode("<item>", $rawitems[0]);
						$nb = count($items);
						$maximum = ( ( $nb - 1 ) < 10) ? ( $nb - 1 ) : 40;
						
						for ($i = 0 ; $i < $maximum ; $i++)
						{
							eregi("<title>(.*)</title>", $items[ $i + 1 ], $title);
							eregi("<link>(.*)</link>", $items[ $i + 1 ], $link);
							eregi("<description>(.*)</description>", $items[ $i + 1 ], $description);
								
							$title[1] = str_replace('"', "", $title[1]);
							$title[1] = str_replace('<![CDATA[', "", $title[1]);
							$title[1] = str_replace(']]>', "", $title[1]);
							
							$this->main->MyConn->put("NOTICE ".$T[1]." [02Programme TV] " . $title[1]);
						}
					}
				}
			}
		}
	}
	
	/**
	 * VDM au hasard
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function VDM ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!vdm)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]) && !file_exists(dirname(__FILE__) . '/data/channel.function.disabled/' . strtolower($T[3])))
		{
			if($this->main->MyConn->GlobalAntiFlood(1, 300, "privmsg.vdm." . $T[2]) != true)
			{
				/**
				 * Configure timeout and option
				 */
				
				$ctx = stream_context_create(array('http' => array('timeout' => 5)));
				
				/**
				 * Get num version API VDM
				 */
				
				$__GET__VERSION__API__ = trim(file_get_contents("http://api.viedemerde.fr/version", 0, $ctx));
				
				/**
				 * Get VDM random
				 */
				
				$__GET__VDM__RANDOM__ = file_get_contents("http://api.viedemerde.fr/".$__GET__VERSION__API__."/view/random?key=readonly", 0, $ctx);
				
				/**
				 * Parsing data
				 */
				
				$__GET__VDM__RANDOM__ = utf8_decode($__GET__VDM__RANDOM__);
				
				/**
				 * GO
				 */
				
				if($__GET__VERSION__API__ === FALSE || $__GET__VDM__RANDOM__ === FALSE)
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Hey ! Une erreur s'est produite pendant la connexion au serveur de VDM. Essai à nouveau pour voir...");
				} else
				{
					preg_match('/<vdm id=\"(.*)\">/', $__GET__VDM__RANDOM__, $matches);
					$id = html_entity_decode($matches[1]);
					
					preg_match('/<auteur>(.*)<\/auteur>/', $__GET__VDM__RANDOM__, $matches);
					$auteur = html_entity_decode($matches[1]);
					
					preg_match('/<categorie>(.*)<\/categorie>/', $__GET__VDM__RANDOM__, $matches);
					$categorie = html_entity_decode($matches[1]);
					
					preg_match('/<texte>(.*)<\/texte>/', $__GET__VDM__RANDOM__, $matches);
					$texte = html_entity_decode($matches[1]);
					
					preg_match('/<date>(.*)<\/date>/', $__GET__VDM__RANDOM__, $matches);
					$date = date("\l\e d/m/y à H:i", strtotime(html_entity_decode($matches[1])));
					
					$this->main->MyConn->put("PRIVMSG ".$T[3]." [02VDM au hasard] #".$id." " . strip_tags ( html_entity_decode ( $texte, ENT_QUOTES, 'utf-8' ) ) . "");
				}
			}
		}
	}
	
	/**
	 * 
	 * Mini link creator
	 * 
	 */
	
	private function MiniLink ($IRCtxt)
	{
	if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!link|!minilink|!lm) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]) && !file_exists(dirname(__FILE__) . '/data/channel.function.disabled/' . strtolower($T[3])))
		{
			if($this->main->MyConn->GlobalAntiFlood(1, 20, "privmsg.minilink." . $T[2]) != true)
			{
				$T[6] = trim($T[6]);
				
				if(empty($T[6]))
				{
					$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! J'suis pas devin tête d'ampoule ! Essai de taper une adresse internet pour voir...");
				} else
				{
					$__ID__GENERATE__UNIQ = str_replace('-', '9', crc32(mt_rand()));
					
					$fp = fopen('/home/quitenet/redir.news/' . $__ID__GENERATE__UNIQ . '.html', "w");
					fputs($fp, "<meta http-equiv=\"refresh\" content=\"0;url=" . $T[6] . "\" /><p>Vous allez &ecirc;tre redirig&eacute; vers <a href=\"" . $T[6] . "\">" . $T[6] . "</a></p>");
					fclose($fp);
					
					$this->main->MyConn->put("PRIVMSG ".$T[3]." [02QuiteNet Mini Lien] Adresse web réduite : http://r.quitenet.org/" . $__ID__GENERATE__UNIQ . ".html");
				}
			}
		}
	}
}

?>
