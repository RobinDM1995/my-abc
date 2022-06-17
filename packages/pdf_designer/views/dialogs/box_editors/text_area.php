<?php

/**
 * @project:   PDFDesigner (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

defined('C5_EXECUTE') or die("Access Denied.");

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
  
View::element('/dashboard/Help', null, 'pdf_designer');
?>

<div class="ccm-ui">
    <form method="post" id="ccmEditBoxForm" data-dialog-form="edit-box" action="<?php echo sprintf("%s?templateId=%s&boxId=%s", $this->action('submit'), $box->getTemplateId(), $box->getBoxId()); ?>">

        <fieldset>
            <legend>
                <?php echo t("Font Settings"); ?>
            </legend>

            <div class="form-group">
                <label for="FontName" class="control-label">
                    <?php echo t("Font name"); ?>
                </label>        
                
                <select name="FontName" class="form-control ccm-input-text">
                    <?php
                        $selectedFontName = $box->getAttribute("FontName");

                        foreach ($box->getFontNames() as $fontName) {
                            print sprintf(
                                "<option value=\"%s\"%s>%s</option>\n",
                                $fontName,
                                $fontName === $selectedFontName ? " selected" : "",
                                $fontName
                            );
                        }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="FontSize" class="control-label">
                    <?php echo t("Font Size"); ?>
                </label>        
                
                <select name="FontSize" class="form-control ccm-input-text">
                    <?php
                        $selectedFontSize = intval($box->getAttribute("FontSize", 10));

                        foreach ($box->getFontSizes() as $fontSize) {
                            print sprintf(
                                "<option value=\"%s\"%s>%s pt</option>\n",
                                $fontSize,
                                $fontSize === $selectedFontSize ? " selected" : "",
                                $fontSize
                            );
                        }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="FontColor" class="control-label">
                    <?php echo t("Font Color"); ?>
                </label>  
        
                <input type="text" class="form-control ccm-input-text colorpicker" name="FontColor" value="<?php echo $box->getAttribute("FontColor", "#000000"); ?>">     
            </div>
        </fieldset>

        <fieldset>
            <legend>
                <?php echo t("Content"); ?>
            </legend>

            <div class="form-group">
                <label for="Text" class="control-label">
                    <?php echo t("Content"); ?>
                </label>       

                <?php $uniqueId = $app->make('helper/validation/identifier')->getString(18); ?>
              
                <?php if (version_compare(APP_VERSION, '5.7.4', '>=')): ?>
                    <?php
                        $editor = $app->make('editor');

                        echo $editor->outputStandardEditor('Text', $box->getAttribute("Text"), array('id' => 'Text-' . $uniqueId))
                    ?>
                <?php else: // Legacy support?>
                    <textarea id="Text-<?php echo $uniqueId; ?>" name="Text" class="form-control ccm-input-text"><?php echo $box->getAttribute("Text"); ?></textarea>

                    <script>
                        $(document).ready(function() {
                            $("#Text-<?php echo $uniqueId; ?>").redactor({
                                plugins: ['alignment', 'undoredo', 'table'],
                                buttons: ['bold', 'underline', 'italic', 'link', 'image', 'horizontalrule', 'alignment']
                            });
                        });
                    </script>
                <?php endif; ?>
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

<style>
    .ui-colorpicker-rgb label, .ui-colorpicker-hsv label, .ui-colorpicker-hsl label, .ui-colorpicker-lab label, .ui-colorpicker-cmyk label, .ui-colorpicker-alpha label {
        width: 2.5em;
        display: inline-block;
        margin-left: 10px;
    }
    
    .ui-colorpicker-preview-container {
        display: none !important;
    }
    
    .ui-colorpicker-swatches {
        display: none !important;
    }
    
    .ui-colorpicker-hsv,
    .ui-colorpicker-lab,
    .ui-colorpicker-rgb,
    .ui-colorpicker-cmyk
    {
        margin-top: 10px !important;
        margin-left: 10px !important;
        margin-right: 10px !important;
    }
    
    .ui-colorpicker-cmyk,
    .ui-colorpicker-lab {
        margin-left: -20px !important;
        margin-right: 40px !important;
        
    }
    
    .ui-table-wrapper {
        background: #fff;
        padding-bottom: 1px;
        padding-top: 1px;
    }
    
    .ui-colorpicker table {
        margin: 25px;
        background: transparent !important;
    }
</style>
    
<script>
    $(document).ready(function() {
        $('.colorpicker').colorpicker({
            modal: true,
            okOnEnter: true,
            parts: "full",
            colorFormat: "<?php echo $box->getTemplateEntity()->getUseCmyk() ? "cp,mp,yp,kp" : "#HEX"; ?>",
            inline: true,
            regional: '<?php echo(in_array(Localization::activeLanguage(), array("de", "el", "en", "fr", "nl", "pt-br", "ru")) ? Localization::activeLanguage() : "en"); ?>',
            title: '',
            open: function() {
                // transform markup
                var $p = $(".ui-colorpicker");
                
                $p.addClass("ccm-ui");
                
                $p.find("table").wrap("<div class=\"ui-table-wrapper\" />");
                
                $p.find(".ui-dialog-titlebar-close").remove();
                
                $p.find(".ui-dialog-buttonset")
                    .removeClass("ui-dialog-buttonset")
                    .parent()
                    .removeClass("ui-dialog-buttonset")
                    .addClass("dialog-buttons")
                    .addClass("ui-helper-clearfix")
                    .addClass("ccm-ui");

                $p.find(".ui-colorpicker-cancel")
                    .attr("class", "")
                    .addClass("btn")
                    .addClass("pull-left")
                    .addClass("btn-default");
                
                $p.find(".ui-colorpicker-ok")
                    .attr("class", "")
                    .addClass("btn")
                    .addClass("btn-primary")
                    .addClass("pull-right");
            }
        });
    });
</script>