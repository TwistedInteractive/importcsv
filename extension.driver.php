<?php
require_once(TOOLKIT.'/class.author.php');

Class extension_importcsv extends Extension
{
	// About this extension:
	public function about()
	{
		return array(
			'name' => 'Import/export CSV',
			'version' => '0.1',
			'release-date' => '2010-10-19',
			'author' => array(
				'name' => 'Giel Berkers',
				'website' => 'http://www.gielberkers.com',
				'email' => 'info@gielberkers.com'),
			'description' => 'Import a CSV file to create new entries for a certain section, or export an existing section to a CSV file'
		);
	}
	
	public function fetchNavigation() {
		// echo $this->_Parent->Author->isDeveloper() ? true : false;
		// print_r(get_class_methods($this));
		// $this->updated();
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
