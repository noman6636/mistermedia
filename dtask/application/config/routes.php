<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
//$route['default_controller'] = 'welcome';
$route['login'] = 'user/login';
$route['register'] = 'user/register';
$route['logout'] = 'user/logout';
$route['forgotPassword'] = 'user/forgotPassword';
$route['getProfile'] = 'user/getProfile';
$route['updateProfile'] = 'user/updateProfile';
$route['updatePassword'] = 'user/updatePassword';
$route['getFriendList'] = 'user/getFriendList';
$route['removeFriend'] = 'user/removeFriend';
$route['getUserList'] = 'user/getUserList';
$route['getRequestList'] = 'user/getRequestList';
$route['sendRequest'] = 'user/sendRequest';
$route['acceptRequest'] = 'user/acceptRequest';
$route['rejectRequest'] = 'user/rejectRequest';


$route['addTask'] = 'task/addTask';
$route['tasks'] = 'task/tasks';
$route['viewTask/([^/]+)'] = 'task/viewTask/$1';
$route['schedule'] = 'task/schedule';
$route['assignedTasksList'] = 'task/assignedTasksList';
$route['completedTasks'] = 'task/completedTasks';
$route['sendMessage'] = 'task/sendMessage';
$route['getChatByTaskId/([^/]+)'] = 'task/getChatByTaskId/$1';
$route['completeTask'] = 'task/completeTask';
$route['notifications'] = 'task/notifications';
$route['reminders/([^/]+)'] = 'task/reminders/$1';
$route['addReminder'] = 'task/addReminder';
$route['removeReminder/([^/]+)'] = 'task/removeReminder/$1';
$route['deleteTask/([^/]+)'] = 'task/deleteTask/$1';
$route['dashboard'] = 'task/dashboard';
$route['updateNotificationStatus'] = 'task/updateNotificationStatus';


$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
