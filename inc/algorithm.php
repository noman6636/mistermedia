<?php
class System extends Database  {
	public $checkQuery, $newQuery, $row, $conn;

	public $developmentMode;

    public function __construct($db, $developmentMode = false) {
        $this->conn = $db;
        $this->developmentMode = $developmentMode;
        $this->initialize();
    }

    public function initialize() {
        if ($this->developmentMode) {
            $this->showErrors();
            $this->controlCache();
        }
    }

    public function showErrors() {
        ini_set('display_errors', 1); 
        ini_set('display_startup_errors', 1); 
        error_reporting(E_ALL);
    }

    public function controlCache() {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
    }
    
    public function cleanData($data) {
        $charset = 'UTF-8';
        // $data = htmlspecialchars($data, ENT_QUOTES, $charset);
        $data = strip_tags($data);
        $data = htmlentities($data);
        $data = htmlspecialchars($data);
        $data = trim($data);
        $data = stripslashes($data);
        return $data;
    }

    
    public function checkCacheDisable($url = NULL, $showMsg = false)
    {
        if($url != NULL)
        {
            $checkURL = $this->isURL($url);
        }
        else
        {
            $checkURL = $this->getCurrentPageURL(true);
        }
        $headers = get_headers($checkURL, 1);
        if($showMsg)
        {
        	if (isset($headers['Cache-Control']))
	        {
	            echo '<script>console.log("Cache-Control header exists: ' . $headers['Cache-Control'].'");</script>';
	        }
	        else
	        {
	            echo '<script>console.log("Cache-Control header does not exist.");</script>';
	        }
        }
    }
	
	public function sanitizeURL($url)
    {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url))
        {
            $url = "http://" . $url;
        }

        return $url;
    }

	public function webSettings($name = NULL)
    {
        $query = $this->select('settings', 'id=1');
        if(count($query))
        {
            $row = $query[0];
            if($name != NULL)
            {
                return $row[$name] ?? 'Column '.$name. ' not Found';   
            }
            else
            {
                return $row;
            }
        }
        else
        {
            return ['status' => 0, 'msg' => 'Web Settings not Found'];
        }
	}
    
    public function isURL($url)
    {
        $url = $this->cleanData($url);
        $pattern = '/\.([a-zA-Z]{2,})$/';
        preg_match($pattern, $url, $matches);

        if (isset($matches[1]))
        {
            $tld = $matches[1];
            $domain = $this->sanitizeURL($url);
            return filter_var($domain, FILTER_VALIDATE_URL) !== false;
        }
        else
        {
            return false;
        }
    }

	public function fileInfo($name) {
        switch ($name) {
            case "fullURL":
                return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            case "fileName":
                return substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1);
            case "count":
                $url = parse_url($this->fileInfo('fullURL'))['path'];
                $arr = explode('/', $url);
                return (count($arr) - 1) - 1;
            case "ext":
                $url = $this->fileInfo("fullURL");
                return pathinfo($url, PATHINFO_EXTENSION);
            case "fileNoExt":
                $url = $this->fileInfo("fullURL");
                return pathinfo($url, PATHINFO_FILENAME);
        }
    }

    public function redirect($url, $msg = 'Redirecting...')
    {
        // Escape special characters in the message to prevent XSS attacks
        $msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    
        return '<script>
            document.write("' . $msg . '");
            window.location.replace("' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '");
        </script>';
    }

	public function raino_trim($str)
	{
		$str = Trim(htmlspecialchars($str));
		return $str;
	}

	public function getCurrentPageURL($complete=true)
	{
	    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
	        $url = "https://";   
	    else  
	        $url = "http://";   
	    $url.= $_SERVER['HTTP_HOST'];   
	    if($complete == true)
	    {
	        $url.= $_SERVER['REQUEST_URI']; 
	    }
	    return $url;  
	}
	
	public function randomCode($length)
	{
        return bin2hex(random_bytes($length));
	}

	public function truncate($input, $maxWords, $maxChars)
	{
	    $words = preg_split('/\s+/', $input);
	    $words = array_slice($words, 0, $maxWords);
	    $words = array_reverse($words);

	    $chars = 0;
	    $truncated = array();

	    while(count($words) > 0)
	    {
	        $fragment = trim(array_pop($words));
	        $chars += strlen($fragment);

	        if($chars > $maxChars) break;

	        $truncated[] = $fragment;
	    }
	    $result = implode(' ', $truncated);

	    return $result . ($input == $result ? '' : '...');
	}

	public function adminLogin($email, $pass)
    {
        $email = $this->cleanData($email);
        $pass = $this->cleanData($pass);
        $password = md5($pass);
        $this->newQuery = $this->conn->prepare("SELECT * FROM `settings` WHERE email=:email and password=:password");
        $this->newQuery->execute(array(":email"=>$email, ":password"=>$password));
        if($this->newQuery->rowCount())
        {
            $row = $this->newQuery->fetch();
            $_SESSION['aid'] = $row['id'];
            return '<span class="text-center mt-2 mb-4 success_msg">Account login Successfully</span><script>window.location.href="index.php";</script>';
        }
        else
        {
            return '<span class="text-center mt-2 mb-4 error_msg">Email and Password is Wrong</span>';
        }
    }
	
	public function print($msg, $heading = NULL) {
        echo '<hr><pre>';
        if ($heading != NULL) {
            echo '>> ' . $heading . '<br />------------------------------------</br>';
        }
        print_r($msg);
        echo '</pre><hr>';
    }

    public function checkLoginSession($type = 'user') {
        if (!empty($type)) {
            $id = ($type === 'admin') ? 'aid' : 'uid';
            
            if (isset($_SESSION[$id])) {
                return [
                    "status" => 1,
                    "session_name" => $type,
                    "session_id" => $_SESSION[$id]
                    ];
            } else {
                return [
                    "status" => 0,
                    "session_name" => $type,
                    "msg" => "Session does not exist",
                    "action" => [
                        "redirect_url" => $this->getCurrentPageURL(false) . '/' . $type . '/login/'
                    ]
                ];
            }
        } else {
            return ["status" => 0, "msg" => "Session type is invalid"];
        }
    }


    public function assets($path)
    {
        $add = '';
        if($this->developmentMode)
        {
            $add = '?v='.time();
        }

        $currentURL = $this->getCurrentPageURL(false);
        return $currentURL . '/' . $path.$add;
    }

	function getOS($user_agent)
	{ 

		global $user_agent;
	
		$os_platform  = "Unknown OS Platform";
	
		$os_array     = array(
							  '/windows nt 10/i'      =>  'Windows 10',
							  '/windows nt 6.3/i'     =>  'Windows 8.1',
							  '/windows nt 6.2/i'     =>  'Windows 8',
							  '/windows nt 6.1/i'     =>  'Windows 7',
							  '/windows nt 6.0/i'     =>  'Windows Vista',
							  '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
							  '/windows nt 5.1/i'     =>  'Windows XP',
							  '/windows xp/i'         =>  'Windows XP',
							  '/windows nt 5.0/i'     =>  'Windows 2000',
							  '/windows me/i'         =>  'Windows ME',
							  '/win98/i'              =>  'Windows 98',
							  '/win95/i'              =>  'Windows 95',
							  '/win16/i'              =>  'Windows 3.11',
							  '/macintosh|mac os x/i' =>  'Mac OS X',
							  '/mac_powerpc/i'        =>  'Mac OS 9',
							  '/linux/i'              =>  'Linux',
							  '/ubuntu/i'             =>  'Ubuntu',
							  '/iphone/i'             =>  'iPhone',
							  '/ipod/i'               =>  'iPod',
							  '/ipad/i'               =>  'iPad',
							  '/android/i'            =>  'Android',
							  '/blackberry/i'         =>  'BlackBerry',
							  '/webos/i'              =>  'Mobile'
						);
	
		foreach ($os_array as $regex => $value)
			if (preg_match($regex, $user_agent))
				$os_platform = $value;
	
		return $os_platform;
	}

	public function generateMaskedCsrfToken($csrfToken)
    {
        // Convert the CSRF token from hex to binary to ensure it's compatible with binary mask
        $csrfToken = hex2bin($csrfToken);
        
        // Generate a random mask (binary data) of the same length as the CSRF token
        $mask = random_bytes(strlen($csrfToken));
        
        // XOR the mask with the CSRF token
        $maskedToken = $mask ^ $csrfToken;
        
        // Return the mask (as hex) concatenated with the masked token (also as hex)
        return bin2hex($mask) . bin2hex($maskedToken);
    }
    
    public function validateMaskedCsrfToken($receivedToken)
    {
        // The first 64 characters of the received token are the hex-encoded mask
        $maskHex = substr($receivedToken, 0, 64);
        
        // The remaining part is the hex-encoded masked token
        $maskedTokenHex = substr($receivedToken, 64);
    
        // Convert both parts back to binary
        $mask = hex2bin($maskHex);
        $maskedToken = hex2bin($maskedTokenHex);
        
        // Get the original CSRF token by XORing the mask and the masked token
        $csrfToken = $mask ^ $maskedToken;
        
        // Compare the original CSRF token with the one in the session
        return hash_equals($csrfToken, hex2bin($_SESSION['csrf_token']));
    }




}

?>