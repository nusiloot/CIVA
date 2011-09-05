<?php

class csvFile 
{
  private $file = null;
  private $separator = null;
  private $csvdata = null;
  private $ignore = null;

  public function __construct($file, $ignore_first_if_comment = 1) {
    $this->ignore = $ignore_first_if_comment;
    if (!file_exists($file))
      throw new Exception("Cannont access $file");
    $this->file = $file;
    $handle = fopen($this->file, 'r');
    if (!$handle)
      throw new Exception('invalid_file');
    $buffer = fread($handle, 500);
    fclose($handle);
    $buffer = preg_replace('/$[^\n]*\n/', '', $buffer);
    if (!$buffer) {
      throw new Exception('invalid_file');
    }
    if (!preg_match('/("?)[0-9]{10}("?)([,;\t])/', $buffer, $match)) {
      throw new Exception('invalid_csv_file');
    }
    $this->separator = $match[3];
  }

  public function getCsv() {
    if ($this->csvdata)
      return $this->csvdata;

    $handler = fopen($this->file, 'r');
    if (!$handler)
      throw new Exception('Cannot open csv file anymore');
    $this->csvdata = array();
    while (($data = fgetcsv($handler, 0, $this->separator)) !== FALSE) {
      $this->csvdata[] = $data;
    }
    fclose($handler);
    if ($this->ignore && !preg_match('/^\d{10}$/', $this->csvdata[0][0]))
      array_shift($this->csvdata);
    return $this->csvdata;
  }
}