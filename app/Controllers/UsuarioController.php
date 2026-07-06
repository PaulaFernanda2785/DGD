<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\UsuarioService;

class UsuarioController extends Controller
{
    private UsuarioService $usuarioService;

    public function __construct(\App\Core\Request $request, \App\Core\Response $response)
    {
        parent::__construct($request, $response);
        $this->usuarioService = new UsuarioService();
    }

    public function index(): void
    {
        $data = $this->usuarioService->listar($this->request->query());

        $this->view('usuarios/index', $data + [
            'title' => 'Usuarios',
            'filtros' => $this->request->query(),
        ]);
    }

    public function create(): void
    {
        $this->view('usuarios/create', $this->usuarioService->dadosFormulario() + [
            'title' => 'Novo usuario',
            'errors' => Session::consumeFlash('errors', []),
        ]);
    }

    public function store(): void
    {
        $result = $this->usuarioService->criar($this->request->post());

        if (!$result['success']) {
            Session::flash('errors', $result['errors']);
            Session::flash('_old', $this->request->post());
            $this->redirect('/usuarios/novo');
        }

        Session::flash('success', 'Usuario criado com sucesso.');
        $this->redirect('/usuarios/' . $result['id']);
    }

    public function show(string $id): void
    {
        $this->view('usuarios/show', [
            'title' => 'Detalhe do usuario',
            'usuario' => $this->usuarioService->buscar((int) $id),
        ]);
    }

    public function edit(string $id): void
    {
        $this->view('usuarios/edit', $this->usuarioService->dadosFormulario() + [
            'title' => 'Editar usuario',
            'usuario' => $this->usuarioService->buscar((int) $id),
            'errors' => Session::consumeFlash('errors', []),
        ]);
    }

    public function update(string $id): void
    {
        $result = $this->usuarioService->atualizar((int) $id, $this->request->post());

        if (!$result['success']) {
            Session::flash('errors', $result['errors']);
            Session::flash('_old', $this->request->post());
            $this->redirect('/usuarios/' . $id . '/editar');
        }

        Session::flash('success', 'Usuario atualizado com sucesso.');
        $this->redirect('/usuarios/' . $id);
    }

    public function destroy(string $id): void
    {
        $result = $this->usuarioService->excluir((int) $id);

        if (!$result['success']) {
            Session::flash('error', $result['message']);
            $this->redirect('/usuarios/' . $id);
        }

        Session::flash('success', 'Usuario removido da listagem com sucesso.');
        $this->redirect('/usuarios');
    }
}
