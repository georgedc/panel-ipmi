<?php
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="section-card">
            <div class="section-body p-4">
                <h1 class="h4 mb-3"><?php echo htmlspecialchars($heading ?? __('mvc.error_default_heading')); ?></h1>
                <p class="text-muted mb-4"><?php echo htmlspecialchars($message ?? __('mvc.error_default_message')); ?></p>
                <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars($back_url ?? routeUrl('/dashboard')); ?>"><?php echo htmlspecialchars(__('mvc.error_back')); ?></a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/base.php';
