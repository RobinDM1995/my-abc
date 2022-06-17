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

View::element('/dashboard/Reminder', array("packageHandle" => "pdf_designer", "rateUrl" => "https://www.concrete5.org/marketplace/addons/pdf-designer-for-concrete5/reviews"), 'pdf_designer');
?>

<?php \Concrete\Core\View\View::element('/dashboard/license_check', array("packageHandle" => "pdf_designer"), 'pdf_designer'); ?>

<script>
$(document).ready(function() {
    $("#importTemplates").bind("click", function() {
        $.fn.dialog.open({
            href: "<?php echo $this->url("/dashboard/pdf_designer/dialogs/import_templates"); ?>",
            title: "<?php echo t("Import Templates"); ?>",
            width: '400',
            height: '500',
            modal: true,
            close: function() {
                location.reload();
            }
        });
    });
});
</script>

<div class="ccm-dashboard-header-buttons">
    <a href="javascript:void(0);" class="btn btn-default" id="importTemplates">
        <i class="fa fa-upload" aria-hidden="true"></i> <?php echo t("Import Templates"); ?>
    </a>

    <a href="<?php echo $this->action("add"); ?>" class="btn btn-primary">
        <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t("Add Template"); ?>
    </a>
</div>

<?php if (count($templates) === 0): ?>
    <div class="alert alert-info">
        <?php echo t("You don't have created any templates. Click <a href=\"%s\">here</a> to create one.", $this->action("add")); ?>
    </div>
<?php else: ?>
    <table border="0" cellspacing="0" cellpadding="0" class="ccm-search-results-table">
        <thead>
            <tr>
                <th class="false">
                    <a href="javascript:void(0);">
                        <?php echo t("Template Name"); ?>
                    </a>
                </th>

                <th class="false">
                    &nbsp;
                </th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($templates as $templateId => $templateName): ?>
                <tr>
                    <td>
                        <a href="<?php echo $this->action("edit", $templateId); ?>">
                            <?php echo $templateName; ?>
                        </a>
                    </td>

                    <td>
                        <div class=" pull-right">

                            <a href="<?php echo $this->action("edit", $templateId); ?>" class="btn btn-default">
                                <i class="fa fa-pencil" aria-hidden="true"></i> <?php echo t("Edit"); ?>
                            </a>

                            <a href="<?php echo $this->action("preview", $templateId); ?>" target="_blank" class="btn btn-default">
                                <i class="fa fa-file-pdf-o" aria-hidden="true"></i> <?php echo t("Preview"); ?>
                            </a>

                            <a href="<?php echo $this->action("export", $templateId); ?>" target="_blank" class="btn btn-default">
                                <i class="fa fa-download" aria-hidden="true"></i> <?php echo t("Export"); ?>
                            </a>

                            <a href="<?php echo $this->action("duplicate", $templateId); ?>" class="btn btn-default">
                                <i class="fa fa-files-o" aria-hidden="true"></i> <?php echo t("Duplicate"); ?>
                            </a>

                            <a href="<?php echo $this->action("remove", $templateId); ?>" onclick="return confirm('<?php echo t("Are you sure?"); ?>');" class="btn btn-danger">
                                <i class="fa fa-trash" aria-hidden="true"></i> <?php echo t("Delete"); ?>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php \Concrete\Core\View\View::element('/dashboard/did_you_know', array("packageHandle" => "pdf_designer"), 'pdf_designer'); ?>
