<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\AnexoService;

class AnexoController extends Controller
{
    private AnexoService $anexoService;

    public function __construct(\App\Core\Request $request, \App\Core\Response $response)
    {
        parent::__construct($request, $response);
        $this->anexoService = new AnexoService();
    }

    public function store(string $id): void
    {
        $result = $this->anexoService->salvar((int) $id, $this->request->files('arquivo') ?? [], $this->request->post());

        if (!$result['success']) {
            Session::flash('error', $result['message']);
            $this->redirect('/decretos/' . $id);
        }

        Session::flash('success', 'Anexo enviado com sucesso.');
        $this->redirect('/decretos/' . $id);
    }

    public function download(string $id): void
    {
        $anexo = $this->anexoService->buscarParaDownload((int) $id);
        $this->response->download($anexo['caminho_armazenado'], $anexo['nome_original'], $anexo['mime_type']);
    }

    public function destroy(string $id): void
    {
        $desastreId = $this->anexoService->excluir((int) $id);
        Session::flash('success', 'Anexo removido com sucesso.');
        $this->redirect('/decretos/' . $desastreId);
    }
}
