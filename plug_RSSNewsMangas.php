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

class plug_RSSNewsMangas implements plugin {

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
		$this->ReadRSS($IRCtext);
	}
	
	/**
	 * READ RSS
	 *
	 * @param unknown_type $IRCtext
	 */
	
	private function ReadRSS ($IRCtxt)
	{
		/**
		 * Variables de config
		 */
		
		$CFG['ChannelNews'] = '#NEWSMANGAS';
		
		/**
		 * Liste des FLUX RSS
		 */
		
		$FluxRSSLST = array();
		
		# Raws RSS
		$FluxRSSLST[] = 'http://www.nyaatorrents.org/?page=rss';
		$FluxRSSLST[] = 'http://www.tokyotosho.info/rss.php';
		$FluxRSSLST[] = 'http://mononoke-bt.org/rss/all_weekly.xml';
		$FluxRSSLST[] = 'http://www.animesuki.com/rss.php';
		
		# Mangas News
		$FluxRSSLST[] = 'http://www.animeka.com/rss/actuasite.html';
		$FluxRSSLST[] = 'http://www.animeka.com/rss/articles.html';
		$FluxRSSLST[] = 'http://www.animeka.com/rss/lastanime.html';
		$FluxRSSLST[] = 'http://www.animeka.com/rss/rlzday.html';
		$FluxRSSLST[] = 'http://feeds.fc2.com/fc2/xml?host=yunakiti.blog79';
		
		# Mangas Subtitles
		$FluxRSSLST[] = 'http://www.tvsubtitles.net/rssfr.xml';
		
		/**
		 * TIME OUT
		 */
		
		if(file_exists(dirname(__FILE__) . '/data/.rssreadNewsMangas'))
		{
			if(time() - filemtime(dirname(__FILE__) . '/data/.rssreadNewsMangas') > ( 300 ))
			{
				unlink(dirname(__FILE__) . '/data/.rssreadNewsMangas');
			}
		} else
		{
			$fp = fopen(dirname(__FILE__) . '/data/.rssreadNewsMangas', "w");
			fputs($fp, time());
			fclose($fp);
			
			$RSS['TimeOut'] = true;
		}
		
		/**
		 * Lancement de l'application
		 */
		
		if($RSS['TimeOut'] === true)
		{
			foreach ($FluxRSSLST as $CurrentLST)
			{
				$ctx = stream_context_create(array('http' => array('timeout' => 5)));
				
				$RAW = file_get_contents($CurrentLST, 0, $ctx);
				
				if($RAW === false && !empty($CurrentLST))
				{
					//$this->main->MyConn->put("PRIVMSG ".$CFG['ChannelNews']." [ERROR #0870] : Une erreur s'est produite pendant la mise à jour du flux RSS : " . $CurrentLST);
				} else
				{
					$PARSE__URL = parse_url($CurrentLST);
					$NAME_WEBSITE = strtoupper($PARSE__URL['host']);
					
					if(eregi("<item>(.*)</item>", $RAW, $rawitems))
					{
						$items = explode("<item>", $rawitems[0]);
						$nb = count($items);
						$maximum = ( ( $nb - 1 ) < 10) ? ( $nb - 1 ) : 10;
						
						for ($i = 0 ; $i < $maximum ; $i++)
						{
							eregi("<title>(.*)</title>", $items[ $i + 1 ], $title);
							eregi("<link>(.*)</link>", $items[ $i + 1 ], $link);
							eregi("<description>(.*)</description>", $items[ $i + 1 ], $description);
							
							$title[1] = str_replace('"', "", $title[1]);
							$title[1] = str_replace('<![CDATA[', "", $title[1]);
							$title[1] = str_replace(']]>', "", $title[1]);
							$title[1] = strip_tags ( html_entity_decode ( $title[1], ENT_QUOTES, 'utf-8' ) );
							
							$link[1] = str_replace('<![CDATA[', "", $link[1]);
							$link[1] = str_replace(']]>', "", $link[1]);
							
							$__SHA1__ART = str_replace('-', '', crc32($NAME_WEBSITE . $title[1]));
							
							if(!file_exists(dirname(__FILE__) . '/data/rss.stock/' . $__SHA1__ART))
							{
								/**
								 * Envoi de la NEWS
								 */
								
								$this->main->MyConn->put("PRIVMSG ".$CFG['ChannelNews']." [02".$NAME_WEBSITE."] ".html_entity_decode($title[1])." - http://r.quitenet.org/" . $__SHA1__ART . '.html');
								
								/**
								 * Create minilien redirection
								 */
								
								$__CREATE__MINILIEN__CONTENT = "<meta http-equiv=\"refresh\" content=\"0;url=".str_replace('"', "", $link[1])."\" /><p>Vous allez &ecirc;tre redirig&eacute; vers <a href=\"".str_replace('"', "", $link[1])."\">".str_replace('"', "", $link[1])."</a></p>";
								
								/**
								 * 
								 * Create an file in database
								 * 
								 */
								
								$fp = fopen(dirname(__FILE__) . '/data/rss.stock/' . $__SHA1__ART, "w");
								fputs($fp, $__SHA1__ART);
								fclose($fp);
								
								/**
								 * 
								 * Create an file to redirige news
								 * 
								 */
								
								$fp = fopen('/home/quitenet/redir.news/' . $__SHA1__ART . '.html', "w");
								fputs($fp, $__CREATE__MINILIEN__CONTENT);
								fclose($fp);
							}
						}
					}
				}
			}
		}
	}
}

?>
