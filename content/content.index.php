<?php
require_once(TOOLKIT .'/class.xsltpage.php');
require_once(TOOLKIT . '/class.administrationpage.php');
require_once(TOOLKIT . '/class.sectionmanager.php');
require_once(TOOLKIT . '/class.fieldmanager.php');
require_once(TOOLKIT . '/class.entrymanager.php');
require_once(TOOLKIT . '/class.entry.php');
require_once(EXTENSIONS . '/importcsv/lib/parsecsv-0.3.2/parsecsv.lib.php');

class contentExtensionImportcsvIndex extends AdministrationPage
{
    var $tmpFile;

    public function __construct(&$parent)
    {
        parent::__construct($parent);
        $this->setTitle('Symphony - Import / export CSV');
        $this->tmpFile = MANIFEST . '/tmp/importcsv.csv';
    }


    public function build()
    {
        parent::addStylesheetToHead(URL . '/extensions/importcsv/assets/importcsv.css', 'screen', 70);
        parent::addStylesheetToHead(URL . '/symphony/assets/forms.css', 'screen', 70);
        parent::build();
    }


    public function view()
    {
        if (isset($_POST['import-step-2']) && $_FILES['csv-file']['name'] != '') {
            // Import step 2:
            $this->__importStep2Page();
        } elseif (isset($_POST['import-step-3'])) {
            // Import step 3:
            $this->__importStep3Page();
        } elseif (isset($_REQUEST['export'])) {
            // Export:
            $this->__exportPage();
        } elseif (isset($_POST['ajax'])) {
            // Ajax import:
            $this->__ajaxImport();
        } else {
            // Startpage:
            $this->__indexPage();
        }
    }


    private function __indexPage()
    {
        // Create the XML for the page:
        $xml = new XMLElement('data');
        $sectionsNode = new XMLElement('sections');
        $sm = new SectionManager($this);
        $sections = $sm->fetch();
        foreach ($sections as $section)
        {
            $sectionsNode->appendChild(new XMLElement('section', $section->get('name'), array('id'=>$section->get('id'))));
        }
        $xml->appendChild($sectionsNode);

        // Generate the HTML:
        $xslt = new XSLTPage();
        $xslt->setXML($xml->generate());
        $xslt->setXSL(EXTENSIONS.'/importcsv/content/index.xsl', true);
        $this->Form->setValue($xslt->generate());
        $this->Form->setAttribute('enctype', 'multipart/form-data');
    }


    private function __importStep2Page()
    {
        move_uploaded_file($_FILES['csv-file']['tmp_name'], $this->tmpFile);
        $sectionID = $_POST['section'];

        // Generate the XML:
        $xml = new XMLElement('data', null, array('section-id'=>$sectionID));

        // Get the fields of this section:
        $fieldsNode = new XMLElement('fields');
        $sm = new SectionManager($this);
        $section = $sm->fetch($sectionID);
        $fields = $section->fetchFields();
        foreach ($fields as $field)
        {
            $fieldsNode->appendChild(new XMLElement('field', $field->get('label'), array('id'=>$field->get('id'))));
        }
        $xml->appendChild($fieldsNode);

        // Get the nodes provided by this CSV file:
        $csv = new parseCSV();
        $csv->auto($this->tmpFile);
        $csvNode = new XMLElement('csv');
        foreach ($csv->titles as $key)
        {
            $csvNode->appendChild(new XMLElement('key', $key));
        }
        $xml->appendChild($csvNode);

        // Generate the HTML:
        $xslt = new XSLTPage();
        $xslt->setXML($xml->generate());
        $xslt->setXSL(EXTENSIONS.'/importcsv/content/step2.xsl', true);
        $this->Form->setValue($xslt->generate());
    }


    private function __addVar($name, $value)
    {
        $this->Form->appendChild(new XMLElement('var', $value, array('class'=>$name)));
    }

    private function __importStep3Page()
    {
        // Store the entries:
        $sectionID = $_POST['section'];
        $uniqueAction = $_POST['unique-action'];
        $uniqueField  = $_POST['unique-field'];
        // $ignore			= isset($_POST['ignore']);
        $countNew = 0;
        $countUpdated = 0;
        $countIgnored = 0;
        $countOverwritten = 0;
        $fm = new FieldManager($this);
        $csv = new parseCSV();
        $csv->auto($this->tmpFile);

        // Load the information to start the importing process:
        $this->__addVar('section-id', $sectionID);
        $this->__addVar('unique-action', $uniqueAction);
        $this->__addVar('unique-field', $uniqueField);
        $this->__addVar('import-url', URL.'/symphony/extension/importcsv/');

        // Output the CSV-data:
        $csvData = $csv->data;
        $csvTitles = $csv->titles;
        $this->__addVar('total-entries', count($csvData));

        $count = 0;
        // Have to put it all in HTML to prevent memory issues with larger CSV-files.
        $html = '';
        foreach($csvData as $key => $data)
        {
            // $var = new XMLElement('var', null, array('class'=>'csv-'.$count));
            $html .= '<var class="csv-'.$count.'">';

            $i=0;
            foreach($data as $value)
            {
                $associatedFieldID = $_POST['field-' . $i];
                if($associatedFieldID != 0)
                {
                    $unique = $i == $uniqueField ? 'yes' : 'no';
                    // $var->appendChild(new XMLElement('var', $value, array('field'=>$associatedFieldID, 'unique'=>$unique)));
                    $html.= '<var field="'.$associatedFieldID.'" unique="'.$unique.'">'.$value.'</var>';
                }
                $i++;
            }
            $html .= '</var>';
            $count++;
        }
        $this->Form->appendChild(new XMLElement('div', $html));

        // $this->addHeaderToPage('Importing CSV...');
        $this->addScriptToHead(URL.'/extensions/importcsv/assets/import.js');

        $this->Form->appendChild(new XMLElement('h2', __('Import in progress...')));
        $this->Form->appendChild(new XMLElement('div', '<div class="bar"></div>', array('class'=>'progress')));
        $this->Form->appendChild(new XMLElement('div', null, array('class'=>'console')));
        


        /*
        foreach ($csv->data as $key => $data) {
            // Store the data in a new entry:
            $entry = new Entry($this);
            $entry->set('section_id', $sectionID);
            $i = 0;
            $store = true;
            $new = true;
            $update = false;
            foreach ($data as $value)
            {
                // If there is detected an update, do not process the next functions, for it will waste precious cpu power:
                if (!$update) {
                    $associatedFieldID = $_POST['field-' . $i];
                    $isUnique = isset($_POST['unique-' . $i]);

                    if ($associatedFieldID != 0) {
                        // This value needs to be stored
                        $field = $fm->fetch($associatedFieldID);
                        // Check if the field is of the type 'upload':
                        if ($field->get('type') == 'upload') {
                            $destination = $field->get('destination');
                            // Check if the file exists:
                            if (file_exists(DOCROOT . $destination . '/' . $value)) {
                                // File exists, create the link:
                                $filename = str_replace('/workspace/', '/', $destination) . '/' . str_replace($destination, '', $value);
                                // Check if there already exists an entry with this filename. If so, this entry will not be stored (filename must be unique)
                                $sql = 'SELECT COUNT(*) AS `total` FROM `tbl_entries_data_' . $associatedFieldID . '` WHERE `file` = \'' . $filename . '\';';
                                $total = Symphony::Database()->fetchVar('total', 0, $sql);
                                // echo $filename.': '.$total.'<br />';
                                // echo $total;
                                if ($total == 0) {
                                    // echo $total;
                                    $fileData = $field->processRawFieldData($value, $field->__OK__);
                                    $fileData['file'] = $filename;
                                    $fileData['size'] = filesize(DOCROOT . $destination . '/' . $value);
                                    $fileData['mimetype'] = mime_content_type(DOCROOT . $destination . '/' . $value);
                                    $fileData['meta'] = serialize($field->getMetaInfo(DOCROOT . $destination . '/' . $value, $fileData['mimetype']));
                                    $entry->setData($associatedFieldID, $fileData);
                                } else {
                                    // File already exists, don't store:
                                    $store = false;

                                }
                            }
                        } elseif ($field->get('type') == 'select') {
                            $value = $field->processRawFieldData(explode(',', $value), $field->__OK__);
                            $entry->setData($associatedFieldID, $value);
                        } elseif ($field->get('type') == 'selectbox_link') {
                            // Import selectbox link:
                            // Get the correct ID of the related fields
                            $related_field = Symphony::Database()->fetchVar('related_field_id', 0, 'SELECT `related_field_id` FROM `tbl_fields_selectbox_link` WHERE `field_id` = ' . $field->get('id'));
                            $v = $field->processRawFieldData(explode(',', $value), $field->__OK__);
                            $related_ids = array('relation_id'=>array());
                            foreach ($v['relation_id'] as $key => $value)
                            {
                                $related_ids['relation_id'][] = Symphony::Database()->fetchVar('entry_id', 0, 'SELECT `entry_id` FROM `tbl_entries_data_' . $related_field . '` WHERE `value` = \'' . trim($value) . '\';');
                            }
                            $entry->setData($associatedFieldID, $related_ids);
                        } else {
                            $fieldData = $field->processRawFieldData($value, $field->__OK__);
                            $entry->setData($associatedFieldID, $fieldData);
                        }

                    }


                    if ($isUnique) {
                        // This value is marked is unique. Check if there is an existing item with this value in the database:
                        $entryID = $this->__scanDatabase($value, $associatedFieldID);
                        if ($entryID != false) {
                            // See what rules apply:
                            switch ($uniqueAction)
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
                                case 'update' :
                                    {
                                    // Update this entry:
                                    $update = true;
                                    $store = false;
                                    break;
                                    }
                            }
                        }
                    }
                }
                $i++;
            }

            // Store the entry:
            if ($store) {
                $entry->commit();
                if ($new) {
                    $countNew++;
                } else {
                    $countOverwritten++;
                }
            } else {
                if ($update) {
                    // Update the entry:
                    // Get the original entry:
                    $em = new EntryManager($this);
                    $entry = $em->fetch($entryID);
                    $entry = $entry[0];

                    $i = 0;
                    // Get the original data and update it:
                    foreach ($data as $value)
                    {
                        $associatedFieldID = $_POST['field-' . $i];
                        if ($associatedFieldID != 0) {
                            // $fieldData = $entry->getData($associatedFieldID);
                            $field = $fm->fetch($associatedFieldID);
                            if(is_array($value))
                            {
                                $newValue = array();
                                foreach($value as $v)
                                {
                                    $newValue[] = trim($v);
                                }
                                $value = $newValue;
                            } else {
                                $value = trim($value);
                            }
                            $fieldData = $field->processRawFieldData($value, $field->__OK__, false, $entryID);
                            $entry->setData($associatedFieldID, $fieldData);
                        }
                        $i++;
                    }
                    $entry->commit();

                    $countUpdated++;
                } else {
                    $countIgnored++;
                }
            }
        }
        // Import is complete, delete temporary file:
        unlink($this->tmpFile);
        // Show output message
        $this->Form->appendChild(new XMLElement('h2', __('Import Complete')));
        $this->Form->appendChild(new XMLElement('p', __('Newly added entries: ' . $countNew)));
        $this->Form->appendChild(new XMLElement('p', __('Updated entries: ' . $countUpdated)));
        $this->Form->appendChild(new XMLElement('p', __('Overwritten entries: ' . $countOverwritten)));
        $this->Form->appendChild(new XMLElement('p', __('Ignored entries: ' . $countIgnored)));
        */

    }


    /**
     * Check to see if there exists an entry with a certain value and returns the ID of it.
     * Note: This only works if the field-type stores it's data in a field called 'value'.
     * @param    $value        string    The value to search for
     * @param    $fieldID    int        The ID of the field.
     * @return    mixed                The ID of the entry or false if no entry is found
     */
    private function __scanDatabase($value, $fieldID)
    {
        $result = Symphony::Database()->fetch('DESCRIBE `tbl_entries_data_' . $fieldID . '`;');
        foreach ($result as $tableColumn)
        {
            if ($tableColumn['Field'] == 'value') {
                $searchResult = Symphony::Database()->fetchVar('entry_id', 0, 'SELECT `entry_id` FROM `tbl_entries_data_' . $fieldID . '` WHERE `value` = \'' . addslashes(trim($value)) . '\';');
                return $searchResult;
            }
        }
        return false;
    }


    private function getDrivers()
    {
        $classes = glob(EXTENSIONS.'/importcsv/drivers/*.php');
        $drivers = array();
        foreach($classes as $class)
        {
            include_once($class);
            $a = explode('_', str_replace('.php', '', basename($class)));
            $driverName = '';
            for($i = 1; $i < count($a); $i++)
            {
                if($i > 1)
                {
                    $driverName .= '_';
                }
                $driverName .= $a[$i];
            }
            $className = 'ImportDriver_'.$driverName;
            $drivers[$driverName] = new $className;
        }
        return $drivers;
    }

    private function __ajaxImport()
    {
        // Load the drivers:
        $drivers = $this->getDrivers();

        // Load the fields:
        $sectionID     = $_POST['section-id'];
        $uniqueAction  = $_POST['unique-action'];
        $uniqueField   = $_POST['unique-field'];
        $uniqueFound   = false;
        $uniqueID      = 0;
        $uniqueValue   = 0;
        $messageSuffix = '';

        // Load the fieldmanager:
        $fm = new FieldManager($this);

        // Start by creating a new entry:
        $entry = new Entry($this);
        $entry->set('section_id', $sectionID);

        // Get the post values:
        $i = 0;
        foreach($_POST as $key => $value)
        {
            if(substr($key, 0, 6) == 'field-')
            {
                $a = explode('-', $key);
                if(count($a) == 2)
                {
                    $fieldID = intval($a[1]);
                    $field = $fm->fetch($fieldID);
                    // Get the corresponding field-type:
                    $type = $field->get('type');
                    if(isset($drivers[$type]))
                    {
                        $drivers[$type]->setField($field);
                        $data = $drivers[$type]->import($value);
                    } else {
                        $drivers['default']->setField($field);
                        $data = $drivers['default']->import($value);
                    }
                    // Set the data:
                    if($data != false)
                    {
                        $entry->setData($fieldID, $data);
                    }
                    // Check for an update:
                    if($uniqueField == $i && $uniqueFound == false)
                    {
                        $uniqueFound = true;
                        $uniqueID    = $fieldID;
                        $uniqueValue = $value;
                    }
                } else {
                    die(__('[ERROR: No field id sent for: "'.$value.'"]'));
                }
                $i++;
            }
        }

        if($uniqueFound)
        {
            // Update? Ignore? Add new?
            switch($uniqueAction)
            {
                case 'update' :
                    {
                        // See if there is an entry with this value:
                        $entryID = $this->__scanDatabase($uniqueValue, $uniqueID);
                        if($entryID != false)
                        {
                            $entry->set('id', $entryID);
                            $messageSuffix = ' updated entry: '.$entryID;
                        }
                        break;
                    }
                case 'ignore' :
                    {
                        die(__('[DUPLICATE: IGNORED]'));
                        break;
                    }
            }
        }

        // Store the entry:
        $entry->commit();

        // When the script gets here, it means everything has worked out fine!
        die('[OK]'.$messageSuffix);
    }


    private function __exportPage()
    {
        // Load the drivers:
        $drivers = $this->getDrivers();

        // Get the fields of this section:
        $sectionID = $_REQUEST['section-export'];
        $sm = new SectionManager($this);
        $em = new EntryManager($this);
        $section = $sm->fetch($sectionID);
        $fileName = $section->get('handle') . '_' . date('Y-m-d') . '.csv';
        $fields = $section->fetchFields();

        $headers = array();
        foreach ($fields as $field)
        {
            $headers[] = '"' . str_replace('"', '""', $field->get('label')) . '"';
        }

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        // Show the headers:
        echo implode(',', $headers) . "\n";

        // Show the content:
        $entries = $em->fetch(null, $sectionID);
        foreach ($entries as $entry)
        {
            $line = array();
            foreach ($fields as $field)
            {
                $data = $entry->getData($field->get('id'));
                $type = $field->get('type');
                if(isset($drivers[$type]))
                {
                    $drivers[$type]->setField($field);
                    $value = $drivers[$type]->export($data);
                } else {
                    $drivers['default']->setField($field);
                    $value = $drivers['default']->export($data);
                }
                $line[] = '"' . str_replace('"', '""', $value) . '"';

                /*
                if (isset($data['value'])) {
                    // Just get the value
                    $value = $data['value'];
                    // Delete line-endings:
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $value = str_replace(array("\n", "\r"), '', $value);
                    $line[] = '"' . str_replace('"', '""', $value) . '"';
                } elseif (isset($data['file'])) {
                    // This is a file upload field
                    $line[] = '"' . str_replace('"', '""', $data['file']) . '"';
                } elseif (isset($data['relation_id'])) {
                    // Selectbox link? Or another extension which uses 'relation_id'?
                    switch ($field->get('type'))
                    {
                        case 'selectbox_link' :
                            {
                            // Get the correct values of the related field
                            $related_field = Symphony::Database()->fetchVar('related_field_id', 0, 'SELECT `related_field_id` FROM `tbl_fields_selectbox_link` WHERE `field_id` = ' . $field->get('id'));
                            if (!is_array($data['relation_id'])) {
                                $data['relation_id'] = array($data['relation_id']);
                            }
                            $related_values = array();
                            foreach ($data['relation_id'] as $relation_id)
                            {
                                $value = Symphony::Database()->fetch('SELECT * FROM `tbl_entries_data_' . $related_field . '` WHERE `entry_id` = ' . $relation_id . ';');
                                if (isset($value[0]['value'])) {
                                    $related_values[] = str_replace('"', '""', $value[0]['value']);
                                } else {
                                    // Fallback to empty value:
                                    $related_values[] = '';
                                }
                            }

                            $line[] = '"'.implode(',', $related_values).'"';
                            break;
                            }
                        default :
                            {
                            // Fallback to empty value:
                            $line[] = '';
                            break;
                            }
                    }
                } else {
                    // Fallback to empty value:
                    $line[] = '';
                }
                */
            }
            echo implode(',', $line) . "\n";
        }
        die();
    }


}

