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
<?php \Concrete\Core\View\View::element('/dashboard/license_check', array("packageHandle" => "pdf_designer"), 'pdf_designer'); ?>

<div class="ccm-dashboard-header-buttons">
    <a href="<?php echo $this->action("preview", $templateId); ?>" class="btn btn-default" target="_blank">
        <i class="fa fa-file-pdf-o" aria-hidden="true"></i> <?php echo t("Preview"); ?>
    </a>

    <a href="javascript:void(0);" class="btn btn-default" onclick="$('#editGeneralSettings').click();">
        <i class="fa fa-wrench" aria-hidden="true"></i> <?php echo t("Template Options"); ?>
    </a>
</div>

<?php \Concrete\Core\View\View::element('/dashboard/did_you_know', array("packageHandle" => "pdf_designer"), 'pdf_designer'); ?>

<div class="ccm-dashboard-form-actions-wrapper">
    <div class="ccm-dashboard-form-actions">
        <a href="<?php echo $this->url("/dashboard/pdf_designer"); ?>" class="btn btn-default">
            <i class="fa fa-angle-left" aria-hidden="true"></i> <?php echo t("Back to Overview"); ?>
        </a>
    </div>
</div>

<script id="leftPanelBoxTypes" type="x-tmpl-mustache">
    <div id="ccm-panel-add-block" class="ccm-panel ccm-panel-left ccm-panel-transition-slide ccm-panel-active ccm-panel-loaded" style="display: none;">
        <div class="ccm-panel-content-wrapper ccm-ui">
            <div class="ccm-panel-content ccm-panel-content-visible">
                <section>
                    <div data-panel-menu="accordion" class="ccm-panel-header-accordion">
                        <nav>
                            <span>
                                <?php echo t("Box Types"); ?>
                            </span>
                        </nav>
                    </div>

                    <div class="ccm-panel-content-inner" id="ccm-panel-add-blocktypes-list">

                        <div class="ccm-panel-add-block-set">
                            <ul>
                                <?php foreach ($boxTypes as $boxTypeId => $boxType): ?>
                                    <li>
                                        <a data-box-type="<?php echo $boxTypeId; ?>" class="ccm-panel-add-block-draggable-block-type" href="javascript:void(0)">
                                            <p>
                                                <img src="<?php echo $pdfDesigner->getBoxImage($boxTypeId); ?>">

                                                <span>
                                                    <?php echo $boxType; ?>
                                                </span>
                                            </p>

                                            <a data-box-type="<?php echo $boxTypeId; ?>" class="ccm-panel-add-block-draggable-block-type-dragger" style="user-select: none; cursor: pointer;"></a>

                                            <div class="ccm-block-cover"></div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</script>

<script id="leftPanel" type="x-tmpl-mustache">
    <div id="ccm-panel-page" class="ccm-panel ccm-panel-left ccm-panel-transition-slide ccm-panel-loaded ccm-panel-active" style="display: none;">
        <div class="ccm-panel-content-wrapper ccm-ui">
            <div class="ccm-panel-content ccm-panel-content-visible">
                <section>
                    <header>
                        <?php echo t("Template Settings"); ?>
                    </header>

                    <menu class="ccm-panel-page-basics">
                        <li>
                            <a href="javascript:void(0);" data-panel-page="general" class="ccm-panel-menu-item-active">
                                <?php echo t("General"); ?>
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0);" data-panel-page="paper-type">
                                <?php echo t("Paper Size"); ?>
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0);" data-panel-page="margins">
                                <?php echo t("Margins"); ?>
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0);" data-panel-page="letter-paper">
                                <?php echo t("Letterhead"); ?>
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0);" data-panel-page="custom-fonts">
                                <?php echo t("Custom Fonts"); ?>
                            </a>
                        </li>
                    </menu>

                    <menu>
                        <li>
                            <a href="javascript:void(0);" data-panel-page="sample-placeholders">
                                <?php echo t("Sample Placeholders"); ?>
                            </a>
                        </li>

                        <li>
                            <a href="javascript:void(0);" data-panel-page="code-embed">
                                <?php echo t("Embed"); ?>
                            </a>
                        </li>
                    </menu>
                </section>
            </div>
        </div>
    </div>
</script>

<script id="fontsTable" type="x-tmpl-mustache">
    {{#fonts}}
        <div id="font-{{id}}" class="well">
            <div class="form-group">
                <label class="control-label">
                    <?php echo t("Font Name"); ?>
                </label>

                <div>
                    <input type="text" value="{{fontName}}" class="form-control" name="fonts[{{id}}][fontName]">
                </div>
            </div>

            <div class="form-group">
                <label class="control-label">
                    <?php echo t("Font File (Regular)"); ?>
                </label>

                <div class="ccm-file-selector" data-file-selector="fonts[{{id}}][regularFontFileId]" data-file-id="{{regularFontFileId}}"></div>
            </div>

            <div class="form-group">
                <label class="control-label">
                    <?php echo t("Font File (Italic)"); ?>
                </label>

                <div class="ccm-file-selector" data-file-selector="fonts[{{id}}][italicFontFileId]" data-file-id="{{italicFontFileId}}"></div>
            </div>

            <div class="form-group">
                <label class="control-label">
                    <?php echo t("Font File (Bold)"); ?>
                </label>

                <div class="ccm-file-selector" data-file-selector="fonts[{{id}}][boldFontFileId]" data-file-id="{{boldFontFileId}}"></div>
            </div>

            <div class="form-group">
                <label class="control-label">
                    <?php echo t("Font File (Bold Italic)"); ?>
                </label>

                <div class="ccm-file-selector" data-file-selector="fonts[{{id}}][boldItalicFontFileId]" data-file-id="{{boldItalicFontFileId}}"></div>
            </div>

            <a href="javascript:void(0);" class="btn btn-danger deleteFont" data-id="{{id}}">
                <i class="fa fa-trash" aria-hidden="true"></i> <?php echo t("Remove Font"); ?>
            </a>
        </div>
    {{/fonts}}
</script>

<script id="templateSettings" type="x-tmpl-mustache">
    <div id="templateSettingsContainer">
        <div id="ccm-panel-detail-form-actions-wrapper" class="ccm-ui">
            <div class="ccm-panel-detail-form-actions dialog-buttons" style="opacity: 1;">
                <a href="javascript:void(0);" class="pull-right btn btn-success" type="button">
                    <?php echo t("Save Changes"); ?>
                </a>

                <a href="javascript:void(0);" class="btn btn-default" onclick="pdfDesigner.__closeSideMenu();">
                    <?php echo t("Cancel"); ?>
                </a>
            </div>
        </div>

        <div id="ccm-panel-detail-page" class="ccm-panel-detail ccm-panel-detail-transition-fade ccm-panel-detail-transition-fade-apply">
            <form method="post" action="#" class="ccm-panel-detail-content-form">
                <div class="panel-page general">
                    <div class="ccm-panel-detail-content">

                        <section class="ccm-ui">
                            <header>
                                <?php echo t("General"); ?>
                            </header>


                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Template name"); ?>
                                </label>

                                <div>
                                    <input type="text" class="form-control" name="templateTitle" id="templateTitle" value="{{templateTitle}}">

                                    <div class="help-block">
                                        <?php echo t("Required"); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Dimension type"); ?>
                                </label>

                                <div>
                                    <select class="form-control changeDimensionControl" name="useMm" id="useMm" data-set-value="{{#useMm}}1{{/useMm}}{{^useMm}}0{{/useMm}}">
                                        <option value="1"{{#useMm}} selected{{/useMm}}><?php echo t("mm"); ?></option>
                                        <option value="0"{{^useMm}} selected{{/useMm}}><?php echo t("inch"); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Color model"); ?>
                                </label>

                                <div>
                                    <select class="form-control" name="useCmyk" id="useCmyk" data-set-value="{{#useCmyk}}1{{/useCmyk}}{{^useCmyk}}0{{/useCmyk}}">
                                        <option value="1"{{#useCmyk}} selected{{/useCmyk}}><?php echo t("CMYK"); ?></option>
                                        <option value="0"{{^useCmyk}} selected{{/useCmyk}}><?php echo t("RGB"); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Grid"); ?>
                                </label>

                                <div>
                                    <select class="form-control" name="showGrid" id="showGrid" data-set-value="{{#showGrid}}1{{/showGrid}}{{^showGrid}}0{{/showGrid}}">
                                        <option value="1"{{#showGrid}} selected{{/showGrid}}><?php echo t("Yes"); ?></option>
                                        <option value="0"{{^showGrid}} selected{{/showGrid}}><?php echo t("No"); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Grid Size"); ?>
                                </label>

                                <div class="dimension-mm">
                                    <input type="text" class="form-control recalculate-dimension" name="gridSize" id="gridSize" value="{{gridSize}}" data-copy-dimension="gridSizeInch" data-copy-dimension-type="inch">

                                    <div class="help-block">
                                        <?php echo t("Dimension in mm"); ?>
                                    </div>
                                </div>

                                <div class="dimension-inch hidden">
                                    <input type="text" class="form-control recalculate-dimension" name="gridSizeInch" value="{{gridSizeInch}}" id="gridSizeInch" data-copy-dimension="gridSize" data-copy-dimension-type="mm" data-inch-init-value="{{gridSize}}">

                                    <div class="help-block">
                                        <?php echo t("Dimension in inch"); ?>
                                    </div>
                                </div>

                            </div>
                        </section>
                    </div>
                </div>

                <div class="panel-page paper-type hidden">
                    <div class="ccm-panel-detail-content">

                        <section class="ccm-ui">
                            <header>
                                <?php echo t("Paper Size"); ?>
                            </header>

                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Paper Size"); ?>
                                </label>

                                <div>

                                    <?php echo $form->select("paperType", $paperTypes, null, array("data-set-value" => "{{paperType}}")); ?>

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Page Orientation"); ?>
                                </label>

                                <div>
                                    <select class="form-control" name="portraitMode" id="portraitMode" data-set-value="{{#portraitMode}}1{{/portraitMode}}{{^portraitMode}}0{{/portraitMode}}">
                                        <option value="1"{{#portraitMode}} selected{{/portraitMode}}><?php echo t("Portrait"); ?></option>
                                        <option value="0"{{^portraitMode}} selected{{/portraitMode}}><?php echo t("Landscape"); ?></option>
                                    </select>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="panel-page margins hidden">
                    <div class="ccm-panel-detail-content">

                        <section class="ccm-ui">
                            <header>
                                <?php echo t("Margins"); ?>
                            </header>


                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Top"); ?>
                                </label>

                                <div class="dimension-mm">
                                    <input type="text" class="form-control recalculate-dimension" name="marginTop" id="marginTop" value="{{marginTop}}" data-copy-dimension="marginTopInch" data-copy-dimension-type="inch">

                                    <div class="help-block">
                                        <?php echo t("Dimension in mm"); ?>
                                    </div>
                                </div>

                                <div class="dimension-inch hidden">
                                    <input type="text" class="form-control recalculate-dimension" id="marginTopInch" name="marginTopInch" value="{{marginTopInch}}" data-copy-dimension="marginTop" data-copy-dimension-type="mm" data-inch-init-value="{{marginTop}}">

                                    <div class="help-block">
                                        <?php echo t("Dimension in inch"); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Bottom"); ?>
                                </label>

                                <div class="dimension-mm">
                                    <input type="text" class="form-control recalculate-dimension" name="marginBottom" id="marginBottom" value="{{marginBottom}}" data-copy-dimension="marginBottomInch" data-copy-dimension-type="inch">

                                    <div class="help-block">
                                        <?php echo t("Dimension in mm"); ?>
                                    </div>
                                </div>

                                <div class="dimension-inch hidden">
                                    <input type="text" class="form-control recalculate-dimension" id="marginBottomInch" name="marginBottomInch" value="{{marginBottomInch}}" data-copy-dimension="marginBottom" data-copy-dimension-type="mm" data-inch-init-value="{{marginBottom}}">

                                    <div class="help-block">
                                        <?php echo t("Dimension in inch"); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Left"); ?>
                                </label>

                                <div class="dimension-mm">
                                    <input type="text" class="form-control recalculate-dimension" name="marginLeft" id="marginLeft" value="{{marginLeft}}" data-copy-dimension="marginLeftInch" data-copy-dimension-type="inch">

                                    <div class="help-block">
                                        <?php echo t("Dimension in mm"); ?>
                                    </div>
                                </div>

                                <div class="dimension-inch hidden">
                                    <input type="text" class="form-control recalculate-dimension" id="marginLeftInch" name="marginLeftInch" value="{{marginLeftInch}}" data-copy-dimension="marginLeft" data-copy-dimension-type="mm" data-inch-init-value="{{marginLeft}}">

                                    <div class="help-block">
                                        <?php echo t("Dimension in inch"); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Right"); ?>
                                </label>

                                <div class="dimension-mm">
                                    <input type="text" class="form-control recalculate-dimension" name="marginRight" id="marginRight" value="{{marginRight}}" data-copy-dimension="marginRightInch" data-copy-dimension-type="inch">

                                    <div class="help-block">
                                        <?php echo t("Dimension in mm"); ?>
                                    </div>
                                </div>

                                <div class="dimension-inch hidden">
                                    <input type="text" class="form-control recalculate-dimension" id="marginRightInch" name="marginRightInch" value="{{marginRightInch}}" data-copy-dimension="marginRight" data-copy-dimension-type="mm" data-inch-init-value="{{marginRight}}">

                                    <div class="help-block">
                                        <?php echo t("Dimension in inch"); ?>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div  class="panel-page custom-fonts hidden">
                    <div class="ccm-panel-detail-content">

                        <section class="ccm-ui">
                            <header>
                                <?php echo t("Custom Fonts"); ?>
                            </header>

                            <div class="alert alert-info">
                                <?php echo t("Here you can manage your Custom Fonts. You have to allow the file extensions TTF, OTF and PFB first in your concrete5 settings to enable this feature. Please go to <a href=\"%s\" target=\"_blank\">Sytem &amp; Settings > Files > Allowed File Types</a>.", $this->url("/dashboard/system/files/filetypes")); ?>
                            </div>

                            <div class="clearfix"></div>

                            <a href="javascript:void(0);" id="addFont" class="btn btn-default pull-left">
                                <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t("Add Font"); ?>
                            </a>

                            <a href="javascript:void(0);" id="importGoogleFont" class="btn btn-default pull-left" style="margin-left: 15px;">
                                <i class="fa fa-upload" aria-hidden="true"></i> <?php echo t("Import Font from Google"); ?>
                            </a>

                            <div class="clearfix"></div>

                            <hr>

                            <div id="fontsTableView"></div>
                        </section>
                    </div>
                </div>

                <div  class="panel-page letter-paper hidden">
                    <div class="ccm-panel-detail-content">

                        <section class="ccm-ui">
                            <header>
                                <?php echo t("Letterhead"); ?>
                            </header>

                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("First Page"); ?>
                                </label>

                                <div class="ccm-file-selector" data-file-selector="letterPaperFirstPageFileId" data-file-id="{{letterPaperFirstPageFileId}}">

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <?php echo t("Following Page"); ?>
                                </label>

                                <div class="ccm-file-selector" data-file-selector="letterPaperFollowingPageFileId" data-file-id="{{letterPaperFollowingPageFileId}}">

                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="panel-page sample-placeholders hidden">
                    <div class="ccm-panel-detail-content">

                        <section class="ccm-ui">
                            <header>
                                <?php echo t("Sample Placeholders"); ?>
                            </header>

                            <p>
                                <?php echo t("Create sample placeholders here to test the PDF generation."); ?>
                            </p>

                            <hr>

                            <textarea name="sampleData" id="sampleData" class="hidden">{{sampleData}}</textarea>

                            <div id="jsonEditor" class="json-editor"></div>
                        </section>
                    </div>
                </div>


                <div class="panel-page code-embed hidden">
                    <div class="ccm-panel-detail-content">

                        <section class="ccm-ui">
                            <header>
                                <?php echo t("Embed"); ?>
                            </header>

                            <p class="lead">
                                <?php echo t("Simple Usage"); ?>
                            </p>

                            <p>
                                <?php echo t("The following lines of code will output the current template as a PDF:"); ?>
                            </p>

<pre>
use Concrete\Package\PdfDesigner\Src\PDFDesigner;

...

PDFDesigner::getTemplateByName("{{templateTitle}}")->outputPDF();
</pre>

                            <hr>

                            <p class="lead">
                                <?php echo t("Working with Placeholders"); ?>
                            </p>

                            <p>
                                <?php echo t("The following sample code explains you, how to fill the template with dynamic content."); ?>
                            </p>

                            <p>
                                <?php echo t("Paste following code snippet into your sourcecode:"); ?>
                            </p>

<pre>
use Concrete\Package\PdfDesigner\Src\PDFDesigner;

...

// Use createPDF instead of outputPDF, if you want the get the generated PDF data as a string.
PDFDesigner::getTemplateByName("{{templateTitle}}")->outputPDF(
    array(
        "receiver" => array(
            "firstname" => "Mister",
            "lastname" => "Smith",
            "street" => "Name of the Street",
            "zip" => "Zip Code",
            "city" => "City"
        )
    )
);
</pre>

                            <p>
                                <?php echo t("Click on Edit Content of a box and paste the following placeholders into the content area."); ?>
                            </p>


<pre>
{receiver.firstname} {receiver.lastname}
{receiver.street}
{receiver.zip} {receiver.city}
</pre>

                            <hr>

                            <p class="lead">
                                <?php echo t("Working with Tables"); ?>
                            </p>

                            <p>
                                <?php echo t("The following sample code explains you, how to fill a Table Box with dynamic content."); ?>
                            </p>

                            <p>
                                <?php echo t("Paste following code snippet into your sourcecode:"); ?>
                            </p>

<pre>
PDFDesigner::getTemplateByName("{{templateTitle}}")->outputPDF(
    array(
        "myTable" => array(
            // Define the Columns
            "columns" => array(
                array(
                    "align" => "L", // Left Align
                    "text" => "Column 1"
                ),
                array(
                    "align" => "C", // Left Align
                    "text" => "Column 2"
                ),
                array(
                    "align" => "R", // Right Align
                    "text" => "Column 3"
                )
            ),

            "rows" => array(
                array(
                    "Data 1",
                    "Data 2",
                    "Data 3"
                ),
                array(
                    "Data 1",
                    "Data 2",
                    "Data 3"
                ),
                array(
                    "Data 1",
                    "Data 2",
                    "Data 3"
                )

                // Add so many rows you want...
            )
        )
    )
);
</pre>
                            <p>
                                <?php echo t("Click on Edit Content of a Table Box and set the Data Source to <strong>{myTable}</strong>."); ?>
                            </p>
                        </section>
                    </div>
                </div>
            </form>
        </div>
    </div>
</script>

<div id="pdfDesigner"></div>

<script>
    $(document).ready(function () {
        pdfDesigner.init({
            actionHandler: "<?php echo $this->action(""); ?>",
            canvasEl: document.getElementById("pdfDesigner"),
            templateId: <?php echo $templateId; ?>,
            urls: {
                dialogEditBox: "<?php echo $this->url("/dashboard/pdf_designer/dialogs/edit_box"); ?>",
                dialogChangePosition: "<?php echo $this->url("/dashboard/pdf_designer/dialogs/change_position"); ?>",
                dialogChangeBoxType: "<?php echo $this->url("/dashboard/pdf_designer/dialogs/change_box_type"); ?>",
                dialogImportGoogleFont: "<?php echo $this->url("/dashboard/pdf_designer/dialogs/import_google_font"); ?>",
                dialogAddBox: "<?php echo $this->url("/dashboard/pdf_designer/dialogs/add_box"); ?>"
            },
            i18n: {
                menuDelete: "<?php echo t("Delete"); ?>",
                menuEditBox: "<?php echo t("Edit Content"); ?>",
                menuClose: "<?php echo t("Close"); ?>",
                menuChangeBoxType: "<?php echo t("Box Type"); ?>",
                menuChangePosition: "<?php echo t("Position"); ?>",
                dialogTitleEditBox: "<?php echo t("Edit Content"); ?>",
                dialogTitleBoxType: "<?php echo t("Change Box Type"); ?>",
                dialogTitlePosition: "<?php echo t("Change Position"); ?>",
                dialogTitleImportGoogleFont: "<?php echo t("Import Font from Google"); ?>",
                dialogTitleAddBox: "<?php echo t("Add Box"); ?>",
                toolbarButton: "<?php echo t("Template Settings"); ?>",
                toolbarButtonAddBox: "<?php echo t("Add Box"); ?>",
                transmissionErrorMessage: "<?php echo t("There was an error while saving."); ?>",
                pageTitle: "<?php echo t("PDF Designer - Edit Template - {0}"); ?>",
                chooseFileText: "<?php echo t("Choose File"); ?>",
                confirmQuestion: "<?php echo t("Are you sure?"); ?>",
                googleFontInstalled: "<?php echo t("The font was successfully installed."); ?>"
            }
        });
    });
</script>
