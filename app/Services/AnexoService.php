<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\HttpException;
use App\Repositories\AnexoRepository;
use App\Repositories\DecretoRepository;

class AnexoService
{
    private AnexoRepository $anexos;
    private DecretoRepository $decretos;
    private AuditoriaService $auditoria;

    public function __construct()
    {
        $this->anexos = new AnexoRepository();
        $this->decretos = new DecretoRepository();
        $this->auditoria = new AuditoriaService();
    }

    public function salvar(int $desastreId, array $file, array $data): array
    {
        if (!$this->decretos->findById($desastreId)) {
            throw new HttpException(404, 'Registro de desastre nao encontrado.');
        }

        $validation = $this->validarArquivo($file);

        if ($validation !== null) {
            return ['success' => false, 'message' => $validation];
        }

        $uploadConfig = config('upload');
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
        $dir = $uploadConfig['path'] . DIRECTORY_SEPARATOR . $desastreId;

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $target = $dir . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file((string) $file['tmp_name'], $target)) {
            return ['success' => false, 'message' => 'Nao foi possivel salvar o arquivo enviado.'];
        }

        $id = $this->anexos->create([
            'desastre_id' => $desastreId,
            'tipo_anexo_id' => (int) ($data['tipo_anexo_id'] ?? 0),
            'nome_original' => (string) $file['name'],
            'nome_arquivo' => $safeName,
            'caminho_armazenado' => $target,
            'extensao' => $extension,
            'mime_type' => (string) mime_content_type($target),
            'tamanho_bytes' => (int) $file['size'],
            'hash_sha256' => hash_file('sha256', $target) ?: null,
            'descricao' => trim((string) ($data['descricao'] ?? '')) ?: null,
            'enviado_por' => Auth::id(),
        ]);

        $this->auditoria->registrar('anexos', 'upload', [
            'entidade' => 'desastre_anexos',
            'entidade_id' => $id,
            'valor_novo' => ['desastre_id' => $desastreId, 'nome_original' => $file['name']],
        ]);

        return ['success' => true, 'id' => $id];
    }

    public function buscarParaDownload(int $id): array
    {
        $anexo = $this->anexos->findById($id);

        if (!$anexo || !is_file($anexo['caminho_armazenado'])) {
            throw new HttpException(404, 'Anexo nao encontrado.');
        }

        return $anexo;
    }

    public function excluir(int $id): int
    {
        $anexo = $this->buscarParaDownload($id);
        $this->anexos->softDelete($id, Auth::id() ?? 0);
        $this->auditoria->registrar('anexos', 'excluir', [
            'entidade' => 'desastre_anexos',
            'entidade_id' => $id,
            'valor_anterior' => ['nome_original' => $anexo['nome_original']],
        ]);

        return (int) $anexo['desastre_id'];
    }

    private function validarArquivo(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'Selecione um arquivo valido.';
        }

        $config = config('upload');
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $config['allowed_extensions'], true)) {
            return 'Extensao de arquivo nao permitida.';
        }

        if ((int) $file['size'] > ((int) $config['max_mb'] * 1024 * 1024)) {
            return 'Arquivo acima do tamanho maximo permitido.';
        }

        $mime = mime_content_type((string) $file['tmp_name']);

        if (!in_array($mime, $config['allowed_mime_types'], true)) {
            return 'Tipo MIME de arquivo nao permitido.';
        }

        return null;
    }
}
