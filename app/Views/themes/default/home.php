<?php
ob_start();
?>
<div class="hero-banner mb-4">
    <h1 class="h3 mb-2 text-white"><?php echo htmlspecialchars(__('mvc.home_title')); ?></h1>
    <p class="mb-0"><?php echo htmlspecialchars(__('mvc.home_subtitle')); ?></p>
</div>
<div class="section-card">
    <div class="section-body p-4">
        <ul class="mb-0">
            <li><?php echo htmlspecialchars(__('mvc.home_point_one')); ?></li>
            <li><?php echo htmlspecialchars(__('mvc.home_point_two')); ?></li>
            <li><?php echo htmlspecialchars(__('mvc.home_point_three')); ?></li>
        </ul>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/base.php';
