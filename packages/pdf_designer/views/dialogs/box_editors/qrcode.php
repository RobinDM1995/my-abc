<?php

defined('C5_EXECUTE') or die("Access Denied.");

View::element('/dashboard/Help', null, 'pdf_designer');

?>

<div class="ccm-ui">
    <form method="post" id="ccmEditBoxForm" data-dialog-form="edit-box" action="<?php echo sprintf("%s?templateId=%s&boxId=%s", $this->action('submit'), $box->getTemplateId(), $box->getBoxId()); ?>">
            
        <fieldset>
            <legend>
                <?php echo t("QR Code"); ?>
            </legend>

            <div class="form-group">
                <label for="Data" class="control-label">
                    <?php echo t("Data"); ?>
                </label>        
                
                <input id="Data" name="Data" value="<?php echo $box->getAttribute("Data"); ?>" class="form-control ccm-input-text">
            </div>
        </fieldset>
        
        <div class="dialog-buttons">
            <button class="btn btn-default pull-left" data-dialog-action="cancel">
                <?php echo t('Cancel')?>
            </button>
            
            <button type="button" data-dialog-action="submit" class="btn btn-primary pull-right">
                <?php echo t('Save')?>
            </button>
        </div>
    </form>
</div>