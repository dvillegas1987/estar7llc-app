<?php

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class AttachedFile extends MyAppModel
{
    public const DB_TBL = 'tbl_attached_files';
    public const DB_TBL_TEMP = 'tbl_attached_files_temp';
    public const DB_TBL_PREFIX = 'afile_';

    public const FILETYPE_PRODCAT_IMAGE = 1;
    public const FILETYPE_PRODUCT_IMAGE = 2;
    public const FILETYPE_SELLER_APPROVAL_FILE = 3;
    public const FILETYPE_SHOP_LOGO = 4;
    public const FILETYPE_SHOP_BANNER = 5;

    public const FILETYPE_PAYMENT_METHOD = 6;

    public const FILETYPE_USER_IMAGE = 7;
    public const FILETYPE_BRAND_LOGO = 8;
    public const FILETYPE_USER_PROFILE_IMAGE = 9; /*User profile original image*/
    public const FILETYPE_USER_PROFILE_CROPED_IMAGE = 10;    /*User profile croped image*/
    public const FILETYPE_CATEGORY_ICON = 11; /*Used for mobile and shop templates*/
    public const FILETYPE_CATEGORY_BANNER = 12; /* Used in category detail page */
    public const FILETYPE_CATEGORY_BANNER_SELLER = 13; /* Used in seller shop template page */
    public const FILETYPE_SHOP_BACKGROUND_IMAGE = 14; /* Used in seller shop template page */
    public const FILETYPE_FRONT_LOGO = 15;
    public const FILETYPE_HOME_PAGE_BANNER = 16;
    public const FILETYPE_SOCIAL_PLATFORM_IMAGE = 17;
    public const FILETYPE_BANNER = 18;
    public const FILETYPE_ADMIN_LOGO = 19;
    public const FILETYPE_EMAIL_LOGO = 20;
    public const FILETYPE_FAVICON = 21;
    public const FILETYPE_COLLECTION_IMAGE = 22;
    public const FILETYPE_COLLECTION_BG_IMAGE = 23;
    public const FILETYPE_CATEGORY_IMAGE = 24;
    public const FILETYPE_BUYER_RETURN_PRODUCT = 25;
    public const FILETYPE_SELLER_CATALOG_REQUEST = 26;
    public const FILETYPE_DISCOUNT_COUPON_IMAGE = 27;
    public const FILETYPE_BLOG_CONTRIBUTION = 28;
    public const FILETYPE_BLOG_POST_IMAGE = 29;
    public const FILETYPE_BATCH_IMAGE = 30;
    public const FILETYPE_SOCIAL_FEED_IMAGE = 31;
    public const FILETYPE_TESTIMONIAL_IMAGE = 32;
    public const FILETYPE_PROMOTION_MEDIA = 33;
    public const FILETYPE_PAYMENT_PAGE_LOGO = 34;
    public const FILETYPE_ADMIN_PROFILE_IMAGE = 35;
    public const FILETYPE_ADMIN_PROFILE_CROPED_IMAGE = 36;
    public const FILETYPE_WATERMARK_IMAGE = 37;
    public const FILETYPE_APPLE_TOUCH_ICON = 38;
    public const FILETYPE_MOBILE_LOGO = 39;
    public const FILETYPE_CPAGE_BACKGROUND_IMAGE = 40;
    public const FILETYPE_PROMOTION_BANNER = 41;
    public const FILETYPE_SELLER_PRODUCT_DIGITAL_DOWNLOAD = 42;
    public const FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD = 43;
    public const FILETYPE_CATEGORY_COLLECTION_BG_IMAGE = 44;
    public const FILETYPE_SELLER_PAGE_SLOGAN_BG_IMAGE = 45;
    public const FILETYPE_ADVERTISER_PAGE_SLOGAN_BG_IMAGE = 46;
    public const FILETYPE_AFFILIATE_PAGE_SLOGAN_BG_IMAGE = 47;
    public const FILETYPE_CUSTOM_PRODUCT_IMAGE = 48;
    public const FILETYPE_INVOICE_LOGO = 49;
    public const FILETYPE_BRAND_COLLECTION_BG_IMAGE = 50;
    public const FILETYPE_BULK_IMAGES = 51;
    public const FILETYPE_BRAND_IMAGE = 52;
    public const FILETYPE_SHOP_COLLECTION_IMAGE = 53;
    public const FILETYPE_PLUGIN_LOGO = 54;
    public const FILETYPE_PRODCAT_IMAGE_PATH = 'category/';
    public const FILETYPE_PRODUCT_IMAGE_PATH = 'product/';
    public const FILETYPE_BLOG_POST_IMAGE_PATH = 'blog-post/';
    public const FILETYPE_BULK_IMAGES_PATH = 'bulk-images/';
    public const FILETYPE_APP_MAIN_SCREEN_IMAGE = 55;
    public const FILETYPE_APP_LOGO = 56;
    public const FILETYPE_PUSH_NOTIFICATION_IMAGE = 57;
    public const FILETYPE_FIRST_PURCHASE_DISCOUNT_IMAGE = 58;
    public const FILETYPE_META_IMAGE = 59;

    public const APP_IMAGE_WIDTH = 640;
    public const APP_IMAGE_HEIGHT = 480;

    public const RATIO_TYPE_SQUARE = 1;
    public const RATIO_TYPE_RECTANGULAR = 2;

    public function __construct($fileId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $fileId);
        $this->objMainTableRecord->setSensitiveFields(array());
        ini_set('post_max_size', '64M');
        ini_set('upload_max_filesize', '64M');
    }

    public static function returnBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $size = Fatutility::int($val);
        switch ($last) {
            case 'g':
                $size *= (1024 * 1024 * 1024);
                break;
            case 'm':
                $size *= (1024 * 1024);
                break;
            case 'k':
                $size *= 1024;
                break;
            default:
                $size = 1024;
                break;
        }
        return $size;
    }

    public static function maxFileUploadInBytes()
    {
        //select maximum upload size
        $maxUpload = static::returnBytes(ini_get('upload_max_filesize'));
        //select post limit
        $maxPost = static::returnBytes(ini_get('post_max_size'));
        //select memory limit
        $memoryLimit = static::returnBytes(ini_get('memory_limit'));
        // return the smallest of them, this defines the real limit
        return min($maxUpload, $maxPost, $memoryLimit);
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'ta');
        return $srch;
    }


    public static function getFileTypeArray($langId)
    {
        return $arr = array(
            static::FILETYPE_PRODCAT_IMAGE => Labels::getLabel('LBL_Product_Category_Image', $langId),
            static::FILETYPE_CATEGORY_ICON => Labels::getLabel('LBL_Category_Icon', $langId),
            static::FILETYPE_CATEGORY_IMAGE => Labels::getLabel('LBL_Category_Image', $langId),
            static::FILETYPE_CATEGORY_BANNER => Labels::getLabel('LBL_Category_Banner', $langId),
            static::FILETYPE_CATEGORY_BANNER_SELLER => Labels::getLabel('LBL_Category_Banner_Seller', $langId),
        );
        return $arr;
    }

    public static function getImgAttrTypeArray($langId)
    {
        return $arr = array(
            static::FILETYPE_PRODUCT_IMAGE => Labels::getLabel('LBL_Products', $langId),
            static::FILETYPE_BRAND_LOGO => Labels::getLabel('LBL_Brand_Logo', $langId),
            static::FILETYPE_BRAND_IMAGE => Labels::getLabel('LBL_Brand_Banner', $langId),
            /* static::FILETYPE_CATEGORY_IMAGE => Labels::getLabel('LBL_Categories', $langId), */
            static::FILETYPE_CATEGORY_BANNER => Labels::getLabel('LBL_Category_Banner', $langId),
            static::FILETYPE_BLOG_POST_IMAGE => Labels::getLabel('LBL_Blogs', $langId),
        );
        return $arr;
    }

    public static function getRatioTypeArray($langId)
    {
        return $arr = array(
            static::RATIO_TYPE_SQUARE => Labels::getLabel('LBL_1:1', $langId),
            static::RATIO_TYPE_RECTANGULAR => Labels::getLabel('LBL_16:9', $langId)
        );
        return $arr;
    }

    // $compareSize in KiloByte
    public function isUploadedFile($fileTmpName, $compareSize = 0)
    {
        $compareSize = FatUtility::int($compareSize);
        if (1 > $compareSize || static::maxFileUploadInBytes() < $compareSize) {
            $compareSize = static::maxFileUploadInBytes();
        }
        if (filesize($fileTmpName) > $compareSize) {
            $this->error = Labels::getLabel('MSG_INVALID_SIZE', CommonHelper::getLangId());
            return false;
        }

        if (!is_uploaded_file($fileTmpName)) {
            $this->error = Labels::getLabel('MSG_Unable_To_Upload_File', CommonHelper::getLangId());
            return false;
        }

        return true;
    }

    public static function checkSize($file, $compareSize)
    {
        $compareSize = FatUtility::convertToType($compareSize, FatUtility::VAR_FLOAT);
        if (filesize($file) > $compareSize) {
            return false;
        }
        return true;
    }

    public static function getMultipleAttachments($fileType, $recordId, $recordSubid = 0, $langId = 0, $displayUniversalImage = true, $screen = 0, $size = 0, $haveSubIdZero = false)
    {
        $fileType = FatUtility::int($fileType);
        $recordId = FatUtility::int($recordId);
        $recordSubid = FatUtility::int($recordSubid);
        $langId = FatUtility::int($langId);

        $srch = new SearchBase(static::DB_TBL);
        $srch->doNotCalculateRecords();
        $srch->addCondition('afile_type', '=', $fileType);
        $srch->addCondition('afile_record_id', '=', $recordId);

        if ($recordSubid || $recordSubid == -1 || $haveSubIdZero) {
            if ($recordSubid == -1) {
                /* -1, becoz, needs to show, products universal image as well, in that case, value passed is as -1 */
                $recordSubid = 0;
            }
            $srch->addCondition('afile_record_subid', '=', $recordSubid);
        }

        if ($recordId == 0) {
            $srch->addOrder('afile_id', 'desc');
            $srch->addOrder('afile_display_order');
        } else {
            $srch->addOrder('afile_display_order');
        }

        if ($langId > 0) {
            $cnd = $srch->addCondition('afile_lang_id', '=', $langId);
            if ($displayUniversalImage) {
                $cnd->attachCondition('afile_lang_id', '=', '0');
                $srch->addOrder('afile_lang_id', 'DESC');
            }
        }

        if ($screen > 0) {
            $srch->addCondition('afile_screen', '=', $screen);
        }

        if ($langId == 0) {
            $srch->addCondition('afile_lang_id', '=', 0);
        }

        if ($size > 0) {
            $srch->setPageSize($size);
        }
        /* die($srch->getQuery()); */
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs, 'afile_id');
    }

    public static function getAttachment($fileType, $recordId, $recordSubid = 0, $langId = 0, $displayUniversalImage = true, $screen = 0)
    {
        $data = static::getMultipleAttachments($fileType, $recordId, $recordSubid, $langId, $displayUniversalImage, $screen);
        if (count($data) > 0) {
            reset($data);
            return current($data);
        }

        return [
            'afile_id' => -1,
            'afile_type' => 0,
            'afile_record_id' => -1,
            'afile_record_subid' => 0,
            'afile_lang_id' => 0,
            'afile_screen' => 0,
            'afile_physical_path' => '',
            'afile_name' => '',
            'afile_attribute_title' => '',
            'afile_attribute_alt' => '',
            'afile_aspect_ratio' => 0,
            'afile_display_order' => 0,
            'afile_downloaded_times' => 0,
            'afile_updated_at' => '',
        ];
    }

    public function validateFile($file, $name, $defaultLangIdForErrors)
    {
        if (!empty($file) && !empty($name) && file_exists($file)) {
            $fileExt = pathinfo($name, PATHINFO_EXTENSION);
            $fileExt = strtolower($fileExt);
            if (false === in_array($fileExt, applicationConstants::allowedFileExtensions())) {
                $this->error = Labels::getLabel('MSG_INVALID_FILE_EXTENSION', $defaultLangIdForErrors);
                return false;
            }

            if (strpos(CONF_UPLOADS_PATH, 's3://') === false) {
                $fileMimeType = mime_content_type($file);
                if (false === in_array($fileMimeType, applicationConstants::allowedMimeTypes())) {
                    $this->error = Labels::getLabel('MSG_INVALID_FILE_MIME_TYPE', $defaultLangIdForErrors);
                    return false;
                }
            }
        } else {
            $this->error = Labels::getLabel('MSG_NO_FILE_UPLOADED', $defaultLangIdForErrors);
            return false;
        }
    }

    public function saveAttachment($fl, $fileType, $recordId, $recordSubid, $name, $displayOrder = 0, $uniqueRecord = false, $langId = 0, $screen = 0, $aspectRatio = 0)
    {
        $defaultLangIdForErrors = ($langId == 0) ? $this->commonLangId : $langId;

        if (false === $this->validateFile($fl, $name, $defaultLangIdForErrors)) {
            return false;
        }

        $path = CONF_UPLOADS_PATH;

        $path = $this->fileLocToSave($fileType, $path);

        /* creation of folder date wise [ */
        $date_wise_path = date('Y') . '/' . date('m') . '/';
        /* ] */
        $path = $path . $date_wise_path;

        $saveName = time() . '-' . preg_replace('/[^a-zA-Z0-9]/', '', $name);
        if (strpos(CONF_UPLOADS_PATH, 's3://') !== false) {
            $fileExt = pathinfo($name, PATHINFO_EXTENSION);
            $fileExt = strtolower($fileExt);
            if ('zip' == $fileExt) {
                $saveName = time() . '-' . preg_replace('/[^a-zA-Z0-9.]/', '', $name);
            }
        }

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        while (file_exists($path . $saveName)) {
            $saveName = rand(10, 99) . '-' . $saveName;
        }

        if (!move_uploaded_file($fl, $path . $saveName)) {
            $this->error = Labels::getLabel('MSG_COULD_NOT_SAVE_FILE', $defaultLangIdForErrors);
            return false;
        }

        if (strpos(CONF_UPLOADS_PATH, 's3://') !== false) {
            $saveName = preg_replace('/[^a-zA-Z0-9-]/', '', $saveName);
        }
        $fileLoc = $date_wise_path . $saveName;

        return $this->updateFileToDb($fileType, $recordId, $recordSubid, $fileLoc, $name, $langId, $screen, $displayOrder, $uniqueRecord, $aspectRatio);
    }

    public function moveAttachment($filePath, $fileType, $recordId, $recordSubid, $name, $displayOrder = 0, $uniqueRecord = false, $langId = 0, $screen = 0)
    {
        $defaultLangIdForErrors = ($langId == 0) ? $this->commonLangId : $langId;

        $path = CONF_UPLOADS_PATH;
        $file = $path . $filePath;

        if (false === $this->validateFile($file, $name, $defaultLangIdForErrors)) {
            return false;
        }

        $path = $this->fileLocToSave($fileType, $path);

        /* creation of folder date wise [ */
        $date_wise_path = date('Y') . '/' . date('m') . '/';
        /* ] */
        $path = $path . $date_wise_path;

        $saveName = time() . '-' . preg_replace('/[^a-zA-Z0-9]/', '', $name);

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        while (file_exists($path . $saveName)) {
            $saveName = rand(10, 99) . '-' . $saveName;
        }

        if (false === copy($file, $path . $saveName)) {
            $this->error = Labels::getLabel('MSG_COULD_NOT_SAVE_FILE', $defaultLangIdForErrors);
            return false;
        }

        $fileLoc = $date_wise_path . $saveName;

        return $this->updateFileToDb($fileType, $recordId, $recordSubid, $fileLoc, $name, $langId, $screen, $displayOrder, $uniqueRecord);
    }

    private function updateFileToDb($fileType, $recordId, $recordSubid, $fileLoc, $name, $langId, $screen, $displayOrder, $uniqueRecord, $aspectRatio = 0)
    {
        $defaultLangIdForErrors = ($langId == 0) ? $this->commonLangId : $langId;
        $this->assignValues(
            array(
                'afile_type' => $fileType,
                'afile_record_id' => $recordId,
                'afile_record_subid' => $recordSubid,
                'afile_physical_path' => $fileLoc,
                'afile_name' => $name,
                'afile_lang_id' => $langId,
                'afile_screen' => $screen,
                'afile_aspect_ratio' => $aspectRatio
            )
        );

        $db = FatApp::getDb();

        if ($displayOrder == -1) {
            //@todo display order thing needs to be checked.
            $smt = $db->prepareStatement(
                'SELECT MAX(afile_display_order) AS max_order FROM ' . static::DB_TBL . '
                    WHERE afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ?'
            );
            $smt->bindParameters('iii', $fileType, $recordId, $recordSubid, $langId);

            $smt->execute();
            $row = $smt->fetchAssoc();

            $displayOrder = FatUtility::int($row['max_order']) + 1;
        }

        $this->setFldValue('afile_display_order', $displayOrder);
        $this->setFldValue('afile_updated_at', date("Y-m-d H:i:s"));

        if (!$this->save()) {
            $this->error = Labels::getLabel('MSG_COULD_NOT_SAVE_FILE', $defaultLangIdForErrors);
            return false;
        }

        if ($uniqueRecord) {
            $db->deleteRecords(
                static::DB_TBL,
                array(
                    'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ?  AND afile_id != ? AND afile_screen = ?',
                    'vals' => array($fileType, $recordId, $recordSubid, $langId, $this->mainTableRecordId, $screen)
                )
            );
        }
        return $fileLoc;
    }

    public function fileLocToSave($fileType, $path = '')
    {
        /* files path[ */
        switch ($fileType) {
            case self::FILETYPE_PRODCAT_IMAGE:
                $path .= self::FILETYPE_PRODCAT_IMAGE_PATH;
                break;
            case self::FILETYPE_PRODUCT_IMAGE:
            case self::FILETYPE_CUSTOM_PRODUCT_IMAGE:
                $path .= self::FILETYPE_PRODUCT_IMAGE_PATH;
                break;
            case self::FILETYPE_BLOG_POST_IMAGE:
                $path .= self::FILETYPE_BLOG_POST_IMAGE_PATH;
                break;
            case self::FILETYPE_BULK_IMAGES:
                $path .= self::FILETYPE_BULK_IMAGES_PATH;
                break;
        }
        /* ] */
        return $path;
    }

    public function saveImage($fl, $fileType, $recordId, $recordSubid, $name, $displayOrder = 0, $uniqueRecord = false, $lang_id = 0, $mimeType = '', $screen = 0, $aspectRatio = 0)
    {
        if (getimagesize($fl) === false && $mimeType != 'image/svg+xml') {
            $this->error = Labels::getLabel('MSG_UNRECOGNISED_IMAGE_FILE', $this->commonLangId);
            return false;
        }
        return $this->saveAttachment($fl, $fileType, $recordId, $recordSubid, $name, $displayOrder, $uniqueRecord, $lang_id, $screen, $aspectRatio);
    }

    /* public function checkExtension($file,
    $allowedExt = array()) {

    $fileExt = pathinfo($file, PATHINFO_EXTENSION);

    if (getimagesize($fl) === false ) {
    $this->error = 'ERR_UNRECOGNISED_IMAGE_FILE';
    return false;
    }
    return false;
    } */

    /* always call this function using image controller and pass relavant arguments. */
    public static function displayImage($image_name, $w, $h, $no_image = '', $uploadedFilePath = '', $resizeType = ImageResize::IMG_RESIZE_EXTRA_ADDSPACE, $apply_watermark = false, $cache = true, $imageCompression = true)
    {
        ob_end_clean();
        ini_set('memory_limit', '-1');
        if ($no_image == '') {
            $no_image = 'images/defaults/no_image.jpg';
        } else {
            $no_image = 'images/defaults/' . $no_image;
        }

        $originalImageName = $image_name;

        if (trim($uploadedFilePath) != '') {
            $uploadedFilePath = CONF_UPLOADS_PATH . $uploadedFilePath;
        } else {
            $uploadedFilePath = CONF_UPLOADS_PATH;
        }

        $fileMimeType = '';
        $imagePath = $uploadedFilePath . $image_name;

        if (empty($image_name) || !file_exists($uploadedFilePath . $image_name)) {
            $imagePath = $no_image;
        }

        // $fileMimeType = mime_content_type($imagePath);
        if (strpos($_SERVER['REQUEST_URI'], '?t=') === false) {
            $filemtime = filemtime($imagePath);
            $_SERVER['REQUEST_URI'] = rtrim($_SERVER['REQUEST_URI'], '/') . '/?t=' . $filemtime;
        }

        static::setHeaders();

        $w = FatUtility::int($w);
        $h = FatUtility::int($h);

        static::checkModifiedHeader($imagePath);

        if (CONF_USE_FAT_CACHE && $cache) {
            ob_get_clean();
            ob_start();
            static::setContentType($imagePath);
            $fileContent = FatCache::get($_SERVER['REQUEST_URI'], null, '.jpg');
            if ($fileContent) {
                static::loadImage($fileContent, $imagePath);
            }
        }

        try {
            static::setLastModified($imagePath);
            static::setContentType($imagePath);
            $img = new ImageResize($imagePath);
        } catch (Exception $e) {
            try {
                /*In S3 bucket for some large files PHP functions like getImageSize gives some error. So handled the same accordingly */
                if (CONF_USE_FAT_CACHE && strpos(CONF_UPLOADS_PATH, 's3://') !== false) {
                    static::setLastModified($imagePath);
                    static::setContentType($imagePath);
                    $readFileFromCache = FatCache::get($imagePath, CONF_IMG_CACHE_TIME, '.jpg');
                    if (!$readFileFromCache) {
                        $fileContent = file_get_contents($imagePath);
                        FatCache::set($imagePath, $fileContent, '.jpg');
                    }

                    $tempPath = CONF_INSTALLATION_PATH . 'public' . UrlHelper::getCachedUrl($imagePath, CONF_IMG_CACHE_TIME, '.jpg');
                    $img = new ImageResize($tempPath);
                } else {
                    $img = static::getDefaultImage($imagePath, $w, $h);
                    $img->setExtraSpaceColor(204, 204, 204);
                }
            } catch (Exception $e) {
                $img = static::getDefaultImage($imagePath, $w, $h);
                $img->setExtraSpaceColor(204, 204, 204);
            }
        }

        $img->setResizeMethod($resizeType);
        //$img->setResizeMethod(ImageResize::IMG_RESIZE_RESET_DIMENSIONS);
        if ($w && $h) {
            $img->setMaxDimensions($w, $h);
        }

        if ($apply_watermark && !empty($imagePath)) {
            $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_WATERMARK_IMAGE, 0, 0, CommonHelper::getLangId());
            $wtrmrk_file = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
            if (!empty($wtrmrk_file)) {
                $wtrmrk_file = $uploadedFilePath . $wtrmrk_file;
                $ext_watermark = substr($wtrmrk_file, -3);
                $imageInfo = getimagesize($wtrmrk_file);
                $OriginalImageInfo = getimagesize($imagePath);
                $img_w = $w;
                $img_h = $h;
                $wtrmrk_w = $imageInfo[0];
                $wtrmrk_h = $imageInfo[1];
                $img->setWaterMark($wtrmrk_file, $img_w - $wtrmrk_w - 20, $img_h - $wtrmrk_h - 20);
                $fileMimeType = 'image/png';
            }
        }


        if (CONF_USE_FAT_CACHE && $cache) {
            ob_end_clean();
            ob_start();
            ini_set('memory_limit', '-1');
            static::setContentType($imagePath, $fileMimeType);
            if ($imageCompression) {
                $img->displayImage(80, false);
            } else {
                $img->displayImage(100, false);
            }
            $imgData = ob_get_clean();
            FatCache::set($_SERVER['REQUEST_URI'], $imgData, '.jpg');
            static::loadImage($imgData, $imagePath);
        }

        static::setContentType($imagePath);

        if ($imageCompression) {
            $img->displayImage(80, false);
        } else {
            $img->displayImage(100, false);
        }
    }

    public static function loadImage($imgData, $image_name)
    {
        static::setHeaders();
        static::setLastModified($image_name);
        echo $imgData;
        exit;
    }

    public static function setHeaders()
    {
        header('Cache-Control: public, max-age=31536000, stale-while-revalidate=604800');
        header("Pragma: public");
        header("Expires: " . date('r', strtotime("+1 year")));
    }

    public static function setContentType($imagePath, $fileMimeType = '')
    {
        if (strpos(CONF_UPLOADS_PATH, 's3://') === false) {
            if (empty($fileMimeType)) {
                $fileMimeType = mime_content_type($imagePath);
            }

            if ($fileMimeType != '') {
                header("content-type: " . $fileMimeType);
            } else {
                header("content-type: image/jpeg");
            }
            return;
        }

        if (substr($imagePath, strlen($imagePath) - 3, strlen($imagePath)) == "svg") {
            header("Content-type: image/svg+xml");
        } else {
            header("content-type: image/jpeg");
        }
    }

    public static function setLastModified($image_name)
    {
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($image_name)) . ' GMT', true, 200);
    }

    public static function checkModifiedHeader($image_name)
    {
        $headers = FatApp::getApacheRequestHeaders();
        if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($image_name))) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($image_name)) . ' GMT', true, 304);
            exit;
        }
    }

    public static function getDefaultImage($image_name, $width, $height, $useCache = false)
    {
        $file_extension = substr($image_name, strlen($image_name) - 3, strlen($image_name));
        if ($file_extension == "svg") {
            header("Content-type: image/svg+xml");
            static::setLastModified($image_name);
            // $image_name = static::setDimensions($image_name, $width, $height);
            echo file_get_contents($image_name);
            exit;
        }
        return $img = new ImageResize($image_name);
    }

    public static function displayOriginalImage($image_name, $no_image = '', $uploadedFilePath = '', $cache = false)
    {
        ob_end_clean();
        if ($no_image == '') {
            $no_image = 'images/defaults/no_image.jpg';
        } else {
            $no_image = 'images/defaults/' . $no_image;
        }

        if (trim($uploadedFilePath) != '') {
            $uploadedFilePath = CONF_UPLOADS_PATH . $uploadedFilePath;
        } else {
            $uploadedFilePath = CONF_UPLOADS_PATH;
        }

        $fileMimeType = '';

        $imagePath = $uploadedFilePath . $image_name;

        if (empty($image_name) || !file_exists($uploadedFilePath . $image_name)) {
            $imagePath = $no_image;
        }

        if (strpos($_SERVER['REQUEST_URI'], '?t=') === false) {
            $filemtime = filemtime($imagePath);
            $_SERVER['REQUEST_URI'] = rtrim($_SERVER['REQUEST_URI'], '/') . '/?t=' . $filemtime;
        }

        static::setHeaders();

        static::checkModifiedHeader($imagePath);

        if (CONF_USE_FAT_CACHE && $cache) {
            ob_get_clean();
            ob_start();
            static::setContentType($imagePath);
            $fileContent = FatCache::get($_SERVER['REQUEST_URI'], null, '.jpg');
            if ($fileContent) {
                static::loadImage($fileContent, $imagePath);
            }
        }

        try {
            static::setLastModified($imagePath);
            static::setContentType($imagePath);
            $fileContent = file_get_contents($imagePath);
            if (CONF_USE_FAT_CACHE && $cache) {
                FatCache::set($_SERVER['REQUEST_URI'], $fileContent, '.jpg');
                static::loadImage($fileContent, $imagePath);
            }
        } catch (Exception $e) {
            static::setLastModified($imagePath);
            static::setContentType($imagePath);
            $fileContent = file_get_contents($imagePath);
        }

        if (CONF_USE_FAT_CACHE && $cache) {
            FatCache::set($_SERVER['REQUEST_URI'], $fileContent, '.jpg');
            static::loadImage($fileContent, $imagePath);
        }
        echo $fileContent;
    }

    public static function updateDownloadCount($aFileId = 0)
    {
        $aFileId = FatUtility::int($aFileId);
        $digitalFile = array('afile_downloaded_times' => 'mysql_func_afile_downloaded_times+1');
        $where = array('smt' => 'afile_id = ?', 'vals' => array($aFileId));
        $db = FatApp::getDb();
        if (!$db->updateFromArray(static::DB_TBL, $digitalFile, $where, true)) {
            return false;
        }
        return true;
    }

    public static function downloadAttachment($image_name, $downloadFileName)
    {
        ob_end_clean();
        // die(CONF_UPLOADS_PATH . $image_name);
        if (!empty($image_name) && file_exists(CONF_UPLOADS_PATH . $image_name)) {
            $image_name = CONF_UPLOADS_PATH . $image_name;
            $mineType=  mime_content_type($image_name);            
            header('Content-Description: File Transfer');
            header("Content-type: $mineType");           
            if (strpos($_SERVER ['HTTP_USER_AGENT'], "MSIE") > 0) {
                header('Content-Disposition: attachment; filename="' . rawurlencode(basename($downloadFileName)) . '"');
            } else {
                header('Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode(basename($downloadFileName)));
            }
            header('Content-Length: ' . filesize($image_name));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: public');
            header('Pragma: public');
            readfile($image_name);
        }
    }

    public static function getTempImages($limit = false)
    {
        $srch = new SearchBase(AttachedFile::DB_TBL_TEMP, 'aft');
        $srch->addCondition('aft.afile_downloaded', '=', applicationConstants::NO);
        //$srch->addOrder('aft.afile_id', 'asc');
        $srch->addOrder('rand()');
        if ($limit > 0) {
            $srch->setPageSize($limit);
        }
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs);
        if ($row == false) {
            return array();
        } else {
            return $row;
        }
    }

    public static function getImageName($url, $arr = array())
    {
        if (empty($arr)) {
            return;
        }

        $imageName = '';
        $isUrlArr = parse_url($url);
        if (is_array($isUrlArr) && isset($isUrlArr['host'])) {
            if (static::isValidImageUrl($url)) {
                $imgFileContent = static::getRemoteFileContent($url);
                if ($imgFileContent) {
                    $imageName = static::uploadTempImage($imgFileContent, $url, $arr);
                }
            }
        } else {
            $imageName = $url;
        }
        return $imageName;
    }

    public static function uploadTempImage($imgFileContent, $url, $arr = array())
    {
        if (empty($arr)) {
            return;
        }

        $name = substr(preg_replace('/[^a-zA-Z0-9\/\-\_\.]/', '', basename($url)), 0, 50);
        $path = CONF_UPLOADS_PATH;

        /* files path[ */
        switch ($arr['afile_type']) {
            case self::FILETYPE_PRODCAT_IMAGE:
                $path .= self::FILETYPE_PRODCAT_IMAGE_PATH;
                break;
            case self::FILETYPE_PRODUCT_IMAGE:
                $path .= self::FILETYPE_PRODUCT_IMAGE_PATH;
                break;
            case self::FILETYPE_BLOG_POST_IMAGE:
                $path .= self::FILETYPE_BLOG_POST_IMAGE_PATH;
                break;
        }
        /* ] */

        /* creation of folder date wise [ */
        $date_wise_path = date('Y') . '/' . date('m') . '/';
        /* ] */
        $path = $path . $date_wise_path;

        // $saveName = time() . '-' . preg_replace('/[^a-zA-Z0-9]/', '', $name);
        $saveName = time() . '-' . substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), -12);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        while (file_exists($path . $saveName)) {
            $saveName = rand(10, 99) . '-' . $saveName;
        }

        $localfile = $path . $saveName;
        $res = file_put_contents($localfile, $imgFileContent);
        if (!$res) {
            return;
        }

        $fileType = $arr['afile_type'];
        $recordId = $arr['afile_record_id'];
        $recordSubid = $arr['afile_record_subid'];
        $langId = $arr['afile_lang_id'];
        $screen = $arr['afile_screen'];
        $data = array(
            'afile_type' => $fileType,
            'afile_record_id' => $recordId,
            'afile_record_subid' => $recordSubid,
            'afile_physical_path' => $date_wise_path . $saveName,
            'afile_lang_id' => $langId,
            'afile_screen' => $arr['afile_screen'],
            'afile_display_order' => $arr['afile_display_order'],
            'afile_name' => $name,
            'afile_updated_at' => date('Y-m-d H:i:s'),
        );

        $db = FatApp::getDb();
        if ($arr['afile_unique'] == applicationConstants::YES) {
            $db->deleteRecords(
                static::DB_TBL,
                array(
                    'smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ?  AND afile_screen = ?',
                    'vals' => array($fileType, $recordId, $recordSubid, $langId, $screen)
                )
            );
        }

        $db->insertFromArray(static::DB_TBL, $data);
        return $date_wise_path . $saveName;
    }

    public static function isValidImageUrl($url)
    {
        if (getimagesize($url) !== false) {
            return true;
        }
        return false;
    }

    public static function getRemoteFileContent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $file = curl_exec($ch);
        if ($file === false) {
            return false;
        }
        curl_close($ch);
        return $file;
    }

    public function deleteFile($fileType, $recordId, $fileId = 0, $record_subid = 0, $langId = -1, $screen = 0)
    {
        $fileType = FatUtility::int($fileType);
        $recordId = FatUtility::int($recordId);
        $fileId = FatUtility::int($fileId);
        $record_subid = FatUtility::int($record_subid);
        $langId = FatUtility::int($langId);
        $allowedFileTypes = [
            AttachedFile::FILETYPE_ADMIN_LOGO,
            AttachedFile::FILETYPE_FRONT_LOGO,
            AttachedFile::FILETYPE_EMAIL_LOGO,
            AttachedFile::FILETYPE_FAVICON,
            AttachedFile::FILETYPE_SOCIAL_FEED_IMAGE,
            AttachedFile::FILETYPE_PAYMENT_PAGE_LOGO,
            AttachedFile::FILETYPE_WATERMARK_IMAGE,
            AttachedFile::FILETYPE_APPLE_TOUCH_ICON,
            AttachedFile::FILETYPE_MOBILE_LOGO,
            AttachedFile::FILETYPE_CATEGORY_COLLECTION_BG_IMAGE,
            AttachedFile::FILETYPE_BRAND_COLLECTION_BG_IMAGE,
            AttachedFile::FILETYPE_INVOICE_LOGO,
            AttachedFile::FILETYPE_APP_MAIN_SCREEN_IMAGE,
            AttachedFile::FILETYPE_APP_LOGO,
            AttachedFile::FILETYPE_PUSH_NOTIFICATION_IMAGE,
        ];
        //if (!in_array($fileType, $allowedFileTypes) && (!$fileType || !$recordId)) {
        // Remove condition of $recordId for handle all data of add/edit product category in single form
        if (!in_array($fileType, $allowedFileTypes) && !$fileType) {
            $this->error = Labels::getLabel('MSG_INVALID_REQUEST', $this->commonLangId);
            return false;
        }

        /* default will delete all files of requested recordId */
        $smt1 = 'afile_type = ? AND afile_record_id = ?';
        $dataArr1 = array($fileType, $recordId);
        $deleteStatementArr = array('smt' => 'afile_type = ? AND afile_record_id = ?', 'vals' => array($fileType, $recordId));

        if ($record_subid > 0) {
            $deleteStatementArr = array('smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ?', 'vals' => array($fileType, $recordId, $record_subid));
        }

        if ($langId != -1) {
            /* delete lang Specific file */
            $deleteStatementArr = array('smt' => 'afile_type = ? AND afile_record_id = ? AND afile_lang_id = ? AND afile_screen = ?', 'vals' => array($fileType, $recordId, $langId, $screen));
            if ($record_subid > 0) {
                $deleteStatementArr = array('smt' => 'afile_type = ? AND afile_record_id = ? AND afile_record_subid = ? AND afile_lang_id = ?', 'vals' => array($fileType, $recordId, $record_subid, $langId));
            }
        }

        if ($fileId) {
            /* delete single file */
            $deleteStatementArr = array('smt' => 'afile_type = ? AND afile_record_id = ? AND afile_id=?', 'vals' => array($fileType, $recordId, $fileId));
        }
        
        $db = FatApp::getDb();
        if (!$db->deleteRecords('tbl_attached_files', $deleteStatementArr)) {
            $this->error = $db->getError();
            return false;
        }
        //@todo:: not deleted physical file from the system.
        return true;
    }

    public function extractZip($file)
    {
        rename($file, $file . '.zip');
        $zip = new ZipArchive();
        $res = $zip->open($file . '.zip');
        if ($res === true) {
            $zip->extractTo($file);
            $zip->close();
            unlink($file . '.zip');
            return true;
        } else {
            return false;
        }
    }

    public static function registerS3ClientStream()
    {
        if (strpos(CONF_UPLOADS_PATH, 's3://') === false) {
            return;
        }

        if (!defined('S3_KEY')) {
            trigger_error('S3 Settings not found.', E_USER_ERROR);
        }

        $client = S3Client::factory([
            'credentials' => ['key' => S3_KEY, 'secret' => S3_SECRET],
            'region' => S3_REGION,
            'version' => 'latest'
        ]);
        $client->registerStreamWrapper();
    }

    public static function setTimeParam($dateTime)
    {
        $time = strtotime($dateTime);
        return ($time > 0) ? '?t=' . $time : '';
    }

    public static function uploadErrorMessage($code, $langId)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = Labels::getLabel("MSG_THE_UPLOADED_FILE_EXCEEDS_THE_UPLOAD_MAX_FILESIZE_DIRECTIVE_IN_PHP.INI", $langId);
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = Labels::getLabel("MSG_THE_UPLOADED_FILE_EXCEEDS_THE_MAX_FILE_SIZE_DIRECTIVE_THAT_WAS_SPECIFIED_IN_THE_HTML_FORM", $langId);
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = Labels::getLabel("MSG_THE_UPLOADED_FILE_WAS_ONLY_PARTIALLY_UPLOADED", $langId);
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = Labels::getLabel("MSG_NO_FILE_WAS_UPLOADED", $langId);
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = Labels::getLabel("MSG_MISSING_A_TEMPORARY_FOLDER", $langId);
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = Labels::getLabel("MSG_FAILED_TO_WRITE_FILE_TO_DISK", $langId);
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = Labels::getLabel("MSG_FILE_UPLOAD_STOPPED_BY_EXTENSION", $langId);
                break;

            default:
                $message = Labels::getLabel("MSG_UNKNOWN_UPLOAD_ERROR", $langId);
                break;
        }
        return $message;
    }
}
