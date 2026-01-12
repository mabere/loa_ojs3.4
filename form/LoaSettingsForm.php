<?php

namespace APP\plugins\generic\loa\form;

use PKP\form\Form;
use APP\template\TemplateManager;
use PKP\form\validation\FormValidatorPost;
use PKP\form\validation\FormValidatorCSRF;
use PKP\db\DAORegistry;
use APP\core\Application;
use APP\notification\NotificationManager;

if (!defined('LOCALE_COMPONENT_PKP_COMMON')) {
    require_once(PKP_LIB_PATH . '/classes/i18n/PKPLocale.php');
}

class LoaSettingsForm extends Form
{
    private $contextId;
    private $plugin;

    public function __construct($plugin, $contextId)
    {
        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
        $this->plugin = $plugin;
        $this->contextId = $contextId;

        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    public function initData()
    {
        $this->_data = [
            'loaGenreId' => $this->plugin->getSetting($this->contextId, 'loaGenreId'),
            // 'loaHeader' => $this->plugin->getSetting($this->contextId, 'loaHeader'),
            'loaBody' => $this->plugin->getSetting($this->contextId, 'loaBody'),
            // 'loaFooter' => $this->plugin->getSetting($this->contextId, 'loaFooter'),
        ];
        parent::initData();
    }

    public function readInputData()
    {
        $this->readUserVars(['loaGenreId', 'loaBody']);//['loaHeader', 'loaFooter']
        parent::readInputData();
    }

    public function fetch($request, $template = null, $display = false)
    {
        \PKPLocale::requireComponents([\LOCALE_COMPONENT_PKP_DEFAULT, \LOCALE_COMPONENT_APP_AUTHOR, \LOCALE_COMPONENT_PKP_COMMON]);
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->plugin->getName());
        $genreDao = DAORegistry::getDAO('GenreDAO');
        $genresResultFactory = $genreDao->getByContextId($this->contextId);
        $genreOptions = [];
        while ($genre = $genresResultFactory->next()) {
            $genreOptions[$genre->getId()] = $genre->getLocalizedName();
        }
        $templateMgr->assign('genreOptions', $genreOptions);
        return parent::fetch($request, $template, $display);
    }

    public function execute(...$functionArgs)
    {
        $this->plugin->updateSetting($this->contextId, 'loaGenreId', (int) $this->getData('loaGenreId'), 'int');
        // $this->plugin->updateSetting($this->contextId, 'loaHeader', $this->getData('loaHeader'), 'string');
        $this->plugin->updateSetting($this->contextId, 'loaBody', $this->getData('loaBody'), 'string');
        // $this->plugin->updateSetting($this->contextId, 'loaFooter', $this->getData('loaFooter'), 'string');

        $request = Application::get()->getRequest();
        $notificationManager = new NotificationManager();
        $notificationManager->createTrivialNotification(
            $request->getUser()->getId(),
            \NOTIFICATION_TYPE_SUCCESS,
            ['contents' => __('plugins.generic.loa.settings.saved')]
        );
        parent::execute(...$functionArgs);
    }


}