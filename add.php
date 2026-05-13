<?php
// Handle the file upload first
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_FILES['userfile']) && $_FILES['userfile']['error'] == UPLOAD_ERR_OK) {
        $uploaddir = $_SERVER['DOCUMENT_ROOT'] . "/items_image/";
        
        // Create directory if it doesn't exist
        if(!file_exists($uploaddir)) {
            mkdir($uploaddir, 0755, true);
        }
        
        // Generate a unique filename to prevent overwrites
        $filename = uniqid() . '_' . basename($_FILES['userfile']['name']);
        $uploadfile = $uploaddir . $filename;
        
        // Check if it's an actual image
        $check = getimagesize($_FILES['userfile']['tmp_name']);
        if($check !== false) {
            if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
                echo "File was successfully uploaded.";
            } else {
                echo "Possible file upload attack!";
            }
        } else {
            echo "File is not an image.";
        }
    } else {
        echo "Upload error: " . $_FILES['userfile']['error'];
    }
}
?>