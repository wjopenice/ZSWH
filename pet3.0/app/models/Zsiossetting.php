<?php
use Phalcon\Mvc\Model;

class Zsiossetting extends Model{
    public $id;
    public $version_number;
	public $version_name;
	public $update_content;
	public $is_forced_update;
}