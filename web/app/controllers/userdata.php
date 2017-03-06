<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 15:53
 */

class Userdata extends XIN_Controller
{
    public function __construct()
    {
        parent::__construct();
        $post = $this->input->post();
        $post = $this->input->get() ? $this->input->get() : $this->input->post();
        $this->load->model(array('user_model', 'errors_model'));
        $result = $this->user_model->verify($post['uid'], $post['token']);
        if ($result->getCode() != Errors_model::SUCC) {
            exit($result->toJson());
        }
    } 
    public function upload()
    {
        $post = $this->input->get() ? $this->input->get() : $this->input->post();
        $result = new Errors_model();
        $this->load->model('userdata_model');
        $this->userdata_model->upload($post);
        //var_dump($post);
        echo $result->toJson();
        return;
    }
}