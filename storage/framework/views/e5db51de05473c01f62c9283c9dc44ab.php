<?php $__env->startSection('title', 'Pagos Pendientes'); ?>
<?php $__env->startSection('page_title', 'Pagos SINPE pendientes'); ?>

<?php $__env->startSection('content'); ?>
<div class="stat-card">
    <?php if($payments->isEmpty()): ?>
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-check-circle fa-3x text-success mb-3 d-block"></i>
            <p class="fw-600">No hay pagos pendientes. ¡Todo al día!</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr><th>Owner</th><th>Plan</th><th>Monto</th><th>Comprobante</th><th>Fecha</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="fw-600" style="font-size:13px"><?php echo e($p->subscription->user->name); ?></div>
                        <div class="text-muted" style="font-size:11px"><?php echo e($p->subscription->user->email); ?></div>
                    </td>
                    <td style="font-size:13px"><?php echo e($p->subscription->plan?->name ?? '—'); ?></td>
                    <td class="fw-700" style="font-size:13px">₡<?php echo e(number_format($p->amount,0,',','.')); ?></td>
                    <td>
                        <?php if($p->proof_path): ?>
                            <a href="<?php echo e(\Storage::disk('s3')->url($p->proof_path)); ?>" target="_blank" class="btn btn-sm" style="background:#e3f2fd;color:#1565c0;border-radius:8px;font-size:11px">
                                <i class="fa-solid fa-image me-1"></i>Ver comprobante
                            </a>
                        <?php else: ?> <span class="text-muted" style="font-size:12px">Sin archivo</span> <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#666"><?php echo e($p->created_at->format('d/m/Y H:i')); ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <form action="<?php echo e(route('admin.payments.approve', $p)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button class="btn btn-sm" style="background:#e8f5e9;color:#2e7d32;border-radius:8px;font-size:11px;font-weight:600" onclick="return confirm('¿Aprobar este pago?')">
                                    ✓ Aprobar
                                </button>
                            </form>
                            <form action="<?php echo e(route('admin.payments.reject', $p)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button class="btn btn-sm" style="background:#ffebee;color:#c62828;border-radius:8px;font-size:11px;font-weight:600" onclick="return confirm('¿Rechazar este pago?')">
                                    ✗ Rechazar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/erickperezrayo/Documents/canchascr/resources/views/pages/admin/subscriptions/pending-payments.blade.php ENDPATH**/ ?>