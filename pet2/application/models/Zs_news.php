<?php
use Phalcon\Mvc\Model;

class Zs_news extends Model{
    public $id;
    public $title;
    public $author;
    public $create_time;
    public $click;
    public $pic;
    public $content;
    public $type;
}