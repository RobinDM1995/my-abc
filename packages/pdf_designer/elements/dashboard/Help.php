<?php

/**
 * @project:   PDF Designer
 *
 * @author     Fabian Bitter (fabian@bitter.de)
 * @copyright  (C) 2017 Fabian Bitter
 * @version    1.2.1
 */
defined('C5_EXECUTE') or die('Access denied');

Core::make('help')->display(t("If you need support please click <a href=\"%s\">here</a>.", "https://bitbucket.org/fabianbitter/pdf_designer/issues/new"));
