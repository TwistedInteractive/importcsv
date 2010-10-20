<?php
	require_once(TOOLKIT . '/class.administrationpage.php');
	require_once(TOOLKIT . '/class.sectionmanager.php');
	require_once(TOOLKIT . '/class.fieldmanager.php');
	require_once(TOOLKIT . '/class.entrymanager.php');
	require_once(TOOLKIT . '/class.entry.php');
	
	class contentExtensionImportcsvIndex extends AdministrationPage
	{
		var $tmpFile; 
		
		public function __construct(&$parent)
		{
			parent::__construct($parent);
			$this->setTitle('Symphony - Import / export CSV');
			$this->tmpFile = MANIFEST.'/tmp/importcsv.csv';
		}
		
		
		
		public function build() {
			parent::addStylesheetToHead(URL . '/extensions/importcsv/assets/importcsv.css', 'screen', 70);
			parent::addStylesheetToHead(URL . '/symphony/assets/forms.css', 'screen', 70);
			parent::build();
		}
		
		
		
		public function view() {
			if(isset($_POST['import-step-2']) && $_FILES['csv-file']['name'] != '')
			{
				// Import step 2:
				$this->__importStep2Page();
			} elseif(isset($_POST['import-step-3'])) {
				// Import step 3:
				$this->__importStep3Page();
			} elseif(isset($_POST['export'])) {
				// Export
				$this->__exportPage();
			} else {
				// Startpage:
				$this->__indexPage();
			}
		}
		
		
		
		private function __indexPage()
		{
			$this->Form->setAttribute('enctype', 'multipart/form-data');
			
			$fieldset = new XMLElement('fieldset', null, array('class'=>'left'));
			$fieldset->appendChild(new XMLElement('h2', __('Import CSV')));
			$fieldset->appendChild(new XMLElement('p', __('Select an XML-file to upload:')));
			$file = new XMlElement('label');
			$file->appendChild(Widget::Input('csv-file', null, 'file'));
			$fieldset->appendChild($file);
			$fieldset->appendChild(new XMLElement('p', __('Select a section as target:')));
			
			$sm = new SectionManager($this);
			$sections = $sm->fetch();
			$options  = array();
			foreach($sections as $section)
			{
				$options[] = array($section->get('id'), false, $section->get('name'));
			}
			$select = new XMLElement('label');
			$select->appendChild(Widget::Select('section', $options, array('class'=>'small')));
			$fieldset->appendChild($select);
			$fieldset->appendChild(new XMLElement('p', __('Don\'t worry, going to the next step won\'t import anything yet.')));
			$fieldset->appendChild(Widget::Input('import-step-2', __('Next step'), 'submit'));
			
			$this->Form->appendChild($fieldset);
			
			$fieldset = new XMLElement('fieldset', null, array('class'=>'right'));
			$fieldset->appendChild(new XMLElement('h2', __('Export CSV')));
			$fieldset->appendChild(new XMLElement('p', __('Please choose a section you wish to export to a CSV file.')));
			$select = new XMLElement('label');
			$select->appendChild(Widget::Select('section-export', $options, array('class'=>'small')));
			$fieldset->appendChild($select);
			$fieldset->appendChild(Widget::Input('export', __('Export CSV'), 'submit'));
			
			$this->Form->appendChild($fieldset);
		}
		
		
		
		private function __importStep2Page()
		{
			move_uploaded_file($_FILES['csv-file']['tmp_name'], $this->tmpFile);
			// Read the first line:
			if (($handle = fopen($this->tmpFile, "r")) !== false) {
				$data = fgetcsv($handle, 1000, ",");
				fclose($handle);
				
				$fieldset = new XMLElement('fieldset');
				$fieldset->appendChild(new XMLElement('h2', __('Select corresponding field')));
				$fieldset->appendChild(new XMLElement('p', __('Please select the corresponding field which should be used to populate the entry with:')));
				$table = new XMLElement('table', null, array('class'=>'import'));
				
				$sectionID = $_POST['section'];
				$sm = new SectionManager($this);
				$section = $sm->fetch($sectionID);
				$fields = $section->fetchFields();
				
				$options = array();
				$options[] = array(0, false, 'Don\'t use', 'dont-use');
				foreach($fields as $field)
				{
					$options[] = array($field->get('id'), false, $field->get('label'));
				}
				
				$i = 0;
				foreach($data as $key)
				{
					foreach($options as &$option)
					{
						$option[1] = false;
						if(strtolower($option[2]) == strtolower($key)) {
							$option[1] = true;
						}
					}
					$row = new XMLElement('tr');
					$row->appendChild(new XMLElement('th', $key));
					$row->appendChild(new XMLElement('td', '→'));
					$fieldSelector = new XMLElement('td');
					$fieldSelector->appendChild(Widget::Select('field-'.$i, $options, array('class'=>'small')));
					$row->appendChild($fieldSelector);
					$uniqueSelector = new XMLElement('td');
					$uniqueSelector->appendChild(new XMLElement('input', 'Unique', array('type'=>'checkbox', 'name'=>'unique-'.$i)));
					$row->appendChild($uniqueSelector);
					$table->appendChild($row);
					$i++;
				}
				
				$fieldset->appendChild($table);
				$fieldset->appendChild(new XMLElement('p', __('When a field is marked as \'unique\' special rules will apply if an entry with one ore more unique values already exists.')));
				$uniqueOptions = array();
				$uniqueOptions[] = array('default', false, 'Add new entry anyway (default)');
				$uniqueOptions[] = array('ignore', false, 'Do nothing (ignore)');
				$uniqueOptions[] = array('overwrite', false, 'Replace existing entry with new one (overwrite)');
				$label = new XMLElement('label', 'With unique fields:');
				$label->appendChild(Widget::Select('unique-action', $uniqueOptions, array('class'=>'small')));
				$fieldset->appendChild($label);
				$fieldset->appendChild(new XMLElement('input', ' Ignore first line', array('type'=>'checkbox', 'name'=>'ignore', 'checked'=>'checked')));
				$fieldset->appendChild(new XMLElement('input', null, array('type'=>'hidden', 'name'=>'section', 'value'=>$sectionID)));
				$fieldset->appendChild(new XMLElement('p', __('<br />Please double-check everything. Clicking on \'import\' will start the import process. There is no undo.')));
				$fieldset->appendChild(Widget::Input('import-step-3', __('Import'), 'submit'));
				
				$this->Form->appendChild($fieldset);
			} else {
				$this->Form->appendChild(new XMLElement('p', __('Something went horribly wrong!')));
			}
		}
		
		
		
		private function __importStep3Page()
		{
			// Store the entries:
			$sectionID		= $_POST['section'];
			$uniqueAction	= $_POST['unique-action'];
			$ignore			= isset($_POST['ignore']);
			$countNew		= 0;
			$countUpdated	= 0;
			$countIgnored	= 0;
			
			$fm = new FieldManager($this);
			
			// Read the CSV file:
			if (($handle = fopen($this->tmpFile, "r")) !== false) {
				if($ignore) {
					// Ignore the first line by reading it:
					fgetcsv($handle);
				}
				
				while (($data = fgetcsv($handle)) !== false) {
					// Store the data in a new entry:
					$entry = new Entry($this);
					$entry->set('section_id', $sectionID);
					$i = 0;
					$store = true;
					$new   = true;
					foreach($data as $value)
					{
						$associatedFieldID = $_POST['field-'.$i];
						$isUnique = isset($_POST['unique-'.$i]);
						if($associatedFieldID != 0)
						{
							// This value needs to be stored
							$field = $fm->fetch($associatedFieldID);
							$data = $field->processRawFieldData($value, $field->__OK__);
							$entry->setData($associatedFieldID, $data);
						}
						if($isUnique)
						{
							// This value is marked is unique. Check if there is an existing item in the database:
							$entryID = $this->__scanDatabase($value, $associatedFieldID);
							if($entryID != false)
							{
								// See what rules apply:							
								switch($uniqueAction)
								{
									case 'overwrite' :
										{
											// Overwrite the existing entry:
											$entry->set('id', $entryID);
											$new = false;
											break;
										}
									case 'ignore' :
										{
											// Don't store this entry:
											$store = false;
											break;
										}										
								}
							}
						} 
						$i++;					
					}
					// Store the entry:
					if($store)
					{
						$entry->commit();
						if($new)
						{
							$countNew++;
						} else {
							$countUpdated++;
						}
					} else {
						$countIgnored++;
					}
				}
				fclose($handle);
				// Import is complete, delete temporary file:
				unlink($this->tmpFile);
				// Show output message
				$this->Form->appendChild(new XMLElement('h2', __('Import Complete')));
				$this->Form->appendChild(new XMLElement('p', __('Newly added entries: '.$countNew)));
				$this->Form->appendChild(new XMLElement('p', __('Updated entries: '.$countUpdated)));
				$this->Form->appendChild(new XMLElement('p', __('Ignored entries: '.$countIgnored)));			
			} else {
				$this->Form->appendChild(new XMLElement('p', __('Cannot open file!')));
			}
		}
		
		
		/**
		 * Check to see if there exists an entry with a certain value and returns the ID of it.
		 * Note: This only works if the field-type stores it's data in a field called 'value'.
		 * @param	$value		string	The value to search for
		 * @param	$fieldID	int		The ID of the field.
		 * @return	mixed				The ID of the entry or false if no entry is found
		 */
		private function __scanDatabase($value, $fieldID)
		{
			$result = Symphony::Database()->fetch('DESCRIBE `tbl_entries_data_'.$fieldID.'`;');
			// print_r($result);
			foreach($result as $tableColumn)
			{
				if($tableColumn['Field'] == 'value')
				{
					$searchResult = Symphony::Database()->fetchVar('entry_id', 0, 'SELECT `entry_id` FROM `tbl_entries_data_'.$fieldID.'` WHERE `value` = \''.addslashes($value).'\';');
					return $searchResult;		
				}
			}			
			return false;
		}
		
		
		private function __exportPage()
		{
			$sectionID = $_POST['section-export'];
			$sm = new SectionManager($this);
			$em = new EntryManager($this);
			$section = $sm->fetch($sectionID);
			$fileName = $section->get('handle').'_'.date('Y-m-d').'.csv';
			$fields = $section->fetchFields();
			
			$headers = array();
			foreach($fields as $field)
			{
				$headers[] = '"'.str_replace('"', '""', $field->get('label')).'"';
			}
			
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="'.$fileName.'"');
			
			// Show the headers:
			echo implode(',', $headers)."\n";
			
			// Show the content:
			$entries = $em->fetch(null, $sectionID);
			foreach($entries as $entry)
			{
				$line = array();
				foreach($fields as $field)
				{
					$data = $entry->getData($field->get('id'));
					if(isset($data['value'])) {
						$value = $data['value'];
						// Delete line-endings:
						$value = str_replace(array("\n", "\r"), '', $value);
						$line[] = '"'.str_replace('"', '""', $value).'"';
					} else {
						$line[] = '';
					}
				}
				echo implode(',', $line)."\n";
			}
			die();
		}
		
		
		
	}
