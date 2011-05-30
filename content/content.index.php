<?php
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

        $fieldset = new XMLElement('fieldset', null, array('class' => 'left'));
        $fieldset->appendChild(new XMLElement('h2', __('Import CSV')));
        $fieldset->appendChild(new XMLElement('p', __('Select a CSV file to upload:')));

        $file = new XMlElement('label');
        $file->appendChild(Widget::Input('csv-file', null, 'file'));
        $fieldset->appendChild($file);

        $fieldset->appendChild(new XMLElement('p', __('Select a section as target:')));

        $sm = new SectionManager($this);
        $sections = $sm->fetch();
        $options = array();
        foreach ($sections as $section)
        {
            $options[] = array($section->get('id'), false, $section->get('name'));
        }
        $select = new XMLElement('label');
        $select->appendChild(Widget::Select('section', $options, array('class' => 'small')));
        $fieldset->appendChild($select);
        $fieldset->appendChild(new XMLElement('p', __('Don\'t worry, going to the next step won\'t import anything yet.')));
        $fieldset->appendChild(Widget::Input('import-step-2', __('Next step'), 'submit'));

        $this->Form->appendChild($fieldset);

        $fieldset = new XMLElement('fieldset', null, array('class' => 'right'));
        $fieldset->appendChild(new XMLElement('h2', __('Export CSV')));
        $fieldset->appendChild(new XMLElement('p', __('Please choose a section you wish to export to a CSV file.')));
        $select = new XMLElement('label');
        $select->appendChild(Widget::Select('section-export', $options, array('class' => 'small')));
        $fieldset->appendChild($select);
        $fieldset->appendChild(Widget::Input('export', __('Export CSV'), 'submit'));
        $fieldset->appendChild(new XMLElement('p', __('<br /><strong>Tip:</strong> You can also create a direct link to export a certain section by creating a link like <code>/symphony/extension/importcsv/?export&amp;section-export=9</code>, where <code>section-export</code> is the ID of the section you wish to export. In combination with the <a href="https://github.com/makenosound/publish_shortcuts">publish shortcuts extension</a> this can be a great addition to your clients\' site.')));
        $this->Form->appendChild($fieldset);
    }


    private function __importStep2Page()
    {
        move_uploaded_file($_FILES['csv-file']['tmp_name'], $this->tmpFile);

        $csv = new parseCSV();
        $csv->auto($this->tmpFile);

        $fieldset = new XMLElement('fieldset');
        $fieldset->appendChild(new XMLElement('h2', __('Select corresponding field')));
        $fieldset->appendChild(new XMLElement('p', __('Please select the corresponding field which should be used to populate the entry with:')));
        $table = new XMLElement('table', null, array('class' => 'import'));

        $sectionID = $_POST['section'];
        $sm = new SectionManager($this);
        $section = $sm->fetch($sectionID);
        $fields = $section->fetchFields();

        $options = array();
        $options[] = array(0, false, 'Don\'t use', 'dont-use');
        foreach ($fields as $field)
        {
            $options[] = array($field->get('id'), false, $field->get('label'));
        }

        $i = 0;
        foreach ($csv->titles as $key)
        {
            foreach ($options as &$option)
            {
                $option[1] = false;
                if (strtolower($option[2]) == strtolower($key)) {
                    $option[1] = true;
                }
            }
            $row = new XMLElement('tr');
            $row->appendChild(new XMLElement('th', $key));
            $row->appendChild(new XMLElement('td', '→'));
            $fieldSelector = new XMLElement('td');
            $fieldSelector->appendChild(Widget::Select('field-' . $i, $options, array('class' => 'small')));
            $row->appendChild($fieldSelector);
            $uniqueSelector = new XMLElement('td');
            $uniqueSelector->appendChild(new XMLElement('input', 'Unique', array('type' => 'checkbox', 'name' => 'unique-' . $i)));
            $row->appendChild($uniqueSelector);
            $table->appendChild($row);
            $i++;
        }

        $fieldset->appendChild($table);
        $fieldset->appendChild(new XMLElement('p', __('To import fields of the type <em>\'upload field\'</em>, make sure the filename used in your CSV is the same as the file you wish to import. Also, the file you wish to import should already be placed manually in the correct folder (which is the folder you picked as destination folder for the field).')));
        $fieldset->appendChild(new XMLElement('p', __('When a field is marked as \'unique\' special rules will apply if an entry with one ore more unique values already exists.')));
        $uniqueOptions = array();
        $uniqueOptions[] = array('default', false, __('Add new entry anyway (default)'));
        $uniqueOptions[] = array('update', false, __('Update existing value (update)'));
        $uniqueOptions[] = array('ignore', false, __('Do nothing (ignore)'));
        $uniqueOptions[] = array('overwrite', false, __('Replace existing entry with new one (overwrite)'));
        $label = new XMLElement('label', 'With unique fields:');
        $label->appendChild(Widget::Select('unique-action', $uniqueOptions, array('class' => 'small')));
        $fieldset->appendChild($label);
        $fieldset->appendChild(new XMLElement('input', null, array('type' => 'hidden', 'name' => 'section', 'value' => $sectionID)));
        $fieldset->appendChild(new XMLElement('p', __('<br />Please double-check everything. Clicking on \'import\' will start the import process. There is no undo.')));
        $fieldset->appendChild(new XMLElement('p', __('<p><em>The use of this software is at your own risk. This software is licenced under the <a href="http://en.wikipedia.org/wiki/MIT_License" target="_blank">MIT Licence</a>.</em></p>')));
        $fieldset->appendChild(Widget::Input('import-step-3', __('Import'), 'submit'));

        $this->Form->appendChild($fieldset);
    }


    private function __importStep3Page()
    {
        // Store the entries:
        $sectionID = $_POST['section'];
        $uniqueAction = $_POST['unique-action'];
        // $ignore			= isset($_POST['ignore']);
        $countNew = 0;
        $countUpdated = 0;
        $countIgnored = 0;
        $countOverwritten = 0;
        $fm = new FieldManager($this);
        $csv = new parseCSV();
        $csv->auto($this->tmpFile);

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
                            $data = $field->processRawFieldData(explode(',', $value), $field->__OK__);
                            $entry->setData($associatedFieldID, $data);
                        } elseif ($field->get('type') == 'selectbox_link') {
                            // Import selectbox link:
                            // Get the correct ID of the related fields
                            $related_field = Symphony::Database()->fetchVar('related_field_id', 0, 'SELECT `related_field_id` FROM `tbl_fields_selectbox_link` WHERE `field_id` = ' . $field->get('id'));
                            $data = $field->processRawFieldData(explode(',', $value), $field->__OK__);
                            $related_ids = array('relation_id'=>array());
                            foreach ($data['relation_id'] as $key => $value)
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
                            $fieldData = $field->processRawFieldData(trim($value), $field->__OK__, false, $entryID);
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


    private function __exportPage()
    {
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
            }
            echo implode(',', $line) . "\n";
        }
        die();
    }


}

if (!function_exists('mime_content_type')) {

    function mime_content_type($filename)
    {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.', $filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}
