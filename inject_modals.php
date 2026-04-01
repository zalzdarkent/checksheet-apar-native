<?php
/**
 * Final injection script:
 * - Ensures $area is set at top of each file
 * - Ensures btn-add ID exists on the button
 * - Injects modal trigger JS + create.php include at bottom
 */

$files = [
    // [filepath, area, type]
    ['module/apar/ace/index.php',        'Ace',      'apar'],
    ['module/apar/disa/index.php',       'Disa',     'apar'],
    ['module/apar/machining/index.php',  'Machining','apar'],
    ['module/apar/office/index.php',     'Office',   'apar'],
    ['module/hydrant/ace/index.php',     'Ace',      'hydrant'],
    ['module/hydrant/disa/index.php',    'Disa',     'hydrant'],
    ['module/hydrant/machining/index.php','Machining','hydrant'],
    ['module/hydrant/office/index.php',  'Office',   'hydrant'],
];

foreach ($files as [$f, $area, $type]) {
    if (!file_exists($f)) { echo "NOT FOUND: $f\n"; continue; }
    $content = file_get_contents($f);
    $changed = false;

    // 1. Check/fix $area = '...' at top of file
    if (!preg_match('/\$area\s*=/', $content)) {
        $content = preg_replace('/^<\?php\s*/', "<?php\n\$area = '$area';\n", $content, 1);
        $changed = true;
        echo "  [$f] Added \$area\n";
    }

    // 2. Ensure btn has id="btn-add-{$type}"
    $btnId = "btn-add-{$type}";
    if (strpos($content, $btnId) === false) {
        $content = str_replace(
            '<button class="btn btn-primary btn-round">',
            "<button class=\"btn btn-primary btn-round\" id=\"$btnId\">",
            $content
        );
        $changed = true;
        echo "  [$f] Added button ID\n";
    }

    // 3. Inject modal trigger + include at end of </script> if not yet done
    $modalId = "modal-add-{$type}";
    $formId  = "form-add-{$type}";
    $areaSelector = "add-{$type}-area";
    $codeSelector = "add-{$type}-code";
    $createPath = ($type === 'apar')
        ? "__DIR__ . '/../create.php'"
        : "__DIR__ . '/../create.php'";

    if (strpos($content, $modalId) === false) {
        $inject = "
        // Open Add {$type} Modal
        \$('#$btnId').on('click', function() {
            var modalEl = document.getElementById('$modalId');
            var myModal = new bootstrap.Modal(modalEl);
            \$('#$formId')[0].reset();
            \$('#$areaSelector').val('$area').trigger('change');
            if ('$area' === 'Office') {
                \$('#$codeSelector').prop('readonly', false);
            }
            myModal.show();
        });
    });
</script>

<?php include({$createPath}); ?>
";
        // Replace the closing </script> tag (the last one)
        $lastPos = strrpos($content, "    });\n</script>");
        if ($lastPos !== false) {
            $content = substr($content, 0, $lastPos) . $inject . substr($content, $lastPos + strlen("    });\n</script>") + 1);
            // Actually, let's replace cleanly
        }

        // Try a clean replacement of the last });\n</script>
        $content = preg_replace('/    \}\);\n<\/script>(?![\s\S]*<\/script>)/m', $inject, $content);
        $changed = true;
        echo "  [$f] Injected modal trigger\n";
    } else {
        // Replace old jQuery modal show with BS5
        $old = "\$('#$modalId').modal('show')";
        $new = "new bootstrap.Modal(document.getElementById('$modalId')).show()";
        if (strpos($content, $old) !== false) {
            $content = str_replace($old, $new, $content);
            $changed = true;
            echo "  [$f] Fixed modal show call\n";
        }
    }

    if ($changed) {
        file_put_contents($f, $content);
        echo "SAVED: $f\n";
    } else {
        echo "OK (no changes): $f\n";
    }
}
echo "\nAll done.\n";
