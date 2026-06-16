<?php
/**
 * ClickableCard.php
 * Reusable component to wrap cards in an anchor tag for full-card semantic linkability.
 */
$href = $data['href'] ?? '#';
$class = $data['class'] ?? '';
$style = $data['style'] ?? '';
$content = $data['content'] ?? '';
$id = $data['id'] ?? uniqid('card_');
?>
<a id="<?= e($id) ?>" href="<?= e($href) ?>" class="clickable-card-wrapper <?= e($class) ?>" style="display: block; text-decoration: none; color: inherit; <?= e($style) ?>">
    <?= $content ?>
</a>
