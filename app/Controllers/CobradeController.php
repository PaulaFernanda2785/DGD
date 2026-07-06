<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CobradeService;

class CobradeController extends Controller
{
    private CobradeService $cobradeService;

    public function __construct(\App\Core\Request $request, \App\Core\Response $response)
    {
        parent::__construct($request, $response);
        $this->cobradeService = new CobradeService();
    }

    public function grupos(): void
    {
        $this->json(['success' => true, 'data' => $this->cobradeService->grupos()]);
    }

    public function subgrupos(): void
    {
        $grupoId = $this->request->query('grupo_id');
        $this->json(['success' => true, 'data' => $this->cobradeService->subgrupos($grupoId ? (int) $grupoId : null)]);
    }

    public function tipos(): void
    {
        $subgrupoId = $this->request->query('subgrupo_id');
        $this->json(['success' => true, 'data' => $this->cobradeService->tipos($subgrupoId ? (int) $subgrupoId : null)]);
    }

    public function subtipos(): void
    {
        $tipoId = $this->request->query('tipo_id');
        $this->json(['success' => true, 'data' => $this->cobradeService->subtipos($tipoId ? (int) $tipoId : null)]);
    }

    public function detalhe(string $id): void
    {
        $this->json(['success' => true, 'data' => $this->cobradeService->detalhe((int) $id)]);
    }
}
