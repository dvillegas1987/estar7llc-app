<div class="popup__body">
    <div class="img-container">
      <img src="<?php echo (isset($image)) ? $image : ''; ?>" alt="Picture" id="new-img" class="img_responsive cropper-hidden">
      <div class="loader-positon" id="loader-js"></div>
    </div>
    <span class="gap"></span>
    <div class="align--center rotator-actions" id="actions" >
        <div class="docs-buttons">
            <div class="btn-group">
              <button type="button" class="btn btn-secondary" data-method="rotate" data-option="-90" title="<?php echo Labels::getLabel('LBL_Rotate_Left', $siteLangId); ?>">
                <span class="docs-tooltip" data-toggle="tooltip" data-placement="right">
                  <span class="fa fa-undo-alt"></span>
                </span>
              </button>
              <button type="button" class="btn btn-secondary" data-method="rotate" data-option="90" title="<?php echo Labels::getLabel('LBL_Rotate_Right', $siteLangId); ?>">
                <span class="docs-tooltip" data-toggle="tooltip" data-placement="right">
                  <span class="fa fa-redo-alt"></span>
                </span>
              </button>
            </div>
            <div class="btn-group">
              <button type="button" class="btn btn-secondary" data-method="scaleX" data-option="-1" title="<?php echo Labels::getLabel('LBL_Flip_Horizontal', $siteLangId); ?>">
                <span class="docs-tooltip" data-toggle="tooltip" data-placement="right">
                  <span class="fa fa-arrows-alt-h"></span>
                </span>
              </button>
              <button type="button" class="btn btn-secondary" data-method="scaleY" data-option="-1" title="<?php echo Labels::getLabel('LBL_Flip_Vertical', $siteLangId); ?>">
                <span class="docs-tooltip" data-toggle="tooltip" data-placement="right">
                  <span class="fa fa-arrows-alt-v"></span>
                </span>
              </button>
            </div>
            <div class="btn-group">
               <button type="button" class="btn btn-secondary" data-method="reset" title="<?php echo Labels::getLabel('LBL_Upload_image_file', $siteLangId); ?>">
                <span class="docs-tooltip" data-toggle="tooltip" data-placement="right">
                  <span class="fa fa-sync-alt"></span> <?php echo Labels::getLabel('LBL_Reset', $siteLangId); ?>
                </span>
              </button>
              <label class="btn btn-secondary btn-upload mb-0" for="inputImage" title="<?php echo Labels::getLabel('LBL_Upload_image_file', $siteLangId); ?>">
                <input type="file" class="sr-only" id="inputImage" name="file" accept="image/*">
                <span class="docs-tooltip" data-toggle="tooltip" data-placement="right">
                  <span class="fa fa-upload"></span> <?php echo Labels::getLabel('LBL_Browse', $siteLangId); ?>
                </span>
              </label>
              <button type="button" class="btn btn-secondary" data-method="getCroppedCanvas" title="<?php echo Labels::getLabel('LBL_Update', $siteLangId); ?>">
                <span class="docs-tooltip" data-toggle="tooltip" data-placement="right">
                  <span class="fa fa-crop-alt"></span> <?php echo Labels::getLabel('LBL_Crop', $siteLangId); ?>
                </span>
              </button>
            </div>
        </div>
    </div>
</div>
