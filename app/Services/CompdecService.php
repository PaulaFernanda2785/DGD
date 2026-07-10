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

    public function dadosEdicao(int $id): array
    {
        $compdec = $this->buscar($id);

        return [
            'compdec' => $compdec,
            'ubm' => $this->compdecs->findUbmByCompdec($compdec),
            'ubmOptions' => $this->compdecs->ubmOptions(),
        ];
    }

    public function dadosDetalhe(int $id): array
    {
        $compdec = $this->buscar($id);

        return [
            'compdec' => $compdec,
            'ubm' => $this->compdecs->findUbmByCompdec($compdec),
        ];
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
        $ubmPayload = $this->normalizarUbm($data, $payload);
        $errors = $this->validar($payload);
        $errors = array_merge_recursive($errors, $this->validarFormatoCoordenadas($data));
        $errors = array_merge_recursive($errors, $this->validarUbm($ubmPayload));
        $foto = $this->processarFoto($files['foto_coordenador'] ?? null, $registro);

        if (!$foto['success']) {
            $errors['foto_coordenador'][] = $foto['message'];
        }

        if ($ubmPayload['id'] > 0) {
            $ubmSelecionada = $this->compdecs->findUbmById($ubmPayload['id']);

            if (!$ubmSelecionada) {
                $errors['ubm_nome'][] = 'A UBM selecionada nÃ£o foi encontrada. Atualize a tela e selecione novamente.';
            } else {
                $payload['ubm_nome'] = trim((string) $ubmSelecionada['nome']);
                $ubmPayload['nome'] = $payload['ubm_nome'];

                if ($ubmPayload['latitude'] === null && $ubmPayload['longitude'] === null) {
                    $ubmPayload['latitude'] = $this->floatOrNull($ubmSelecionada['latitude'] ?? null);
                    $ubmPayload['longitude'] = $this->floatOrNull($ubmSelecionada['longitude'] ?? null);
                }
            }
        } elseif (($payload['ubm_nome'] ?? null) !== null) {
            $ubmSelecionada = $this->compdecs->findUniqueUbmByName((string) $payload['ubm_nome']);

            if ($ubmSelecionada) {
                $ubmPayload['id'] = (int) $ubmSelecionada['id'];
                $payload['ubm_nome'] = trim((string) $ubmSelecionada['nome']);
                $ubmPayload['nome'] = $payload['ubm_nome'];

                if ($ubmPayload['latitude'] === null && $ubmPayload['longitude'] === null) {
                    $ubmPayload['latitude'] = $this->floatOrNull($ubmSelecionada['latitude'] ?? null);
                    $ubmPayload['longitude'] = $this->floatOrNull($ubmSelecionada['longitude'] ?? null);
                }
            } elseif ($this->compdecs->countUbmsByName((string) $payload['ubm_nome']) > 1) {
                $errors['ubm_nome'][] = 'Existe mais de uma UBM com esse nome. Selecione a unidade correta na lista inteligente.';
            }
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
            $this->compdecs->syncUbm(
                $atualizado,
                $ubmPayload['latitude'],
                $ubmPayload['longitude'],
                $ubmPayload['ativo'],
                $ubmPayload['id'] > 0 ? $ubmPayload['id'] : null
            );
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

        return array_merge_recursive(
            $errors,
            $this->validarParCoordenadas($data['latitude'] ?? null, $data['longitude'] ?? null, 'latitude', 'longitude', 'COMPDEC')
        );
    }

    private function validarUbm(array $data): array
    {
        $errors = $this->validarParCoordenadas($data['latitude'], $data['longitude'], 'ubm_latitude', 'ubm_longitude', 'UBM');

        if (($data['latitude'] !== null || $data['longitude'] !== null) && trim((string) ($data['nome'] ?? '')) === '') {
            $errors['ubm_nome'][] = 'Informe a UBM antes de salvar a geolocalizaÃ§Ã£o.';
        }

        return $errors;
    }

    private function validarFormatoCoordenadas(array $data): array
    {
        $errors = [];
        $fields = [
            'latitude' => 'Latitude da COMPDEC',
            'longitude' => 'Longitude da COMPDEC',
            'ubm_latitude' => 'Latitude da UBM',
            'ubm_longitude' => 'Longitude da UBM',
        ];

        foreach ($fields as $field => $label) {
            $raw = trim(str_replace(',', '.', (string) ($data[$field] ?? '')));

            if ($raw !== '' && filter_var($raw, FILTER_VALIDATE_FLOAT) === false) {
                $errors[$field][] = $label . ': informe uma coordenada numÃ©rica vÃ¡lida.';
            }
        }

        return $errors;
    }

    private function validarParCoordenadas(?float $latitude, ?float $longitude, string $latitudeField, string $longitudeField, string $label): array
    {
        $errors = [];
        $hasLatitude = $latitude !== null;
        $hasLongitude = $longitude !== null;

        if ($hasLatitude !== $hasLongitude) {
            $errors[$hasLatitude ? $longitudeField : $latitudeField][] = $label . ': informe latitude e longitude juntas.';
            return $errors;
        }

        if (!$hasLatitude) {
            return $errors;
        }

        if ($latitude < -10.5 || $latitude > 3.5) {
            $errors[$latitudeField][] = $label . ': latitude fora dos limites esperados para o ParÃ¡.';
        }

        if ($longitude < -59.5 || $longitude > -45.5) {
            $errors[$longitudeField][] = $label . ': longitude fora dos limites esperados para o ParÃ¡.';
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
            'latitude' => $this->coordinateOrNull($data['latitude'] ?? null),
            'longitude' => $this->coordinateOrNull($data['longitude'] ?? null),
        ];
    }

    private function normalizarUbm(array $data, array $payload): array
    {
        return [
            'id' => max((int) ($data['ubm_id'] ?? 0), 0),
            'nome' => $payload['ubm_nome'] ?? null,
            'latitude' => $this->coordinateOrNull($data['ubm_latitude'] ?? null),
            'longitude' => $this->coordinateOrNull($data['ubm_longitude'] ?? null),
            'ativo' => (string) ($data['ubm_ativo'] ?? '1') === '1',
        ];
    }

    private function coordinateOrNull(mixed $value): ?float
    {
        $value = trim(str_replace(',', '.', (string) $value));

        if ($value === '') {
            return null;
        }

        $float = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $float === false ? null : (float) $float;
    }

    private function floatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
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

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return ['success' => false, 'message' => 'Envie uma imagem JPG ou PNG.', 'path' => null];
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            return ['success' => false, 'message' => 'Arquivo de foto inválido.', 'path' => null];
        }

        $mime = mime_content_type($tmpName) ?: '';

        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
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
