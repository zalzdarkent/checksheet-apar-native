<?php
/**
 * Fix script:
 * 1. Replace jQuery modal('show') triggers with Bootstrap 5 syntax
 * 2. Replace jQuery modal('hide') calls with Bootstrap 5 syntax
 * in all apar and hydrant index.php files
 */

$files = [
    'module/apar/ace/index.php',
    'module/apar/disa/index.php',
    'module/apar/machining/index.php',
    'module/apar/office/index.php',
    'module/hydrant/ace/index.php',
    'module/hydrant/disa/index.php',
    'module/hydrant/machining/index.php',
    'module/hydrant/office/index.php',
];

foreach ($files as $f) {
    if (!file_exists($f)) {
        echo "NOT FOUND: $f\n";
        continue;
    }

    $content = file_get_contents($f);
    $original = $content;

    // Fix: $('#modal-add-apar').modal('show') → new bootstrap.Modal(document.getElementById('modal-add-apar')).show()
    $content = preg_replace(
        "/\\\$\('#modal-add-apar'\)\.modal\('show'\)/",
        "new bootstrap.Modal(document.getElementById('modal-add-apar')).show()",
        $content
    );

    // Fix: $('#modal-add-hydrant').modal('show') → new bootstrap.Modal(document.getElementById('modal-add-hydrant')).show()
    $content = preg_replace(
        "/\\\$\('#modal-add-hydrant'\)\.modal\('show'\)/",
        "new bootstrap.Modal(document.getElementById('modal-add-hydrant')).show()",
        $content
    );

    if ($content !== $original) {
        file_put_contents($f, $content);
        echo "Updated: $f\n";
    } else {
        echo "No change: $f\n";
    }
}
echo "Done.\n";
