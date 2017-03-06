<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 18:35
 */

class Website extends XIN_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $act = strtolower($this->uri->segments[2]);
        $this->load->model('errors_model');
        if($act == 'login' || $act == 'reg'  || $act == 'about' || $act == 'rank') return;
        $this->validLogin();
    }
    //首页
    public function index()
    {
        $result = new Errors_model();
        $this->load->model('card_task_model');
        $uid = $_COOKIE['uid'];
        $data = $this->card_task_model->todo($uid);
        $ret = $result->setData($data)->toArray();
        $this->render('index.html',$ret);
    }
    //登录
    public function login()
    {
        $this->load->model('user_model');
        $result = $this->user_model->verify($_COOKIE['uid'], $_COOKIE['token']);
        if ($result->getCode() == Errors_model::SUCC) {
            header('location:/website/index');
            exit();
        }
        
        $this->render('login.html',[]);
    }
    //注册
    public function reg()
    {
        $this->render('register.html',[]);
    }
    //创建任务
    public function add()
    {
        $this->load->model('card_model');
        $data = $this->card_model->getCardList();
        $result = new Errors_model();
        $aa = $result->setData($data)->toArray();
        $this->render('add.html', $aa);
    }
    //任务详情
    public function detail()
    {
        $result = new Errors_model();
        $task_id = $this->input->get('id');
        if (empty($task_id)) {
            header('location:/website/index');
            exit();
            //echo $result->setCode(Errors_model::ERR_PARAM_MISSING);
            //return;
        }
        $this->load->model('card_task_model');
        $info = $this->card_task_model->getDetailById($task_id);
        $ret = $result->setData($info)->toArray();
        $this->render('taskinfo.html', $ret);
    }
    //获取所有任务列表
    public function all()
    {
        $result = new Errors_model();
        $this->load->model('card_task_model');
        $uid = $_COOKIE['uid'];
        $data = $this->card_task_model->todo($uid);
        $ret['todo'] = $result->setData($data)->toArray()['data'];
        
        $data = $this->card_task_model->expire($uid);
        $ret['expire'] = $result->setData($data)->toArray()['data'];
        
        $this->render('tasks.html',$ret);
    }
    //付出回报
    public function gain()
    {
        $this->load->model('card_task_model');
        $result = new Errors_model();
        $uid = $_COOKIE['uid'];
        //$data = array();
        $data = $this->card_task_model->payed($uid);
        
        $ret['payed'] = $result->setData($data)->toArray()['data'];
        
        //TODO:回报接口
        
        $this->load->model('task_reward_model');
        $rewardList = $this->task_reward_model->listAll($uid);
        $result = new Errors_model();
        $ret['gain'] = $result->setData($rewardList)->toArray()['data'];
        
        if(!$ret['payed']['total']) $ret['payed']['total'] = 0;
        if(!$ret['gain']['total']) $ret['gain']['total'] = 0;
        
        $this->render('gain.html', $ret);
    }
    public function about()
    {
        $this->render('about.html', []);
    }
    
    public function logout()
    {
        setcookie('uid','');
        setcookie('token','');
        header('location:/website/login');
        exit();
    }
    
    public function rank()
    {
        $this->render('rank.html', []);
    }
    
    public function validLogin()
    {
        $this->load->model('user_model');
        $result = $this->user_model->verify($_COOKIE['uid'], $_COOKIE['token']);
        if ($result->getCode() != Errors_model::SUCC) {
            header('location:/website/login');
            exit();
        }
    }
}