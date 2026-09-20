<?php ($title = 'Audit Viewer'); ?>
<?php ($header = 'Audit Viewer'); ?>
<?php ($headerSubtitle = 'Review perubahan operasional tanpa buka database langsung, lengkap dengan actor, modul, dan ringkasan before/after.'); ?>

<?php $__env->startSection('content'); ?>
    <div class="page-shell">
        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Filter Audit Log</h3>
                    <div class="ui-surface-subtitle">Saring audit berdasarkan modul, actor, action, dan periode kejadian.</div>
                </div>
            </div>

            <form method="GET" class="filter-grid">
                <div class="span-3">
                    <label class="field-label">Module</label>
                    <select name="module" class="form-control form-control-sm">
                        <option value="">Semua Module</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $moduleOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($moduleOption); ?>" <?php if($module === $moduleOption): echo 'selected'; endif; ?>><?php echo e($moduleOption); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="span-3">
                    <label class="field-label">Actor</label>
                    <select name="actor_id" class="form-control form-control-sm">
                        <option value="">Semua Actor</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $actors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($actor->id); ?>" <?php if($actorId === (int) $actor->id): echo 'selected'; endif; ?>><?php echo e($actor->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="span-2">
                    <label class="field-label">Action</label>
                    <select name="action" class="form-control form-control-sm">
                        <option value="">Semua Action</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actionOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($actionOption); ?>" <?php if($action === $actionOption): echo 'selected'; endif; ?>><?php echo e($actionOption); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="span-2">
                    <label class="field-label">Record ID</label>
                    <input type="number" name="record_id" value="<?php echo e($recordId ?: ''); ?>" class="form-control form-control-sm" placeholder="Contoh: 44">
                </div>
                <div class="span-2">
                    <label class="field-label">Date From</label>
                    <input type="date" name="date_from" value="<?php echo e($dateFrom); ?>" class="form-control form-control-sm">
                </div>
                <div class="span-2">
                    <label class="field-label">Date To</label>
                    <input type="date" name="date_to" value="<?php echo e($dateTo); ?>" class="form-control form-control-sm">
                </div>
                <div class="span-1"><button class="btn btn-primary btn-sm w-100">Apply</button></div>
                <div class="span-1"><a href="<?php echo e(route('audit.index')); ?>" class="btn btn-light btn-sm w-100">Reset</a></div>
            </form>
        </section>

        <section class="ui-surface">
            <div class="ui-surface-head">
                <div>
                    <h3 class="ui-surface-title">Audit Log List</h3>
                    <div class="ui-surface-subtitle">Ringkasan perubahan per event untuk memudahkan review cepat.</div>
                </div>
            </div>

            <div class="table-wrap table-responsive">
                <table class="table table-hover ui-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Actor</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Record</th>
                            <th>Changed Fields</th>
                            <th>Before</th>
                            <th>After</th>
                            <th>IP</th>
                            <th class="text-end">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td>
                                    <div class="doc-number"><?php echo e(\Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i')); ?></div>
                                </td>
                                <td>
                                    <div class="doc-number"><?php echo e($log->actor_label); ?></div>
                                    <div class="doc-meta"><?php echo e($log->role_labels->isNotEmpty() ? $log->role_labels->implode(', ') : 'No role'); ?></div>
                                </td>
                                <td><?php echo e($log->module); ?></td>
                                <td><span class="qty"><?php echo e($log->action); ?></span></td>
                                <td>#<?php echo e($log->record_id ?: '-'); ?></td>
                                <td>
                                    <div class="doc-number"><?php echo e($log->changed_field_count); ?></div>
                                    <div class="doc-meta"><?php echo e($log->changed_field_count > 0 ? $log->changed_fields->take(3)->implode(', ') : 'No diff'); ?></div>
                                </td>
                                <td><?php echo e($log->old_summary); ?></td>
                                <td><?php echo e($log->new_summary); ?></td>
                                <td><?php echo e($log->ip_address ?: '-'); ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-light btn-sm" data-toggle="modal" data-target="#auditDetailModal<?php echo e($log->id); ?>">
                                        View Detail
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="auditDetailModal<?php echo e($log->id); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <div>
                                                <h5 class="modal-title mb-1">Audit Detail <?php echo e($log->module); ?> #<?php echo e($log->record_id ?: '-'); ?></h5>
                                                <div class="doc-meta"><?php echo e($log->action); ?> | <?php echo e(\Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i')); ?> | <?php echo e($log->actor_label); ?></div>
                                            </div>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="info-grid mb-3">
                                                <div class="info-box">
                                                    <div class="info-label">Changed Fields</div>
                                                    <div class="info-value"><?php echo e($log->changed_field_count); ?></div>
                                                    <div class="doc-meta"><?php echo e($log->changed_fields->isNotEmpty() ? $log->changed_fields->implode(', ') : 'Tidak ada perbedaan field.'); ?></div>
                                                </div>
                                                <div class="info-box">
                                                    <div class="info-label">Actor</div>
                                                    <div class="info-value"><?php echo e($log->actor_label); ?></div>
                                                    <div class="doc-meta"><?php echo e($log->role_labels->isNotEmpty() ? $log->role_labels->implode(', ') : 'No role'); ?></div>
                                                </div>
                                                <div class="info-box">
                                                    <div class="info-label">IP Address</div>
                                                    <div class="info-value"><?php echo e($log->ip_address ?: '-'); ?></div>
                                                    <div class="doc-meta">Action <?php echo e($log->action); ?></div>
                                                </div>
                                            </div>

                                            <div class="filter-grid px-0 pb-0">
                                                <div class="span-6">
                                                    <label class="field-label">Before Payload</label>
                                                    <textarea class="form-control form-control-sm" rows="14" readonly><?php echo e($log->old_pretty_json); ?></textarea>
                                                </div>
                                                <div class="span-6">
                                                    <label class="field-label">After Payload</label>
                                                    <textarea class="form-control form-control-sm" rows="14" readonly><?php echo e($log->new_pretty_json); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada audit log pada filter ini.</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-3 pb-3">
                <?php echo e($auditLogs->links()); ?>

            </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.erp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\audit\index.blade.php ENDPATH**/ ?>