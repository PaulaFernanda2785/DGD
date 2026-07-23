<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Services\TipoAjudaService;
use App\Repositories\TipoAjudaRepository;

class TipoAjudaController extends Controller
{
    public function index(): void
    {
        $this->view('tipos_ajuda/index', (new TipoAjudaService())->listar($this->request->query()) + ['title' => 'Tipos de ajuda']);
    }
    public function form(?string $id = null): void { $tipo = $id === null ? ['nome'=>'','unidade_medida'=>'','ativo'=>1] : (new TipoAjudaRepository())->find((int)$id); if (!$tipo) { $this->redirect('/tipos-ajuda'); return; } $this->view('tipos_ajuda/form', ['title'=>$id===null?'Novo tipo de ajuda':'Editar tipo de ajuda','tipo'=>$tipo,'errors'=>Session::consumeFlash('errors',[])]); }
    public function save(?string $id = null): void { $nome=trim((string)$this->request->post('nome')); $unidade=trim((string)$this->request->post('unidade_medida')); if ($nome==='' || $unidade==='') { Session::flash('errors',['geral'=>['Informe nome e unidade de medida.']]); $this->redirect($id===null?'/tipos-ajuda/novo':'/tipos-ajuda/'.$id.'/editar'); } (new TipoAjudaRepository())->salvar($id===null?null:(int)$id,$nome,$unidade,(string)$this->request->post('ativo','1')==='1'); Session::flash('success','Tipo de ajuda salvo com sucesso.'); $this->redirect('/tipos-ajuda'); }
    public function status(string $id): void { (new TipoAjudaRepository())->status((int)$id,(string)$this->request->post('ativo')==='1'); $this->redirect('/tipos-ajuda'); }
    public function destroy(string $id): void { (new TipoAjudaRepository())->excluir((int)$id); Session::flash('success','Tipo de ajuda excluído.'); $this->redirect('/tipos-ajuda'); }
}
