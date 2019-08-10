<?php
use Phalcon\Mvc\Model;

class Zscardreply extends Model{
    public $id;
    public $feedback_id;
    public $content;
    public $create_time;
    public $user_id;
	public $floor_id;
	public $feedback_user_id;
	public $c_id;
}