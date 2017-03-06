<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 15:37
 */

class Task_reward_model extends XIN_Model
{
    const REWARD_TYPE_PART = 1;
    const REWARD_TYPE_FULL = 2;
    const REWARD_TYPE_EXCELLENT = 3;

    const REWARD_STATUS_UNPAYED = 1; //未支付
    const REWARD_STATUS_PAYED = 2; //已支付

    protected static $TABLE = 'task_reward';

    public function __construct()
    {
        parent::__construct('task_reward', 'reward_id', 'xin_walker');
    }

    public function listAll($uid)
    {
        $this->load->library('Fn');
        $this->load->model('card_model');
        $card_model = new Card_model();
        if (empty($uid)) {
            return array();
        }

        //获取所有已经获得的奖励
        $totalReward = $this->db->where('userid', $uid)->select_sum('reward_amount')->get(self::$TABLE)->row_array();
        $return = array();
        $return['total'] = $totalReward['reward_amount'];
        $rewardList = $this->db->where('userid', $uid)->order_by('create_time', 'desc')->get(self::$TABLE)->result_array();
        if (empty($rewardList)) {
            return array();
        }
        $taskIds = array_column($rewardList, 'task_id');
        $taskList = $this->db->where_in('task_id', $taskIds)->get('card_task')->result_array();
        $taskList = Fn::array_reindex($taskList, 'task_id');//var_dump($taskList);
        foreach ($rewardList as &$row) {
            //$row['actual_distance'] = $row['distance'];
            $row['distance'] = $taskList[$row['task_id']]['distance'];
            $cardInfo = $card_model->getCardInfo($row['card_id']);
            $row['card_name'] = $cardInfo['card_name'];
        }
        $return['list'] = $rewardList;//var_dump($return);
        return $return;
    }
}