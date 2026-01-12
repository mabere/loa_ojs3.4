<?php

/**
 * @file plugins/generic/loa/pages/LoaHandler.php
 */

/**namespace APP\plugins\generic\loa\pages;

use APP\core\Application;
use APP\handler\Handler;
use APP\facades\Repo;
use PKP\config\Config;

class LoaHandler extends Handler
{
    /**
     * Menampilkan halaman verifikasi LoA.
     * @param array $args
     * @param \APP\core\Request $request
     */
// public function verify($args, $request)
// {
//     $submissionId = isset($args[0]) ? (int) $args[0] : 0;
//     $tokenFromUrl = $request->getUserVar('token');

//     $submission = Repo::submission()->get($submissionId);
//     $templateMgr = \APP\template\TemplateManager::getManager($request);
//     $this->setupTemplate($request);

//     $salt = Config::getVar('security', 'salt');
//     $expectedToken = hash('sha256', $submissionId . $salt);

//     if ($submission && $tokenFromUrl && hash_equals($expectedToken, $tokenFromUrl)) {
//         $latestPublication = $submission->getLatestPublication();
//         $authors = $latestPublication->getData('authors');
//         $authorNames = [];
//         foreach ($authors as $author) {
//             $authorNames[] = $author->getFullName();
//         }

//         $templateMgr->assign([
//             'isValid' => true,
//             'submissionId' => $submission->getId(),
//             'articleTitle' => $latestPublication->getLocalizedTitle(),
//             'authorNamesString' => implode(', ', $authorNames),
//             'journalTitle' => $request->getContext()->getLocalizedName(),
//         ]);
//     } else {
//         $templateMgr->assign('isValid', false);
//     }

//     $plugin = \PKP\plugins\PluginRegistry::getPlugin('generic', 'loaplugin');
//     $templateMgr->display($plugin->getTemplateResource('verificationPage.tpl'));
// }




namespace APP\plugins\generic\loa\pages;

use APP\core\Application;
use APP\handler\Handler;
use APP\facades\Repo;
use PKP\config\Config;
use PKP\file\FileManager;

class LoaHandler extends Handler
{
    /**
     * Menampilkan halaman verifikasi LoA.
     * @param array $args
     * @param \APP\core\Request $request
     */
    public function verify($args, $request)
    {
        $submissionId = isset($args[0]) ? (int) $args[0] : 0;
        $tokenFromUrl = $request->getUserVar('token');

        $submission = Repo::submission()->get($submissionId);
        $templateMgr = \APP\template\TemplateManager::getManager($request);
        $this->setupTemplate($request);

        // Verifikasi token
        $salt = Config::getVar('security', 'salt');
        $expectedToken = hash('sha256', $submissionId . $salt);

        if ($submission && $tokenFromUrl && hash_equals($expectedToken, $tokenFromUrl)) {
            $latestPublication = $submission->getLatestPublication();
            $authors = $latestPublication->getData('authors');
            $authorNames = [];
            foreach ($authors as $author) {
                $authorNames[] = $author->getFullName();
            }

            $templateMgr->assign([
                'isValid' => true,
                'submissionId' => $submission->getId(),
                'articleTitle' => $latestPublication->getLocalizedTitle(),
                'authorNamesString' => implode(', ', $authorNames),
                'journalTitle' => $request->getContext()->getLocalizedName(),
            ]);
        } else {
            $templateMgr->assign('isValid', false);
        }

        $plugin = \PKP\plugins\PluginRegistry::getPlugin('generic', 'loaplugin');
        $templateMgr->display($plugin->getTemplateResource('verificationPage.tpl'));
    }

    /**
     * Menangani permintaan unduh file LoA.
     * @param array $args
     * @param \APP\core\Request $request
     */
    public function download($args, $request)
    {
        $submissionId = isset($args[0]) ? (int) $args[0] : 0;
        $tokenFromUrl = $request->getUserVar('token');
        $user = $request->getUser();
        $submission = Repo::submission()->get($submissionId);

        if (!$submission || !$user) {
            header("HTTP/1.0 404 Not Found");
            return;
        }

        // Verifikasi token keamanan
        $salt = Config::getVar('security', 'salt');
        $expectedToken = hash('sha256', $submissionId . $user->getId() . $salt . 'download');
        if (!$tokenFromUrl || !hash_equals($expectedToken, $tokenFromUrl)) {
            header("HTTP/1.0 403 Forbidden");
            echo "Invalid download token.";
            return;
        }

        // Verifikasi izin pengguna (apakah dia editor atau penulis naskah ini)
        $userRoles = Repo::user()->getRoles($user->getId(), $submission->getContextId());
        $isManagerOrEditor = !empty(array_intersect([ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT], array_map(function ($role) {
            return $role->getId();
        }, $userRoles)));
        $isAuthor = Repo::author()->dao->authorExists($user->getId(), $submission->getId());

        if (!$isManagerOrEditor && !$isAuthor) {
            header("HTTP/1.0 403 Forbidden");
            echo "You do not have permission to access this file.";
            return;
        }

        // Jika semua verifikasi lolos, kirimkan file
        $filePath = Config::getVar('files', 'files_dir') . '/loa_files/LoA-' . $submission->getId() . '.pdf';
        if (file_exists($filePath)) {
            $fileManager = new FileManager();
            $fileManager->downloadFile($filePath, null, false); // false = jangan hapus setelah diunduh
        } else {
            header("HTTP/1.0 404 Not Found");
        }
    }
}