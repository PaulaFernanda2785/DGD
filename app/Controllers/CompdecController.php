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
        $this->view('compdecs/show', $this->compdecService->dadosDetalhe((int) $id) + [
            'title' => 'Detalhe da COMPDEC',
            'stylesheets' => ['/assets/vendor/leaflet/leaflet.css'],
            'scripts' => ['/assets/vendor/leaflet/leaflet.js'],
        ]);
    }

    public function edit(string $id): void
    {
        $this->view('compdecs/edit', $this->compdecService->dadosEdicao((int) $id) + [
            'title' => 'Editar COMPDEC',
            'errors' => Session::consumeFlash('errors', []),
            'stylesheets' => ['/assets/vendor/leaflet/leaflet.css'],
            'scripts' => ['/assets/vendor/leaflet/leaflet.js'],
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
