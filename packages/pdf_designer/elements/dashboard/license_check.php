<?php

/**
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2019 Fabian Bitter
 * @version    1.2.1
 */

defined('C5_EXECUTE') or die('Access denied');

$app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
/** @var $packageService \Concrete\Core\Package\PackageService */
$packageService = $app->make(\Concrete\Core\Package\PackageService::class);
/** @var $pkg \Concrete\Core\Package\Package */
$pkg = $packageService->getByHandle($packageHandle);
/** @var $config \Concrete\Core\Config\Repository\Repository\Repository */
$config = $app->make(\Concrete\Core\Config\Repository\Repository::class);

?>

<?php if (is_object($pkg) && !$pkg->getConfig()->get('license_check.hide')): ?>

    <?php if (!\Concrete\Core\Marketplace\Marketplace::getInstance()->isConnected()): ?>
        <div class="alert alert-danger">
            <a href="#" class="close" data-dismiss="alert" aria-label="close" onclick="hideLiceneseCheck();">&times;</a>

            <?php echo t("You have not connected your site to the Marketplace. To benefit from updates, connect your site to the Marketplace and assign the purchased licenses."); ?>
        </div>
    <?php endif; ?>


    <script>
        var hideLiceneseCheck = function() {
            $.get("<?php echo h(\Concrete\Core\Support\Facade\Url::to("/bitter/" . $pkg->getPackageHandle() . "/license_check/hide")); ?>");
        };
    </script>
<?php endif; ?>
