<?php

class plug_Youtube implements plugin {

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
		$this->SeekYoutube($IRCtext);
	}
	
	/**
	 * Function to seek in Youtube
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function SeekYoutube ($IRCtxt)
	{
		if(preg_match('`^:(.*?)!(.*?) PRIVMSG (.*?) :(.*?)(!youtube) (.*)`', $IRCtxt, $T) && !file_exists(dirname(__FILE__) . '/data/ignore/' . $T[2]))
		{
			if($this->main->MyConn->GlobalAntiFlood(1, 20, "privmsg.youtube.search." . $T[2]) != true)
			{
				$T[6] = trim($T[6]);
				
				if(empty($T[6]))
				{
					$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! J'suis pas devin tête d'ampoule ! Essai de taper des mots pour voir...");
				} else
				{
					$ctx = stream_context_create(array('http' => array('timeout' => 5))); 
					
					$__Open__Google__Search__ = file_get_contents("http://www.youtube.com/results?search_query=".urlencode($T[6])."&search_type=&aq=f", 0, $ctx);
					
					if($__Open__Google__Search__ === FALSE)
					{
						$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! Une erreur s'est produite pendant la connexion à Youtube. Essai à nouveau pour voir...");
					} else
					{
						preg_match_all("#<[aA](\s)*(href|HREF)(\s)*=(\s)*[\"|\'](.*?)[\"|\'](.*?)>#is", $__Open__Google__Search__, $Results, PREG_PATTERN_ORDER);
						
						foreach ($Results[5] as $Link)
						{
							if(preg_match('#\/watch\?v\=([a-zA-Z0-9])#', $Link))
							{
								
								if($__SHA1__LINK__  !=sha1($Link))
								{
									$ResultGoogleSearch[] = 'http://www.youtube.com' . trim($Link);
								}
								
								$__SHA1__LINK__ = sha1($Link);
							}
						}
						
						// BETA VERSION YOUTUBE SEARCH
						$this->main->MyConn->put("PRIVMSG ".$T[3]." *** QOpServ Plugin YouTube Search : BETA Version (merci d'être indulgent).");
						
						if(!is_array($ResultGoogleSearch))
						{
							$this->main->MyConn->put("PRIVMSG ".$T[3]." Hey ! Les termes de recherche Youtube spécifiés - ".$T[6]." – ne correspondent à aucune vidéo.");
						} else
						{
							$this->main->MyConn->put("PRIVMSG ".$T[3]." [02Recherche YouTube] Premier résultat - " . $ResultGoogleSearch[0]);
							$this->main->MyConn->put("PRIVMSG ".$T[3]." [02Recherche YouTube] Second résultat - " . $ResultGoogleSearch[1]);
						}
					}
				}
			}
		}
	}
}

?>
