<?php
$v = View::getInstance();
$v->addHeaderItem('<script src="' . $_SERVER['CONTEXT_PREFIX'] . '/packages/abcstart/js/clipboard.js"></script>');
?>
<script>
  $(document).ready(function() {
    new Clipboard('.btn-clipboard');
  });
</script>
<style>
  .ccm-ui .checkbox {
    margin-top: 3px;
    margin-bottom: 3px;
  }
</style>
<div class="row">
  <div class="col-sm-12">
    <h1>Steps before installing this package</h1>
  </div>
</div>
<div class="row">
  <div class="col-sm-12">
    <p style="font-size: 17px;">
      A couple of steps should be taken before installing this package. These steps are not mandatory but not completing these tasks will probably hurt a cute puppy somewhere in the world.
    </p>
  </div>
</div>
<div class="row">
  <div class="col-sm-12">
    <h2>Config concrete.php</h2>
    <p>
      Create the file 'application/config/concrete.php' and insert the following code.<br/>
      (Change where needed: email name, sitemap frequency, ...)<br/><br/>
      <a class="btn btn-primary btn-clipboard" href="#" id="btnConcrete"
        data-clipboard-text="&lt;?php
$remoteIp = $_SERVER['REMOTE_ADDR'];
return array(
          'email' => array(
              'default' => array(
                'address' => 'noreply@thissite.be',
                'name' => 'sitename'
              ),
              'form_block' => array(
                'address' => 'noreply@thissite.be',
              ),
              'validate_registration' => array(
                'address' => 'noreply@thissite.be',
              ),
              'forgot_password' => array(
                'address' => 'noreply@thissite.be',
              ),
            ),
            'marketplace' => array(
              'enabled' => false
            ),
            'external' => array(
              'news_overlay' => false,
              'news' => false
            ),
            'sitemap_xml' => array(
              'file'      => 'sitemap.xml',
              'frequency' => 'weekly',
              'priority'  => 0.7
            ),
            'accessibility' => array(
              'toolbar_titles' => true,
              'toolbar_large_font' => false
            ),
            'white_label' => array(
              'name' => 'ABC IT en Web Solutions',
              'logo' => false,
              'background_url' => true
            ),
            'session' => array(
                'name' => 'CONCRETE5',
                'handler' => 'file',
                'save_path' => null,
                'max_lifetime' => 86400,
                'cookie' => array(
                    'cookie_path' => false, // set a specific path here if you know it, otherwise it'll default to relative
                    'cookie_lifetime' => 0,
                    'cookie_domain' => false,
                    'cookie_secure' => false,
                    'cookie_httponly' => true
                )
            ),
            'urls' => array(
              'background_url' => 'http://www.mijnwebsitebouwen.be/images/mijnwebsitebouwen_be_vierkant.png'
            ),
            'security' => array(
              'trusted_proxies' => array(
                'ips' => [$remoteIp],
              ),
            ),
            'seo' => array(
                'title_format' => '%2$s - %1$s',
                'title_segment_separator' => ' - ',
            )
          );
?&gt;">
        Copy code to clipboard
      </a>
    </p>
  </div>
</div>
<div class="row">
  <div class="col-sm-12">
    <h2>Config System Pages in Theme Template <small>Optional - app.php</small></h2>
      <p>
        Place in the file 'application/config/app.php' following code.<br/><br/>
        <a class="btn btn-primary btn-clipboard" href="#" id="btnConcrete"
          data-clipboard-text="&lt;?php
return [
    'canonical-url' => '',
    'canonical-ssl-url' => '',
    'theme_paths' => array(
      '/login'            => 'abcbasic',
      '/account'          => 'abcbasic',
      '/account/*'        => 'abcbasic',
      '/register'         => 'abcbasic',
      '/maintenance_mode' => 'abcbasic',
      '/upgrade'          => 'abcbasic'
    ),
];
?&gt;">
          Copy code to clipboard
        </a>
      </p>
  </div>
</div>
<div class="row">
  <div class="col-sm-12">
    <h2>Config Blocks for Moderator Group</h2>
    <p>
      What blocks should the moderator be able to use? It's totally up to you, but don't leave it empty or they will be mad.<br />
      Let me help you out! I already selected the most used blocks.
    </p>
    <?php $blockList = Concrete\Core\Block\BlockType\BlockTypeList::getInstalledList(); ?>
    <?php $selectedBlocks = array("content",
                                  "image",
                                  "image_slider",
                                  "page_list",
                                  "youtube",
                                  "page_title",
                                  "form",
                                  "express_form",
                                  "share_this_page",
                                  "file",
                                  "call_to_action"); ?>
    <?php if(!empty($blockList)) { ?>

        <?php
          $blocks = array();
          $blocks['call_to_action'] = 'Button';
          foreach($blockList as $block) {
            $bName = $block->getBlockTypeName();
            $bHandle = $block->getBlockTypeHandle();
            $blocks[$bHandle] = $bName;
          }
        ?>

        <?php if(!empty($blocks)) { ?>
          <?php asort($blocks); ?>
          <?php $bCounter = 0; ?>
          <?php foreach($blocks as $bHandle => $bName) {
            if($bCounter % 3 == 0) {
              echo '<div class="row">';
            }

            echo '<div class="col-sm-4">';

            echo '<div class="checkbox">';
              echo '<label><input type="checkbox" name="moderatorBlocks[' . $bHandle . ']" value="1" ' . (in_array($bHandle, $selectedBlocks) ? "checked" : "") . '>' . $bName . '</label>';
            echo '</div>';

            echo '</div>';
            $bCounter++;
            if($bCounter % 3 == 0 || $bCounter == count($blockList)) {
              echo '</div>';
            }
          } ?>
        <?php } ?>
      </div>
    <?php } ?>

  </div>
</div>
