<?php

class plug_CRDFBL implements plugin {

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
		$this->ConnexionLaunch($IRCtext);
	}
	
	/**
	 * Launch CMD to connect in QuiteNet Network
	 *
	 * @param unknown_type $IRCtxt
	 */
	
	private function ConnexionLaunch($IRCtxt)
	{
		if(preg_match("/Notice -- Client connecting (on port|at) (.*): ([^ ]+) \\(([^@]+)@([^\\)]+)\\)/", $IRCtxt, $T))
		{			
			if(!empty($T[5]))
			{				
				if(preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/", $T[5]))
				{					
					$__OUTPUT__IP__ = $T[5];
				} else
				{
					$__OUTPUT__IP__ = gethostbyname($T[5]);
				}
				
				if(empty($__OUTPUT__IP__))
				{
					$this->main->MyConn->put("PRIVMSG #IRCOPS User Connection -> Get IP failed -> ".trim($T[5]).".");
				} else
				{
					if(!file_exists(dirname(__FILE__) . '/data/CRDFBlacklistCommunity') || !is_writeable(dirname(__FILE__) . '/data/CRDFBlacklistCommunity'))
					{
						$this->main->MyConn->put("PRIVMSG #IRCOPS User Connection -> CRDFBlacklistCommunity file not found or istn't redeable -> ".trim($T[5]).".");
					} else
					{
						$__OPEN__FILE__ = file(dirname(__FILE__) . '/data/CRDFBlacklistCommunity');
						
						foreach ($__OPEN__FILE__ as $__IP__FOUND__LINE)
						{
							$filtreExpIP[] = $__IP__FOUND__LINE;
						}
						
						if(!is_array($filtreExpIP))
						{
							$this->main->MyConn->put("PRIVMSG #IRCOPS User Connection -> PHP Anti Spammers Database Filter is not valid (found=".count($filtreExpIP).") -> ".trim($T[5]).".");
						} else
						{
							foreach($filtreExpIP as $IPValue)
							{
								if(ereg("^" . str_replace(".", "\.", $IPValue), $__OUTPUT__IP__))
								{
									$this->main->MyConn->put("PRIVMSG #IRCOPS User Connection -> IP found in the CRDF BlackList Community -> ".trim($T[5]).".");
									break;
								}
							}
						}
					}
				}
				
				unset($__OUTPUT__IP__);
			}
		}
	}
}

?>
