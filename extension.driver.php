<?php
require_once(TOOLKIT.'/class.author.php');

Class extension_importcsv extends Extension
{
	// About this extension:
	public function about()
	{
		return array(
			'name' => 'Import/export CSV',
			'version' => '0.3',
			'release-date' => '2011-12-15',
			'author' => array(
				'name' => 'Giel Berkers',
				'website' => 'http://www.gielberkers.com',
				'email' => 'info@gielberkers.com'),
			'description' => 'Import a CSV file to create new entries for a certain section, or export an existing section to a CSV file'
		);
	}
	
	public function fetchNavigation() {
		if(Administration::instance()->Author->isDeveloper())
		{
			return array(
				array(
					'location'	=> __('System'),
					'name'		=> __('Import / Export CSV'),
					'link'		=> '/'
				)
			);
		}
	}

    public function update()
    {
        if(file_exists(TMP.'/importcsv.csv'))
        {
            @unlink(TMP.'/importcsv.csv');
        }
    }
	
	
}
