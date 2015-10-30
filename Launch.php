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

/**
 * 
 * Mécanisme de chargement automatique des classes selon leur nom
 * 
 */

function __autoload ($class)
{
	if(is_readable($class . ".php"))
	{
		require_once $class . ".php";
	}
}

/**
 * 
 * Nouvelle instance de la classe IRCMain
 * 
 */

$MainProc = new IRCMain("ssl://127.0.0.1", 6660, "#QuiteNet", "QOpServ", "127.0.0.1", "crdf.fr");

/**
 * 
 * Chargement des plugins
 * 
 */

$MainProc -> AddPlug('plug_Flood');
$MainProc -> AddPlug('plug_IRCOps');
$MainProc -> AddPlug('plug_vHost');
$MainProc -> AddPlug('plug_Admin');
$MainProc -> AddPlug('plug_RSSNEWS');
$MainProc -> AddPlug('plug_RSSNewsMangas');
$MainProc -> AddPlug('plug_Google');
$MainProc -> AddPlug('plug_Tools');
$MainProc -> AddPlug('plug_Fansubs');
$MainProc -> AddPlug('plug_Help');
$MainProc -> AddPlug('plug_Archiver');

/**
 * 
 * Lancement du Robot IRC
 * 
 */

$MainProc -> run();

?>