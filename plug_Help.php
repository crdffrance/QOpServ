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

class plug_Help implements plugin {

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
		$this->HelpMENU($IRCtext);
	}
	
	/**
	 * Affichage du menu d'aide
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function HelpMENU ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG '.IRCConn::$myBotName.' :(help|HELP)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			/**
			 * 
			 * List of available commands
			 * 
			 * *** Channel ***
			 * 0.0		=>		Public Command
			 * 0.1		=>		Admin Command
			 * 
			 * *** Private message ***
			 * 1.0		=>		Public Command
			 * 1.1		=>		Admin Command
			 * 
			 */
			
			// Channel Public Command
			
			$HelpMenu[] = array(0.0, "!google", "Faire une recherche Google");
			$HelpMenu[] = array(0.0, "!image", "Faire une recherche sur Google Images");
			$HelpMenu[] = array(0.0, "!ircops", "Appel un IRCOp de QuiteNet IRC (uniquement en cas d'urgence)");
			$HelpMenu[] = array(0.0, "!crdfrbl", "Recherche dans la CRDF Blacklist Community une adresse IP");
			$HelpMenu[] = array(0.0, "!tv", "Affiche le programme TV");
			$HelpMenu[] = array(0.0, "!vdm", "VDM au hasard");
			$HelpMenu[] = array(0.0, "!vhost", "Faire une demande de VHOST sur QuiteNet IRC");
			$HelpMenu[] = array(0.0, "!avancement", "Afficher l'avancement (ou le planning) d'une fansub");
			
			// Channel Admin Command
			
			$HelpMenu[] = array(0.1, "!plugin", "Affichage des plugins QOpServ");
			$HelpMenu[] = array(0.1, "!load", "Charge un plugin");
			$HelpMenu[] = array(0.1, "!unload", "Décharge un plugin");
			$HelpMenu[] = array(0.1, "!rehash", "Recharge un plugin");
			$HelpMenu[] = array(0.1, "!list", "Affiche les Vhosts en attente de confirmation");
			$HelpMenu[] = array(0.1, "!on", "Active (ou accepter) une Vhost");
			$HelpMenu[] = array(0.1, "!off", "Désactive (ou refuse) une Vhost");
			$HelpMenu[] = array(0.1, "!log", "Active/désactive les logs sur un channel");
			$HelpMenu[] = array(0.1, "!triggers", "Active/désactive les principales commandes du robot");
			$HelpMenu[] = array(0.1, "!advhost", "Active/désactive la commande !vhost");
			$HelpMenu[] = array(0.1, "!defcon", "Active/désactive les protections au niveau du robot");
			
			// Private Message Public Command
			
			$HelpMenu[] = array(1.0, "IDENTIFY", "Connexion à un compte ADMIN");
			$HelpMenu[] = array(1.0, "LOGOUT", "Déconnexion d'un compte ADMIN");
			
			// Private Message Admin Command
			
			$HelpMenu[] = array(1.1, "MODPASSWORD", "Modifier le mot de passe de son compte");
			$HelpMenu[] = array(1.1, "SAY", "Fait parler le robot sur un channel");
			$HelpMenu[] = array(1.1, "NOTICE", "Envoyer une notice à un utilisateur");
			$HelpMenu[] = array(1.1, "RESTART", "Redémarre le robot");
			$HelpMenu[] = array(1.1, "KILL", "KILL un usager");
			$HelpMenu[] = array(1.1, "JOIN", "Fait joindre le robot sur un channel");
			$HelpMenu[] = array(1.1, "PART", "Fait quitter le robot d'un channel");
			$HelpMenu[] = array(1.1, "AUTOJOIN", "Ajoute un channel en auto-join");
			$HelpMenu[] = array(1.1, "AUTOPART", "Supprime un channel en auto-join");
			$HelpMenu[] = array(1.1, "AUTOCHANLIST", "Liste les channels en auto-join");
			$HelpMenu[] = array(1.1, "IGNORE", "Ignore une host");
			$HelpMenu[] = array(1.1, "UNIGNORE", "Enlève une ignore d'une host");
			$HelpMenu[] = array(1.1, "IGNORELIST", "Liste d'ignorance");
			$HelpMenu[] = array(1.1, "LISTADMIN", "Liste des administrateurs");
			$HelpMenu[] = array(1.1, "ADDADMIN", "Ajouter un administrateur du robot");
			$HelpMenu[] = array(1.1, "DELADMIN", "Supprimer un administrateur du robot");
			$HelpMenu[] = array(1.1, "MODADMINPASSWORD", "Modifier le mot de passe d'un administrateur du robot");
			
			/**
			 * COMMAND
			 */
			
			if(!is_array($HelpMenu))
			{
				$this->main->MyConn->put("NOTICE ".$T[1]." Une erreur s'est produite : le tableau contenant le menu d'aide n'est pas valide.");
			} else
			{
				/**
				 * BEGIN HELP MENU
				 */
				
				$this->main->MyConn->put("NOTICE ".$T[1]." ========================== AIDE ===========================");
				
				if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." * Connecté en tant qu'administrateur de ".IRCConn::$myBotName.".");
				} else
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." * Connecté en tant qu'utilisateur de ".IRCConn::$myBotName.".");
				}
				
				/**
				 * Array analyze
				 */
				
				/**
				 * USER 0.0
				 */
				
				$this->main->MyConn->put("NOTICE ".$T[1]." ===========================================================");
				$this->main->MyConn->put("NOTICE ".$T[1]." ========= Commandes accessibles sur les channels ==========");
				$this->main->MyConn->put("NOTICE ".$T[1]." ===========================================================");
				
				// UNSET
				unset($__ARRAY__HELP__MENU__);
				
				foreach ($HelpMenu as $__ARRAY__HELP__MENU__)
				{
					/**
					 * USER
					 */
					
					if($__ARRAY__HELP__MENU__[0] == "0.0")
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." ".$__ARRAY__HELP__MENU__[1]."  :  " . $__ARRAY__HELP__MENU__[2] . " ;");
					}
				}
				
				/**
				 * USER 1.0
				 */
				
				$this->main->MyConn->put("NOTICE ".$T[1]." ===========================================================");
				$this->main->MyConn->put("NOTICE ".$T[1]." ========= Commandes accessibles en message privé ==========");
				$this->main->MyConn->put("NOTICE ".$T[1]." ===========================================================");
				
				// UNSET
				unset($__ARRAY__HELP__MENU__);
				
				foreach ($HelpMenu as $__ARRAY__HELP__MENU__)
				{
					/**
					 * USER
					 */
					
					if($__ARRAY__HELP__MENU__[0] == "1.0")
					{
						$this->main->MyConn->put("NOTICE ".$T[1]." ".$__ARRAY__HELP__MENU__[1]."  :  " . $__ARRAY__HELP__MENU__[2] . " ;");
					}
				}
				
				/**
				 * ADMIN 0.1
				 */
				
				// UNSET
				unset($__ARRAY__HELP__MENU__);
				
				if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." ===========================================================");
					$this->main->MyConn->put("NOTICE ".$T[1]." ============== Commandes ADMIN : Channels =================");
					$this->main->MyConn->put("NOTICE ".$T[1]." ===========================================================");
					
					foreach ($HelpMenu as $__ARRAY__HELP__MENU__)
					{
						if($__ARRAY__HELP__MENU__[0] == "0.1")
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." ".$__ARRAY__HELP__MENU__[1]."  :  " . $__ARRAY__HELP__MENU__[2] . " ;");
						}
					}
				} else
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." ===========================================================");
				}
				
				/**
				 * ADMIN 1.1
				 */
				
				// UNSET
				unset($__ARRAY__HELP__MENU__);
				
				if(file_exists(dirname(__FILE__) . '/data/admin.host/' . $T[2]))
				{
					$this->main->MyConn->put("NOTICE ".$T[1]." ===========================================================");
					$this->main->MyConn->put("NOTICE ".$T[1]." ============== Commandes ADMIN : Privés ==================");
					$this->main->MyConn->put("NOTICE ".$T[1]." ===========================================================");
					
					foreach ($HelpMenu as $__ARRAY__HELP__MENU__)
					{
						if($__ARRAY__HELP__MENU__[0] == "1.1")
						{
							$this->main->MyConn->put("NOTICE ".$T[1]." ".$__ARRAY__HELP__MENU__[1]."  :  " . $__ARRAY__HELP__MENU__[2] . " ;");
						}
					}
					
					$this->main->MyConn->put("NOTICE ".$T[1]." ===========================================================");
				}
			}
		}
	}
}

?>
