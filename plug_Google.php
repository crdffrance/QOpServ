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

class plug_Google implements plugin {

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
		$this->SeekGoogleSearchWeb($IRCtext);
		$this->SeekGoogleSearchImages($IRCtext);
	}
	
	/**
	 * Function to seek in Google Search Web
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function SeekGoogleSearchWeb ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!search|!google) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]) && !file_exists(dirname(__FILE__) . '/data/channel.function.disabled/' . strtolower($T[3])))
		{
			if($this->main->MyConn->GlobalAntiFlood(1, 20, "privmsg.google.search." . $T[2]) != true)
			{
				$T[6] = trim($T[6]);
				
				if(empty($T[6]))
				{
					$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! J'suis pas devin tête d'ampoule ! Essai de taper des mots pour voir...");
				} else
				{
					$ctx = stream_context_create(array('http' => array('timeout' => 5))); 
					
					$__Open__Google__Search__ = file_get_contents("http://www.google.fr/search?q=".urlencode($T[6])."&ie=utf-8&oe=utf-8&aq=t&rls=org.mozilla:fr:official&client=firefox-a", 0, $ctx);
					
					if($__Open__Google__Search__ === FALSE)
					{
						$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! Une erreur s'est produite pendant la connexion à Google Search. Essai à nouveau pour voir...");
					} else
					{
						preg_match_all('#<h3 class="r"><a href="([^"]*)" class=l>(([^<]|<[^a][^ ])*)</a></h3>#i', $__Open__Google__Search__, $Results, PREG_SET_ORDER);
						
						if(count($Results) == 0)
						{
							$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! Aucun document ne correspond aux termes de recherche spécifiés.");
						} else
						{
							for($n = 0 ; $n < 2 ; $n++)
							{
								$this->main->MyConn->put("PRIVMSG ".$T[3]." [02Recherche Google Web] ".strip_tags ( html_entity_decode ( $Results [$n] [2], ENT_QUOTES, 'utf-8' ) )." - " . $Results [$n] [1]);
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * Function to seek in Google Search Image
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function SeekGoogleSearchImages ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!img|!image) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]) && !file_exists(dirname(__FILE__) . '/data/channel.function.disabled/' . strtolower($T[3])))
		{
			if($this->main->MyConn->GlobalAntiFlood(1, 20, "privmsg.google.search." . $T[2]) != true)
			{
				$T[6] = trim($T[6]);
				
				if(empty($T[6]))
				{
					$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! J'suis pas devin tête d'ampoule ! Essai de taper des mots pour voir...");
				} else
				{
					$ctx = stream_context_create(array('http' => array('timeout' => 5))); 
					
					$__Open__Google__Search__ = file_get_contents("http://images.google.fr/images?hl=fr&client=firefox-a&rls=org.mozilla:fr:official&um=1&q=".urlencode($T[6])."&sa=N&start=63&ndsp=21", 0, $ctx);
					
					if($__Open__Google__Search__ === FALSE)
					{
						$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! Une erreur s'est produite pendant la connexion à Google Search. Essai à nouveau pour voir...");
					} else
					{
						preg_match_all('#\["([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)","([^"]*)",\[([^\]]*)\],"([^"]*)"#i', $__Open__Google__Search__, $Results, PREG_SET_ORDER);
						
						if(count($Results) == 0)
						{
							$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! Aucun document ne correspond aux termes de recherche spécifiés.");
						} else
						{
							for($n = 0 ; $n < 2 ; $n++)
							{
								$this->main->MyConn->put("PRIVMSG ".$T[3]." [02Recherche Google Images] ".strip_tags ( html_entity_decode ( $Results [$n] [4] ) )." (" . $Results [$n] [10] . " - " . $Results [$n] [12] . ").");
							}
						}
					}
				}
			}
		}
	}
}

?>
