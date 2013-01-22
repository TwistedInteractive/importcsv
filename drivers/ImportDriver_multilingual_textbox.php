<?php

/*
 * Import Driver
 *
 * Each field should have a specific class for import-export functions.
 * When none is found, the default fallback is this class.
 */

class ImportDriver_multilingual_textbox extends ImportDriver_default {

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_default()
    {
        $this->type = 'multilingual_textbox';
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value       The value to import
     * @param  $entry_id    If a duplicate is found, an entry ID will be provided.
     * @return The data returned by the field object
     */
    public function import($value, $entry_id = null)
    {
        // $languageCodes = $this->field->getSupportedLanguageCodes();
		$languageCodes = FLang::getLangs();
        $dataArr       = unserialize($value);
		if($dataArr == false) {
			// Content was not serialized, use this content for every language:
			$dataArr = array();
			foreach($languageCodes as $code)
			{
				$dataArr[$code] = $value;
			}
		}
        $newValue      = array();
        foreach($languageCodes as $code)
        {
            if(isset($dataArr[$code]))
            {
                $newValue[$code] = $dataArr[$code];
            }
        }
        $data = $this->field->processRawFieldData($newValue, $status, $message, false, $entry_id);
        return $data;
    }

    /**
     * Process the data so it can be exported to a CSV
     * @param  $data    The data as provided by the entry
     * @param  $entry_id    The ID of the entry that is exported
     * @return string   A string representation of the data to import into the CSV file
     */
    public function export($data, $entry_id = null)
    {
		$storeData = array();
        foreach($data as $key => $value)
        {
            if(substr($key, 0, 16) == 'value_formatted-')
            {
                $a = explode('-', $key);
                $storeData[$a[1]] = $value;
            }
        }
        return serialize($storeData);
    }

}
