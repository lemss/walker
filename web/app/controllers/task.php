<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 15:50
 */

class Task extends XIN_Controller
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

    public function create()
    {
        $this->load->model(array('errors_model', 'card_task_model'));
        $result = new Errors_model();
        $post = $this->input->post();
        if (!isset($post['card_id']) || !isset($post['distance']) || !isset($post['deadline']) || !isset($post['amount'])) {
            echo $result->setCode(Errors_model::ERR_PARAM_MISSING)->toJson();
            return;
        }
        $result = $this->card_task_model->createTask($post);
        echo $result->toJson();
        die;
    }

    public function pay()
    {
        $result = new Errors_model();
        $post = $this->input->post();
        if (!isset($post['task_id'])) {
            echo $result->setCode(Errors_model::ERR_PARAM_MISSING);
            return;
        }
        $this->load->model('card_task_model');
        $updateRet = $this->card_task_model->pay($post['task_id']);
        if (!$updateRet) {
            echo $result->setCode(Errors_model::ERR_DB_ERROR)->toJson();
            return;
        }
        echo $result->toJson();
        return;
    }

    public function todo()
    {
        $result = new Errors_model();
        $this->load->model('card_task_model');
        $uid = $this->input->post('uid');
        $data = $this->card_task_model->todo($uid);
        echo $result->setData($data)->toJson();
        return;
    }

    public function expire()
    {
        $result = new Errors_model();
        $this->load->model('card_task_model');
        $uid = $this->input->post('uid');
        $data = $this->card_task_model->expire($uid);
        echo $result->setData($data)->toJson();
        return;
    }

    //获取已经付费的任务列表
    public function payed()
    {
        $this->load->model('card_task_model');
        $result = new Errors_model();
        $uid = $this->input->post('uid');
        //$data = array();
        $data = $this->card_task_model->payed($uid);
        echo $result->setData($data)->toJson();
        return;
    }

    public function detail()
    {
        $result = new Errors_model();
        $task_id = $this->input->post('task_id');
        if (empty($task_id)) {
            echo $result->setCode(Errors_model::ERR_PARAM_MISSING);
            return;
        }
        $this->load->model('card_task_model');
        $info = $this->card_task_model->getDetailById($task_id);
        echo $result->setData($info)->toJson();
    }
}