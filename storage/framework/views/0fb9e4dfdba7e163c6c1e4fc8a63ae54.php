<?php $__env->startSection('title', 'Usuarios'); ?>
<?php $__env->startSection('page_title', 'Usuarios'); ?>

<?php $__env->startSection('content'); ?>
<div class="stat-card">
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr><th>Usuario</th><th>Rol</th><th>Suscripción</th><th>Registro</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <?php if($u->avatar): ?>
                                <img src="<?php echo e($u->avatar); ?>" class="rounded-circle" width="36" height="36">
                            <?php else: ?>
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;background:<?php echo e($u->role==='admin' ? '#6C63FF' : ($u->role==='owner' ? '#000' : '#e0e0e0')); ?>;color:<?php echo e(in_array($u->role,['admin','owner']) ? '#fff' : '#555'); ?>;font-weight:700;font-size:13px">
                                    <?php echo e(strtoupper(substr($u->name,0,1))); ?>

                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="fw-600" style="font-size:13px"><?php echo e($u->name); ?></div>
                                <div class="text-muted" style="font-size:11px"><?php echo e($u->email); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="<?php echo e($u->role==='admin' ? 'background:#ede7f6;color:#4527a0' : ($u->role==='owner' ? 'background:#e8eaf6;color:#283593' : 'background:#f5f5f5;color:#555')); ?>;border-radius:20px;font-size:11px;padding:4px 10px">
                            <?php echo e(ucfirst($u->role)); ?>

                        </span>
                    </td>
                    <td>
                        <?php if($u->subscription): ?>
                            <?php echo $u->subscription->status_badge; ?>

                            <div class="text-muted" style="font-size:11px"><?php echo e($u->subscription->plan?->name); ?></div>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:12px">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#888"><?php echo e($u->created_at->format('d/m/Y')); ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <select onchange="changeRole('<?php echo e($u->id); ?>', this.value)" class="form-select form-select-sm" style="border-radius:8px;font-size:11px;max-width:100px">
                                <?php $__currentLoopData = ['user','owner','admin']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($role); ?>" <?php echo e($u->role===$role?'selected':''); ?>><?php echo e(ucfirst($role)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php if($u->id !== auth()->id()): ?>
                            <button onclick="deleteUser('<?php echo e($u->id); ?>')" class="btn btn-sm" style="background:#ffebee;color:#c62828;border-radius:8px;font-size:11px">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <?php echo e($users->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
async function changeRole(id, role) {
    try {
        await axios.put(`/admin/usuarios/${id}`, { role, name: '' });
        Toast.fire({ icon:'success', title:'Rol actualizado.' });
    } catch(e) { Toast.fire({ icon:'error', title:'Error.' }); }
}

async function deleteUser(id) {
    const { isConfirmed } = await Swal.fire({ title:'¿Eliminar usuario?', icon:'warning', showCancelButton:true, confirmButtonColor:'#c62828', confirmButtonText:'Eliminar' });
    if (!isConfirmed) return;
    try {
        await axios.delete(`/admin/usuarios/${id}`);
        Toast.fire({ icon:'success', title:'Usuario eliminado.' });
        setTimeout(() => location.reload(), 1000);
    } catch(e) { Toast.fire({ icon:'error', title: e.response?.data?.message || 'Error.' }); }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/erickperezrayo/Documents/canchascr/resources/views/pages/admin/users/index.blade.php ENDPATH**/ ?>