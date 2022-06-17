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

$form = Core::make("helper/form");
?>

<div class="ccm-ui">
    <form method="post" id="ccmEditBoxForm" data-dialog-form="edit-box" action="<?php echo sprintf("%s?templateId=%s&boxId=%s", $this->action('submit'), $box->getTemplateId(), $box->getBoxId()); ?>">

        <fieldset>
            <legend>
                <?php echo t("Internal Image"); ?>
            </legend>

            <div class="form-group">
                <label for="Image" class="control-label">
                    <?php echo t("Image"); ?>
                </label>        
                
                <?php
                    $al = Loader::helper('concrete/asset_library');
                    
                    $fileId = intval($box->getAttribute("Image"));
                    
                    if ($fileId > 0) {
                        $bf = File::getByID($fileId);
                    } else {
                        $bf = null;
                    }
                    
                    echo $al->file('Image', 'Image', t('Choose File'), $bf)
                ?>
            </div>
            
            <p class="help-block">
                <?php echo t("Leave this field blank to use an external URL."); ?>
            </p>

        </fieldset>
            
        <fieldset>
            <legend>
                <?php echo t("External Image"); ?>
            </legend>
            
            <div class="form-group">
                <label for="Image" class="control-label">
                    <?php echo t("Image URL"); ?>
                </label>        
                
                <?php echo $form->text("ImageUrl", $box->getAttribute("ImageUrl")); ?>
            </div>
            
            <p class="help-block">
                <?php echo t("You can enter a URL or use a {placeholder}."); ?>
            </p>

            <div class="form-group">
                <label for="ImageFileType" class="control-label">
                    <?php echo t("Image File Type"); ?>
                </label>        
                
                <?php echo $form->select("ImageFileType", array("JPG" => t("JPG"), "PNG" => t("PNG"), "GIF" => t("GIF")), $box->getAttribute("ImageFileType")); ?>
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