<div class="popup__body">
    <div class="img-container">
      <img src="<?php echo (isset($image)) ? $image : ''; ?>" alt="Picture" id="new-img" class="img_responsive cropper-hidden">
      <div class="loader-positon" id="loader-js"></div>
    </div>
    <span class="gap"></span>
    <div class="text-center rotator-actions" id="actions" >
        <div class="docs-buttons">
            <div class="btn-group">
              <button type="button" class="btn btn-brand" data-method="rotate" data-option="-90" title="<?php echo Labels::getLabel('LBL_Rotate_Left', $adminLangId); ?>">
                <span class="docs-tooltip" data-toggle="tooltip">
                  <span class="fa fa-undo-alt"></span>
                </span>
              </button>
              <button type="button" class="btn btn-brand" data-method="rotate" data-option="90" title="<?php echo Labels::getLabel('LBL_Rotate_Right', $adminLangId); ?>">
                <span class="docs-tooltip" data-toggle="tooltip">
                  <span class="fa fa-redo-alt"></span>
                </span>
              </button>
            </div>
            <div class="btn-group">
              <button type="button" class="btn btn-brand" data-method="scaleX" data-option="-1" title="<?php echo Labels::getLabel('LBL_Flip_Horizontal', $adminLangId); ?>">
                <span class="docs-tooltip" data-toggle="tooltip">
                  <span class="fa fa-arrows-alt-h"></span>
                </span>
              </button>
              <button type="button" class="btn btn-brand" data-method="scaleY" data-option="-1" title="<?php echo Labels::getLabel('LBL_Flip_Vertical', $adminLangId); ?>">
                <span class="docs-tooltip" data-toggle="tooltip">
                  <span class="fa fa-arrows-alt-v"></span>
                </span>
              </button>
            </div>
            <div class="btn-group">
              <button type="button" class="btn btn-brand" data-method="reset" title="<?php echo Labels::getLabel('LBL_Upload_image_file', $adminLangId); ?>">
                 <span class="docs-tooltip" data-toggle="tooltip">
                   <span class="fa fa-sync-alt"></span> <?php echo Labels::getLabel('LBL_Reset', $adminLangId); ?>
                 </span>
              </button>
              <label class="btn btn-brand btn-upload" for="inputImage" title="<?php echo Labels::getLabel('LBL_Upload_image_file', $adminLangId); ?>">
                <input type="file" class="sr-only" id="inputImage" name="file" accept="image/*">
                <span class="docs-tooltip" data-toggle="tooltip">
                  <span class="fa fa-upload"></span> <?php echo Labels::getLabel('LBL_Browse', $adminLangId); ?>
                </span>
              </label>
              <button type="button" class="btn btn-brand" data-method="getCroppedCanvas" title="<?php echo Labels::getLabel('LBL_Update', $adminLangId); ?>">
                <span class="docs-tooltip" data-toggle="tooltip">
                  <span class="fa fa-crop-alt"></span> <?php echo Labels::getLabel('LBL_Crop', $adminLangId); ?>
                </span>
              </button>
            </div>
        </div>
    </div>
</div>
