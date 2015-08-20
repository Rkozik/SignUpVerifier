<?php
/**
 * Created by PhpStorm.
 * User: Bob
 * Date: 8/19/15
 * Time: 11:29 PM
 */

class Home_verify extends CI_Model
{
    public function __construct()
    {
        parent::__construct();

    }
    public function verify($url,$is_verified)
    {
        $insert_data = array(
            'timestamp' => date("D M j G:i:s T Y"),
            'url' => $url,
            'is_verified' => $is_verified
        );

        $this->db->insert('suv_signups', $insert_data);

        $data['result'] = "Successfully Submitted";
        $this->load->view('home_index',$data);
    }

    public function status($url)
    {
        $this->db->select('is_verified');
        $this->db->from('suv_signups');
        $this->db->where('url',$url);
        $this->db->limit(1);

        $query = $this->db->get();
        $result = ($query->num_rows()==1?TRUE:FALSE);
        if($result == TRUE)
        {
            // Already exist, prompt
            $status = "on-file";
        }
        else
        {
            // Does NOT exist, submit new entry
            $status = "new";
        }
        return $status;
    }
}