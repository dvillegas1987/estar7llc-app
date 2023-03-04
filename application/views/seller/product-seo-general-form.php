<?php require_once('sellerProductSeoTop.php');?>
<div class="form__subcontent">
    <?php
        $productSeoForm->setFormTagAttribute('class', 'form form--horizontal');
        $productSeoForm->setFormTagAttribute('onsubmit', 'setupProductMetaTag(this); return(false);');
        $productSeoForm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-';
        $productSeoForm->developerTags['fld_default_col'] = 12;
        echo $productSeoForm->getFormHtml();
    ?>
</div>
