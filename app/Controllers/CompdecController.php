<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\CompdecService;

class CompdecController extends Controller
{
    private CompdecService $compdecService;

    public function __construct(\App\Core\Request $request, \App\Core\Response $response)
    {
        parent::__construct($request, $response);
        $this->compdecService = new CompdecService();
    }

    public function index(): void
    {
        $data = $this->compdecService->listar($this->request->query());

        $this->view('compdecs/index', $data + [
            'title' => 'COMPDECs',
            'filtros' => $this->request->query(),
        ]);
    }

    public function show(string $id): void
    {
        $this->view('compdecs/show', [
            'title' => 'Detalhe da COMPDEC',
            'compdec' => $this->compdecService->buscar((int) $id),
        ]);
    }

    public function edit(string $id): void
    {
        $this->view('compdecs/edit', [
            'title' => 'Editar COMPDEC',
            'compdec' => $this->compdecService->buscar((int) $id),
            'errors' => Session::consumeFlash('errors', []),
        ]);
    }

    public function update(string $id): void
    {
        $result = $this->compdecService->atualizar((int) $id, $this->request->post(), $this->request->files());

        if (!$result['success']) {
            Session::flash('errors', $result['errors']);
            Session::flash('_old', $this->request->post());
            $this->redirect('/compdecs/' . $id . '/editar');
        }

        Session::flash('success', 'COMPDEC atualizada com sucesso.');
        $this->redirect('/compdecs/' . $id);
    }

    public function municipio(string $municipioId): void
    {
        $compdec = $this->compdecService->buscarPorMunicipio((int) $municipioId);

        $this->json([
            'data' => $compdec,
        ], $compdec ? 200 : 404);
    }
}
