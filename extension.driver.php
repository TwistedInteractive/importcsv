<?php
require_once(TOOLKIT.'/class.author.php');

Class extension_importcsv extends Extension
{
	// About this extension:
	public function about()
	{
		return array(
			'name' => 'Import/export CSV',
			'version' => '0.1.4',
			'release-date' => '2011-05-30',
			'author' => array(
				'name' => 'Giel Berkers',
				'website' => 'http://www.gielberkers.com',
				'email' => 'info@gielberkers.com'),
			'description' => 'Import a CSV file to create new entries for a certain section, or export an existing section to a CSV file'
		);
	}
	
	public function fetchNavigation() {
		$author = new Author();
		$author->loadAuthorFromUsername($_SESSION['sym-']['username']);		
		
		if($author->isDeveloper())
		{
			return array(
				array(
					'location'	=> 'System',
					'name'		=> 'Import / export CSV',
					'link'		=> '/'
				)
			);
		}
	}
	
	
}
