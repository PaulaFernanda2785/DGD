<?php if (($paginacao['paginas'] ?? 1) > 1): ?>
    <nav class="pagination">
        <?php for ($i = 1; $i <= $paginacao['paginas']; $i++): ?>
            <?php $query = array_merge($filtros, ['page' => $i]); ?>
            <a class="<?= $i === (int) $paginacao['pagina'] ? 'active' : ''; ?>" href="<?= e(url('/decretos') . '?' . http_build_query($query)); ?>">
                <?= e($i); ?>
            </a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
