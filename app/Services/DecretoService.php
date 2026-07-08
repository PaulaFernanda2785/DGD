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
    private DecretoHistoricoService $historico;
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
        $this->historico = new DecretoHistoricoService();
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

        $protocoloCorrigido = $this->protocolo->corrigirMunicipioEmProtocolo(
            (string) $registro['protocolo_dgd'],
            (string) $registro['municipio']
        );

        if ($protocoloCorrigido !== $registro['protocolo_dgd']) {
            $this->decretos->updateProtocolo($id, $protocoloCorrigido);
            $registro['protocolo_dgd'] = $protocoloCorrigido;
        }

        return $registro + [
            'anexos' => $this->anexos->byDesastre($id),
            'historico' => $this->historico->listar($id),
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
        $observacaoHistorico = $this->observacaoHistorico($data);
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
                'justificativa' => $observacaoHistorico,
            ]);
            $this->historico->registrar($id, 'Cadastro do decreto', null, $payload['protocolo_dgd'], $observacaoHistorico);

            Database::commit();

            $warnings = $this->salvarAnexosDoFormulario($id, $files, $data, $observacaoHistorico);

            return ['success' => true, 'id' => $id, 'warnings' => $warnings];
        } catch (Throwable) {
            Database::rollBack();

            return ['success' => false, 'errors' => ['geral' => ['Nao foi possivel cadastrar o desastre.']]];
        }
    }

    public function atualizar(int $id, array $data, array $files = []): array
    {
        $registro = $this->buscarParaEdicao($id);
        $observacaoHistorico = $this->observacaoHistorico($data);
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
                'justificativa' => $observacaoHistorico,
            ]);
            $this->registrarAlteracoes($id, $registro, $payload, $observacaoHistorico);

            $warnings = $this->salvarAnexosDoFormulario($id, $files, $data, $observacaoHistorico);

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

    public function atualizarStatus(int $id, string $field, int $value, ?string $observacao = null): void
    {
        $registro = $this->buscarParaEdicao($id);
        $this->decretos->updateStatus($id, $field, $value, Auth::id() ?? 0);
        $this->auditoria->registrar('decretos', 'editar_status_listagem', [
            'entidade' => 'desastres',
            'entidade_id' => $id,
            'valor_anterior' => [$field => $registro[$field] ?? null],
            'valor_novo' => [$field => $value],
            'justificativa' => $observacao,
        ]);

        if ((string) ($registro[$field] ?? '') !== (string) $value) {
            $this->historico->registrar(
                $id,
                $this->campoHistoricoLabel($field),
                $this->valorHistorico($field, $registro[$field] ?? null),
                $this->valorHistorico($field, $value),
                $observacao
            );
        }
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

    private function salvarAnexosDoFormulario(int $desastreId, array $files, array $data, ?string $observacao = null): array
    {
        $result = $this->anexoService->salvarMultiplos(
            $desastreId,
            $files['anexos'] ?? [],
            $data['anexo_descricao'] ?? [],
            $observacao
        );

        if ($result['errors'] === []) {
            return [];
        }

        return ['Alguns anexos nao foram enviados: ' . implode(' ', $result['errors'])];
    }

    private function registrarAlteracoes(int $desastreId, array $registro, array $payload, ?string $observacao): void
    {
        foreach ($payload as $field => $newValue) {
            if (in_array($field, ['atualizado_por'], true)) {
                continue;
            }

            $oldValue = $registro[$field] ?? null;

            if ($this->valoresIguais($oldValue, $newValue)) {
                continue;
            }

            $this->historico->registrar(
                $desastreId,
                $this->campoHistoricoLabel($field),
                $this->valorHistorico($field, $oldValue),
                $this->valorHistorico($field, $newValue),
                $observacao
            );
        }
    }

    private function valoresIguais(mixed $oldValue, mixed $newValue): bool
    {
        $oldValue = $oldValue === null ? '' : trim((string) $oldValue);
        $newValue = $newValue === null ? '' : trim((string) $newValue);

        return $oldValue === $newValue;
    }

    private function observacaoHistorico(array $data): ?string
    {
        $observacao = trim((string) ($data['historico_observacao'] ?? ''));

        return $observacao === '' ? null : $observacao;
    }

    private function campoHistoricoLabel(string $field): string
    {
        return [
            'municipio_id' => 'Município',
            'ubm_id' => 'UBM atuante',
            'compdec_id' => 'COMPDEC',
            'compdec_regiao_integracao' => 'Região de integração',
            'compdec_prefeito' => 'Prefeito',
            'compdec_coordenador' => 'Coordenador COMPDEC',
            'compdec_telefone' => 'Telefone COMPDEC',
            'compdec_email' => 'E-mail COMPDEC',
            'tipo_decreto_id' => 'Tipo de decreto',
            'cobrade_subtipo_id' => 'Subtipo COBRADE',
            'data_desastre' => 'Data do desastre',
            'protocolo_s2id' => 'Protocolo S2ID',
            'numero_decreto_municipal' => 'Número do decreto municipal',
            'data_decreto_municipal' => 'Data do decreto municipal',
            'numero_decreto_homologacao_estadual' => 'Número do decreto estadual',
            'data_decreto_homologacao' => 'Data de homologação',
            'homologacao_status_id' => 'Homologação',
            'reconhecimento_status_id' => 'Reconhecimento',
            'protocolo_pae_pge' => 'Protocolo PAE/PGE',
            'data_envio_pge' => 'Data de envio à PGE',
            'status_envio_pge_id' => 'Status de envio à PGE',
            'analista_id' => 'Analista',
            'recurso_resposta_status_id' => 'Recurso de resposta',
            'recurso_reconstrucao_status_id' => 'Recurso de reconstrução',
            'numero_obitos' => 'Óbitos',
            'numero_feridos' => 'Feridos',
            'numero_enfermos' => 'Enfermos',
            'numero_desabrigados' => 'Desabrigados',
            'numero_desalojados' => 'Desalojados',
            'numero_outros_afetados' => 'Outros afetados',
            'observacoes' => 'Observações',
        ][$field] ?? $field;
    }

    private function valorHistorico(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($field) {
            'municipio_id' => $this->nomePorId($this->dominios->municipios(), (int) $value),
            'ubm_id' => $this->nomePorId($this->dominios->ubms(), (int) $value),
            'tipo_decreto_id' => $this->nomePorId($this->dominios->tiposDecreto(), (int) $value),
            'homologacao_status_id' => $this->nomePorId($this->dominios->statusHomologacao(), (int) $value),
            'reconhecimento_status_id' => $this->nomePorId($this->dominios->statusReconhecimento(), (int) $value),
            'status_envio_pge_id' => $this->nomePorId($this->dominios->statusEnvioPge(), (int) $value),
            'recurso_resposta_status_id', 'recurso_reconstrucao_status_id' => $this->nomePorId($this->dominios->statusRecurso(), (int) $value),
            'analista_id' => $this->nomePorId($this->dominios->analistas(), (int) $value),
            'cobrade_subtipo_id' => $this->cobradeSubtipoHistorico((int) $value),
            default => (string) $value,
        };
    }

    private function nomePorId(array $items, int $id): ?string
    {
        foreach ($items as $item) {
            if ((int) ($item['id'] ?? 0) === $id) {
                return (string) ($item['nome'] ?? $id);
            }
        }

        return (string) $id;
    }

    private function cobradeSubtipoHistorico(int $id): ?string
    {
        foreach ($this->cobrade->subtiposComHierarquia() as $subtipo) {
            if ((int) ($subtipo['id'] ?? 0) === $id) {
                return trim((string) (($subtipo['codigo'] ? $subtipo['codigo'] . ' - ' : '') . $subtipo['nome']));
            }
        }

        return (string) $id;
    }
}
