<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="sectionhead">
    <h4><?php echo Labels::getLabel('LBL_Seller_Packages_Listings', $adminLangId);?>
    </h4>
    <?php
        $url = UrlHelper::generateUrl('FaqCategories');
        $data = [
            'adminLangId' => $adminLangId,
            'statusButtons' => false,
            'deleteButton' => false,
            'otherButtons' => [
                [
                    'attr' => [
                        'href' => 'javascript:void(0)',
                        'onclick' => "searchPackages()",
                        'title' => Labels::getLabel('LBL_BACK', $adminLangId)
                    ],
                    'label' => '<i class="fas fa-arrow-left"></i>'
                ],
            ]
        ];

        if ($canEdit && $spackageData[sellerPackages::DB_TBL_PREFIX . 'type'] != sellerPackages::FREE_TYPE || ($spackageData[sellerPackages::DB_TBL_PREFIX . 'type'] == sellerPackages::FREE_TYPE && empty($arr_listing))) {
            $data['otherButtons'][] = [
                'attr' => [
                    'href' => 'javascript:void(0)',
                    'onclick' => "planForm(" . $spackageId . ")",
                    'title' => Labels::getLabel('LBL_Add_New', $adminLangId)
                ],
                'label' => '<i class="fas fa-plus"></i>'
            ];
        }

        $this->includeTemplate('_partial/action-buttons.php', $data, false);
    ?>
</div>
<div class="sectionbody">
    <div class="tablewrap">
        <?php
        $arr_flds = array(
            'listserial' => Labels::getLabel('LBL_#', $adminLangId),
            SellerPackagePlans::DB_TBL_PREFIX . 'price' => Labels::getLabel('LBL_Plan_Price', $adminLangId),
            'action' => '',
        );

        $tbl = new HtmlElement(
            'table',
            array('width' => '100%', 'class' => 'table table-responsive', 'id' => 'options')
        );

        $th = $tbl->appendElement('thead')->appendElement('tr');
        foreach ($arr_flds as $val) {
            $e = $th->appendElement('th', array(), $val);
        }

        $sr_no = 0;
        foreach ($arr_listing as $sn => $row) {
            $sr_no++;
            $tr = $tbl->appendElement('tr');
            $tr->setAttribute("id", $row[SellerPackagePlans::DB_TBL_PREFIX . 'id']);
            if ($row['spplan_active'] != applicationConstants::ACTIVE) {
                $tr->setAttribute("class", "fat-inactive nodrag nodrop");
            }
            foreach ($arr_flds as $key => $val) {
                $td = $tr->appendElement('td');
                switch ($key) {
                    case 'listserial':
                        $td->appendElement('plaintext', array(), $sr_no);
                        break;
                    case SellerPackagePlans::DB_TBL_PREFIX . 'price':
                        $td->appendElement('plaintext', array(), SellerPackagePlans::getPlanPriceWithPeriod($row, $row[SellerPackagePlans::DB_TBL_PREFIX . 'price']), true);
                        break;
                    case SellerPackagePlans::DB_TBL_PREFIX . 'trial_interval':
                        $td->appendElement('plaintext', array(), SellerPackagePlans::getPlanTrialPeriod($row), true);
                        break;

                        break;

                    case 'action':
                        if ($canEdit) {
                            $td->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn btn-clean btn-sm btn-icon', 'title' => Labels::getLabel('LBL_Edit', $adminLangId), "onclick" => "planForm(" . $row[SellerPackagePlans::DB_TBL_PREFIX . 'spackage_id'] . "," . $row[SellerPackagePlans::DB_TBL_PREFIX . 'id'] . ")"), '<i class="far fa-edit icon"></i>', true);
                        }
                        break;
                    default:
                        $td->appendElement('plaintext', array(), $row[$key], true);
                        break;
                }
            }
        }
        if (count($arr_listing) == 0) {
            $tbl->appendElement('tr')->appendElement('td', array('colspan' => count($arr_flds)), Labels::getLabel('LBL_No_Records_Found', $adminLangId));
        }
        echo $tbl->getHtml();?>
    </div>
</div>