<?php

namespace App\Controllers;

use App\Core\Controller;

class ReportController extends Controller
{
    public function index(): void
    {
        $this->requireRole('administrador');
        $this->view('reportes', ['title' => 'Reportes de Abogados']);
    }
}
