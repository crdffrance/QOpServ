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

class plug_Archiver implements plugin {

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
		$this->LogArchiver($IRCtext);
	}
	
	/**
	 * Function to logs channels
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function LogArchiver ($IRCtxt)
	{
		/**
		 * Emplacement du dossier pour enregistrer les logs
		 */
		
		$FOLDER_LOGS = "/home/quitenet/logs/logs/";
		
		/**
		 * Log PRIVMSG
		 */
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if(file_exists(dirname(__FILE__) . '/data/channels.logs/' . strtolower($T[3])))
			{
				$fp = fopen($FOLDER_LOGS . strtolower($T[3]) . "." . date("d.m.Y") . ".log", "a+");
				fputs($fp, time() . "\t"  . "PRIVMSG" . "\t" . strtolower($T[3]) . "\t" . $T[2] . "\t" . trim($T[1]) . "\t" . trim($T[4]) . "\n");
				fclose($fp);
			}
		}
	}
}

?>
