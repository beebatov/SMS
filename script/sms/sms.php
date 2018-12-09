<?php

/**
 *
 */

class sms
{
    
    public $student;
    public $student_ob;
    
    public function __construct()
    {
        
        $this->db         = new database();
        $this->conn       = $this->db->conn;
        $this->student_ob = new student();
        $this->student    = $this->student_ob->get_student_info();
        
    }
    
    public function select($query)
    {
        return $this->result = $this->db->select($query);
    }
    
    public function get_buy_sms_list()
    {
        $sql  = "select sms_add.*,user.uname as user from sms_add INNER JOIN user ON user.id=add_by ORDER BY sms_add.id DESC";
        $info = $this->db->get_sql_array($sql);
        return $info;
    }
    
    public function get_send_sms_list()
    {
        $sql  = "select sms_list.*,user.uname as user from sms_list INNER JOIN user ON user.id=sender ORDER BY sms_list.id DESC";
        $info = $this->db->get_sql_array($sql);
        return $info;
    }
    
    public function send_sms_getway($info)
    {
        
        $to      = $info['to'];
        $message = $info['message'];
        $token   = $info['token'];
        $url     = $info['gateway'];
        
        $data = array(
            'to' => "$to",
            'message' => "$message",
            'token' => "$token"
        );
        $ch   = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $smsresult = curl_exec($ch);
        
    }
    
    
    public function sms_balance()
    {
        $now             = $this->db->date();
        $sql             = "select SUM(total_sms) as total_sms ,SUM(total_send) as total_send from sms_add WHERE '$now' BETWEEN start AND end";
        $info            = $this->db->get_sql_array($sql);
        $info            = $info[0];
        $info['balance'] = $info['total_sms'] - $info['total_send'];
        $balance         = $info['balance'];
        
        $sql                = "select SUM(total_sms) as total_sms,SUM(total_send) as total_send from sms_add";
        $info               = $this->db->get_sql_array($sql);
        $info               = $info[0];
        $total_sms          = $info['total_sms'];
        $total_send         = $info['total_send'];
        $ex                 = ($total_sms - ($total_send + $balance));
        $data['total_sms']  = $total_sms;
        $data['total_send'] = $total_send;
        $data['ex']         = $ex;
        $data['balance']    = $balance;
        return $data;
    }
    
    
    public function send_sms($sms_list)
    {
        
        $data = $this->check_sms_balance_list($sms_list);
        if ($data['per'] == 0) {
            echo "Insufficient Balance.Please Recharge Your Balance And Again Send SMS";
            return;
        }
        $total      = $data['total'];
        $start_time = strtotime($this->db->date());
        $this->sms_dist($total);
        $this->insert_sms_database($sms_list);
        $this->send_mobile_phone($sms_list);
        $end_time = strtotime($this->db->date());
        $diff     = $end_time - $start_time;
        
        echo "All SMS Successfully Send.<li>Total SMS Send: $total</li><li>Total Time: $diff Second</li>";
    }
    
    
    public function check_sms_balance_list($sms_list)
    {
        
        $total = 0;
        foreach ($sms_list as $key => $value) {
            $len = $value['len'];
            $total += $len;
        }
        $info          = $this->sms_balance();
        $balance       = $info['balance'];
        $data['total'] = $total;
        $data['per']   = ($total > $balance) ? 0 : 1;
        
        return $data;
    }
    
    
    public function sms_dist($total)
    {
        $now  = $this->db->date();
        $sql  = "select id,total_sms,total_send from sms_add WHERE '$now' BETWEEN start AND end ORDER BY end ASC";
        $info = $this->db->get_sql_array($sql);
        
        $data = array();
        foreach ($info as $key => $value) {
            $id         = $value['id'];
            $total_sms  = $value['total_sms'];
            $total_send = $value['total_send'];
            $due        = $total_sms - $total_send;
            if ($total <= 0)
                break;
            if ($due <= 0)
                continue;
            $use = ($due >= $total) ? $total : $due;
            $total -= $use;
            $res['id']         = $id;
            $res['total_send'] = $use + $total_send;
            array_push($data, $res);
        }
        
        foreach ($data as $key => $value) {
            $this->db->sql_action("sms_add", "update", $value, "no");
        }
        
    }
    
    public function insert_sms_database($sms_list)
    {
        
        $sql     = "select * from sms_setting";
        $info    = $this->db->get_sql_array($sql);
        $info    = $info[0];
        $gateway = $info['gateway'];
        $token   = $info['token'];
        foreach ($sms_list as $key => $value) {
            $info            = array();
            $info['number']  = $value['to'];
            $info['message'] = mysqli_real_escape_string($this->db->conn, $value['message']);
            $info['len']     = $value['len'];
            $info['gateway'] = $gateway;
            $info['date']    = $this->db->date();
            $info['token']   = $token;
            $info['sender']  = 3;
            $this->db->sql_action("sms_list", "insert", $info, "no");
        }
    }
    
    
    
    
    
    public function send_mobile_phone($sms_list)
    {
        $sql     = "select * from sms_setting";
        $info    = $this->db->get_sql_array($sql);
        $info    = $info[0];
        $gateway = $info['gateway'];
        $token   = $info['token'];
        foreach ($sms_list as $key => $value) {
            $data            = array();
            $data['to']      = $value['to'];
            $data['message'] = $value['message'];
            $data['gateway'] = $gateway;
            $data['token']   = $token;
            $this->send_sms_getway($data);
        }
        
    }
    
    
    
    public function make_sms_array($number, $text)
    {
        $len = strlen($text);
        $len = ceil($len / 160);
        $res = array(
            "to" => $number,
            "message" => $text,
            "len" => $len
        );
        return $res;
    }
    
    public function valid_mobile($number)
    {
        $c    = strlen($number);
        $flag = 0;
        if ($c == 11)
            $flag = 1;
        return $flag;
    }
    
    
    public function syn_info($id)
    {
        $info1                    = $this->student;
        $st_info                  = $info1[$id];
        $info['{{student_name}}'] = $st_info['name'];
        $info['{{nick_name}}']    = $st_info['nick'];
        $info['{{id}}']           = $st_info['id'];
        
        return $info;
    }
    
    public function get_sms_recever_option()
    {
        echo " <option value='0'> --Select Recever-- </option>
                <option value='a'> ALL </option>
                <option value='s'> Student </option>
                <option value='g'> Guardians </option>";
    }
    
    public function get_syn()
    {
        $info                     = array();
        $info['{{student_name}}'] = "Student Name";
        $info['{{nick_name}}']    = "Nick Name";
        $info['{{id}}']           = "Student Id";
        return $info;
    }
    
    public function get_syntext()
    {
        $info = $this->get_syn();
        foreach ($info as $key => $value) {
            echo "<option value='$key'>$value</option>";
        }
    }
    
    
    public function get_sms($student_id, $text)
    {
        $info = $this->syn_info($student_id);
        $syn  = $this->get_syn();
        foreach ($syn as $key => $value) {
            $syn_val  = $key;
            $info_val = $info[$key];
            $text     = str_replace($syn_val, $info_val, $text);
        }
        
        return $text;
    }
    
    public function test()
    {
        echo "hello";
    }
    
    
    
}

?>