<?php if (($paginacao['paginas'] ?? 1) > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= (int) $paginacao['paginas']; $i++): ?>
            <?php $query['page'] = $i; ?>
            <a class="<?= $i === (int) $paginacao['pagina'] ? 'active' : ''; ?>" href="<?= e($baseUrl . '?' . http_build_query($query)); ?>">
                <?= e((string) $i); ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
