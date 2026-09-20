<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between items-center">
            <div class="flex items-center gap-6">
                <a href="<?php echo e(route('dashboard')); ?>" class="text-sm font-semibold text-gray-900">
                    ERP Monitoring
                </a>
                <a href="<?php echo e(route('profile.edit')); ?>" class="text-sm text-gray-600 hover:text-gray-900">
                    Profile
                </a>
            </div>
            <div class="text-sm text-gray-500">
                <?php echo e(auth()->user()->email ?? ''); ?>

            </div>
        </div>
    </div>
</nav>
<?php /**PATH C:\laragon\www\erp\erp-monitoring-po\resources\views\layouts\navigation.blade.php ENDPATH**/ ?>