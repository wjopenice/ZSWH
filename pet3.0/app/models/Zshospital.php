<?php
use Phalcon\Mvc\Model;
use app\core\Pdb;

class Zshospital extends Model{
    public $h_id;
    public $name;
	public $info;
	public $tel;
	public $himg;
	public $lbs;
	public $longitude;
	public $latitude;
	public $merc_type;
    public $scale;
    public $pet_r_num;
    public $receiver_cid;
    public $business_license;
    public $chain;
    public $sphere;
    public $doctors_cid;
}