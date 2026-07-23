<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\TipoAjudaService;

class TipoAjudaController extends Controller
{
    public function index(): void
    {
        $this->view('tipos_ajuda/index', (new TipoAjudaService())->listar($this->request->query()) + ['title' => 'Tipos de ajuda']);
    }
}
