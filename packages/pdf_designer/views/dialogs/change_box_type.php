<?php

/**
 * @project:   PDFDesigner (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

defined('C5_EXECUTE') or die("Access Denied.");

View::element('/dashboard/Help', null, 'pdf_designer');
?>

<div class="ccm-ui">
    <form method="post" id="ccmEditBoxForm" data-dialog-form="change-box-type" action="<?php echo sprintf("%s?templateId=%s&boxId=%s", $this->action('submit'), $box->getTemplateId(), $box->getBoxId()); ?>">

        <fieldset>
            <legend>
                <?php echo t("Box Type"); ?>
            </legend>

            <div class="form-group">
                <label for="boxType" class="control-label">
                    <?php echo t("Box Type"); ?>
                </label>    
                
                <select name="boxType" class="form-control ccm-input-text">
                    <?php
                        foreach ($box->getEditors() as $editorId => $editorName) {
                            print sprintf(
                                "<option value=\"%s\"%s>%s</option>\n",
                                $editorId,
                                $editorId === $box->getBoxType() ? " selected" : "",
                                $editorName
                            );
                        }
                    ?>
                </select>
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