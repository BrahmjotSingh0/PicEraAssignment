<?php
session_start();
$upload_dir = 'images/';
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
$max_size = 5 * 1024 * 1024;
$images_per_page = 6;

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$upload_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $files = $_FILES['image'];
    $success = 0;
    $fail = 0;
    $fail_msgs = [];
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            if (in_array($files['type'][$i], $allowed_types) && $files['size'][$i] <= $max_size) {
                $filename = uniqid() . '-' . basename($files['name'][$i]);
                $destination = $upload_dir . $filename;
                if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                    $success++;
                } else {
                    $fail++;
                    $fail_msgs[] = $files['name'][$i] . ' failed to upload.';
                }
            } else {
                $fail++;
                $fail_msgs[] = $files['name'][$i] . ' wrong type or too big.';
            }
        } else {
            $fail++;
            $fail_msgs[] = $files['name'][$i] . ' upload error.';
        }
    }
    if ($success > 0) {
        $upload_message = "$success image(s) uploaded!";
    }
    if ($fail > 0) {
        $upload_message .= ($upload_message ? '<br>' : '') . "$fail image(s) failed.<br>" . implode('<br>', $fail_msgs);
    }
}

$images = array_filter(glob($upload_dir . '*.{jpg,jpeg,png}', GLOB_BRACE), 'is_file');
rsort($images);

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$total = count($images);
$pages = ceil($total / $images_per_page);
$offset = ($page - 1) * $images_per_page;
$show = array_slice($images, $offset, $images_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Album</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Photo Album</h1>
        <form action="index.php?page=<?php echo $page; ?>" method="post" enctype="multipart/form-data" id="upload-form">
            <input type="file" name="image[]" accept=".jpg,.jpeg,.png" required multiple>
            <button type="submit">Upload</button>
            <?php if ($upload_message): ?>
                <p class="message"><?php echo htmlspecialchars($upload_message); ?></p>
            <?php endif; ?>
        </form>
        <div class="album">
            <div class="column left">
                <?php
                for ($i = 0; $i < min(3, count($show)); $i++) {
                    echo '<img src="' . htmlspecialchars($show[$i]) . '" alt="Photo">';
                }
                ?>
            </div>
            <div class="column right">
                <?php
                for ($i = 3; $i < min(6, count($show)); $i++) {
                    echo '<img src="' . htmlspecialchars($show[$i]) . '" alt="Photo">';
                }
                ?>
            </div>
        </div>
        <?php if ($pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="index.php?page=<?php echo $page - 1; ?>" class="btn">Previous</a>
                <?php endif; ?>
                <span>Page <?php echo $page; ?> of <?php echo $pages; ?></span>
                <?php if ($page < $pages): ?>
                    <a href="index.php?page=<?php echo $page + 1; ?>" class="btn">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="script.js"></script>
</body>
</html>