<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST received</h2>";
    echo "<h3>FILES:</h3><pre>"; print_r($_FILES); echo "</pre>";
    echo "<h3>POST:</h3><pre>"; print_r($_POST); echo "</pre>";
    
    if (isset($_FILES['video_file'])) {
        $err = $_FILES['video_file']['error'];
        $errMessages = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder',
            UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by extension',
        ];
        echo "<h3>Upload error code " . $err . ": " . ($errMessages[$err] ?? 'Unknown') . "</h3>";
        
        if ($err === UPLOAD_ERR_OK) {
            $dest = __DIR__ . '/test_upload_' . time() . '.mp4';
            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $dest)) {
                echo "<div style='color:green'>SUCCESS: Saved to $dest</div>";
            } else {
                echo "<div style='color:red'>FAILED: move_uploaded_file returned false<br>tmp_name=" . $_FILES['video_file']['tmp_name'] . "<br>dest=$dest</div>";
            }
        }
    }
    
    echo "<h3>PHP info:</h3><pre>";
    echo "upload_tmp_dir: " . ini_get('upload_tmp_dir') . "\n";
    echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
    echo "post_max_size: " . ini_get('post_max_size') . "\n";
    echo "php.ini: " . php_ini_loaded_file() . "\n";
    echo "</pre>";
}
?>
<!DOCTYPE html>
<html>
<body>
<h2>Upload Test</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="video_file" accept="video/*"><br><br>
    <button type="submit">Upload Test File</button>
</form>
<hr>
<h3>Server Info</h3>
<pre>
upload_max_filesize: <?php echo ini_get('upload_max_filesize'); ?>
post_max_size: <?php echo ini_get('post_max_size'); ?>
upload_tmp_dir: <?php echo ini_get('upload_tmp_dir'); ?>
php.ini: <?php echo php_ini_loaded_file(); ?>
</pre>
</body>
</html>
