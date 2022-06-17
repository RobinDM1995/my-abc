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
    <?php echo t("Do you want to install some Sample Data?"); ?>
</p>

<input type="checkbox" name="installSampleData" id="installSampleData" value="1" checked="checked"/> <label for="installSampleData"><?php echo t("Install Sample Data"); ?></label>