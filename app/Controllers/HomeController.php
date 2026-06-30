<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller
{
    public function info(): void
    {
        $mdContent = file_get_contents(__DIR__ . '/../../data/info.md');

        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $mdContent);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/^(\d+)\. (.+)$/m', '<li>$1. $2</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $html);
        $html = preg_replace('/<ul>\s*<li>\d+\. /', '<ol><li>', $html);
        $html = preg_replace('/<\/li>\s*<\/ul>/', '</li></ol>', $html);
        $html = preg_replace('/\n\n/', '</p><p>', $html);
        $html = '<p>' . $html . '</p>';
        $html = preg_replace('/<p>\s*<ul>/', '<ul>', $html);
        $html = preg_replace('/<\/ul>\s*<\/p>/', '</ul>', $html);
        $html = preg_replace('/<p>\s*<ol>/', '<ol>', $html);
        $html = preg_replace('/<\/ol>\s*<\/p>/', '</ol>', $html);
        $html = preg_replace('/<p>\s*<\/p>/', '', $html);

        $this->view('info', ['title' => 'Información de la Causa', 'infoHtml' => $html]);
    }
}
