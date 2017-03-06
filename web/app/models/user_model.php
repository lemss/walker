<?php
/**
 * Created by PhpStorm.
 * User: luwenwei
 * Date: 2017/3/2
 * Time: 15:35
 */

class User_model extends XIN_Model
{
    const SALT_KEY_LEN = 8;
    protected static $TABLE = 'user';

    public function __construct()
    {
        parent::__construct('user', 'userid', 'xin_walker');
    }

    public function register($mobile, $password)
    {
        $this->load->library('Fn');
        //1判断用户是否存在
        //2添加用户 且返回uid, token
        $this->load->model('errors_model');
        $result = new Errors_model();
        $user = $this->db->where('mobile', $mobile)->get('user')->row_array();
        if ($user) {
            return $result->setCode(Errors_model::ERR_USER_EXIST);
        }
        $salt = Fn::getRandomStr(self::SALT_KEY_LEN);

        //error_log("password;".$password .' '. $salt."\n",3,"/tmp/luwenwei.log");
        //error_log("password;".md5($password) . $salt."\n",3,"/tmp/luwenwei.log");
        //error_log("password;".md5(md5($password) . $salt)."\n",3,"/tmp/luwenwei.log");
        $dbPass = substr(md5(md5($password) . $salt), 0, 16); //密码验证
        $userInfo = array(
            'salt' => $salt,
            'password' => $dbPass,
            'mobile' => $mobile,
        );
        //error_log(var_export($userInfo, true)."\n",3,"/tmp/luwenwei.log");
        //var_dump($userInfo);
        $insertRet = $this->db->insert(self::$TABLE, $userInfo);
        if (!$insertRet) {
            return $result->setCode(Errors_model::ERR_DB_ERROR);
        }
        $id = $this->db->insert_id();
        $token = substr(md5($id . md5($salt)),16, 16); //token验证
        return $result->setData(array('userid' => $id, 'token' => $token));
    }

    public function login($mobile, $password)
    {
        $this->load->model('errors_model');
        $result = new Errors_model();
        $row = $this->db->where('mobile', $mobile)->get('user')->row_array();
        if (empty($row)) {
            return $result->setCode(Errors_model::ERR_USER_NOT_EXIST);
        }

        $salt = $row['salt'];
        $p = substr(md5(md5($password) . $salt), 0, 16);
        $id = $row['userid'];
        $token = substr(md5($id . md5($salt)),16, 16);
        $data = array('uid' => $id, 'token' => $token);
        if ($row['password'] == $p) {
            return $result->setCode(Errors_model::SUCC)->setData($data);
        } else {
            return $result->setCode(Errors_model::ERR_USER_VERIFY_ERROR);
        }
    }

    public function verify($uid, $token)
    {
        //var_dump(func_get_args());
        $this->load->model('errors_model');
        $result = new Errors_model();
        $user = $this->db->where('userid', $uid)->get('user')->row_array();
        if (!$user) {
            return $result->setCode(Errors_model::ERR_USER_NOT_EXIST);
        }
        $expect = substr(md5($uid . md5($user['salt'])), 16, 16);
        //var_dump($expect, $token);
        if ($token == $expect) {
            return $result->setCode(Errors_model::SUCC);
        } else {
            return $result->setCode(Errors_model::ERR_USER_VERIFY_ERROR);
        }
    }
}