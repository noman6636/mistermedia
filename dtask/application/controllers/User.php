<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
// require APPPATH . '/libraries/Format.php';


class User extends REST_Controller
{
    
    function sendEmailWithBlue($email, $subject, $message, $attachment = ''){
                $this->load->library('phpmailer_lib');
                $mail = $this->phpmailer_lib->load();
    			
    			// SMTP configuration
    			$mail->isSMTP();
    			$mail->Host     	= 'smtp-relay.sendinblue.com';
    			$mail->SMTPAuth 		= true;
    			$mail->Username 		= 'alisoftware66@gmail.com';
    			$mail->Password 		= 'MLJ0WhESfdtwOxK9';
    // 			$mail->SMTPSecure 	= 'ssl';
    			$mail->Port     	= 587;
    			
    			$mail->setFrom('noreply@alisofttech.com', 'D Task');
    			$mail->addAddress($email);	
    			$mail->Subject = $subject;
    			$mail->isHTML(true);
    			$mail->Body = $message;
    			
    			if($attachment!=''){
    			    $mail->AddAttachment($attachment);
    			}
    			// Send email
    			if($mail->send()){
    			    	$mail->clearAddresses();
    			    return 'DONE';
    			}else{
    			    	$mail->clearAddresses();
    			    return 'ERROR';
    			}
    // 			$mail->send();
    				
    		
    }
    
    public function __construct(){

     parent::__construct();

     // Load model
     $this->load->model('common_model');
     $this->load->library('gcm');
    
    }
  
    public function register_post()
    {
       
        $firstname = $this->post('firstname');
        $lastname = $this->post('lastname');
        $username = $this->post('username');
        $email = $this->post('email');
        $password = md5($this->post('password'));
        $mobile_code = str_replace('+', '', $this->post('mobile_code'));
        $mobile_number = $this->post('mobile_number');

			
		$login_ip			=	$_SERVER['REMOTE_ADDR'];
		$user_agent			=	$_SERVER['HTTP_USER_AGENT'];
		
		
		$check_username = $this->common_model->get_query("select * from app_users where username = '$username'")->num_rows();
		$check_email = $this->common_model->get_query("select * from app_users where email = '$email'")->num_rows();
		$check_phone = $this->common_model->get_query("select * from app_users where mobile_number = '$mobile_number'")->num_rows();
		
		if($check_username > 0){
		    $msg = 'Username already using by another user.';
				
			$status = REST_Controller::HTTP_BAD_REQUEST;
            // Prepare the response
            $response = ['status' => $status, 'msg' => $msg];
            $this->set_response($response, $status);
            return;
            exit();
		}
		
		if($check_email > 0){
		    $msg = 'Email already registered in our system.';
				
			$status = REST_Controller::HTTP_BAD_REQUEST;
            // Prepare the response
            $response = ['status' => $status, 'msg' => $msg];
            $this->set_response($response, $status);
            return;
            exit();
		}
		
	    if($check_phone > 0){
		    $msg = 'Mobile number already registered in our system.';
				
			$status = REST_Controller::HTTP_BAD_REQUEST;
            // Prepare the response
            $response = ['status' => $status, 'msg' => $msg];
            $this->set_response($response, $status);
            return;
            exit();
		}
		
		$data = array();
		$data['firstname'] = $firstname;
		$data['lastname'] = $lastname;
		$data['username'] = $username;
		$data['email'] = $email;
		$data['password'] = $password;
		$data['created_on'] = date('Y-m-d H:i:s');
		$data['mobile_code'] = $mobile_code;
		$data['mobile_number'] = $mobile_number;
		$data['status'] = 1;
        $this->common_model->insert_array('app_users', $data);
				   
        
        $msg = 'Your account has been created successfully.';
				
		$status = REST_Controller::HTTP_OK;
        // Prepare the response
        $response = ['status' => $status, 'msg' => $msg];
        $this->set_response($response, $status);
        return;
       
    }
    
    public function login_post()
    {
        
        $username = $this->post('email');
        $fcm_token = $this->post('fcm_token');
        $password = md5($this->post('password'));
        $auto = $this->post('auto');
		
		$login_ip			=	$_SERVER['REMOTE_ADDR'];
		$user_agent			=	$_SERVER['HTTP_USER_AGENT'];
		
			$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/'; 
        
			if (preg_match($regex, $username)) {
				$get_field		=	'email';
			} else {
				$get_field		=	'username';
			}
			$query = $this->db->query('select * from app_users where '.$get_field.' = "'.$username.'" and password = "'.$password.'" and deleted = 0');
			
            
			if($query->num_rows() > 0) {
				
        	
				    $row = $query->row();
				
				    $profile = array();
					$profile['id'] = $row->id;
					$profile['name'] = $row->firstname.' '.$row->lastname;
					$profile['username'] = $row->username;
				 	$profile['email'] = $row->email;
				 	$profile['fcm_token'] = $row->fcm_token;
				 	
				 	
				    $profile['profile_pic'] = $row->profile_pic;
				    
				    $profile['loggedin'] = 1;
				    ////////////FIREBASE TOKEN////////////
				    $uuid= $profile['id'];
				    if($fcm_token != ""){
    				    if($auto == 0){
    				        if($profile['fcm_token'] != ''){
    				            
    				            if($profile['fcm_token'] != $fcm_token){
    				                $this->db->query("UPDATE app_users set fcm_token = '' WHERE fcm_token = '$fcm_token'");
    				                $this->db->query("UPDATE app_users set fcm_token = '$fcm_token' WHERE id = '$uuid'");
    				                $token = $profile['fcm_token'];
                                    $this->gcm->setMessage(1, "Logged-in on other device", 'This account is logged-in on other device recently and is logged out from here automatically.');
                                    $this->gcm->setPage('LOGOUT_USER', $uuid);
                                    $this->gcm->clearRecepients();
            	                    $this->gcm->addRecepient($token);
            	                    
            	                    $this->gcm->send();
    				            }
    				            
    				        }else{
    				            $this->db->query("UPDATE app_users set fcm_token = '' WHERE fcm_token = '$fcm_token'");
    				            $this->db->query("UPDATE app_users set fcm_token = '$fcm_token' WHERE id = '$uuid'");
    				        }

    				        
    				    }else{
    				        
    				        
    				        if($profile['fcm_token']!=''){
    				            if($profile['fcm_token'] == $fcm_token){
    				                $profile['loggedin'] = 1;
    				            }else{
    				                $profile['loggedin'] = 0;
    				            }
    				        }else{
    				            $tokenUserdCheck = $this->common_model->get_query("select * from app_users where fcm_token = '$fcm_token'");
    				            if($tokenUserdCheck->num_rows() > 0){
    				              $profile['loggedin'] = 0;
    				            }else{
        				            $this->db->query("UPDATE app_users set fcm_token = '$fcm_token' WHERE id = '$uuid'");
        				            $profile['loggedin'] = 1;
    				            }
    				            
    				        }
    				    }
				    }
		
				   	
					$data_ip_upd['last_login']	=	date('Y-m-d H:i:s');
					$data_ip_upd['last_ip']		=	$login_ip;
					$this->common_model->update_array(array('id'=>$row->id),'app_users',$data_ip_upd);
					$user_id = $profile['id'];
					$this->common_model->get_query("update app_tasks set read_status = 1 where (FIND_IN_SET($user_id,assigned_to)) and (status = 0 || status = 1) and deleted = 0 and read_status = 0");
					
					$tokenData['id'] = $profile['id'];
				    $tokenData['username'] = $profile['username'];
					$tokenData['email'] = $profile['email'];
                    $tokenData['timestamp'] = now();
                   
                    $profile['my_tasks'] = $this->db->query("select * from app_tasks where (FIND_IN_SET($user_id,assigned_to)) and (status = 0 || status = 1) and deleted = 0")->num_rows();
                    $profile['assigned_tasks'] = $this->db->query("select * from app_tasks where created_by = '$user_id' and status <> 100 and deleted = 0")->num_rows();
                    // Create a token
                    $token = AUTHORIZATION::generateToken($tokenData);
                    // Set HTTP status code
                    $status = REST_Controller::HTTP_OK;
                    // Prepare the response
                    $response = ['status' => $status, 'token' => $token, 'profile' => $profile];
                    // REST_Controller provide this method to send responses
		
				   
				
			} else {
				
				$msg = 'Invalid Credentials!, Please enter correct one';
				
				$status = REST_Controller::HTTP_UNAUTHORIZED;
                // Prepare the response
                $response = ['status' => $status, 'msg' => $msg];
			}


        
        $this->set_response($response, $status);
        return;
       
    }
    
    
    
    public function forgotPassword_post()
    {
        
        $email = $this->post('email');

		
			$query  = $this->common_model->get_query("select * from app_users where email = '$email'");
			if($query->num_rows() > 0) {
				$row = $query->row();
				    
				 $newPassword = rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
				 $nupp = md5($newPassword);
				 
                 
                 $this->sendEmailWithBlue($email, 'Reset Password - D Task', 'Your new password is '.$newPassword);
           
                 //Send mail 
                 
                $this->common_model->get_query("update app_users set password = '$nupp' where email = '$email'");
                 // Set HTTP status code
                $status = REST_Controller::HTTP_OK;
                // Prepare the response
                $response = ['status' => $status,  'msg' => "New password sent to your email."];
                // REST_Controller provide this method to send responses
                
				
			
                    
		
				   
				
			} else {
				
				$msg = 'Email not found in server.';
				
				$status = REST_Controller::HTTP_BAD_REQUEST;
                // Prepare the response
                $response = ['status' => $status, 'msg' => $msg];
			}


        
        $this->set_response($response, $status);
        return;
       
    }
    
    public function logout_post()
    {
        
     $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                        $uid = $decodedToken->id;
                        $this->common_model->get_query("update app_users set fcm_token = '' where id = '$uid'");
                        $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Logout Successfully."];
                        $this->set_response($response, REST_Controller::HTTP_OK);
                        return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }

   
    
    public function getProfile_get()
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $user = array();
                $user_query = $this->common_model->select_where("*", "app_users", array('id' => $user_id));
                $row = $user_query->row();
                
                $user = json_decode(json_encode($row), true);
                $user['password'] = '';
                
				       
				$user['profile_pic'] = $row->profile_pic;
				       
                $response = ['status' => REST_Controller::HTTP_OK, 'data' => $user];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
    public function getProfileById_get($user_id)
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user = array();
                $user_query = $this->common_model->select_where("*", "app_users", array('id' => $user_id));
                $row = $user_query->row();
                
                $user = json_decode(json_encode($row), true);
                $user['password'] = '';
                
				       
				$user['profile_pic'] = $row->profile_pic;
				       
				     
                
				   
                $response = ['status' => REST_Controller::HTTP_OK, 'data' => $user];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
   
    
    public function getFriendList_get()
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $friends = array();
                $friendList = $this->common_model->get_query("SELECT a.* FROM app_users as a WHERE id IN (SELECT friend_id FROM app_friends WHERE user_id = '$user_id') && a.status = 1 && a.deleted = 0 ORDER BY (SELECT COUNT(id) FROM `app_tasks` WHERE FIND_IN_SET(a.id,assigned_to) && created_by = $user_id) DESC")->result_array();
                
                foreach($friendList as $friend){
                    $friend['password'] = '';
                    
    				$friends[] = $friend;
                }
                
                
                $response = ['status' => REST_Controller::HTTP_OK, 'data' => $friends];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
    public function getUserList_get()
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $users = array();
                $userList = $this->common_model->get_query("SELECT * FROM app_users WHERE id NOT IN (SELECT friend_id FROM app_friends WHERE user_id = '$user_id') && status = 1 && deleted = 0 && id <> '$user_id'")->result_array();
                
                foreach($userList as $user){
                    $user['password'] = '';
                    $user['fullname'] = $user['firstname'].' '.$user['lastname'] .' ('.$user['username'].')';
                    $user['isRequested'] = $this->db->query("SELECT * FROM app_friend_requests WHERE from_user_id = '$user_id' && to_user_id = '{$user['id']}' && status = 0")->num_rows();
                    $user['isPendingRequest'] = $this->db->query("SELECT * FROM app_friend_requests WHERE from_user_id = '{$user['id']}' && to_user_id = '$user_id' && status = 0")->num_rows();
    				
    				$users[] = $user;
                }
                
                
                $response = ['status' => REST_Controller::HTTP_OK, 'data' => $users];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
    public function getRequestList_get()
    {
        
       $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $user_id = $decodedToken->id;
                $users = array();
                $userList = $this->common_model->get_query("SELECT * FROM app_users WHERE id IN (SELECT from_user_id FROM app_friend_requests WHERE to_user_id = '$user_id' AND status = 0) && status = 1 && deleted = 0 && id <> '$user_id'")->result_array();
                
                foreach($userList as $user){
                    $user['password'] = '';
    				
    				$users[] = $user;
                }
                
                
                $response = ['status' => REST_Controller::HTTP_OK, 'data' => $users];

                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
       
    }
    
     public function updateProfile_post()
    {
         $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $uid = $decodedToken->id;
                    $firstname = $this->post('firstname');
                    $lastname = $this->post('lastname');
                    $mobile_number = $this->post('mobile_number');
                    $img = $this->post('img');
                    $this->common_model->update_array(array('id'=>$uid),'app_users',array('firstname' => $firstname, 'lastname' => $lastname, 'mobile_number' => $mobile_number, 'profile_pic' => $img));
                    
                    $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Profile Updated"];
                    $this->set_response($response, REST_Controller::HTTP_OK);
                    return;
               
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
     public function updatePassword_post()
    {
         $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                $uid = $decodedToken->id;
                    $newPassword = md5($this->post('newPassword'));
                  
                    $this->common_model->update_array(array('id'=>$uid),'app_users',array('password' => $newPassword));
                    
                    $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Password Updated"];
                    $this->set_response($response, REST_Controller::HTTP_OK);
                    return;
               
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    public function removeFriend_post()
    {
         $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                    $user_id = $decodedToken->id;
                    $friend_id = $this->post('friend_id');
                    
                    $this->db->query("DELETE FROM app_friends WHERE user_id = '$user_id' && friend_id = '$friend_id'");
                    $this->db->query("DELETE FROM app_friends WHERE user_id = '$friend_id' && friend_id = '$user_id'");
                    
                    $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Removed."];
                    $this->set_response($response, REST_Controller::HTTP_OK);
                    return;
               
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    public function sendRequest_post()
    {
         $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                    $user_id = $decodedToken->id;
                    $to_user_id = $this->post('to_user_id');
                    $data = array();
                    $data['from_user_id'] = $user_id;
                    $data['to_user_id'] = $to_user_id;
                    $data['created_on'] = date('Y-m-d H:i:s');
                    $data['status'] = 0;
                    
                    $this->common_model->insert_array('app_friend_requests', $data);
                    
                    
                    
                    
                    $from_user = $this->db->query("SELECT * FROM app_users WHERE id = '$user_id'")->row_array();
                    $to_user = $this->db->query("SELECT * FROM app_users WHERE id = '$to_user_id'")->row_array();
                    $fcmmessage = $from_user['firstname'].' '.$from_user['lastname'].' sent you friend request.';
                    
                    $data = array();
                    $data['notification_for'] = $to_user_id;
                    $data['title'] = 'Friend Request';
                    $data['message'] = $fcmmessage;
                    $data['type'] = 'request-list';
                    $data['type_id'] = 0;
                    $data['created_date'] = date('Y-m-d H:i:s');
                    $data['status'] = 0;
                    
                    $this->common_model->insert_array('app_notifications', $data);
                    
                    if($to_user['fcm_token']!=''){
                        
                    
                        $this->gcm->setMessage(1, "Friend Request", $fcmmessage);
                        $this->gcm->clearRecepients();
                        $this->gcm->addRecepient($to_user['fcm_token']);
                        
                        $this->gcm->send();
                    }
                    
                    
                    $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Your request has been sent."];
                    $this->set_response($response, REST_Controller::HTTP_OK);
                    return;
               
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    public function acceptRequest_post()
    {
         $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                    $user_id = $decodedToken->id;
                    $friend_id = $this->post('friend_id');
                    $this->db->query("UPDATE app_friend_requests SET status = '1' WHERE from_user_id = '$friend_id' AND to_user_id = '$user_id'");
                    $now = date('Y-m-d H:i:s');
                    $this->db->query("INSERT INTO app_friends SET user_id = '$user_id', friend_id = '$friend_id', created_on = '$now'");
                    $this->db->query("INSERT INTO app_friends SET user_id = '$friend_id', friend_id = '$user_id', created_on = '$now'");
                    
                    
                    
                    
                    $user = $this->db->query("SELECT * FROM app_users WHERE id = '$user_id'")->row_array();
                    $friend = $this->db->query("SELECT * FROM app_users WHERE id = '$friend_id'")->row_array();
                    $fcmmessage = $user['firstname'].' '.$user['lastname'].' accepted your friend request.';
                    
                    $data = array();
                    $data['notification_for'] = $friend_id;
                    $data['title'] = 'Request Accepted';
                    $data['message'] = $fcmmessage;
                    $data['type'] = 'friend-list';
                    $data['type_id'] = 0;
                    $data['created_date'] = date('Y-m-d H:i:s');
                    $data['status'] = 0;
                    
                    $this->common_model->insert_array('app_notifications', $data);
                    
                    if($friend['fcm_token']!=''){
                        
                    
                        $this->gcm->setMessage(1, "Request Accepted", $fcmmessage);
                        $this->gcm->clearRecepients();
                        $this->gcm->addRecepient($friend['fcm_token']);
                        
                        $this->gcm->send();
                    }
                    
                    
                    $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Request Accepted"];
                    $this->set_response($response, REST_Controller::HTTP_OK);
                    return;
               
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    public function rejectRequest_post()
    {
         $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            //TODO: Change 'token_timeout' in application\config\jwt.php
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);

            // return response if token is valid
            if ($decodedToken != false) {
                    $user_id = $decodedToken->id;
                    $friend_id = $this->post('friend_id');
                    $this->db->query("UPDATE app_friend_requests SET status = '2' WHERE from_user_id = '$friend_id' AND to_user_id = '$user_id'");
                    
                    
                    $response = ['status' => REST_Controller::HTTP_OK, 'msg' => "Request Rejected"];
                    $this->set_response($response, REST_Controller::HTTP_OK);
                    return;
               
                
            }
        }
        
                // Prepare the response
        $response = ['status' => REST_Controller::HTTP_UNAUTHORIZED, 'msg' => "Unauthorised Token"];

        $this->set_response($response, REST_Controller::HTTP_UNAUTHORIZED);
    }
    
}