<?php
use Phalcon\Mvc\Model;

class Zsshortmessage extends Model{
    public $id;
    public $phone;
	public $code;
	public $send_time;
	public $smsId;
	public $create_time;
	public $pid;
	public $status;
	public $ratio;
}