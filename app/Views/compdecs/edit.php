<?php
    $possuiCompdec = (string) old('tem_compdec', $compdec['tem_compdec'] ?? '0');
?>

<div class="page-header page-header-modern compdec-edit-header">
    <div>
        <span class="breadcrumb">COMPDECs &gt; Editar</span>
        <h1><?= e($compdec['municipio']); ?></h1>
        <p>Atualize os dados institucionais, contatos, UBM atuante e foto do coordenador.</p>
    </div>

    <div class="actions">
        <a class="button button-light" href="<?= e(url('/compdecs/' . $compdec['id'])); ?>">Voltar</a>
    </div>
</div>

<?php require view_path('components/form_errors'); ?>

<form method="post" action="<?= e(url('/compdecs/' . $compdec['id'] . '/editar')); ?>" enctype="multipart/form-data" class="form-grid compdec-edit-form">
    <?= csrf_input(); ?>

    <section class="span-2 form-section compdec-edit-profile-section">
        <div class="form-section-heading">
            <div>
                <span>01</span>
                <h2>Coordenador e foto</h2>
            </div>
            <p>Atualize a imagem e os dados principais do responsável pela COMPDEC municipal.</p>
        </div>

        <div class="compdec-edit-profile">
            <div class="compdec-edit-photo">
                <?= compdec_photo_thumb($compdec['foto_coordenador'] ?? null, $compdec['coordenador'] ?? 'Coordenador COMPDEC', 'compdec-photo-large'); ?>
                <label class="button button-light" for="foto_coordenador">Selecionar foto</label>
                <input id="foto_coordenador" name="foto_coordenador" type="file" accept="image/jpeg,image/png">
                <small>Arquivos JPG ou PNG. A imagem atual será mantida se nenhum arquivo for enviado.</small>
            </div>

            <div class="compdec-edit-grid">
                <div class="field">
                    <label for="coordenador">Coordenador</label>
                    <input id="coordenador" name="coordenador" value="<?= e(old('coordenador', $compdec['coordenador'] ?? '')); ?>" placeholder="Nome do coordenador">
                </div>

                <div class="field">
                    <label for="tem_compdec">Situação da COMPDEC</label>
                    <select id="tem_compdec" name="tem_compdec">
                        <option value="1" <?= $possuiCompdec === '1' ? 'selected' : ''; ?>>Possui COMPDEC</option>
                        <option value="0" <?= $possuiCompdec === '0' ? 'selected' : ''; ?>>Não possui COMPDEC</option>
                    </select>
                </div>

                <div class="field">
                    <label for="telefone">Telefone</label>
                    <input id="telefone" name="telefone" value="<?= e(old('telefone', $compdec['telefone'] ?? '')); ?>" placeholder="(00) 00000-0000">
                </div>

                <div class="field">
                    <label for="email">E-mail</label>
                    <input id="email" name="email" type="email" value="<?= e(old('email', $compdec['email'] ?? '')); ?>" placeholder="email@dominio.gov.br">
                </div>
            </div>
        </div>
    </section>

    <section class="span-2 form-section compdec-edit-location-section">
        <div class="form-section-heading">
            <div>
                <span>02</span>
                <h2>Município e atuação</h2>
            </div>
            <p>Dados territoriais e relacionamento operacional com UBM, prefeitura e região de integração.</p>
        </div>

        <div class="compdec-edit-grid">
            <div class="field">
                <label>Município</label>
                <input value="<?= e($compdec['municipio']); ?>" disabled>
            </div>

            <div class="field">
                <label>Código IBGE</label>
                <input value="<?= e($compdec['municipio_codigo'] ?? ''); ?>" disabled>
            </div>

            <div class="field">
                <label for="regiao_integracao">Região de integração</label>
                <input id="regiao_integracao" name="regiao_integracao" value="<?= e(old('regiao_integracao', $compdec['regiao_integracao'] ?? '')); ?>" placeholder="Região de integração">
            </div>

            <div class="field">
                <label for="ubm_nome">UBM atuante</label>
                <input id="ubm_nome" name="ubm_nome" value="<?= e(old('ubm_nome', $compdec['ubm_nome'] ?? '')); ?>" placeholder="Unidade Bombeiro Militar atuante">
            </div>

            <div class="field">
                <label for="prefeito">Prefeito</label>
                <input id="prefeito" name="prefeito" value="<?= e(old('prefeito', $compdec['prefeito'] ?? '')); ?>" placeholder="Nome do prefeito">
            </div>

            <div class="field">
                <label for="data_atualizacao">Data de atualização</label>
                <input id="data_atualizacao" name="data_atualizacao" value="<?= e(old('data_atualizacao', $compdec['data_atualizacao'] ?? '')); ?>" placeholder="AAAA-MM-DD ou data registrada">
            </div>
        </div>
    </section>

    <section class="span-2 form-section compdec-edit-contact-section">
        <div class="form-section-heading">
            <div>
                <span>03</span>
                <h2>Endereço e observações cadastrais</h2>
            </div>
            <p>Informe o endereço institucional ou registre a ausência de dado quando ainda não houver cadastro.</p>
        </div>

        <div class="field">
            <label for="endereco">Endereço</label>
            <input id="endereco" name="endereco" value="<?= e(old('endereco', $compdec['endereco'] ?? '')); ?>" placeholder="Endereço da COMPDEC ou da prefeitura">
        </div>
    </section>

    <div class="form-actions form-actions-sticky span-2">
        <button type="submit" class="button button-primary">Salvar alterações</button>
        <a class="button button-light" href="<?= e(url('/compdecs/' . $compdec['id'])); ?>">Cancelar</a>
    </div>
</form>
