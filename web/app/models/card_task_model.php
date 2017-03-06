<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 15:38
 */

class Card_task_model extends XIN_Model
{
    protected static $TABLE = 'card_task';

    const TASK_PAY_STATUS_NOPAY = 1;
    const TASK_PAY_STATUS_PAYED = 2;

    const TASK_STATUS_NO_START = 1;
    const TASK_STATUS_IS_GOING = 2;
    const TASK_STATUS_STOP_FINISHED = 3;
    const TASK_STATUS_STOP_PART_FINISHED = 4;
    const TASK_STATUS_STOP_NO_START = 5;
    const TASK_STATUS_STOP_BUT_CONFIRM = 6; //结束但是待确认

    public function __construct()
    {
        parent::__construct('card_task', 'task_id', 'xin_walker');
        $this->load->model('errors_model');
    }

    public function createTask($params)
    {
        $this->load->model('card_model');
        $result = new Errors_model();
        if (!isset($params['card_id']) || !isset($params['distance']) || !isset($params['amount']) || !isset($params['deadline']))
        {
            return $result->setCode(Errors_model::ERR_PARAM_MISSING);
        }
        if ($this->card_model->checkCardId($params['card_id'])) {
            return $result->setCode(Errors_model::ERR_CARD_INVALID);
        }
        $deadline = strtotime($params['deadline'] . ' 23:59:59');
        if ($deadline <= time()) {
            //return $result->setCode(Errors_model::ERR_DEADLINE_ERROR);
        }
        $data = array(
            'userid' => $params['uid'],
            'card_id' => $params['card_id'],
            'distance' => $params['distance'],
            'amount' => $params['amount'],
            'deadline' => $deadline,
            'pay_status' => self::TASK_PAY_STATUS_NOPAY,
            'task_status' => self::TASK_STATUS_NO_START,
        );
        $row = $this->db->where('userid', $params['uid'])
            ->where('deadline', $deadline)
            ->where('pay_status', self::TASK_PAY_STATUS_PAYED)
            ->get(self::$TABLE)->row_array();
        if ($row) {
            return $result->setCode(Errors_model::ERR_DAY_TASK_ALREADY_EXIST);
        }
         $insertRet = $this->db->insert(self::$TABLE, $data);
        if ($insertRet) {
            $id = $this->db->insert_id();
            return $result->setData(array('task_id' => $id));
        } else {
            return $result->setCode(Errors_model::ERR_DB_ERROR);
        }
    }
    public function pay($id)
    {
        $row = $this->getInfoById($id);
        if (empty($row)) {
            return false;
        }
        if ($row['pay_status'] != self::TASK_PAY_STATUS_NOPAY) {
            return false;
        }
        return $this->db->update(self::$TABLE, array('pay_status' => self::TASK_PAY_STATUS_PAYED), array('task_id' => $id));
    }
    public function todo($uid)
    {
        $this->load->model('card_model');
        $where = array('deadline >=', time());
        $data = $this->db->where('userid', $uid)->where('pay_status', self::TASK_PAY_STATUS_PAYED)->where('deadline >=', time())->order_by('deadline','asc')->get(self::$TABLE)->result_array();
        foreach ($data as &$row) {
            $cardInfo = $this->card_model->getCardInfo($row['card_id']);
            $row['card_name'] = $cardInfo['card_name'];
        }
        return $data;
    }
    public function expire($uid)
    {
        $this->load->model('card_model');
        $card_model = new Card_model();
        //所有已经完成的步数
        //所有已完成支付的订单
        $data = $this->db->where('userid', $uid)->where('pay_status', self::TASK_PAY_STATUS_PAYED)->where('deadline <', time())->order_by('deadline', 'desc')->get(self::$TABLE)->result_array();
        $distance = $this->db->where('userid', $uid)->select_sum('actual_distance')->get('task_reward')->row_array();
        $taskIds = array_column($data, 'task_id');
        if (empty($taskIds)) {
            return array();
        }
        $rewardList = $this->db->where_in('task_id', $taskIds)->get('task_reward')->result_array();
        $this->load->library('Fn');
        $rewardList = Fn::array_reindex($rewardList, 'task_id');
        foreach ($data as &$row) {
            $cardInfo = $card_model->getCardInfo($row['card_id']);
            $d = $rewardList[$row['task_id']]['actual_distance'];
            $row['actual_distance'] = $d ? $d : 0;
            $row['card_name'] = $cardInfo['card_name'];
        }
        $return = array(
            'total_distance' => $distance['actual_distance'],
            'list' => $data,
        );
        return $return;
    }
    public function payed($uid)
    {
        $data = array('total' => 0, 'list' => array());
        $where = array('uid', $uid);
        $where = array('pay_status', self::TASK_PAY_STATUS_PAYED);
        $order = array('deadline', 'desc');
        $data['list'] = $this->db->where('userid', $uid)->where('pay_status', self::TASK_PAY_STATUS_PAYED)
            ->order_by('deadline', 'desc')->get(self::$TABLE)->result_array();
        $totalAmount = $this->db->where('userid', $uid)->where('pay_status', self::TASK_PAY_STATUS_PAYED)
            ->select('sum(amount) as total')->get(self::$TABLE)->row_array();
        $data['total'] = $totalAmount['total'];
        return $data;
    }
    public function getInfoById($id)
    {
        $row = $this->db->where('task_id', $id)->get(self::$TABLE)->row_array();
        return $row;
    }
    public function getDetailById($id)
    {
        $this->load->model(array('card_model', 'task_reward_model'));
        $row = $this->db->where('task_id', $id)->get(self::$TABLE)->row_array();
        if (empty($row)) {
            return array();
        }
        $cardId = $row['card_id'];
        $cardInfo = $this->card_model->getCardInfo($cardId);//var_dump($cardId, $cardInfo);die;
        if (empty($cardInfo)) {
            $row['card_name'] = '';
            return $row;
        }
        //未结束的不展示更多，只展示card_name
        $row['card_name'] = $cardInfo['card_name'];
        if ($row['status'] == self::TASK_STATUS_IS_GOING || $row['status'] == self::TASK_STATUS_NO_START
            || $row['status'] == self::TASK_STATUS_STOP_NO_START
            || $row['status'] == self::TASK_STATUS_STOP_BUT_CONFIRM
        ) {
            return $row;
        }
        $row['actual_distance'] = 0;

        //完成的 展示其它数据
        $rewardInfo = $this->db->where('task_id', $row['task_id'])->get('task_reward')->row_array();
        if (empty($rewardInfo)) {
            return $row;
        }
        $row['actual_distance'] = $rewardInfo['actual_distance'];
        $row['reward_amount'] = $rewardInfo['reward_amount'];
        //var_dump($row['card_id'], $rewardInfo['reward_id']);//die;
        $rewardAllBack = array(
            Task_reward_model::REWARD_TYPE_EXCELLENT,
            Task_reward_model::REWARD_TYPE_FULL,
        );
        //var_dump($rewardInfo);
        if (in_array($rewardInfo['reward_type'], $rewardAllBack)) {
            $history = $this->db->where('userid', $row['userid'])->where_in('reward_type', $rewardAllBack)->where('deadline <=', $row['deadline'])->select('reward_id')->get('task_reward')->result_array();//count_all_results();
            //var_dump($history);
            $row['card_reward_index'] = count($history);
        } else {
            $row['card_reward_index'] = 0;
        }

        return $row;
    }
}