<?php
/*
============================================================
contains  : class FileUpload
version   : 1.3.0.0

============================================================

last addition :

* if '->upload_path' doesn't exist, it is created!

* maximum file size : ->max_file_size

* files successfully uploaded property : ->files_uploaded

* default chmod property : ->chmod

============================================================

If you want to check if it's really, really an image...
    $imginfo = getimagesize($uploadedfile);
    switch ($imginfo[2]) {
      case 1: // gif
      case 2: // jpg
      case 3: // png
      case 4: // swf
      default: // not an image
    }

*/

class FileUpload {
  // ======== Private ========
  private $_is_error = false;       // error flag
  private $_errors = null;          // array of errors encountered during upload

  // ======== Public ========
  public $overwrite = false;        // allow file overwrites
  public $upload_path = '';       // path to upload to
  public $chmod = null;           // default chmod value for the uploaded files (once uploaded, they will be chmod-ed)

  public $files_uploaded = null;    // contains a list of files that uploaded successfully

  public $valid_extensions = null;  // array of valid file extensions (format: '.xyz')
  public $valid_mime_types = null;  // array of valid mime types (format: 'abcd/xyz')
  public $max_file_size = null;   // maximum size in bytes of the uploaded files

  // ======== CONSTRUCTOR / DESTRUCTOR ========
  function __construct($upload_path = null) {
    if ($upload_path) { $this->upload_path = $upload_path; }
  } // / FileUpload()

  // ======== Methods ========

 /**
  * close object function
  */
  function close() {
  } // /->close()

  /**
   * function to upload
   * @param array $files  form-post file data array to upload (e.g. $_FILES['input_name'] )
  */
  function upload($files = null) {
    $this->_errors = null;
    $this->files_uploaded = null;

    if (!$files) {
      $this->_errors[] = 'No file data given';
      exit;
    }

    if (!$this->upload_path) {
      $this->_errors[] = 'No upload path set';
      exit;
    }

    foreach ($files['name'] as $k => $v) {
      if ($files['size'][$k]) {
        // Clear any crap from the file name
        $filename = preg_replace('/[^a-z0-9._]/', '', $v);
        $filename = str_replace(' ', '_', $filename);
        $filename = str_replace('%20', '_', $filename);
        $filename = strtolower($filename);

        $file_ext = strrchr($filename,'.');

        $file_error = false;

        if ( ($this->max_file_size) && ($files['size'][$k]>$this->max_file_size) ) {
          $file_error = true;
          $this->_errors[] = "Error uploading '{$files['name' ][$k]}' : File exceeded the maximum upload size: {$this->max_file_size} bytes";
        }

        if ( ($this->valid_extensions) && (!in_array($file_ext,$this->valid_extensions)) ) {
          $file_error = true;
          $this->_errors[] = "Error uploading '{$files['name' ][$k]}' : You can only upload files with the extension(s) ". implode(', ',$this->valid_extensions);
        }

        if ( ($this->valid_mime_types) && (!in_array($files['type'][$k],$this->valid_mime_types)) ) {
          $file_error = true;
          $this->_errors[] = "Error uploading '{$files['name'][$k]}' of type '{$files['type'][$k]}' : You can only upload files with the mime type(s) ". implode(', ',$this->valid_mime_types);
        }

        if ( ($this->overwrite) && (file_exists("{$this->upload_path}{$files['name'][$k]}")) ) {
          $file_error = true;
          $this->_errors[] = "Error uploading '{$files['name'][$k]}' : File already exists";
        }

        if (!is_dir($this->upload_path)) {
          mkdir($this->upload_path);
        }

        if (!$file_error) {
          if ( (!move_uploaded_file($files['tmp_name'][$k], "{$this->upload_path}{$files['name'][$k]}")) ) {
            $this->_errors[] = "Error uploading '{$files['name'][$k]}' : Could not move the file from the upload directory";
          } else {
            $this->files_uploaded[] = $files['name'][$k];
          }

          @unlink($files['tmp_name'][$k]);
          if ($this->chmod) {
            if (!chmod("{$this->upload_path}{$files['name'][$k]}",$this->chmod)) {
              $this->_errors[] = "Error uploading '{$files['name'][$k]}' : Could not move the file from the upload directory";
            }
          }
        }
      }
    }
  } // /->upload()

/**
 * Function to check if it is an error
 * @return boolean
 */
  function is_error() {
    return is_array($this->_errors);
  } // /->is_error()

/**
 * Function to get the errors
 * @return mixed
 */
  function get_errors() {
    return $this->_errors;
  } // /->get_errors()

} // / class FileUpload

?>
