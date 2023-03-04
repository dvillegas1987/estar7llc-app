<?php

class EditorController extends FatController
{
    private $common;
    private $task;

    public function demoPhoto($image = "", $w = 0, $h = 0)
    {
        // self::displayImage($image, 5, 5, true);
        $fileMimeType = mime_content_type($image);
        $obj = new ImageResize($image);
        $obj->setMaxDimensions($w, $h);
        $obj->setResizeMethod(imageResize::IMG_RESIZE_EXTRA_ADDSPACE);

        if ($fileMimeType != '') {
            header("content-type: " . $fileMimeType);
        } else {
            header("Content-Type: " . $size['mime']);
        }
        $obj->displayImage(80, false);
    }

    public function editorImage($dir = '', $img = '')
    {
        ob_end_clean();
        if ($img == '') {
            $pth = CONF_UPLOADS_PATH . 'editor/' . ltrim($dir, '/');
        } else {
            if (!empty($dir)) {
                $dir = ltrim($dir, '/') . '/';
            }
            $pth = CONF_UPLOADS_PATH . 'editor/' . $dir . ltrim($img, '/');
        }


        if (!file_exists($pth)) {
            $pth =  'images/defaults/no_image.jpg';
        }

        if (strpos(CONF_UPLOADS_PATH, 's3://') !== false) {
            $ext = substr($pth, strlen($pth) - 3, strlen($pth));
            if (in_array($ext, ['txt', 'pdf', 'zip'])) {
                $this->loadAttachment($pth);
            }

            if ($ext == "svg") {
                header("Content-type: image/svg+xml");
            } else {
                header("content-type: image/jpeg");
            }
            $fileContent = file_get_contents($pth);
            echo $fileContent;
            exit;
        }
        $fileMimeType = mime_content_type($pth);

        $ext = pathinfo($pth, PATHINFO_EXTENSION);
        if ($ext == "svg") {
            CommonHelper::editorSvg($pth);
            exit;
        }

        if (in_array($ext, ['txt', 'pdf', 'zip'])) {
            $this->loadAttachment($pth);
        }

        $size = getimagesize($pth);

        if ($size) {
            list($w, $h) = getimagesize($pth);
        }
        $obj = new ImageResize($pth);
        $obj->setMaxDimensions($w, $h);
        $obj->setResizeMethod(imageResize::IMG_RESIZE_EXTRA_ADDSPACE);

        if ($fileMimeType != '') {
            header("content-type: " . $fileMimeType);
        } else {
            header("Content-Type: " . $size['mime']);
        }
        $obj->displayImage(80, false);
    }

    private function loadAttachment($pth)
    {
        header("Content-type: application/octet-stream");      
        if (strpos($_SERVER ['HTTP_USER_AGENT'], "MSIE") > 0) {
            header('Content-Disposition: attachment; filename="' . rawurlencode(basename($pth)) . '"');
        } else {
            header('Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode(basename($pth)));
        }
        header('Content-Length: ' . filesize($pth));
        readfile($pth);
        exit;
    }
}
