<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 15:49
 */

class User extends XIN_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function register()
    {
        $mobile = $this->input->post('mobile');
        $password = $this->input->post('password');
        $this->load->model('user_model');
        $data = $this->user_model->register($mobile, $password);
        echo $data->toJson();
    }

    public function login()
    {
        $mobile = $this->input->post('mobile');
        $password = $this->input->post('password');
        $this->load->model('user_model');
        $data = $this->user_model->login($mobile, $password);
        //var_dump($data);
        echo $data->toJson();
    }

    public function verify()
    {
        $uid = $this->input->post('uid');
        $token = $this->input->post('token');
        $this->load->model(array('user_model', 'errors_model'));
        $error = new Errors_model();
        $result = $this->user_model->verify($uid, $token);
        echo $result->toJson();
        return;
    }
}