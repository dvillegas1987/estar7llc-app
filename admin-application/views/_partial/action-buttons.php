<?php 
defined('SYSTEM_INIT') or die('Invalid Usage');

$div = new HtmlElement("div", array("class" => "section__toolbar"));
$msg = isset($msg) ? $msg : '';
if ((!isset($statusButtons) || true === $statusButtons)) {
    $div->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn-clean btn-sm btn-icon btn-secondary toolbar-btn-js d-none', 'title' => Labels::getLabel('LBL_Publish', $adminLangId), "onclick" => "toggleBulkStatues(1, '" . $msg . "')"), '<i class="fas fa-eye"></i>', true);

    $div->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn-clean btn-sm btn-icon btn-secondary toolbar-btn-js d-none', 'title' => Labels::getLabel('LBL_Unpublish', $adminLangId), "onclick" => "toggleBulkStatues(0, '" . $msg . "')"), '<i class="fas fa-eye-slash"></i>', true);
}

if (!isset($deleteButton) || true === $deleteButton) {
    $div->appendElement('a', array('href' => 'javascript:void(0)', 'class' => 'btn-clean btn-sm btn-icon btn-secondary toolbar-btn-js d-none', 'title' => Labels::getLabel('LBL_Delete', $adminLangId), "onclick" => "deleteSelected()"), '<i class="fas fa-trash"></i>', true);
}

if (isset($otherButtons) && is_array($otherButtons)) {
    foreach ($otherButtons as $attr) {
        $class = isset($attr['attr']['class']) ? $attr['attr']['class'] : '';
        $attr['attr']['class'] = 'btn-clean btn-sm btn-icon btn-secondary ' . $class;
        $div->appendElement('a', $attr['attr'], (string) $attr['label'], true);
    }
}

echo $div->getHtml();
