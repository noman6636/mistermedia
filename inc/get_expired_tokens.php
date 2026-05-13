<?php
require_once "config.php";

// Check session
if (!isset($_SESSION['admin_id'])) {
    exit();
}

$html = '';
$expired = $conn->query("SELECT * FROM app_accounts WHERE deleted = 0 AND active = 1 AND auth_token != '' AND IsTokenInvalid = '1'");

while ($expire = $expired->fetch_assoc()) {
    $html .= `
    <div class="alert alert-danger" role="alert">
        <div class="alert-body">Token for ebay account username <b>${expire['account_username']}</b> has been expired. 
            <a href="connect_ebay.php?renewToken=${expire['id']}">Click to Renew</a>
        </div>
    </div>`;
}

$conn->close();
echo $html;
?>