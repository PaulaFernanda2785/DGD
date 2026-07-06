<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Core\HttpException;
use App\Repositories\PerfilRepository;
use App\Repositories\UsuarioRepository;
use Throwable;

class UsuarioService
{
    private UsuarioRepository $usuarios;
    private PerfilRepository $perfis;
    private AuditoriaService $auditoria;

    public function __construct()
    {
        $this->usuarios = new UsuarioRepository();
        $this->perfis = new PerfilRepository();
        $this->auditoria = new AuditoriaService();
    }

    public function listar(array $filters): array
    {
        return [
            'usuarios' => $this->usuarios->paginate($filters),
            'perfis' => $this->perfis->allActive(),
        ];
    }

    public function dadosFormulario(): array
    {
        return ['perfis' => $this->perfis->allActive()];
    }

    public function buscar(int $id): array
    {
        $usuario = $this->usuarios->findById($id);

        if (!$usuario) {
            throw new HttpException(404, 'Usuario nao encontrado.');
        }

        return $usuario;
    }

    public function criar(array $data): array
    {
        $errors = $this->validar($data, true);

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            Database::beginTransaction();

            $id = $this->usuarios->create($this->normalizar($data) + [
                'senha_hash' => password_hash((string) $data['senha'], PASSWORD_DEFAULT),
                'trocar_senha_proximo_acesso' => isset($data['trocar_senha_proximo_acesso']) ? 1 : 0,
                'criado_por' => Auth::id(),
            ]);

            $this->auditoria->registrar('usuarios', 'criar', [
                'entidade' => 'usuarios',
                'entidade_id' => $id,
                'valor_novo' => ['email' => mb_strtolower(trim((string) $data['email']))],
            ]);

            Database::commit();

            return ['success' => true, 'id' => $id];
        } catch (Throwable $exception) {
            Database::rollBack();

            return ['success' => false, 'errors' => ['geral' => ['Nao foi possivel criar o usuario. Verifique duplicidade de e-mail ou CPF.']]];
        }
    }

    public function atualizar(int $id, array $data): array
    {
        $usuario = $this->buscar($id);
        $errors = $this->validar($data, false);

        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $normalized = $this->normalizar($data) + [
            'atualizado_por' => Auth::id(),
            'senha_hash' => null,
            'trocar_senha_proximo_acesso' => isset($data['trocar_senha_proximo_acesso']) ? 1 : 0,
        ];

        if (!empty($data['senha'])) {
            if ((string) $data['senha'] !== (string) ($data['senha_confirmacao'] ?? '')) {
                return ['success' => false, 'errors' => ['senha_confirmacao' => ['A confirmacao da senha nao confere.']]];
            }

            $normalized['senha_hash'] = password_hash((string) $data['senha'], PASSWORD_DEFAULT);
        }

        if ($this->removeriaUltimoAdmin($usuario, $normalized)) {
            return ['success' => false, 'errors' => ['perfil_id' => ['Nao e permitido remover ou inativar o ultimo Admin ativo.']]];
        }

        try {
            Database::beginTransaction();
            $this->usuarios->update($id, $normalized);

            $this->auditoria->registrar('usuarios', 'editar', [
                'entidade' => 'usuarios',
                'entidade_id' => $id,
                'valor_anterior' => $this->auditableUser($usuario),
                'valor_novo' => [
                    'nome' => $normalized['nome'],
                    'email' => $normalized['email'],
                    'perfil_id' => $normalized['perfil_id'],
                    'ativo' => $normalized['ativo'],
                    'senha_redefinida' => $normalized['senha_hash'] !== null,
                ],
            ]);

            Database::commit();

            return ['success' => true];
        } catch (Throwable $exception) {
            Database::rollBack();

            return ['success' => false, 'errors' => ['geral' => ['Nao foi possivel atualizar o usuario. Verifique duplicidade de e-mail ou CPF.']]];
        }
    }

    public function excluir(int $id): array
    {
        $usuario = $this->buscar($id);

        if ($this->removeriaUltimoAdmin($usuario, ['perfil_id' => (int) $usuario['perfil_id'], 'ativo' => 0])) {
            return ['success' => false, 'message' => 'Nao e permitido excluir o ultimo Admin ativo.'];
        }

        $this->usuarios->softDelete($id, Auth::id() ?? 0);
        $this->auditoria->registrar('usuarios', 'excluir', [
            'entidade' => 'usuarios',
            'entidade_id' => $id,
            'valor_anterior' => $this->auditableUser($usuario),
        ]);

        return ['success' => true];
    }

    public function alterarSenhaPropria(int $id, array $data): array
    {
        $usuario = $this->buscar($id);
        $senhaAtual = (string) ($data['senha_atual'] ?? '');
        $novaSenha = (string) ($data['nova_senha'] ?? '');
        $confirmacao = (string) ($data['confirmar_nova_senha'] ?? '');

        if (!password_verify($senhaAtual, $usuario['senha_hash'])) {
            return ['success' => false, 'errors' => ['senha_atual' => ['Senha atual invalida.']]];
        }

        if (strlen($novaSenha) < 8) {
            return ['success' => false, 'errors' => ['nova_senha' => ['A nova senha deve ter no minimo 8 caracteres.']]];
        }

        if ($novaSenha !== $confirmacao) {
            return ['success' => false, 'errors' => ['confirmar_nova_senha' => ['A confirmacao da nova senha nao confere.']]];
        }

        if (password_verify($novaSenha, $usuario['senha_hash'])) {
            return ['success' => false, 'errors' => ['nova_senha' => ['A nova senha deve ser diferente da senha atual.']]];
        }

        $this->usuarios->updatePassword($id, password_hash($novaSenha, PASSWORD_DEFAULT));
        $this->auditoria->registrar('senha', 'alterar_propria', [
            'entidade' => 'usuarios',
            'entidade_id' => $id,
        ]);

        return ['success' => true];
    }

    private function validar(array $data, bool $creating): array
    {
        $errors = [];

        if (trim((string) ($data['nome'] ?? '')) === '') {
            $errors['nome'][] = 'Informe o nome completo.';
        }

        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Informe um e-mail valido.';
        }

        if (!$this->perfis->findById((int) ($data['perfil_id'] ?? 0))) {
            $errors['perfil_id'][] = 'Selecione um perfil valido.';
        }

        if ($creating || !empty($data['senha'])) {
            if (strlen((string) ($data['senha'] ?? '')) < 8) {
                $errors['senha'][] = 'A senha deve ter no minimo 8 caracteres.';
            }

            if ((string) ($data['senha'] ?? '') !== (string) ($data['senha_confirmacao'] ?? '')) {
                $errors['senha_confirmacao'][] = 'A confirmacao da senha nao confere.';
            }
        }

        return $errors;
    }

    private function normalizar(array $data): array
    {
        return [
            'perfil_id' => (int) $data['perfil_id'],
            'nome' => trim((string) $data['nome']),
            'email' => mb_strtolower(trim((string) $data['email'])),
            'cpf' => preg_replace('/\D+/', '', (string) ($data['cpf'] ?? '')) ?: null,
            'telefone' => trim((string) ($data['telefone'] ?? '')),
            'cargo' => trim((string) ($data['cargo'] ?? '')),
            'instituicao' => trim((string) ($data['instituicao'] ?? 'CEDEC-PA')) ?: 'CEDEC-PA',
            'ativo' => isset($data['ativo']) ? (int) $data['ativo'] : 1,
        ];
    }

    private function removeriaUltimoAdmin(array $usuarioAtual, array $novo): bool
    {
        if (($usuarioAtual['perfil_codigo'] ?? '') !== 'ADMIN') {
            return false;
        }

        $novoPerfil = $this->perfis->findById((int) $novo['perfil_id']);
        $continuaraAdminAtivo = ($novoPerfil['codigo'] ?? '') === 'ADMIN' && (int) $novo['ativo'] === 1;

        return !$continuaraAdminAtivo && $this->usuarios->countActiveAdminsExcept((int) $usuarioAtual['id']) === 0;
    }

    private function auditableUser(array $usuario): array
    {
        return [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'perfil_codigo' => $usuario['perfil_codigo'],
            'ativo' => $usuario['ativo'],
        ];
    }
}
