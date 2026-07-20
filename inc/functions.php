<?php
function getCsrfToken() {
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfInput() {
    $token = htmlspecialchars(getCsrfToken(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        return false;
    }

    if (!is_string($token) || $token === '') {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

function enforceCsrfOrAbort($redirectPath = 'index.php') {
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verifyCsrfToken($token)) {
        $_SESSION['flash'] = '<div class="alert alert-danger" role="alert"><div class="alert-body">Invalid security token. Please try again.</div></div>';
        header('location: ' . $redirectPath);
        exit();
    }
}

function isCliRequest() {
    return (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg');
}

function getRequestHeaderValue($name) {
    $normalized = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    if (isset($_SERVER[$normalized])) {
        return (string)$_SERVER[$normalized];
    }

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $headerName => $headerValue) {
            if (strcasecmp($headerName, $name) === 0) {
                return (string)$headerValue;
            }
        }
    }

    return '';
}

function getConfiguredCronKey($conn = null) {
    $envKey = getenv('DORDERS_CRON_KEY');
    if (is_string($envKey) && $envKey !== '') {
        return $envKey;
    }

    if ($conn instanceof mysqli) {
        $result = $conn->query("SELECT value FROM app_settings WHERE name = 'cron_web_key' LIMIT 1");
        if ($result && $row = $result->fetch_assoc()) {
            $dbKey = isset($row['value']) ? trim((string)$row['value']) : '';
            if ($dbKey !== '') {
                return $dbKey;
            }
        }
    }

    return '';
}

function enforceCronAccess($conn = null) {
    if (isCliRequest()) {
        return;
    }

    $expectedKey = getConfiguredCronKey($conn);
    $providedKey = '';

    if (isset($_GET['cron_key'])) {
        $providedKey = (string)$_GET['cron_key'];
    }

    if ($providedKey === '') {
        $providedKey = getRequestHeaderValue('X-CRON-KEY');
    }

    if ($expectedKey === '' || $providedKey === '' || !hash_equals($expectedKey, $providedKey)) {
        http_response_code(403);
        echo 'Forbidden';
        exit();
    }
}

function removeEmoji($string) {
    // Remove various emoji and symbol Unicode ranges
    $regex_patterns = [
        '/[\x{1F100}-\x{1F1FF}]/u', // Enclosed Alphanumeric
        '/[\x{1F300}-\x{1F5FF}]/u', // Misc Symbols and Pictographs
        '/[\x{1F600}-\x{1F64F}]/u', // Emoticons
        '/[\x{1F680}-\x{1F6FF}]/u', // Transport & Map Symbols
        '/[\x{1F900}-\x{1F9FF}]/u', // Supplemental Symbols
        '/[\x{2600}-\x{26FF}]/u',   // Misc symbols
        '/[\x{2700}-\x{27BF}]/u',   // Dingbats
        '/[\x{FE00}-\x{FE0F}]/u',   // Variation Selectors
        '/[\x{1FA70}-\x{1FAFF}]/u', // Symbols and Pictographs Extended-A
    ];

    foreach ($regex_patterns as $pattern) {
        $string = preg_replace($pattern, '', $string);
    }

    // Remove any non-printable or invalid UTF-8 characters
    $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);

    return $string;
}


function licensecheck() {
    $server_ip = $_SERVER["SERVER_ADDR"];
    $domain = $_SERVER["HTTP_HOST"];
    $curl = curl_init();
    $response["ipallowed"] = 0;
    
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://verify.alisofttech.com/?domain=" . $domain . "&ipaddress=" . $server_ip,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET"
    ));
    
    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response, true);
    
    if ($response["domainallowed"] == 0) {
        echo "Invalid Domain Authentication";
        die;
    }
    
    if ($response["ipallowed"] == 0) {
        sleep(rand(10, 30));
    }
}

function getTitleFromSKU($conn, $sku) {
    $items = $conn->query("SELECT * FROM app_items where sku = '{$sku}'");
    $price = 0;
    
    if ($items->num_rows > 0) {
        $item = $items->fetch_assoc();
        $title = $item["name"];
    } else {
        $item = $conn->query("SELECT * FROM app_packages where sku = '{$sku}'")->fetch_assoc();
        $title = $item["name"];
    }
    
    return $title;
}

function check_add_item($conn, $sku, $name, $price) {
    $check_item = $conn->query("select * from app_items where sku = '{$sku}'");
    $check_package = $conn->query("select * from app_packages where sku = '{$sku}'");
    
    if ($check_item->num_rows == 0 && $check_package->num_rows == 0) {
        $conn->query("insert into app_items set sku = '{$sku}', name = '{$name}', price = '{$price}'");
        $item_id = $conn->insert_id;
        
        $prices_names = $conn->query("select * from app_sellprices_name");
        while ($prices_name = $prices_names->fetch_assoc()) {
            $conn->query("insert into app_sellprices_amount set item_id = '{$item_id}', name_id = '{$prices_name["id"]}', price = '{$price}'");
        }
    }
    
    $conn->query("update app_items set deleted = 0 where sku = '{$sku}'");
    $conn->query("update app_packages set deleted = 0 where sku = '{$sku}'");
}

function addSystemLog($conn, $action, $description, $details) {
    $admin_id = (int)$_SESSION["admin_id"];
    $now = date("Y-m-d H:i:s", strtotime(" + 4 hours"));
    $ip_address = get_client_ip();
    $agent = $_SERVER["HTTP_USER_AGENT"];
    $address = getIPLocation($ip_address);
    $device = get_systemInfo() . " - " . getbrowser();
    
    $conn->query("insert into app_systemlogs set 
        admin_id = '{$admin_id}', 
        datetime = '{$now}', 
        action = '{$action}', 
        description = '{$description}', 
        details = '{$details}', 
        ip_address = '{$ip_address}', 
        address = '{$address}', 
        device = '{$device}', 
        agent = '{$agent}'"
    );
}

function getMessageTextFromContent($htmlContent) {
    $dom = new DOMDocument();
    @$dom->loadHTML($htmlContent);
    $xpath = new DOMXPath($dom);
    $targetElementId = "UserInputtedText";
    $targetElement = $xpath->query("//*[@id='{$targetElementId}']")->item(0);
    
    if ($targetElement) {
        $text = '';
        foreach ($targetElement->childNodes as $node) {
            $text .= $dom->saveHTML($node);
        }
    } else {
        $text = '';
    }
    
    return $text;
}

function timeAgo($time_ago) {
    $time_ago = strtotime($time_ago);
    $cur_time = time();
    $time_elapsed = $cur_time - $time_ago;
    
    $seconds = $time_elapsed;
    $minutes = round($time_elapsed / 60);
    $hours = round($time_elapsed / 3600);
    $days = round($time_elapsed / 86400);
    $weeks = round($time_elapsed / 604800);
    $months = round($time_elapsed / 2600640);
    $years = round($time_elapsed / 31207680);
    
    if ($seconds <= 60) {
        return "just now";
    } elseif ($minutes <= 60) {
        return $minutes == 1 ? "1 minute ago" : "{$minutes} minutes ago";
    } elseif ($hours <= 24) {
        return $hours == 1 ? "1 hour ago" : "{$hours} hrs ago";
    } elseif ($days <= 7) {
        return $days == 1 ? "Yesterday" : "{$days} days ago";
    } elseif ($weeks <= 4.3) {
        return $weeks == 1 ? "1 week ago" : "{$weeks} weeks ago";
    } elseif ($months <= 12) {
        return $months == 1 ? "1 month ago" : "{$months} months ago";
    } else {
        return $years == 1 ? "1 year ago" : "{$years} years ago";
    }
}

function get_systemInfo() {
    $user_agent = $_SERVER["HTTP_USER_AGENT"];
    $os_platform = "Unknown OS Platform";
    
    $os_array = array(
        "/windows nt 10.0/i" => "Windows 10",
        "/windows phone 8/i" => "Windows Phone 8",
        "/windows phone os 7/i" => "Windows Phone 7",
        "/windows nt 6.3/i" => "Windows 8.1",
        "/windows nt 6.2/i" => "Windows 8",
        "/windows nt 6.1/i" => "Windows 7",
        "/windows nt 6.0/i" => "Windows Vista",
        "/windows nt 5.2/i" => "Windows Server 2003/XP x64",
        "/windows nt 5.1/i" => "Windows XP",
        "/windows xp/i" => "Windows XP",
        "/windows nt 5.0/i" => "Windows 2000",
        "/windows me/i" => "Windows ME",
        "/win98/i" => "Windows 98",
        "/win95/i" => "Windows 95",
        "/win16/i" => "Windows 3.11",
        "/macintosh|mac os x/i" => "Mac OS X",
        "/mac_powerpc/i" => "Mac OS 9",
        "/linux/i" => "Linux",
        "/ubuntu/i" => "Ubuntu",
        "/iphone/i" => "iPhone",
        "/ipod/i" => "iPod",
        "/ipad/i" => "iPad",
        "/android/i" => "Android",
        "/blackberry/i" => "BlackBerry",
        "/webos/i" => "Mobile"
    );
    
    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $os_platform = $value;
            break;
        }
    }
    
    $device = !preg_match("/(windows|mac|linux|ubuntu)/i", $os_platform) ? '' : 
             (preg_match("/phone/i", $os_platform) ? '' : '');
    
    return "{$device} {$os_platform}";
}

function generateRandomString($length) {
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

function IsSKUBanned($sku) {
    $sku = trim(strtoupper($sku));
    return (begnWith($sku, "PVT") || begnWith($sku, "PRIVATE") || begnWith($sku, "(PVT)"));
}

function begnWith($str, $begnString) {
    $len = strlen($begnString);
    return substr($str, 0, $len) === $begnString;
}

function getPriceFromSKU($conn, $sku, $name_id) {
    $items = $conn->query("SELECT * FROM app_items where sku = '{$sku}'");
    $price = 0;
    
    if ($items->num_rows > 0) {
        $item = $items->fetch_assoc();
        $price = $conn->query("SELECT * FROM app_sellprices_amount WHERE item_id = '{$item["id"]}' && name_id = '{$name_id}' && type = '1'")->fetch_assoc()["price"];
    } else {
        $item = $conn->query("SELECT * FROM app_packages where sku = '{$sku}'")->fetch_assoc();
        $price = $conn->query("SELECT * FROM app_sellprices_amount WHERE item_id = '{$item["id"]}' && name_id = '{$name_id}' && type = '2'")->fetch_assoc()["price"];
    }
    
    return $price;
}

function getIPLocation($ip) {
    $geolocation = unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=" . $ip));
    return $geolocation["geoplugin_city"] . ", " . $geolocation["geoplugin_countryName"];
}

function htmlToPlainText($str) {
    $str = str_replace("&nbsp;", " ", $str);
    $str = html_entity_decode($str, ENT_QUOTES | ENT_COMPAT, "UTF-8");
    $str = html_entity_decode($str, ENT_HTML5, "UTF-8");
    $str = html_entity_decode($str);
    $str = htmlspecialchars_decode($str);
    $str = strip_tags($str);
    return $str;
}

function getbrowser() {
    $user_agent = $_SERVER["HTTP_USER_AGENT"];
    $browser = "Unknown Browser";
    
    $browser_array = array(
        "/msie/i" => "Internet Explorer",
        "/firefox/i" => "Firefox",
        "/safari/i" => "Safari",
        "/chrome/i" => "Chrome",
        "/opera/i" => "Opera",
        "/netscape/i" => "Netscape",
        "/maxthon/i" => "Maxthon",
        "/konqueror/i" => "Konqueror",
        "/mobile/i" => "Handheld Browser"
    );
    
    foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $browser = $value;
            break;
        }
    }
    
    return $browser;
}

function flash_msg() {
    if (isset($_SESSION["flash"])) {
        $msg = $_SESSION["flash"];
        unset($_SESSION["flash"]);
        return $msg;
    }
    return '';
}

function getStock($conn, $item_id, $sku) {
    $item_id = (int) $item_id;
    $sku_escaped = $conn->real_escape_string((string)$sku);

    $stock_rs = $conn->query("SELECT IFNULL(SUM(qty), 0) AS qty FROM app_stocks WHERE item_id = {$item_id}");
    $total_stock = $stock_rs ? ((int)$stock_rs->fetch_assoc()["qty"] + 0) : 0;

    $item_sale_rs = $conn->query("
        SELECT IFNULL(SUM(a.QuantityPurchased), 0) AS qty
        FROM app_order_items a
        INNER JOIN app_orders b ON b.OrderID = a.OrderID
        WHERE a.SKU = '{$sku_escaped}' AND b.IsArchived = '0'
    ");
    $total_sale_from_order_item = $item_sale_rs ? ((int)$item_sale_rs->fetch_assoc()["qty"] + 0) : 0;

    $package_sale_rs = $conn->query("
        SELECT IFNULL(SUM(pi.qty * IFNULL(oi.qty_sold, 0)), 0) AS qty
        FROM app_packages_items pi
        INNER JOIN app_packages p ON p.id = pi.package_id
        LEFT JOIN (
            SELECT a.SKU, IFNULL(SUM(a.QuantityPurchased), 0) AS qty_sold
            FROM app_order_items a
            INNER JOIN app_orders b ON b.OrderID = a.OrderID
            WHERE b.IsArchived = '0'
            GROUP BY a.SKU
        ) oi ON oi.SKU = p.sku
        WHERE pi.item_id = {$item_id}
    ");
    $total_sale_from_order_package = $package_sale_rs ? ((int)$package_sale_rs->fetch_assoc()["qty"] + 0) : 0;

    return (int)$total_stock - ($total_sale_from_order_item + $total_sale_from_order_package);
}

function get_client_ip() {
    if (getenv("HTTP_CLIENT_IP")) {
        return getenv("HTTP_CLIENT_IP");
    } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
        return getenv("HTTP_X_FORWARDED_FOR");
    } elseif (getenv("HTTP_X_FORWARDED")) {
        return getenv("HTTP_X_FORWARDED");
    } elseif (getenv("HTTP_FORWARDED_FOR")) {
        return getenv("HTTP_FORWARDED_FOR");
    } elseif (getenv("HTTP_FORWARDED")) {
        return getenv("HTTP_FORWARDED");
    } elseif (getenv("REMOTE_ADDR")) {
        return getenv("REMOTE_ADDR");
    }
    
    return "UNKNOWN";
}

// License check at startup
// licensecheck();
?>