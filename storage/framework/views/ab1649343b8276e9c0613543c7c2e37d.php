<?php $__env->startSection('title', 'Suscripciones'); ?>
<?php $__env->startSection('page_title', 'Suscripciones'); ?>

<?php $__env->startSection('content'); ?>
<div class="stat-card">
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr><th>Owner</th><th>Plan</th><th>Estado</th><th>Método</th><th>Precio</th><th>Vence</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="fw-600" style="font-size:13px"><?php echo e($s->user->name); ?></div>
                        <div class="text-muted" style="font-size:11px"><?php echo e($s->user->email); ?></div>
                    </td>
                    <td style="font-size:13px"><?php echo e($s->plan?->name ?? '—'); ?></td>
                    <td><?php echo $s->status_badge; ?></td>
                    <td>
                        <span style="font-size:12px"><?php echo e($s->payment_method === 'card' ? '💳 Tarjeta' : '📱 SINPE'); ?></span>
                    </td>
                    <td class="fw-700" style="font-size:13px">₡<?php echo e(number_format($s->price,0,',','.')); ?></td>
                    <td style="font-size:12px;color:#666"><?php echo e($s->ends_at?->format('d/m/Y') ?? '—'); ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?php echo e(route('admin.subscriptions.show', $s)); ?>" class="btn btn-sm" style="background:#f0f0f0;border-radius:8px;font-size:11px">Ver</a>
                            <select onchange="updateStatus('<?php echo e($s->id); ?>', this.value)" class="form-select form-select-sm" style="border-radius:8px;font-size:11px;max-width:110px">
                                <?php $__currentLoopData = ['active','pending','past_due','failed','canceled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($st); ?>" <?php echo e($s->status===$st?'selected':''); ?>><?php echo e(ucfirst($st)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
async function updateStatus(id, status) {
    try {
        await axios.put(`/admin/suscripciones/${id}`, { status });
        Toast.fire({ icon:'success', title:'Estado actualizado.' });
    } catch(e) { Toast.fire({ icon:'error', title:'Error.' }); }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/erickperezrayo/Documents/canchascr/resources/views/pages/admin/subscriptions/index.blade.php ENDPATH**/ ?>