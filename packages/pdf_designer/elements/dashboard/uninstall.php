<?php

/**
 * @project:   PDFDesigner (concrete5 add-on)
 *
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.2.1
 */

defined('C5_EXECUTE') or die('Access denied');

?>

<p>
    <?php echo t("Do you want to remove all Templates?"); ?>
</p>

<input type="checkbox" name="uninstallTemplates" id="uninstallTemplates" value="1" /> <label for="uninstallTemplates"><?php echo t("Remove all Templates"); ?></label>