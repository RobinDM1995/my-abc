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
    <form method="post" id="ccmEditBoxForm" data-dialog-form="change-position" action="<?php echo sprintf("%s?templateId=%s&boxId=%s", $this->action('submit'), $box->getTemplateId(), $box->getBoxId()); ?>">

        <?php if ($box->getTemplateEntity()->getUseMm()): ?>
            <div class="alert alert-info">
                <?php echo t("All dimensions in mm."); ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <?php echo t("All dimensions in inch."); ?>
            </div>
        <?php endif; ?>
        
        <fieldset>
            <legend>
                <?php echo t("Position"); ?>
            </legend>

            <div class="form-group">
                <label for="positionType" class="control-label">
                    <?php echo t("Position"); ?>
                </label>    
                
                <select name="positionType" class="form-control ccm-input-text">
                    <?php
                        foreach ($box->getPositionTypes() as $positionType => $positionTypeLabel) {
                            print sprintf(
                                "<option value=\"%s\"%s>%s</option>\n",
                                $positionType,
                                $positionType === $box->getPositionType() ? " selected" : "",
                                $positionTypeLabel
                            );
                        }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="yPos" class="control-label">
                    <?php echo t("Top"); ?>
                </label>

                <div class="dimension-mm">
                    <input type="text" name="yPos" id="yPos" class="form-control recalculate-dimension ccm-input-text" value="<?php echo $box->getYPos(); ?>" data-copy-dimension="yPosInch" data-copy-dimension-type="inch">
                </div>
                
                <div class="dimension-inch hidden">
                    <input type="text" id="yPosInch" name="yPosInch" class="form-control recalculate-dimension ccm-input-text" value="<?php echo $box->getYPosInch(); ?>" data-copy-dimension="yPos" data-copy-dimension-type="mm" data-inch-init-value="<?php echo $box->getYPos(); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="xPos" class="control-label">
                    <?php echo t("Left"); ?>
                </label>

                <div class="dimension-mm">
                    <input type="text" name="xPos" id="xPos" class="form-control recalculate-dimension ccm-input-text" value="<?php echo $box->getXPos(); ?>" data-copy-dimension="xPosInch" data-copy-dimension-type="inch">
                </div>
                
                <div class="dimension-inch hidden">
                    <input type="text" id="xPosInch" name="xPosInch" class="form-control recalculate-dimension ccm-input-text" value="<?php echo $box->getXPosInch(); ?>" data-copy-dimension="xPos" data-copy-dimension-type="mm" data-inch-init-value="<?php echo $box->getXPos(); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="width" class="control-label">
                    <?php echo t("Width"); ?>
                </label>

                <div class="dimension-mm">
                    <input type="text" name="width" id="Width" class="form-control recalculate-dimension ccm-input-text" value="<?php echo $box->getWidth(); ?>" data-copy-dimension="WidthInch" data-copy-dimension-type="inch">
                </div>
                
                <div class="dimension-inch hidden">
                    <input type="text" id="WidthInch" name="WidthInch" class="form-control recalculate-dimension ccm-input-text" value="<?php echo $box->getWidthInch(); ?>" data-copy-dimension="Width" data-copy-dimension-type="mm" data-inch-init-value="<?php echo $box->getWidth(); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="height" class="control-label">
                    <?php echo t("Height"); ?>
                </label>

                <div class="dimension-mm">
                    <input type="text" name="height" id="Height" class="form-control recalculate-dimension ccm-input-text" value="<?php echo $box->getHeight(); ?>" data-copy-dimension="HeightInch" data-copy-dimension-type="inch">
                </div>
                
                <div class="dimension-inch hidden">
                    <input type="text" id="HeightInch" name="HeightInch" class="form-control recalculate-dimension ccm-input-text" value="<?php echo $box->getHeightInch(); ?>" data-copy-dimension="Height" data-copy-dimension-type="mm" data-inch-init-value="<?php echo $box->getHeight(); ?>">
                </div>
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
        
        <script>
            pdfDesigner.__initDimensionSwitcher();
        </script>
    </form>
</div>