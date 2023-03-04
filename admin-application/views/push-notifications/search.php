<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$arr_flds = [
    'listserial' => [
        'title' => Labels::getLabel('LBL_#', $adminLangId),
        'attributes' => [
            'width' => '7%'
        ],
    ],
    // 'pnotification_type' => [
    //     'title' => Labels::getLabel('LBL_TYPE', $adminLangId),
    //         'attributes' => [
    //             'width' => '20%'
    //         ]
    // ],
    'notification_detail' => [
        'title' => Labels::getLabel('LBL_DETAIL', $adminLangId),
        'attributes' => [
            'width' => '53%'
        ]
    ],
    'pnotification_notified_on' => [
        'title' => Labels::getLabel('LBL_SCHEDULED_FOR', $adminLangId),
        'attributes' => [
            'width' => '15%'
        ]
    ],
    // 'notify_to' => [
    //     'title' => Labels::getLabel('LBL_NOTIFY_TO', $adminLangId),
    //     'attributes' => [
    //         'width' => '20%'
    //     ]
    // ],
    'pnotification_status' => [
        'title' => Labels::getLabel('LBL_STATUS', $adminLangId),
        'attributes' => [
            'width' => '10%'
        ]
    ],
    'action' => [
        'title' => Labels::getLabel('LBL_Action', $adminLangId),
        'attributes' => [
            'width' => '15%'
        ]
    ]
];

if (!$canEdit) {
    unset($arr_flds['action']);
}
        
$tbl = new HtmlElement('table', array('width' => '100%', 'class' => 'table table-responsive'));
$th = $tbl->appendElement('thead')->appendElement('tr');

foreach ($arr_flds as $key => $val) {
    $th->appendElement('th', $val['attributes'], $val['title']);
}

$sr_no = $page == 1 ? 0 : $pageSize * ($page - 1);
foreach ($arr_listing as $sn => $row) {
    $sr_no++;
    $tr = $tbl->appendElement('tr', array( ));

    foreach ($arr_flds as $key => $val) {
        $td = $tr->appendElement('td');
        switch ($key) {
            case 'listserial':
                $td->appendElement('plaintext', array(), $sr_no);
                break;
            /* case 'pnotification_type':
                $td->appendElement('plaintext', array(), $typeArr[$row[$key]], true);
                break; */
            case 'notification_detail':
                $body = $row['pnotification_description'];
                $htm =  '<strong>' . $row['pnotification_title'] . '</strong><br>';
                $htm .= strlen($body) > 100 ? substr($body, 0, 100) . "..." : $body;
                $td->appendElement('plaintext', array(), $htm, true);
                break;
            case 'pnotification_notified_on':
                $td->appendElement('plaintext', array(), FatDate::format(
                    $row[$key],
                    true,
                    true,
                    FatApp::getConfig('CONF_TIMEZONE', FatUtility::VAR_STRING, date_default_timezone_get())
                ));
                break;
            /* case 'notify_to':
                $buyerHtm = $sellerHtm = '';
                if (0 < $row['pnotification_for_buyer']) {
                    $buyerHtm = '<span class="badge badge-success">' . Labels::getLabel('LBL_BUYERS', $adminLangId) . '</span>';
                }

                if (0 < $row['pnotification_for_seller']) {
                    $sellerHtm = '<span class="badge badge-info">' . Labels::getLabel('LBL_SELLERS', $adminLangId) . '</span>';
                }
                $td->appendElement('plaintext', array(), $buyerHtm . ' ' . $sellerHtm, true);
                break; */
            case 'pnotification_status':
                switch ($row[$key]) {
                    case PushNotification::STATUS_PENDING:
                        $class = 'label--purple';
                        break;
                    case PushNotification::STATUS_PROCESSING:
                        $class = 'label--warning';
                        break;
                    case PushNotification::STATUS_COMPLETED:
                        $class = 'label--success';
                        break;
                    default:
                        $class = 'label-primary';
                        break;
                }
                $htm = '<label class="label ' . $class . '">'  . $statusArr[$row[$key]] . '</label>';
                $td->appendElement('plaintext', array(), $htm, true);
                break;
            case 'action':
                if ($canEdit) {
                    if (PushNotification::STATUS_PENDING == $row['pnotification_status']) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_EDIT', $adminLangId), "onclick" => "addNotificationForm(" . $row['pnotification_id'] . ")"), "<i class='far fa-edit icon'></i>", true);
                    } elseif (PushNotification::STATUS_PENDING != $row['pnotification_status']) {
                        $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_VIEW', $adminLangId), "onclick" => "addNotificationForm(" . $row['pnotification_id'] . ")"), "<i class='far fa-eye icon'></i>", true);
                    }
                    $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_CLONE', $adminLangId), "onclick" => "clone(" . $row['pnotification_id'] . ")"), "<i class='ion-ios-photos icon'></i>", true);
                } 
                break;
        }
    }
}
if (count($arr_listing) == 0) {
    $tbl->appendElement('tr')->appendElement('td', ['colspan' => count($arr_flds)], Labels::getLabel('LBL_No_Records_Found', $adminLangId));
}

echo $tbl->getHtml();

$postedData['page'] = $page;
echo FatUtility::createHiddenFormFromData($postedData, ['name' => 'frmSearchPaging']);
$pagingArr = [
    'pageCount' => $pageCount,
    'page' => $page,
    'pageSize' => $pageSize,
    'recordCount' => $recordCount,
    'adminLangId' => $adminLangId
];
$this->includeTemplate('_partial/pagination.php', $pagingArr, false);
