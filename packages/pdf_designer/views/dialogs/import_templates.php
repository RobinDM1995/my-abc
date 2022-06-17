<?php

/**
 * @project:   PDFDesigner (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

defined('C5_EXECUTE') or die('Access Denied.');

View::element('/dashboard/Help', null, 'pdf_designer');
?>

<style>
#dropzone { margin-bottom: 3rem; }

.dropzone { border: 2px dashed #0087F7; border-radius: 5px; background: white; }
.dropzone .dz-message { font-weight: 400; text-align: center; padding: 50px 0;}
.dropzone .dz-message .note { font-size: 0.8em; font-weight: 200; display: block; margin-top: 1.4rem; }

.dropzone .dz-preview {
    display: none !important;
}

.dropzone .default.message {
    display: none;
}
</style>

<div class="ccm-ui">
    <form method="post" id="ccmImportTemplateForm" data-dialog-form="import-template" action="<?php echo $this->action('submit'); ?>">

        <div id="dropzone" class="dropzone">
            <div class="dz-message needsclick">
                <?php echo t("Drag the template here or click to import."); ?><br />
            </div>
        </div>

        <script>
            $(document).ready(function() {
                $("#dropzone").dropzone({
                    url: "<?php echo $this->action("import"); ?>",
                    allowedFileTypes: '*',
                    uploadOnDrop: true,
                    uploadOnPreview: false,
                    success: function(res, json) {
                        if (json.success) {
                            $.fn.dialog.closeTop();
                        } else {
                            alert("<?php echo t("The file could not be imported."); ?>");
                        }
                    }
                });
            });
        </script>
            
        <div class="dialog-buttons">
            
            <button type="button" data-dialog-action="cancel" class="btn btn-default pull-right">
                <?php echo t('Close')?>
            </button>
        </div>
    </form>
</div>