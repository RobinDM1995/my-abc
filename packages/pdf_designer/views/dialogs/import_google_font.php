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


<div class="ccm-ui">
    <form method="post" id="ccmImportGoogleFontForm" data-dialog-form="import-google-font" action="<?php echo $this->action('submit'); ?>" class="ccm-search-fields">
        
        <div class="form-group">
            <div class="ccm-search-main-lookup-field">
                <i class="fa fa-search"></i>
                <input id="searchTerm" placeholder="<?php echo t("Search Fonts..."); ?>" type="text" class="form-control ccm-input-search">
            </div>
        </div>

        <table border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table" id="fontsTable">
            <thead>
                <tr>
                    <th class="false">
                        <a href="javascript:void(0);">
                            <?php echo t("Font Name"); ?>
                        </a>
                    </th>

                    <th class="false">
                        &nbsp;
                    </th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($fonts as $font): ?>
                    <tr class="fontRow" data-font="<?php echo $font; ?>">
                        <td>
                            <?php echo $font; ?>
                        </td>

                        <td>
                            <div class=" pull-right">
                                <a href="javascript:void(0);" data-font="<?php echo $font; ?>" class="btn btn-default installFont">
                                    <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t("Install Font"); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="dialog-buttons">
            
            <button type="button" data-dialog-action="cancel" class="btn btn-default pull-right">
                <?php echo t('Close')?>
            </button>
        </div>
    </form>
</div>

<script>
    var searchFont = function(searchTerm) {
        $(".fontRow").each(function() {
            if (($(this).data("font").toLowerCase().indexOf(searchTerm.toLowerCase()) >= 0)) {
                $(this).removeClass("hidden");
            } else {
                $(this).addClass("hidden");
            }
        });
    };
    
    $(document).ready(function() {
        $(".installFont").bind("click", function() {
            var font = $(this).data("font");
            
            pdfDesigner.__installGoogleFont(font);
        });
        
        $("#searchTerm").bind("keyup change", function() {
            searchFont($(this).val());
        });
    });
</script>
