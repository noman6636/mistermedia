<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
// require APPPATH . '/libraries/Format.php';


class Task extends REST_Controller
{
    
    public function __construct(){

     parent::__construct();

     // Load model
     $this->load->model('common_model');
     $this->load->library('gcm');
    }
    
    public function text_get(){
        
         $this->gcm->setMessage(1, "Test", "This is test notification");
        // $this->gcm->setPage("chat", $taskId);
        $this->gcm->clearRecepients();
        $this->gcm->addRecepient("d7KWOXPZ90J0hVEzL8zt8y:APA91bHoPzt7tmSzFJsWaW_cqUEv1p5vmf1gd_i4gfLRrgIsdsme6Af_lk6b6j3MQ2Y9zioGJuo-NGwbSMTa-AKcYuZ6fy9aCd3IFFeKaZmC4m0yEY3J_YqICyMMLlqLVe6a4K4hnPvq");
        
        $this->gcm->send();
    }
    
    public function dashboard_get()
    {
        
       $headers = $this->input->request_headers();
      
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $this->common_model->get_query("update app_tasks set read_status = 1 where (FIND_IN_SET($user_id,assigned_to)) and (status = 0 || status = 1) and deleted = 0 and read_status = 0");
                $my_tasks = $this->db->query("select * from app_tasks where (FIND_IN_SET($user_id,assigned_to)) and (status = 0 || status = 1) and deleted = 0")->num_rows();
                $assigned_tasks = $this->db->query("select * from app_tasks where created_by = '$user_id' and status <> 100 and deleted = 0")->num_rows();
                
                $response = ['status' => REST_Controller::HTTP_OK, 'my_tasks' => $my_tasks, 'assigned_tasks' => $assigned_tasks];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
  
    public function schedule_get()
    {
        
       $headers = $this->input->request_headers();
      
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $overdue = array();
                $duetoday = array();
                $date = date('Y-m-d');
                
                $overdue_query = $this->common_model->get_query("select * from app_tasks where assigned_to = '$user_id' and DATE(due_date) < '$date' and (status = 0 || status = 1) and deleted = 0");
             
                
                
                foreach ($overdue_query->result() as $row){
                    
                    $overdueArray=array();
                    $overdueArray['id'] = $row->id;
                    $overdueArray['title'] = $row->title;
                    $overdueArray['description'] = $this->common_model->trim_paragraph($this->common_model->htmlToPlainText($row->description));
                    $overdueArray['due_date'] = date('M d', strtotime($row->due_date));
                    $overdueArray['profile_pic'] = $this->common_model->select_where('profile_pic', 'app_users', array('id'=>$row->created_by))->row()->profile_pic;
                    $overdueArray['created_by'] = $row->created_by;
                    
                    $overdue[] = $overdueArray;
                }
                 
                
                $duetoday_query = $this->common_model->get_query("select * from app_tasks where assigned_to = '$user_id' and DATE(due_date) = '$date' and (status = 0 || status = 1) and deleted = 0");
                
                foreach ($duetoday_query->result() as $row){
                    
                    $duetodayArray=array();
                    $duetodayArray['id'] = $row->id;
                    $duetodayArray['title'] = $row->title;
                    $duetodayArray['description'] = $this->common_model->trim_paragraph($this->common_model->htmlToPlainText($row->description));
                    $duetodayArray['due_date'] = date('M d', strtotime($row->due_date));
                    $duetodayArray['profile_pic'] = $this->common_model->select_where('profile_pic', 'app_users', array('id'=>$row->created_by))->row()->profile_pic;
                    $duetodayArray['created_by'] = $row->created_by;
                    
                    $duetoday[] = $duetodayArray;
                }
                    
                $response = ['status' => REST_Controller::HTTP_OK, 'overdue' => $overdue, 'duetoday' => $duetoday];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }

    public function tasks_get()
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $mytasks = array();
                $assignedtasks = array();
                $date = date('Y-m-d');
                
                $tasks_query = $this->common_model->get_query("select * from app_tasks where (created_by = '$user_id' && FIND_IN_SET($user_id,assigned_to)) and (status = 0 || status = 1) and deleted = 0 order by priority desc, due_date asc");
                
                
                foreach ($tasks_query->result() as $row){
                    
                    $tasksArray=array();
                    $tasksArray['id'] = $row->id;
                    $tasksArray['title'] = $row->title;
                    $tasksArray['description'] = $this->common_model->trim_paragraph($this->common_model->htmlToPlainText($row->description));
                    $tasksArray['due_date'] = date('M d', strtotime($row->due_date));
                    $tasksArray['profile_pic'] = $this->common_model->select_where('profile_pic', 'app_users', array('id'=>$row->created_by))->row()->profile_pic;
                    $tasksArray['created_by'] = $row->created_by;
                    $tasksArray['priority'] = $row->priority;
                    
                    $mytasks[] = $tasksArray;
                }
                
                
                
                $tasks_query = $this->common_model->get_query("select * from app_tasks where (created_by != '$user_id' && FIND_IN_SET($user_id,assigned_to)) and (status = 0 || status = 1) and deleted = 0 order by priority desc, due_date asc");
                
                
                foreach ($tasks_query->result() as $row){
                    
                    $tasksArray=array();
                    $tasksArray['id'] = $row->id;
                    $tasksArray['title'] = $row->title;
                    $tasksArray['description'] = $this->common_model->trim_paragraph($this->common_model->htmlToPlainText($row->description));
                    $tasksArray['due_date'] = date('M d', strtotime($row->due_date));
                    $tasksArray['profile_pic'] = $this->common_model->select_where('profile_pic', 'app_users', array('id'=>$row->created_by))->row()->profile_pic;
                    $tasksArray['created_by'] = $row->created_by;
                    $tasksArray['priority'] = $row->priority;
                    
                    $assignedtasks[] = $tasksArray;
                }
                
                
                
                $response = ['status' => REST_Controller::HTTP_OK, 'mytasks' => $mytasks, 'assignedtasks' => $assignedtasks];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
    
    
    public function assignedTasksList_get()
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $tasks = array();
                $date = date('Y-m-d');
                $tasks_query = $this->common_model->get_query("select * from app_tasks where created_by = '$user_id' and status <> 100 and deleted = 0 order by id desc");
              
                
                foreach ($tasks_query->result() as $row){
                    $sid = explode(",", $row->assigned_to)[0];
                    $tasksArray=array();
                    $tasksArray['id'] = $row->id;
                    $tasksArray['title'] = $row->title;
                    $tasksArray['assigned_to'] = $row->assigned_to;
                    $tasksArray['description'] = $this->common_model->trim_paragraph($this->common_model->htmlToPlainText($row->description));
                    $tasksArray['due_date'] = date('M d', strtotime($row->due_date));
                    $tasksArray['profile_pic'] = $this->common_model->select_where('profile_pic', 'app_users', array('id'=>$sid))->row()->profile_pic;
                    $tasksArray['created_by'] = $row->created_by;
                    $tasksArray['priority'] = $row->priority;
                    $tasksArray['read_status'] = $row->read_status;
                    
                    $tasks[] = $tasksArray;
                }
                
                
                $response = ['status' => REST_Controller::HTTP_OK, 'payload' => $tasks];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
     public function viewTask_get($task_id)
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $date = date('Y-m-d');
                // $tasks_query = $this->common_model->get_query("select * from app_task where (FIND_IN_SET('$user_id',assigned_to) || task_supervisor = $user_id) and status = 0 and deleted = 0 order by priority_id asc");
                 $tasks_query = $this->common_model->get_query("select * from app_tasks where id = '$task_id' and deleted = 0");
                if($tasks_query->num_rows() > 0){
                    $row = $tasks_query->row();
                            $tasksArray['id'] = $row->id;
                            $tasksArray['title'] = $row->title;
                            $tasksArray['description'] = nl2br($row->description);
                            $tasksArray['due_date'] = date('M d, Y H:i', strtotime($row->due_date));
                           
                            $tasksArray['created_by_id'] = $row->created_by;
                            $created_by = $this->common_model->select_where('*', 'app_users', array('id'=>$row->created_by))->row();
                            $tasksArray['created_by'] = $created_by->firstname.' '.$created_by->lastname;
                            $tasksArray['assigned_to_id'] = $row->assigned_to;
                            
                            // $tasksArray['assigned_to'] = $assigned_to->firstname.' '.$assigned_to->lastname;
                            
                            $var=explode(',', $row->assigned_to);
                            $forAssign = '';
                            foreach($var as $val)
                            {
                                
                                if($val==$user_id){
                                    $this->common_model->get_query("update app_tasks set read_status = 100 where id=$task_id");
                                }
                                
                                $assigned_to = $this->common_model->select_where('*', 'app_users', array('id'=>$val))->row();
                                $forAssign .= $assigned_to->firstname.' '.$assigned_to->lastname .', ';
                            }
                            $tasksArray['assigned_to'] = rtrim($forAssign, ', ');
                            $tasksArray['assigned_to_id'] = $row->assigned_to;
                            
                            if($row->priority == 1){
                                $tasksArray['priority'] = 'Low';
                            }else{
                                $tasksArray['priority'] = 'High';
                            }
                            $tasksArray['priority_id'] = $row->priority;
                            
                            
                            $tasksArray['file'] = $row->file;
                            
                            $tasksArray['status'] = $row->status;
                
                
                    $response = ['status' => REST_Controller::HTTP_OK, 'data' => $tasksArray];
    
                    $this->set_response($response, REST_Controller::HTTP_OK);
                    return;
                }else{
                    $response = ['status' => REST_Controller::HTTP_BAD_REQUEST, 'msg' => "Data Now Found"];
                    $this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
                    return;
                }
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
    
    public function completeTask_post()
    {
         $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $taskId = $this->post('taskId');
                $uid = $decodedToken->id;
                $tasks_query = $this->common_model->get_query("select * from app_tasks where (FIND_IN_SET($uid,assigned_to) || created_by = '$uid') and id = '$taskId' and deleted = 0");
                if($tasks_query->num_rows() > 0){
                    
                    
                    $taskdata = $tasks_query->row_array();
                    $assigned_to = explode(",", $taskdata['assigned_to'])[0];
                    $created_user = $this->db->query("SELECT * FROM app_users WHERE id = '{$taskdata['created_by']}'")->row_array();
                    $assigned_user = $this->db->query("SELECT * FROM app_users WHERE id = '$assigned_to'")->row_array();
                    
                    if($taskdata['created_by']==$uid){
                         $this->db->query("UPDATE app_tasks set status = '100' where id = '$taskId'");
                         $this->db->query("UPDATE app_reminders set status = '1' where task_id = '$taskId'");
                        $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Task marked as completed."];
                        
                    }else{
                        $assigned_user = $this->db->query("SELECT * FROM app_users WHERE id = '$uid'")->row_array();
                       $this->db->query("UPDATE app_tasks set status = '100' where id = '$taskId'");
                        $fcmmessage = 'A task ('.$taskdata['title'].') was completed by '.$assigned_user['firstname'].' '.$assigned_user['lastname'];
                    
                        $data1 = array();
                        $data1['notification_for'] = $created_user['id'];
                        $data1['title'] = 'Task Completed';
                        $data1['message'] = $fcmmessage;
                        $data1['type'] = 'view-task';
                        $data1['type_id'] = $taskId;
                        $data1['created_date'] = date('Y-m-d H:i:s');
                        $data1['status'] = 0;
                        
                        $this->common_model->insert_array('app_notifications', $data1);
                        
                        if($created_user['fcm_token']!=''){
                            
                        
                            $this->gcm->setMessage(1, "Task Completed", $fcmmessage);
                            $this->gcm->setPage("chat", $taskId);
                            $this->gcm->clearRecepients();
                            $this->gcm->addRecepient($created_user['fcm_token']);
                            
                            $this->gcm->send();
                        }
                        $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Your task has been mark as completed."];
                    }
                    
                   
                    
                    $this->set_response($response, REST_Controller::HTTP_OK);
                    return;
                }else{
                    $response = ['status' => REST_Controller::HTTP_BAD_REQUEST, 'msg' => "Unknow Task Id.".$taskId];
                    $this->set_response($response, REST_Controller::HTTP_BAD_REQUEST);
                    return;
                }
                
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
     public function completedTasks_get()
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $tasks = array();
                $date = date('Y-m-d');
                $tasks_query = $this->common_model->get_query("select * from app_tasks where (FIND_IN_SET($user_id,assigned_to) || created_by = '$user_id') and status = 100 and deleted = 0 order by id desc");
                
                foreach ($tasks_query->result() as $row){
                    
                    $tasksArray=array();
                    $tasksArray['id'] = $row->id;
                    $tasksArray['title'] = $row->title;
                    $tasksArray['description'] = $this->common_model->trim_paragraph($this->common_model->htmlToPlainText($row->description));
                    $tasksArray['due_date'] = date('M d', strtotime($row->due_date));
                    $tasksArray['profile_pic'] = $this->common_model->select_where('profile_pic', 'app_users', array('id'=>$row->created_by))->row()->profile_pic;
                    $tasksArray['created_by'] = $row->created_by;
                    $tasksArray['priority'] = $row->priority;
                    
                    $tasks[] = $tasksArray;
                }
                
                
                $response = ['status' => REST_Controller::HTTP_OK, 'payload' => $tasks];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
   
  
    public function addTask_post()
    {
         $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $uid = $decodedToken->id;
                
                // if($this->post('description') || $this->post('description') != 'undefined'){
                    $description = $this->post('description');
                // }else{
                //     $description = '';
                // }
                
                if($this->post('task_picture')){
                    $task_picture = $this->post('task_picture');
                }else{
                    $task_picture = '';
                }
                    if(count($this->post('assigned_to')) > 1){
                        if($this->post('type') == 1){
                            foreach($this->post('assigned_to') as $assigned_to){
                                $data = array(
                                    'title'=>$this->post('title'),
                                    'description'=>$description,
                                    'due_date'=> date('Y-m-d H:i:s', strtotime($this->post('due_date'))),
                                    'created_by'=>$uid,
                                    'priority'=>$this->post('priority'),
                                    'file'=>$task_picture,
                                    'created_date'=>date('Y-m-d H:i:s'),
                                    'assigned_to'=>$assigned_to
                                );
                                $this->common_model->insert_array('app_tasks', $data);
                                $task_id = $this->db->insert_id();
                                
                                $user = $this->db->query("SELECT * FROM app_users WHERE id = '$uid'")->row_array();
                                $assigned = $this->db->query("SELECT * FROM app_users WHERE id = '$assigned_to'")->row_array();
                                $fcmmessage = $user['firstname'].' '.$user['lastname'].' assigned you a new task.';
                                
                                $data1 = array();
                                $data1['notification_for'] = $assigned_to;
                                $data1['title'] = 'New Task';
                                $data1['message'] = $fcmmessage;
                                $data1['type'] = 'view-task';
                                $data1['type_id'] = $task_id;
                                $data1['created_date'] = date('Y-m-d H:i:s');
                                $data1['status'] = 0;
                                
                                $this->common_model->insert_array('app_notifications', $data1);
                                
                                if($assigned['fcm_token']!=''){
                                    
                                
                                    $this->gcm->setMessage(1, "New Task", $fcmmessage);
                                    $this->gcm->setPage("view-task", $task_id);
                                    $this->gcm->clearRecepients();
                                    $this->gcm->addRecepient($assigned['fcm_token']);
                                    
                                    $this->gcm->send();
                                }
                            }
                        }else{
                            $data = array(
                                'title'=>$this->post('title'),
                                'description'=>$description,
                                'due_date'=> date('Y-m-d H:i:s', strtotime($this->post('due_date'))),
                                'created_by'=>$uid,
                                'priority'=>$this->post('priority'),
                                'file'=>$task_picture,
                                'created_date'=>date('Y-m-d H:i:s'),
                                'assigned_to'=>implode(',', $this->post('assigned_to'))
                            );
                            $this->common_model->insert_array('app_tasks', $data);
                            
                            $task_id = $this->db->insert_id();
                            foreach($this->post('assigned_to') as $assigned_to){
                                $user = $this->db->query("SELECT * FROM app_users WHERE id = '$uid'")->row_array();
                                $assigned = $this->db->query("SELECT * FROM app_users WHERE id = '$assigned_to'")->row_array();
                                $fcmmessage = $user['firstname'].' '.$user['lastname'].' assigned you a new task.';
                                
                                $data1 = array();
                                $data1['notification_for'] = $assigned_to;
                                $data1['title'] = 'New Task';
                                $data1['message'] = $fcmmessage;
                                $data1['type'] = 'view-task';
                                $data1['type_id'] = $task_id;
                                $data1['created_date'] = date('Y-m-d H:i:s');
                                $data1['status'] = 0;
                                
                                $this->common_model->insert_array('app_notifications', $data1);
                                
                                if($assigned['fcm_token']!=''){
                                    
                                
                                    $this->gcm->setMessage(1, "New Task", $fcmmessage);
                                    $this->gcm->setPage("view-task", $task_id);
                                    $this->gcm->clearRecepients();
                                    $this->gcm->addRecepient($assigned['fcm_token']);
                                    
                                    $this->gcm->send();
                                }
                            }
                            
                        }
                        
                    }else{
                        $data = array(
                            'title'=>$this->post('title'),
                            'description'=>$description,
                            'due_date'=> date('Y-m-d H:i:s', strtotime($this->post('due_date'))),
                            'created_by'=>$uid,
                            'priority'=>$this->post('priority'),
                            'file'=>$task_picture,
                            'created_date'=>date('Y-m-d H:i:s'),
                            'assigned_to'=>implode(',', $this->post('assigned_to'))
                        );
                        $this->common_model->insert_array('app_tasks', $data);  
                        $task_id = $this->db->insert_id();
                        foreach($this->post('assigned_to') as $assigned_to){
                            if($assigned_to != $uid){
                            
                                $user = $this->db->query("SELECT * FROM app_users WHERE id = '$uid'")->row_array();
                                $assigned = $this->db->query("SELECT * FROM app_users WHERE id = '$assigned_to'")->row_array();
                                $fcmmessage = $user['firstname'].' '.$user['lastname'].' assigned you a new task.';
                                
                                $data1 = array();
                                $data1['notification_for'] = $assigned_to;
                                $data1['title'] = 'New Task';
                                $data1['message'] = $fcmmessage;
                                $data1['type'] = 'view-task';
                                $data1['type_id'] = $task_id;
                                $data1['created_date'] = date('Y-m-d H:i:s');
                                $data1['status'] = 0;
                                
                                $this->common_model->insert_array('app_notifications', $data1);
                                
                                if($assigned['fcm_token']!=''){
                                    
                                
                                    $this->gcm->setMessage(1, "New Task", $fcmmessage);
                                    $this->gcm->setPage("view-task", $task_id);
                                    $this->gcm->clearRecepients();
                                    $this->gcm->addRecepient($assigned['fcm_token']);
                                    
                                    $this->gcm->send();
                                }
                            }
                        }
                    }
                    
                //     
                        
                      
                            
                            
                    
                    $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Task addeded."];
                    $this->set_response($response, REST_Controller::HTTP_OK);
                    return;
                
            
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
   
     public function getChatByTaskId_get($task_id)
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $date = date('Y-m-d');
                $chat_query = $this->common_model->get_query("select * from app_conversation where task_id = '$task_id' order by created_date asc");
                $chatArray = array();
                foreach ($chat_query->result() as $row){
                    $chat = array();
                    $chat = $row;
                    $chat->datetime = date('m/d/y H:i', strtotime($row->created_date));
                    
                   
                    
                    $chat->by_img = $this->common_model->select_where('profile_pic', 'app_users', array('id'=>$row->by_user_id))->row()->profile_pic;
                    $chat->for_img = $this->common_model->select_where('profile_pic', 'app_users', array('id'=>$row->for_user_id))->row()->profile_pic;
                    $by_username = $this->common_model->select_where('*', 'app_users', array('id'=>$row->by_user_id))->row();
                     $chat->name = $by_username->firstname.' '.$by_username->lastname;
                    if($row->by_user_id == $user_id){
                         $row->type = '1';
                         
                     }else{
                        //  $for_username = $this->common_model->select_where('*', 'app_users', array('id'=>$row->for_user_id))->row();
                        //  $chat->name = $for_username->firstname.' '.$for_username->lastname;
                         $row->type = '2';
                     }
                    $chatArray [] = $chat;
                }
                
                $this->common_model->get_query("UPDATE app_conversation set status = 1 where task_id = '$task_id' && status = 0 && by_user_id <> '$user_id'");
                
                $response = ['status' => REST_Controller::HTTP_OK, 'payload' => $chatArray];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
     public function sendMessage_post()
    {
         $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $uid = $decodedToken->id;
                $data = array(
                    'task_id'=>$this->post('task_id'),
                    'message'=>$this->post('message'),
                    'by_user_id'=>$uid,
                    'for_user_id'=>$this->post('for_user_id'),
                    'status'=>0,
                    'created_date'=> date('Y-m-d H:i:s')
                );
        
                    $this->common_model->insert_array('app_conversation', $data);
                    
                    $user = $this->db->query("SELECT * FROM app_users WHERE id = '$uid'")->row_array();
                    $for_user_id = $this->db->query("SELECT * FROM app_users WHERE id = '{$data['for_user_id']}'")->row_array();
                    $title = $user['firstname'].' '.$user['lastname'].' sent you a message.';
                    
                    
                    $data1 = array();
                    $data1['notification_for'] = $data['for_user_id'];
                    $data1['title'] = $title;
                    $data1['message'] = $data['message'];
                    $data1['type'] = 'chat';
                    $data1['type_id'] = $data['task_id'];
                    $data1['created_date'] = date('Y-m-d H:i:s');
                    $data1['status'] = 0;
                    
                    $this->common_model->insert_array('app_notifications', $data1);
                    
                    if($for_user_id['fcm_token']!=''){
                        
                    
                        $this->gcm->setMessage(1, $title, $data['message']);
                        $this->gcm->setPage("chat", $data['task_id']);
                        $this->gcm->clearRecepients();
                        $this->gcm->addRecepient($for_user_id['fcm_token']);
                        
                        $this->gcm->send();
                    }
                 
                    

                    
                    $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Message Sent."];
                    $this->set_response($response, REST_Controller::HTTP_OK);
                    
                    return;
                
            
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    
     public function notifications_get()
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $notifications = array();
                $date = date('Y-m-d');
                $now = date('Y-m-d H:i:s');
                $strNow = strtotime($now . " - 7 day");
                $nowSub = date('Y-m-d H:i:s', $strNow);
                $notifyquery = $this->common_model->get_query("select * from app_notifications where notification_for = '$user_id' and created_date > '$nowSub' order by id desc");
                
                foreach ($notifyquery->result() as $row){
                    
                    $notifyArray=array();
                    $notifyArray['id'] = $row->id;
                    $notifyArray['title'] = $row->title;
                    $notifyArray['message'] = $row->message;
                    $notifyArray['type'] = $row->type;
                    $notifyArray['type_id'] = $row->type_id;
                    $notifyArray['status'] = $row->status;
                    $notifyArray['created_on_t'] = date('h:i A', strtotime($row->created_date));
                    $notifyArray['created_on_d'] = date('M d', strtotime($row->created_date));
                    $notifyArray['created'] = $row->created_date;
                    
                    
                    $notifications[] = $notifyArray;
                    
                }
                // $this->common_model->update_array(array('notification_for'=>$user_id),'app_notifications',array('status' => 1));
                // $this->common_model->get_query("update app_tasks set read_status = 1 where (FIND_IN_SET($user_id,assigned_to)) and (status = 0 || status = 1) and deleted = 0");
                
                
                $response = ['status' => REST_Controller::HTTP_OK, 'payload' => $notifications];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
    
    public function reminders_get($task_id)
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $reminders = array();
                $date = date('Y-m-d');
                
                $reminders_query = $this->common_model->get_query("select * from app_reminders where task_id = '$task_id' && user_id = '$user_id' && status = 0 order by datetime asc");
                
                
                foreach ($reminders_query->result() as $row){
                    
                    $rArray=array();
                    $rArray['id'] =$row->id;
                    $rArray['created_on_t'] = date('h:i A', strtotime($row->datetime));
                    $rArray['created_on_d'] = date('M d', strtotime($row->datetime));
                    $rArray['created'] = $row->datetime;
                    
                    $reminders[] = $rArray;
                }
                
                
                $response = ['status' => REST_Controller::HTTP_OK, 'payload' => $reminders];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
    public function removeReminder_get($rid)
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                
                $this->db->query("DELETE FROM app_reminders WHERE id = '$rid'");
                
                $response = ['status' => REST_Controller::HTTP_OK, 'msg' => 'Reminder deleted successfully.'];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
    public function deleteTask_get($id)
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                
                $this->db->query("DELETE FROM app_conversation WHERE task_id = '$id'");
                $this->db->query("DELETE FROM app_notifications WHERE type_id = '$id' && type = 'view-task'");
                $this->db->query("DELETE FROM app_reminders WHERE task_id = '$id'");
                $this->db->query("DELETE FROM app_tasks WHERE id = '$id'");
                
                $response = ['status' => REST_Controller::HTTP_OK, 'msg' => 'Task deleted successfully.'];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
    public function addReminder_post()
    {
         $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $uid = $decodedToken->id;
                $data = array(
                    'task_id'=>$this->post('task_id'),
                    'datetime'=>$this->post('datetime'),
                    'user_id'=>$uid
                );
        
                    $this->common_model->insert_array('app_reminders', $data);
                    
                    $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Reminders added successfully."];
                    $this->set_response($response, REST_Controller::HTTP_OK);
                    
                    return;
                
            
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    public function updateNotificationStatus_post()
    {
         $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $uid = $decodedToken->id;
                $id = $this->post('id');
                
                
                $this->common_model->update_array(array('id'=>$id),'app_notifications',array('status' => 1));
        
                   
                    
                $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Notification Status Updated"];
                $this->set_response($response, REST_Controller::HTTP_OK);
                
                return;
                
            
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    // public function deleteNotification_post(){
        
    //     $headers = $this->input->request_headers();
    //     if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
    //         //TODO: Change 'token_timeout' in application\config\jwt.php
    //         $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

    //         // return response if token is valid
    //         if ($decodedToken != false) {
    //             $user_id = $decodedToken->id;
    //             $company_id = $decodedToken->company_id;
    //             $permissions =  explode(',',$decodedToken->role);
    //         	$id = $this->post('id');
    //         	$task_status = $this->post('task_status');
            	  
    //         	   if($task_status==10){
    //         	       $this->db->query("delete from app_announcement_notification where id = '$id'");
    //         	   }else if($task_status == 11){
    //         	       $this->db->query("delete from app_conversation_notification where id = '$id'");
    //         	   }else{
    //         	       $this->db->query("update app_task_notification set deleted = '1' where id = '$id'");
    //         	   }
            	   
            	       
            	   
    //         	}
    //         	$response = ['status' => REST_Controller::HTTP_OK, 'msg' => 'Notification Deleted. '];
    //         	$this->set_response($response, REST_Controller::HTTP_OK);
    //             return;
    //     }
    
    //          // Prepare the response
    //     $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

    //     $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    // }
    
    public function getTasksByDate_post(){
        
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $company_id = $decodedToken->company_id;
                $permissions =  explode(',',$decodedToken->role);
            	$date = date('Y-m-d', strtotime($this->post('date')));
            	  
            	   //echo $date;
            	   if(in_array("10", $permissions)){
            	       $query = "select * from app_task where (FIND_IN_SET('$user_id',assigned_to) || (task_supervisor = '$user_id' && sub_contractor != '0' && sub_contractor != '')) and  DATE(due_by) = '$date' and status = 0 and deleted = 0 and company_id = '$company_id'";
            	   }else{
            	       $query = "select * from app_task where FIND_IN_SET('$user_id',assigned_to) and  DATE(due_by) = '$date' and status = 0 and deleted = 0 and company_id = '$company_id'";
            	   }
            	   
            	   $tasksArray = array();
            	   
            	   $tasks = $this->db->query($query);
            	   foreach ($tasks->result() as $row){
                    
                        $dtasks=array();
                        $dtasks['id'] = $row->id;
                        $dtasks['job_name'] = $this->common_model->select_where('name', 'app_jobs', array('id'=>$row->job_id))->row()->name;
                        $dtasks['task_name'] = $this->common_model->trim_paragraph($this->common_model->htmlToPlainText($row->task_description));
                        $dtasks['priority'] = $this->common_model->select_where('color_hex', 'app_priority', array('id'=>$row->priority_id))->row()->color_hex;
                        $dtasks['due_by'] = date('M d', strtotime($row->due_by));
                        $dtasks['profile_pic'] = 'https://cms.taskanize.com/uploads/profile_pics/'.$this->common_model->select_where('profile_pic', 'app_users', array('id'=>$row->created_by))->row()->profile_pic;
                        
                        $tasksArray[] = $dtasks;
                    }
                    
                    if(in_array("10", $permissions)){
            	       $dateArray = $this->db->query("SELECT DATE(due_by) as date FROM `app_task` where (FIND_IN_SET('$user_id',assigned_to) || (task_supervisor = '$user_id' && sub_contractor != '0' && sub_contractor != '')) and status = 0 and deleted = 0 and company_id = '$company_id' GROUP by DATE(due_by)")->result_array();
                    }else{
            	       $dateArray = $this->db->query("SELECT DATE(due_by) as date FROM `app_task` where FIND_IN_SET('$user_id',assigned_to) and status = 0 and deleted = 0 GROUP by DATE(due_by) and company_id = '$company_id'")->result_array();
                    }
                    
            	}
            	$response = ['status' => REST_Controller::HTTP_OK, 'payload' => $tasksArray, 'dates' => $dateArray];
            	$this->set_response($response, REST_Controller::HTTP_OK);
                return;
        }
    
             // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    public function getAssignedTasksByDate_post(){
        
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $company_id = $decodedToken->company_id;
                $permissions =  explode(',',$decodedToken->role);
            	$date = date('Y-m-d', strtotime($this->post('date')));
            	  
            	  
            	       $query = "select * from app_task where created_by = '$user_id' and  DATE(due_by) = '$date' and status = 0 and deleted = 0 and company_id = '$company_id'";
            	
            	   
            	   $tasksArray = array();
            	   
            	   $tasks = $this->db->query($query);
            	   foreach ($tasks->result() as $row){
                    
                        $dtasks=array();
                        $dtasks['id'] = $row->id;
                        $dtasks['job_name'] = $this->common_model->select_where('name', 'app_jobs', array('id'=>$row->job_id))->row()->name;
                        $dtasks['task_name'] = $this->common_model->trim_paragraph($this->common_model->htmlToPlainText($row->task_description));
                        $dtasks['priority'] = $this->common_model->select_where('color_hex', 'app_priority', array('id'=>$row->priority_id))->row()->color_hex;
                        $dtasks['due_by'] = date('M d', strtotime($row->due_by));
                        $dtasks['profile_pic'] = 'https://cms.taskanize.com/uploads/profile_pics/'.$this->common_model->select_where('profile_pic', 'app_users', array('id'=>$row->created_by))->row()->profile_pic;
                        
                        $tasksArray[] = $dtasks;
                    }
                    
                    
            	       $dateArray = $this->db->query("SELECT DATE(due_by) as date FROM `app_task` where created_by = '$user_id' and status = 0 and deleted = 0 and company_id = '$company_id' GROUP by DATE(due_by)")->result_array();
                    
                    
            	}
            	$response = ['status' => REST_Controller::HTTP_OK, 'payload' => $tasksArray, 'dates' => $dateArray];
            	$this->set_response($response, REST_Controller::HTTP_OK);
                return;
        }
    
             // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
   
}