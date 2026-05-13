<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	 
	public function __construct(){

     parent::__construct();

     // Load model
     $this->load->model('common_model');
     $this->load->library('gcm');
    
     
    }
	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	public function cronJobs()
	{
	    
	
        
        $date = date('Y-m-d H:i:s');
        $reminders = $this->common_model->get_query("select * from app_reminders where status = 0 && datetime <= '$date'");
        
        foreach ($reminders->result_array() as $row){
            $to_user = $this->db->query("SELECT * FROM app_users where id = '{$row['user_id']}'")->row_array();
            $task = $this->db->query("SELECT * FROM app_tasks where id = '{$row['task_id']}'")->row_array();
            
            $data = array();
                    $data['notification_for'] = $row['user_id'];
                    $data['title'] = 'Task Reminder';
                    $data['message'] = 'You have added a task reminder in ('.$task['title'].')';
                    $data['type'] = 'view-task';
                    $data['type_id'] = $row['task_id'];
                    $data['created_date'] = date('Y-m-d H:i:s');
                    $data['status'] = 0;
                    
                    $this->common_model->insert_array('app_notifications', $data);
            if($to_user['fcm_token']!=''){
                        
                    
                $this->gcm->setMessage(1, $data['title'], $data['message']);
                $this->gcm->clearRecepients();
                $this->gcm->addRecepient($to_user['fcm_token']);
                
                $this->gcm->send();
            }
            $this->common_model->update_array(array('id'=>$row['id']),'app_reminders',array('status' => 1));
        }
            
        
	}
	
	              
}
