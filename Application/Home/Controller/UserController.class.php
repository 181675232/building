<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
class UserController extends Controller {
    public function index(){
    	echo 'user index';
    }
    public function model(){
    	$user = new Model('admin');
    	var_dump($user->select());
    }
}