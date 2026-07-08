<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Repositories\CompdecRepository;
use Throwable;

class CompdecService
{
    private CompdecRepository $compdecs;
    private AuditoriaService $auditoria;

    public function __construct()
    {
        $this->compdecs = new CompdecRepository();
        $this->auditoria = new AuditoriaService();
    }

    public function listar(array $filters): array
    {
        $page = max((int) ($filters['page'] ?? 1), 1);
        $filters = array_intersect_key($filters, array_flip([
            'busca',
            'regiao_integracao',
            'tem_compdec',
            'ubm',
            'page',
        ]));

        return $this->compdecs->paginate($filters, $page, 20) + [
            'resumo' => $this->compdecs->resumo($filters),
            'regioes' => $this->compdecs->regioes(),
            'ubms' => $this->compdecs->ubms(),
            'filtros' => $filters,
        ];
    }

    public function buscar(int $id): array
    {
        $compdec = $this->compdecs->findById($id);

        if (!$compdec) {
            throw new HttpException(404, 'COMPDEC não encontrada.');
        }

        return $compdec;
    }

    public function buscarPorMunicipio(int $municipioId): ?array
    {
        $compdec = $this->compdecs->findByMunicipioId($municipioId);

        if (!$compdec) {
            return null;
        }

        $ubmId = $this->compdecs->syncUbm($compdec);

        return [
            'id' => (int) $compdec['id'],
            'municipio_id' => (int) $compdec['municipio_id'],
            'municipio' => $compdec['municipio'],
            'situacao_compdec' => (int) $compdec['tem_compdec'] === 1 ? 'Possui COMPDEC' : 'Não possui COMPDEC',
            'regiao_integracao' => $this->campoRegistrado($compdec['regiao_integracao'] ?? null),
            'tem_compdec' => (int) $compdec['tem_compdec'],
            'prefeito' => $this->campoRegistrado($compdec['prefeito'] ?? null),
            'ubm_id' => $ubmId,
            'ubm_nome' => $this->campoRegistrado($compdec['ubm_nome'] ?? null),
            'coordenador' => $this->campoRegistrado($compdec['coordenador'] ?? null),
            'telefone' => $this->campoRegistrado($compdec['telefone'] ?? null),
            'email' => $this->campoRegistrado($compdec['email'] ?? null),
        ];
    }

    public function atualizar(int $id, array $data, array $files = []): array
    {
        $registro = $this->buscar($id);
        $payload = $this->normalizar($data);
        $errors = $this->validar($payload);
        $foto = $this->processarFoto($files['foto_coordenador'] ?? null, $registro);

        if (!$foto['success']) {
            $errors['foto_coordenador'][] = $foto['message'];
        }

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        if ($foto['path'] !== null) {
            $payload['foto_coordenador'] = $foto['path'];
        }

        try {
            Database::beginTransaction();
            $this->compdecs->update($id, $payload);
            $atualizado = $this->buscar($id);
            $this->compdecs->syncUbm($atualizado);
            $this->auditoria->registrar('compdecs', 'editar', [
                'entidade' => 'compdecs',
                'entidade_id' => $id,
                'valor_anterior' => $registro,
                'valor_novo' => $payload,
            ]);
            Database::commit();

            return ['success' => true];
        } catch (Throwable) {
            Database::rollBack();

            return ['success' => false, 'errors' => ['geral' => ['Não foi possível atualizar a COMPDEC.']]];
        }
    }

    private function validar(array $data): array
    {
        $errors = [];

        if (($data['email'] ?? null) !== null && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Informe um e-mail válido.';
        }

        foreach (['regiao_integracao', 'prefeito', 'ubm_nome', 'coordenador'] as $field) {
            if (($data[$field] ?? null) !== null && strlen((string) $data[$field]) > 180) {
                $errors[$field][] = 'Informe no máximo 180 caracteres.';
            }
        }

        return $errors;
    }

    private function normalizar(array $data): array
    {
        $strOrNull = static fn (mixed $value): ?string => trim((string) $value) === '' ? null : trim((string) $value);

        return [
            'regiao_integracao' => $strOrNull($data['regiao_integracao'] ?? null),
            'tem_compdec' => (int) (($data['tem_compdec'] ?? '0') === '1'),
            'prefeito' => $strOrNull($data['prefeito'] ?? null),
            'ubm_nome' => $strOrNull($data['ubm_nome'] ?? null),
            'coordenador' => $strOrNull($data['coordenador'] ?? null),
            'telefone' => $strOrNull($data['telefone'] ?? null),
            'email' => $strOrNull($data['email'] ?? null),
            'endereco' => $strOrNull($data['endereco'] ?? null),
            'data_atualizacao' => $strOrNull($data['data_atualizacao'] ?? null),
        ];
    }

    private function campoRegistrado(mixed $value): string
    {
        $value = trim((string) $value);

        return $value === '' || $value === 'Nao foi registrado' ? 'Não foi registrado' : $value;
    }

    private function processarFoto(mixed $file, array $registro): array
    {
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['success' => true, 'path' => null];
        }

        if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Não foi possível receber o arquivo da foto.', 'path' => null];
        }

        $maxBytes = 5 * 1024 * 1024;

        if ((int) ($file['size'] ?? 0) > $maxBytes) {
            return ['success' => false, 'message' => 'A foto deve ter no máximo 5 MB.', 'path' => null];
        }

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return ['success' => false, 'message' => 'Envie uma imagem JPG ou PNG.', 'path' => null];
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return ['success' => false, 'message' => 'Arquivo de foto inválido.', 'path' => null];
        }

        $mime = mime_content_type($tmpName) ?: '';

        if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
            return ['success' => false, 'message' => 'O arquivo enviado não é uma imagem válida.', 'path' => null];
        }

        $directory = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'compdec' . DIRECTORY_SEPARATOR . 'coordenadores';

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return ['success' => false, 'message' => 'Não foi possível preparar a pasta de fotos.', 'path' => null];
        }

        $codigo = preg_replace('/\D+/', '', (string) ($registro['municipio_codigo'] ?? '')) ?: 'compdec';
        $filename = 'coord_defesa_civil_' . $codigo . '_' . bin2hex(random_bytes(8)) . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
        $destination = $directory . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($tmpName, $destination)) {
            return ['success' => false, 'message' => 'Não foi possível salvar a foto enviada.', 'path' => null];
        }

        return ['success' => true, 'path' => '/uploads/compdec/coordenadores/' . $filename];
    }
}
