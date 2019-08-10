<?php
use Phalcon\Mvc\Model;
use app\core\Pdb;

class Zscardclick extends Model{
    public $id;
    public $u_id;
    public $c_id;
	public $status;
	public $create_time;
	public $card_user_id;
}