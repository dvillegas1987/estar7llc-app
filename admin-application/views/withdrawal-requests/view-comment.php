<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_VIEW_COMMENT', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">	
        <div class="border-box border-box--space">
            <p><?php echo nl2br($comment); ?></p>
        </div>	
    </div>
</section>
