<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\DecretoService;

class DecretoController extends Controller
{
    private DecretoService $decretoService;

    public function __construct(\App\Core\Request $request, \App\Core\Response $response)
    {
        parent::__construct($request, $response);
        $this->decretoService = new DecretoService();
    }

    public function index(): void
    {
        $data = $this->decretoService->listar($this->request->query());

        $this->view('decretos/index', $data + [
            'title' => 'Decretos',
            'filtros' => $this->request->query(),
        ]);
    }

    public function create(): void
    {
        $this->view('decretos/create', [
            'title' => 'Novo cadastro',
            'dominios' => $this->decretoService->formData(),
            'errors' => Session::consumeFlash('errors', []),
            'registro' => [],
        ]);
    }

    public function store(): void
    {
        $result = $this->decretoService->cadastrar($this->request->post(), $this->request->files());

        if (!$result['success']) {
            Session::flash('errors', $result['errors']);
            Session::flash('_old', $this->request->post());
            $this->redirect('/decretos/novo');
        }

        Session::flash('success', 'Registro cadastrado com sucesso.');
        if (!empty($result['warnings'])) {
            Session::flash('warning', implode(' ', $result['warnings']));
        }
        $this->redirect('/decretos/' . $result['id']);
    }

    public function show(string $id): void
    {
        $this->view('decretos/show', [
            'title' => 'Detalhe do desastre',
            'registro' => $this->decretoService->buscarDetalhe((int) $id),
            'dominios' => $this->decretoService->formData(),
        ]);
    }

    public function printReport(string $id): void
    {
        $registro = $this->decretoService->buscarDetalhe((int) $id);
        $geradoEm = new \DateTimeImmutable('now');

        ob_start();
        require view_path('decretos/partials/print_report');
        $html = (string) ob_get_clean();

        $this->json([
            'success' => true,
            'title' => 'Relatório do decreto ' . ($registro['protocolo_dgd'] ?? ''),
            'filename' => 'relatorio-' . preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($registro['protocolo_dgd'] ?? 'decreto')) . '.pdf',
            'html' => $html,
        ]);
    }

    public function edit(string $id): void
    {
        $this->view('decretos/edit', [
            'title' => 'Editar desastre',
            'registro' => $this->decretoService->buscarParaEdicao((int) $id),
            'dominios' => $this->decretoService->formData(),
            'errors' => Session::consumeFlash('errors', []),
        ]);
    }

    public function update(string $id): void
    {
        $result = $this->decretoService->atualizar((int) $id, $this->request->post(), $this->request->files());

        if (!$result['success']) {
            Session::flash('errors', $result['errors']);
            Session::flash('_old', $this->request->post());
            $this->redirect('/decretos/' . $id . '/editar');
        }

        Session::flash('success', 'Registro atualizado com sucesso.');
        if (!empty($result['warnings'])) {
            Session::flash('warning', implode(' ', $result['warnings']));
        }
        $this->redirect('/decretos/' . $id);
    }

    public function destroy(string $id): void
    {
        $this->decretoService->excluir((int) $id);
        Session::flash('success', 'Registro removido da listagem com sucesso.');
        $this->redirect('/decretos');
    }

    public function updateStatus(string $id): void
    {
        $field = (string) $this->request->post('campo', '');
        $value = (int) $this->request->post('valor', 0);
        $observacao = trim((string) $this->request->post('historico_observacao', '')) ?: null;
        $dataEnvioPge = trim((string) $this->request->post('data_envio_pge', '')) ?: null;
        $dataHomologacao = trim((string) $this->request->post('data_decreto_homologacao', '')) ?: null;

        try {
            $this->decretoService->atualizarStatus((int) $id, $field, $value, $observacao, $dataEnvioPge, $dataHomologacao);
        } catch (\InvalidArgumentException $exception) {
            if (!$this->request->expectsJson()) {
                Session::flash('error', $exception->getMessage());
                $this->redirect('/decretos');
            }

            $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        if (!$this->request->expectsJson()) {
            Session::flash('success', 'Status atualizado com sucesso.');
            $this->redirect('/decretos');
        }

        $this->json([
            'success' => true,
            'message' => 'Status atualizado com sucesso.',
        ]);
    }
}
