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

class plug_IRCOps implements plugin {

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
		$this->PreventIRCOps($IRCtext);
	}
	
	/**
	 * Command type !ircops
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function PreventIRCOps ($IRCtxt)
	{
		$ChannelsAuthorized = array('#quitenet', "#abuse");
		
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*) :(.*?)(!ircops|!ircop)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if(!in_array(strtolower($T[3]), $ChannelsAuthorized))
			{
				$this->main->MyConn->put("NOTICE ".$T[1]." La commande !ircops ne peut être utilisée que sur le channel #QuiteNet. Tout abus de cette commande sera puni.");
			} else
			{
				if($this->main->MyConn->GlobalAntiFlood(1, 300, "ircops." . $T[2]) === true)
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." Tu ne crois pas qu'il faudrait attendre au moins 10 minutes pour qu'un IRCOps arrive ? Patiente un moment s'il te plait, le temps d'en laisser un venir...");
				} else
				{
					$this->main->MyConn->put("PRIVMSG #IRCOPS --- URGENT --- : l'usager '".$T[1]."' demande l'aide d'un IRCOps sur le channel '".$T[3]."'. Merci de bien vouloir vous rendre immédiatement sur le channel pour traiter sa demande (HL System : ".$this->main->MyConn->HLStatement().").");
					$this->main->MyConn->put("NOTICE ".$T[1]." Les IRCOp ont été prévenus de ta demande. Patiente un moment s'il te plait, le temps d'en laisser un venir...");
				}
			}
		}
	}
}

?>
