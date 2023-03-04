<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="block--empty my-5 text-center">
    <img class="block__img mx-auto mb-3 " src="<?php echo CONF_WEBROOT_URL; ?>images/empty_item.svg" alt="<?php echo Labels::getLabel('LBL_No_record_found', $adminLangId); ?>" width="80">
    <h4><?php if (isset($message)) {
        echo $message;
        } else {
            echo Labels::getLabel('LBL_No_record_found', $adminLangId);
        } ?>
    </h4>
    <div class="action">
        <?php if (!empty($linkArr)) {
            foreach ($linkArr as $link) {
                $onClick = isset($link['onClick']) ? "onClick='".$link['onClick']."'" : "";
                echo "<a href='".$link['href']."' class='themebtn btn-default btn-sm'" .$onClick.  ">".$link['label']."</a>";
            }
        }?>
    </div>
</div>
