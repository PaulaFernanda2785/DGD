<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\PainelService;

class PainelController extends Controller
{
    private PainelService $painelService;

    public function __construct(\App\Core\Request $request, \App\Core\Response $response)
    {
        parent::__construct($request, $response);
        $this->painelService = new PainelService();
    }

    public function index(): void
    {
        $this->view('painel/index', [
            'title' => 'Painel',
            'resumo' => $this->painelService->resumo(),
            'recentes' => $this->painelService->recentes(),
        ]);
    }
}
