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
        $filters = $this->filters();

        $this->view('painel/index', [
            'title' => 'Painel',
            'filters' => $filters,
            'resumo' => $this->painelService->resumo($filters),
            'indicadores' => $this->painelService->indicadores($filters),
            'mapa' => $this->painelService->mapa($filters),
            'recentes' => $this->painelService->recentes($filters),
            'opcoes' => $this->painelService->opcoesFiltros(),
            'stylesheets' => ['/assets/vendor/leaflet/leaflet.css'],
            'scripts' => ['/assets/vendor/leaflet/leaflet.js'],
        ]);
    }

    public function printReport(): void
    {
        $filters = $this->filters();
        $relatorio = $this->painelService->relatorio($filters);
        $geradoEm = new \DateTimeImmutable('now');

        ob_start();
        require view_path('painel/partials/print_report');
        $html = (string) ob_get_clean();

        $filenameSuffix = preg_replace('/[^A-Za-z0-9_-]+/', '-', implode('-', array_filter([
            'painel',
            $filters['ano'] ?? '',
            $filters['municipio_id'] !== '' ? 'municipio-' . $filters['municipio_id'] : '',
            $filters['regiao_integracao'] !== '' ? $filters['regiao_integracao'] : '',
        ]))) ?: 'painel';

        $this->json([
            'success' => true,
            'title' => 'Relatório do painel',
            'filename' => 'relatorio-' . $filenameSuffix . '.pdf',
            'html' => $html,
        ]);
    }

    private function filters(): array
    {
        $query = $this->request->query();

        if (!is_array($query)) {
            $query = [];
        }

        $ano = trim((string) ($query['ano'] ?? (string) date('Y')));
        $ano = preg_match('/^\d{4}$/', $ano) === 1 ? $ano : (string) date('Y');

        return [
            'ano' => $ano,
            'municipio_id' => $this->positiveInt($query['municipio_id'] ?? null),
            'regiao_integracao' => trim((string) ($query['regiao_integracao'] ?? '')),
            'tipo_decreto_id' => $this->positiveInt($query['tipo_decreto_id'] ?? null),
            'homologacao_status_id' => $this->positiveInt($query['homologacao_status_id'] ?? null),
            'reconhecimento_status_id' => $this->positiveInt($query['reconhecimento_status_id'] ?? null),
            'status_prazo_pge' => trim((string) ($query['status_prazo_pge'] ?? '')),
        ];
    }

    private function positiveInt(mixed $value): string
    {
        $value = trim((string) $value);

        if ($value === '' || !ctype_digit($value) || (int) $value <= 0) {
            return '';
        }

        return $value;
    }
}
