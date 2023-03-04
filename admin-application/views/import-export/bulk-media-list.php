<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$arr_flds = array(
    'listserial' => Labels::getLabel('LBL_#', $adminLangId),
    'user' => Labels::getLabel('LBL_User', $adminLangId),
    'afile_physical_path' => Labels::getLabel('LBL_FILE_LOCATION', $adminLangId),
    'files'    => Labels::getLabel('LBL_Files', $adminLangId),
    'action'    => '',
);
if (!$canEdit) {
    unset($arr_flds['action']);
}

$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');
foreach ($arr_flds as $key => $val) {
    if ($key == 'listserial') {
        $e = $th->appendElement('th', array('width' => '5%'), $val);
    } elseif ($key == 'user') {
        $e = $th->appendElement('th', array('width' => '15%'), $val);
    } elseif ($key == 'afile_physical_path') {
        $e = $th->appendElement('th', array('width' => '55%'), $val);
    } elseif ($key == 'files') {
        $e = $th->appendElement('th', array('width' => '5%'), $val);
    } elseif ($key == 'action') {
        $e = $th->appendElement('th', array('width' => '20%'), $val);
    }
}

$sr_no = count($records);
foreach ($records as $sn => $row) {
    $tr = $tbl->appendElement('tr', array());

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            case 'user':
                !empty($row['credential_username']) ? $td->appendElement('a', array('href' => 'javascript:void(0)', 'onClick' => 'redirectfunc("' . UrlHelper::generateUrl('Users') . '",' . $row['afile_record_id'] . ')'), $row['credential_username'] . '( ' . $row['credential_email'] . ' )') : $td->appendElement('plaintext', array(), 'Admin', true);
                break;
            case 'afile_physical_path':
                $path = AttachedFile::FILETYPE_BULK_IMAGES_PATH . $row['afile_physical_path'];
                $td->appendElement('plaintext', array(), $path, true);
                break;
            case 'files':
                $fullPath = CONF_UPLOADS_PATH . AttachedFile::FILETYPE_BULK_IMAGES_PATH . $row['afile_physical_path'];
                $count = Labels::getLabel('LBL_N/A', $adminLangId);
                if (file_exists($fullPath)) {
                    $allFiles = scandir($fullPath);
                    $files_count = array_diff($allFiles, array('..', '.'));
                    $count = count($files_count);
                }
                $td->appendElement('plaintext', array(), $count, true);
                break;
            case 'action':
                if ($canEdit) {
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "removeDir('" . base64_encode(AttachedFile::FILETYPE_BULK_IMAGES_PATH . $row['afile_physical_path']) . "')"), "<i class='fa fa-trash  icon'></i>", true);
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Download', $adminLangId), "onclick" => "downloadPathsFile('" . base64_encode($fullPath) . "')"), "<i class='ion-android-download icon'></i>", true);
                }
                break;
            default:
                $td->appendElement('plaintext', array(), $row[$key], true);
                break;
        }
    }
    $sr_no--;
}
if (count($records) == 0) {
    $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}

echo $tbl->getHtml();
