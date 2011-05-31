<?php

/*
 * Import Driver for type: upload
 */

class ImportDriver_upload extends ImportDriver_default
{


    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_upload()
    {
        $this->type = 'upload';
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value   The value to import
     * @return The data returned by the field object
     */
    public function import($value)
    {
        $destination = $this->field->get('destination');
        // Check if the file exists:
        if (file_exists(DOCROOT . $destination . '/' . $value)) {
            // File exists, create the link:
            $filename = str_replace('/workspace/', '/', $destination) . '/' . str_replace($destination, '', $value);
            // Check if there already exists an entry with this filename. If so, this entry will not be stored (filename must be unique)
            $sql = 'SELECT COUNT(*) AS `total` FROM `tbl_entries_data_' . $this->field->get('id') . '` WHERE `file` = \'' . $filename . '\';';
            $total = Symphony::Database()->fetchVar('total', 0, $sql);
            // echo $filename.': '.$total.'<br />';
            // echo $total;
            if ($total == 0) {
                // echo $total;
                $fileData = $this->field->processRawFieldData($value, $this->field->__OK__);
                $fileData['file'] = $filename;
                $fileData['size'] = filesize(DOCROOT . $destination . '/' . $value);
                $fileData['mimetype'] = mime_content_type(DOCROOT . $destination . '/' . $value);
                $fileData['meta'] = serialize($this->field->getMetaInfo(DOCROOT . $destination . '/' . $value, $fileData['mimetype']));
                // $entry->setData($associatedFieldID, $fileData);
                return $fileData;
            } else {
                // File already exists, don't store:
                return false;
            }
        }
        return false;
    }

    /**
     * Process the data so it can be exported to a CSV
     * @param  $data    The data as provided by the entry
     * @return string   A string representation of the data to import into the CSV file
     */
    public function export($data)
    {
        return ($data['file']);
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

