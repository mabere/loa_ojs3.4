<?php

/**
 * @file plugins/generic/loa/LoaPlugin.php
 */

namespace APP\plugins\generic\loa;

use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use APP\facades\Repo;
use APP\core\Application;
use APP\file\PublicFileManager;
use PKP\config\Config;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use APP\plugins\generic\loa\form\LoaSettingsForm;
use PKP\submissionFile\SubmissionFile;
use PKP\core\Core;
use APP\core\Services;
use PKP\core\JSONMessage;

// Muat autoloader Dompdf
require_once __DIR__ . '/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

// Muat library QR Code
if (!class_exists('QRcode')) {
    require_once __DIR__ . '/phpqrcode/qrlib.php';
}

// Muat file konstanta lokalisasi secara manual jika belum ada
if (!defined('LOCALE_COMPONENT_PKP_COMMON')) {
    require_once(PKP_LIB_PATH . '/classes/i18n/PKPLocale.php');
}

class LoaPlugin extends GenericPlugin
{

    public function register($category, $path, $mainContextId = null)
    {
        if (!parent::register($category, $path, $mainContextId)) {
            return false;
        }

        if ($this->getEnabled()) {
            Hook::add('Decision::add', [$this, 'handleDecisionAdded']);
            Hook::add('TemplateManager::display', [$this, 'displayDownloadButton']);
            Hook::add('LoadHandler', [$this, 'handleLoaPage']);
        }

        return true;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.loa.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.loa.description');
    }

    public function getActions($request, $actionArgs)
    {
        $router = $request->getRouter();
        return array_merge(
            parent::getActions($request, $actionArgs),
            $this->getEnabled() ? [
                new LinkAction(
                    'settings',
                    new AjaxModal(
                        $router->url(
                            $request,
                            null,
                            null,
                            'manage',
                            null,
                            [
                                'verb' => 'settings',
                                'plugin' => $this->getName(),
                                'category' => 'generic'
                            ]
                        ),
                        $this->getDisplayName()
                    ),
                    __('manager.plugins.settings'),
                    null
                ),
            ] : []
        );
    }

    public function manage($args, $request)
    {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $context = $request->getContext();
                $form = new LoaSettingsForm($this, $context->getId());

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        // Kembalikan JSONMessage sederhana, sama seperti plugin bawaan OJS
                        return new JSONMessage(true);
                    }
                } else {
                    $form->initData();
                }

                return new JSONMessage(true, $form->fetch($request));
        }
        return parent::manage($args, $request);
    }

    public function handleLoaPage($hookName, $args)
    {
        $page = &$args[0];

        if ($page === 'loa') {
            $op = &$args[1];
            if ($op === 'verify') {
                $handlerFile = &$args[2];
                $handlerFile = $this->getPluginPath() . '/pages/LoaHandler.php';
                define('HANDLER_CLASS', 'APP\plugins\generic\loa\pages\LoaHandler');
                return true;
            }
        }

        return false;
    }

    public function handleDecisionAdded($hookName, $args)
    {
        $decisionObject = $args[0];
        $decisionType = $decisionObject->getData('decision');

        if ($decisionType === SUBMISSION_EDITOR_DECISION_ACCEPT) {
            $submissionId = $decisionObject->getData('submissionId');
            if (!$submissionId)
                return;

            $submission = Repo::submission()->get($submissionId);
            if (!$submission)
                return;

            $loaHtml = $this->generateLoaHtml($submission);

            if (!empty($loaHtml)) {
                $this->savePdfAsSubmissionFile($submission, $loaHtml);
            }
        }
    }

    public function displayDownloadButton($hookName, $args)
    {
        $templateMgr = $args[0];
        $template = $args[1];

        if (!in_array($template, ['workflow/workflow.tpl', 'authorDashboard/authorDashboard.tpl'])) {
            return;
        }

        $submission = $templateMgr->getTemplateVars('submission');
        if (!$submission)
            return;

        $isAccepted = $submission->getStageId() >= WORKFLOW_STAGE_ID_EDITING;

        if ($isAccepted) {
            $context = Application::get()->getRequest()->getContext();
            $loaGenreId = $this->getSetting($context->getId(), 'loaGenreId');
            if (!$loaGenreId)
                return;

            $loaFile = Repo::submissionFile()
                ->getCollector()
                ->filterBySubmissionIds([$submission->getId()])
                ->filterByGenreIds([(int) $loaGenreId])
                ->getMany()
                ->first();

            if ($loaFile) {
                $request = Application::get()->getRequest();
                $dispatcher = $request->getDispatcher();

                $loaFileUrl = $dispatcher->url(
                    $request,
                    \PKP\core\PKPApplication::ROUTE_COMPONENT,
                    null,
                    'api.file.FileApiHandler',
                    'downloadFile',
                    null,
                    [
                        'submissionFileId' => $loaFile->getId(),
                        'submissionId' => $submission->getId(),
                        'stageId' => $submission->getStageId(),
                        'fileId' => $loaFile->getData('fileId'),
                    ]
                );
                $templateMgr->assign('loaFileUrl', $loaFileUrl);
            }
        }
    }

    public function generateLoaHtml($submission)
    {
        $context = Application::get()->getRequest()->getContext();
        $latestPublication = $submission->getLatestPublication();
        if (!$latestPublication) {
            return '';
        }

        $journalLogoDataUri = '';
        $logoSetting = $context->getSetting('pageHeaderLogoImage');
        $logoData = null;

        if (is_array($logoSetting)) {
            $currentLocale = \PKP\facades\Locale::getLocale();
            $primaryLocale = \PKP\facades\Locale::getPrimaryLocale();

            if (isset($logoSetting[$currentLocale])) {
                $logoData = $logoSetting[$currentLocale];
            } elseif (isset($logoSetting[$primaryLocale])) {
                $logoData = $logoSetting[$primaryLocale];
            }
        }

        if ($logoData && isset($logoData['uploadName'])) {
            $publicFileManager = new PublicFileManager();
            $logoPath = $publicFileManager->getContextFilesPath($context->getId()) . '/' . $logoData['uploadName'];
            if (file_exists($logoPath)) {
                $imageData = file_get_contents($logoPath);
                $imageType = mime_content_type($logoPath);
                $journalLogoDataUri = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
            }
        }

        $logoHtml = '';
        if ($journalLogoDataUri) {
            $logoHtml = '<img src="' . $journalLogoDataUri . '" alt="Journal Logo" style="max-height: 80px; margin-bottom: 20px;">';
        }

        $journalTitle = $context->getLocalizedName();
        $articleTitle = $latestPublication->getLocalizedTitle();
        $submissionId = $submission->getId();
        $acceptanceDate = date('F j, Y');

        $authors = $latestPublication->getData('authors');
        $authorNames = [];
        foreach ($authors as $author) {
            $authorNames[] = $author->getFullName();
        }
        $authorNamesString = implode(', ', $authorNames);

        $qrCodeDataUri = $this->generateQrCodeDataUri($submission);

        $defaultHeader = '{$journalLogo}<h1>LETTER OF ACCEPTANCE</h1><h2>{$journalTitle}</h2>';
        $defaultBody = '<p>Dear Author(s),</p><p>We are pleased to inform you that your manuscript, with the following details, has been accepted for publication in <strong>{$journalTitle}</strong>.</p><p><strong>Submission ID:</strong> {$submissionId}<br><strong>Title:</strong> {$articleTitle}</p><p>Thank you for your valuable contribution. We will contact you soon regarding the next steps in the publication process.</p>';
        $defaultFooter = '<p>Sincerely,</p><img src="{$qrCodeDataUri}" alt="QR Code" width="90"><p><strong>Editor in Chief</strong><br>{$journalTitle}</p>';

        $headerTpl = $this->getSetting($context->getId(), 'loaHeader') ?: $defaultHeader;
        $bodyTpl = $this->getSetting($context->getId(), 'loaBody') ?: $defaultBody;
        $footerTpl = $this->getSetting($context->getId(), 'loaFooter') ?: $defaultFooter;

        $templateMgr = \APP\template\TemplateManager::getManager(Application::get()->getRequest());

        $templateMgr->assign([
            'journalLogo' => $logoHtml,
            'journalTitle' => $journalTitle,
            'articleTitle' => $articleTitle,
            'submissionId' => $submissionId,
            'acceptanceDate' => $acceptanceDate,
            'authorNamesString' => $authorNamesString,
            'qrCodeDataUri' => $qrCodeDataUri,
        ]);

        $headerHtml = $templateMgr->fetch('string:' . $headerTpl);
        $bodyHtml = $templateMgr->fetch('string:' . $bodyTpl);
        $footerHtml = $templateMgr->fetch('string:' . $footerTpl);

        $templateMgr->assign([
            'headerHtml' => $headerHtml,
            'bodyHtml' => $bodyHtml,
            'footerHtml' => $footerHtml,
        ]);

        return $templateMgr->fetch($this->getTemplateResource('pdfView.tpl'));
    }

    private function generateQrCodeDataUri($submission)
    {
        if (!class_exists('QRcode')) {
            require_once __DIR__ . '/phpqrcode/qrlib.php';
        }

        $request = Application::get()->getRequest();
        $context = $request->getContext();
        if (!$context) {
            return '';
        }

        $salt = Config::getVar('security', 'salt');
        $token = hash('sha256', $submission->getId() . $salt);
        $verificationUrl = $request->getBaseUrl() . '/index.php/' . $context->getPath() . '/loa/verify/' . $submission->getId() . '?token=' . $token;

        $tempPngPath = sys_get_temp_dir() . '/loa_qr_' . uniqid() . '.png';
        \QRcode::png($verificationUrl, $tempPngPath, 'L', 4, 2);
        $imageData = file_get_contents($tempPngPath);
        $base64 = base64_encode($imageData);
        unlink($tempPngPath);
        return 'data:image/png;base64,' . $base64;
    }

    public function savePdfAsSubmissionFile($submission, $html)
    {
        $context = Application::get()->getRequest()->getContext();
        $loaGenreId = $this->getSetting($context->getId(), 'loaGenreId');

        if (!$loaGenreId) {
            error_log('Plugin LoA: Genre untuk LoA belum diatur di Settings.');
            return;
        }

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        $tempDir = sys_get_temp_dir();
        $tempPath = tempnam($tempDir, 'LoA');
        file_put_contents($tempPath, $pdfOutput);

        // --- PERBAIKAN FINAL ADA DI SINI ---

        // 1. Pindahkan file fisik ke direktori OJS yang aman & dapatkan fileId
        $submissionDir = Repo::submissionFile()->getSubmissionDir($context->getId(), $submission->getId());
        $fileId = Services::get('file')->add(
            $tempPath,
            $submissionDir . '/LoA-' . $submission->getId() . '-' . uniqid() . '.pdf'
        );

        // 2. Siapkan data untuk objek SubmissionFile
        $params = [
            'submissionId' => $submission->getId(), // WAJIB ada
            'fileId' => $fileId, // WAJIB ada
            'name' => ['en_US' => 'LoA-' . $submission->getId() . '.pdf'],
            'fileStage' => SubmissionFile::SUBMISSION_FILE_ATTACHMENT,
            'genreId' => (int) $loaGenreId,
            'uploaderUserId' => Application::get()->getRequest()->getUser()->getId(),
            'viewable' => true,
        ];

        // 3. Buat objek SubmissionFile baru, LALU tambahkan ke database
        $submissionFile = Repo::submissionFile()->newDataObject($params);
        Repo::submissionFile()->add($submissionFile);

        unlink($tempPath);
    }
}