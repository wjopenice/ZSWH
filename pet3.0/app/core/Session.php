<?php
namespace app\core;
class Session{
	
	public function set($key,$value){
		$_SESSION[$key] = $value;
	}
	
	public function get($key){
		return $_SESSION[$key];
	}
	
	public function has($key){
		if(!empty($_SESSION[$key])){
			return true;
		}else{
			return false;
		}
	} 
	
	public function remove($key){
		unset($_SESSION[$key]);
	}
	
	public function destroy(){
		session_destroy();
	}
	
	
}
