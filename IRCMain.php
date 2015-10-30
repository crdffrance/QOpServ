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

class IRCMain {

	public $MyConn;
	public $Plist = array();
	public $msg;

	// Constructeur  de la classe
	public function __construct($server, $port, $channel, $name, $myip, $mydomain) {
		// Récupération d'une connexion unique
		IRCConn::Init($server, $port, $channel, $name, $myip, $mydomain);
		$this->MyConn = IRCConn::GetInstance();
		// On charge le plugin de base indispensable
		$this->AddPlug('plug_base');
	}

	public function run() {
		while (true) {
			$this->msg = $this->MyConn->get();
			foreach ($this->Plist as $Plug) {
				$Plug->start($this->msg);
			}
		}
	}
	
	public function IsPlugLoad($Pname)
	{
		if(array_key_exists( $Pname, $this->Plist))
		{
			return TRUE;
		} else
		{
			return FALSE;
		}
	}
	
	public function AddPlug($Pname) {
		if ( !array_key_exists ( $Pname, $this->Plist ) ) {
			$this->Plist[$Pname] = new $Pname($this);
		}
	}

	public function UnloadPlug($Pname) {
		if ( array_key_exists ( $Pname, $this->Plist ) ) {
			unset( $this->Plist[$Pname] );
		}
	}

}

?>