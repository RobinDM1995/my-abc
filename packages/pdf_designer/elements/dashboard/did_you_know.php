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

$feedData = [];

if (version_compare(APP_VERSION, '8.0', '>=')) {
    /*
   * Try to get feed from cache
   */

  /** @var $cache \Concrete\Core\Cache\Level\ExpensiveCache */
  $cache = $app->make('cache/expensive');

    $cacheItem = $cache->getItem('bitter.product_feed');

    if ($cacheItem->isMiss()) {
        $cacheItem->lock();

      /*
       * Retrieve live feed from server
       */

      try {

         /** @var $httpClient \Concrete\Core\Http\Client\Client */
         $httpClient = $app->make(\Concrete\Core\Http\Client\Client::class);
          $httpClient->setUri("https://www.bitter.de/bitter/addon_list/feed/json");

          $response = $httpClient->send();

          $response = $httpClient->send();

          if ($response->isSuccess()) {
              $responseBody = $response->getBody();
              $feedData = @json_decode($responseBody, true);

              /*
               * Store feed to cache
               */

              $ttl = 24 * 60 * 60; // 1 day

              $cache->save($cacheItem->set($feedData)->expiresAfter($ttl));
          }
      } catch (\Exception $x) {
          // Can't connect to server. Skip error.
      }
    } else {
        $feedData = $cacheItem->get();
    }
}

/*
 * Get random item
 */

$randomItem = null;

if (is_array($feedData) && count($feedData) > 0) {
    $randomItem = $feedData[array_rand($feedData)];
}

?>

<?php if (is_object($pkg) && !$pkg->getConfig()->get('did_you_know.hide')): ?>
    <?php if (!is_null($randomItem)): ?>
        <div id="did-you-know">
            <hr>

            <div class="<?php echo $randomItem["title"]; ?>">
                <h3>
                    <?php echo t("Did you know?"); ?>
                </h3>

                <p>
                    <?php echo t(
                        "I have many other add-ons to power up your site. Maybe the following add-on sounds interesting to you? If you want to see a full list with all of my add-ons, please click %s. As an existing customer you will get 10%% discount on your next purchase.",
                        sprintf(
                            "<a href=\"%s\" target=\"_blank\">%s</a>",
                            "https://www.bitter.de/",
                            t("here")
                        )
                    ); ?>
                </p>

                <div class="media-row">
                    <div class="pull-left">
                        <img style="width: 49px" src="<?php echo h($randomItem["icon"]); ?>" class="media-object">
                    </div>

                    <div class="media-body">
                        <a href="<?php echo h($randomItem["url"]); ?>" class="btn pull-right btn-sm btn-default"
                           target="_blank">
                            <?php echo t("Details"); ?>
                        </a>

                        <h4 class="media-heading">
                            <?php echo $randomItem["name"]; ?>
                        </h4>

                        <p>
                            <?php echo $randomItem["description"]; ?>
                        </p>
                    </div>
                </div>
            </div>

            <p>
                <?php echo t("Click %s to hide.", sprintf("<a href=\"javascript:void(0);\" onclick=\"return hideDidYouKnow();\">%s</a>", t("here"))); ?>
            </a>
        </div>

        <script>
            var hideDidYouKnow = function() {
                $.get("<?php echo h(\Concrete\Core\Support\Facade\Url::to("/bitter/" . $pkg->getPackageHandle() . "/did_you_know/hide")); ?>");
                $("#did-you-know").remove();
                return false;
            };
        </script>
    <?php endif; ?>
<?php endif; ?>
