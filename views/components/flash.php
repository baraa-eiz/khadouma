<?php
/**
 * flash.php
 * Renders flash messages stored in the session.
 */
use App\Core\Flash;

$alertTypes = [
    'success' => 'alert-success',
    'error' => 'alert-error',
    'warning' => 'alert-warning',
    'info' => 'alert-info'
];

foreach ($alertTypes as $key => $className):
    if (Flash::has($key)):
        $payload = Flash::get($key);
        $messages = is_array($payload) ? $payload : [$payload];
        foreach ($messages as $msg):
?>
            <div class="alert <?= $className ?>" role="alert">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <?php if ($key === 'success'): ?>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?php elseif ($key === 'error'): ?>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?php elseif ($key === 'warning'): ?>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <?php else: ?>
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?php endif; ?>
                    <span><?= e($msg) ?></span>
                </div>
                <button type="button" class="alert-close" aria-label="إغلاق">&times;</button>
            </div>
<?php
        endforeach;
    endif;
endforeach;
?>
