<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Core\HttpException;
use App\Repositories\AnexoRepository;
use App\Repositories\CobradeRepository;
use App\Repositories\CompdecRepository;
use App\Repositories\DecretoRepository;
use App\Repositories\DominioRepository;
use Throwable;

class DecretoService
{
    private DecretoRepository $decretos;
    private DominioRepository $dominios;
    private CompdecRepository $compdecs;
    private CobradeRepository $cobrade;
    private AnexoRepository $anexos;
    private ProtocoloDgdService $protocolo;
    private AuditoriaService $auditoria;
    private AnexoService $anexoService;

    public function __construct()
    {
        $this->decretos = new DecretoRepository();
        $this->dominios = new DominioRepository();
        $this->compdecs = new CompdecRepository();
        $this->cobrade = new CobradeRepository();
        $this->anexos = new AnexoRepository();
        $this->protocolo = new ProtocoloDgdService();
        $this->auditoria = new AuditoriaService();
        $this->anexoService = new AnexoService();
    }

    public function listar(array $filters): array
    {
        $page = max((int) ($filters['page'] ?? 1), 1);

        return $this->decretos->paginate($filters, $page, 20) + [
            'dominios' => $this->formData(),
        ];
    }

    public function formData(): array
    {
        return [
            'municipios' => $this->dominios->municipios(),
            'tiposDecreto' => $this->dominios->tiposDecreto(),
            'statusHomologacao' => $this->dominios->statusHomologacao(),
            'statusReconhecimento' => $this->dominios->statusReconhecimento(),
            'statusRecurso' => $this->dominios->statusRecurso(),
            'statusEnvioPge' => $this->dominios->statusEnvioPge(),
            'tiposAnexo' => $this->dominios->tiposAnexo(),
            'analistas' => $this->dominios->analistas(),
            'cobradeGrupos' => $this->cobrade->grupos(),
            'cobradeSubgrupos' => $this->cobrade->subgrupos(null),
            'cobradeTipos' => $this->cobrade->tipos(null),
            'cobradeSubtipos' => $this->cobrade->subtiposComHierarquia(),
        ];
    }

    public function buscarDetalhe(int $id): array
    {
        $registro = $this->decretos->detalhe($id);

        if (!$registro) {
            throw new HttpException(404, 'Registro de desastre nao encontrado.');
        }

        return $registro + [
            'anexos' => $this->anexos->byDesastre($id),
        ];
    }

    public function buscarParaEdicao(int $id): array
    {
        $registro = $this->decretos->findById($id);

        if (!$registro) {
            throw new HttpException(404, 'Registro de desastre nao encontrado.');
        }

        return $registro;
    }

    public function cadastrar(array $data, array $files = []): array
    {
        $errors = $this->validar($data);

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $municipio = $this->dominios->findMunicipio((int) $data['municipio_id']);

        if (!$municipio) {
            return ['success' => false, 'errors' => ['municipio_id' => ['Municipio invalido.']]];
        }

        try {
            Database::beginTransaction();
            $protocol = $this->protocolo->gerar((string) $data['data_desastre'], $municipio['nome']);
            $payload = $this->normalizar($data) + $protocol + [
                'criado_por' => Auth::id(),
            ];

            $id = $this->decretos->create($payload);
            $this->auditoria->registrar('decretos', 'criar', [
                'entidade' => 'desastres',
                'entidade_id' => $id,
                'valor_novo' => ['protocolo_dgd' => $payload['protocolo_dgd']],
            ]);

            Database::commit();

            $warnings = $this->salvarAnexosDoFormulario($id, $files, $data);

            return ['success' => true, 'id' => $id, 'warnings' => $warnings];
        } catch (Throwable) {
            Database::rollBack();

            return ['success' => false, 'errors' => ['geral' => ['Nao foi possivel cadastrar o desastre.']]];
        }
    }

    public function atualizar(int $id, array $data, array $files = []): array
    {
        $registro = $this->buscarParaEdicao($id);
        $errors = $this->validar($data);

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $payload = $this->normalizar($data) + [
            'atualizado_por' => Auth::id(),
        ];

        try {
            $this->decretos->update($id, $payload);
            $this->auditoria->registrar('decretos', 'editar', [
                'entidade' => 'desastres',
                'entidade_id' => $id,
                'valor_anterior' => $registro,
                'valor_novo' => $payload,
            ]);

            $warnings = $this->salvarAnexosDoFormulario($id, $files, $data);

            return ['success' => true, 'warnings' => $warnings];
        } catch (Throwable) {
            return ['success' => false, 'errors' => ['geral' => ['Nao foi possivel atualizar o desastre.']]];
        }
    }

    public function excluir(int $id): void
    {
        $registro = $this->buscarParaEdicao($id);
        $this->decretos->softDelete($id, Auth::id() ?? 0);
        $this->auditoria->registrar('decretos', 'excluir', [
            'entidade' => 'desastres',
            'entidade_id' => $id,
            'valor_anterior' => ['protocolo_dgd' => $registro['protocolo_dgd']],
        ]);
    }

    public function atualizarStatus(int $id, string $field, int $value): void
    {
        $registro = $this->buscarParaEdicao($id);
        $this->decretos->updateStatus($id, $field, $value, Auth::id() ?? 0);
        $this->auditoria->registrar('decretos', 'editar_status_listagem', [
            'entidade' => 'desastres',
            'entidade_id' => $id,
            'valor_anterior' => [$field => $registro[$field] ?? null],
            'valor_novo' => [$field => $value],
        ]);
    }

    private function validar(array $data): array
    {
        $errors = [];

        foreach (['municipio_id', 'tipo_decreto_id', 'cobrade_subtipo_id', 'data_desastre'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[$field][] = 'Campo obrigatorio.';
            }
        }

        if (!empty($data['data_desastre']) && strtotime((string) $data['data_desastre']) > strtotime('today')) {
            $errors['data_desastre'][] = 'A data do desastre nao pode ser futura.';
        }

        if (($data['municipio_id'] ?? '') !== '' && ($data['ubm_id'] ?? '') !== '') {
            $ubm = $this->dominios->findUbmForMunicipio((int) $data['ubm_id'], (int) $data['municipio_id']);

            if (!$ubm) {
                $errors['ubm_id'][] = 'Selecione uma UBM vinculada ao municipio informado.';
            }
        }

        foreach (['numero_obitos', 'numero_feridos', 'numero_enfermos', 'numero_desabrigados', 'numero_desalojados', 'numero_outros_afetados'] as $field) {
            if ((int) ($data[$field] ?? 0) < 0) {
                $errors[$field][] = 'Informe valor maior ou igual a zero.';
            }
        }

        return $errors;
    }

    private function normalizar(array $data): array
    {
        $intOrNull = static fn (mixed $value): ?int => $value === '' || $value === null ? null : (int) $value;
        $strOrNull = static fn (mixed $value): ?string => trim((string) $value) === '' ? null : trim((string) $value);
        $compdec = $this->compdecs->findByMunicipioId((int) $data['municipio_id']);
        $ubmId = $intOrNull($data['ubm_id'] ?? null);
        $compdecValue = static fn (mixed $value): string => trim((string) $value) === '' ? 'Nao foi registrado' : trim((string) $value);

        if ($compdec) {
            $ubmId ??= $this->compdecs->syncUbm($compdec);
        }

        return [
            'municipio_id' => (int) $data['municipio_id'],
            'ubm_id' => $ubmId,
            'compdec_id' => $compdec ? (int) $compdec['id'] : null,
            'compdec_regiao_integracao' => $compdec ? $compdecValue($compdec['regiao_integracao'] ?? null) : 'Nao foi registrado',
            'compdec_prefeito' => $compdec ? $compdecValue($compdec['prefeito'] ?? null) : 'Nao foi registrado',
            'compdec_coordenador' => $compdec ? $compdecValue($compdec['coordenador'] ?? null) : 'Nao foi registrado',
            'compdec_telefone' => $compdec ? $compdecValue($compdec['telefone'] ?? null) : 'Nao foi registrado',
            'compdec_email' => $compdec ? $compdecValue($compdec['email'] ?? null) : 'Nao foi registrado',
            'tipo_decreto_id' => (int) $data['tipo_decreto_id'],
            'cobrade_subtipo_id' => (int) $data['cobrade_subtipo_id'],
            'data_desastre' => (string) $data['data_desastre'],
            'protocolo_s2id' => $strOrNull($data['protocolo_s2id'] ?? null),
            'numero_decreto_municipal' => $strOrNull($data['numero_decreto_municipal'] ?? null),
            'data_decreto_municipal' => $strOrNull($data['data_decreto_municipal'] ?? null),
            'numero_decreto_homologacao_estadual' => $strOrNull($data['numero_decreto_homologacao_estadual'] ?? null),
            'data_decreto_homologacao' => $strOrNull($data['data_decreto_homologacao'] ?? null),
            'homologacao_status_id' => (int) ($data['homologacao_status_id'] ?? 1),
            'reconhecimento_status_id' => (int) ($data['reconhecimento_status_id'] ?? 1),
            'protocolo_pae_pge' => $strOrNull($data['protocolo_pae_pge'] ?? null),
            'data_envio_pge' => $strOrNull($data['data_envio_pge'] ?? null),
            'status_envio_pge_id' => (int) ($data['status_envio_pge_id'] ?? 1),
            'analista_id' => $intOrNull($data['analista_id'] ?? null),
            'recurso_resposta_status_id' => (int) ($data['recurso_resposta_status_id'] ?? 1),
            'recurso_reconstrucao_status_id' => (int) ($data['recurso_reconstrucao_status_id'] ?? 1),
            'numero_obitos' => max((int) ($data['numero_obitos'] ?? 0), 0),
            'numero_feridos' => max((int) ($data['numero_feridos'] ?? 0), 0),
            'numero_enfermos' => max((int) ($data['numero_enfermos'] ?? 0), 0),
            'numero_desabrigados' => max((int) ($data['numero_desabrigados'] ?? 0), 0),
            'numero_desalojados' => max((int) ($data['numero_desalojados'] ?? 0), 0),
            'numero_outros_afetados' => max((int) ($data['numero_outros_afetados'] ?? 0), 0),
            'observacoes' => $strOrNull($data['observacoes'] ?? null),
        ];
    }

    private function salvarAnexosDoFormulario(int $desastreId, array $files, array $data): array
    {
        $result = $this->anexoService->salvarMultiplos(
            $desastreId,
            $files['anexos'] ?? [],
            $data['anexo_descricao'] ?? []
        );

        if ($result['errors'] === []) {
            return [];
        }

        return ['Alguns anexos nao foram enviados: ' . implode(' ', $result['errors'])];
    }
}
