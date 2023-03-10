<?php
//
// jQuery File Tree PHP Connector
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//

include_once(dirname(dirname(__FILE__)) . "/config.php");
$path_for_images = '';
require_once $_SESSION['WYSIWYGFileManagerRequirements'];

$root = WEBSITEROOT_LOCALPATH;

$_POST['dir'] = urldecode($_POST['dir']);

$isImg = isset($_GET["img"]) ? true: false;

if (file_exists($root . $_POST['dir'])) {
    $files = scandir($root . $_POST['dir']);
    natcasesort($files);
    if (count($files) > 2) { /* The 2 accounts for . and .. */
        echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
        // All dirs
        foreach ($files as $file) {
            if (file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file)) {
                echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
            }
        }
        // All files
        
        $dirPath = '';
        $filePath = str_replace(UPLOAD_DIR . 'editor', '', $_POST['dir']);
        $dirPath = trim($filePath, "/");
        
        foreach ($files as $file) {
            if (file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file)) {
                $ext = preg_replace('/^.*\./', '', $file);
                $ext = strtolower($ext);
                
                if (!$isImg || ($isImg && ($ext == "jpeg" || $ext == "jpg" || $ext == "png" || $ext == "gif"))) {
                    if (trim($dirPath) !='') {
                        echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . UrlHelper::generateUrl("editor", "editor-image", array( $dirPath,$file), "/") . "\">" . htmlentities($file) . "</a></li>";
                    } else {
                        echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . UrlHelper::generateUrl("editor", "editor-image", array($file), "/") . "\">" . htmlentities($file) . "</a></li>";
                    }
                }
            }
        }
        echo "</ul>";
    }
}
