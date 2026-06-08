<?php
/**
 * loading_state.php
 * Renders animated skeleton lines representing content loading state.
 */
?>
<div class="card" style="width: 100%;">
    <div class="card-header" style="border-bottom: none;">
        <div class="skeleton skeleton-title" style="width: 180px; margin: 0;"></div>
    </div>
    <div class="card-body" style="display: flex; flex-direction: column; gap: 12px; padding-top: 0;">
        <div class="skeleton skeleton-row" style="width: 100%; height: 16px;"></div>
        <div class="skeleton skeleton-row" style="width: 95%; height: 16px;"></div>
        <div class="skeleton skeleton-row" style="width: 85%; height: 16px;"></div>
        <div class="skeleton skeleton-row" style="width: 60%; height: 16px;"></div>
    </div>
</div>
