<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<div class="block--empty m-auto text-center">
    <div id="dvAlert">
        <div class="cards-message" >
            <div class="cards-message-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="cards-message-text">
                <?php
                if (isset($message)) {
                    echo $message;
                } else {
                    echo Labels::getLabel('LBL_No_record_found', $siteLangId);
                } ?>
            </div>
        </div>
    </div>
</div>
