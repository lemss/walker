<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 15:53
 */

class Reward extends XIN_Controller
{
    public function __construct()
    {
        parent::__construct();
        $post = $this->input->post();
        $this->load->model(array('user_model', 'errors_model'));
        $result = $this->user_model->verify($post['uid'], $post['token']);
        if ($result->getCode() != Errors_model::SUCC) {
            exit($result->toJson());
        }
    }
    public function listAll()
    {
        $post = $this->input->post();
        $this->load->model('task_reward_model');
        $rewardList = $this->task_reward_model->listAll($post['uid']);
        $result = new Errors_model();
        echo $result->setData($rewardList)->toJson();
        return;
    }
}