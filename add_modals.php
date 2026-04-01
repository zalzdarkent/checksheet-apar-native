<?php

$files = [
    'module/apar/ace/index.php',
    'module/apar/disa/index.php',
    'module/apar/machining/index.php',
    'module/apar/office/index.php',
    'module/hydrant/ace/index.php',
    'module/hydrant/disa/index.php',
    'module/hydrant/machining/index.php',
    'module/hydrant/office/index.php'
];

foreach ($files as $f) {
    if (!file_exists($f)) {
        echo "File $f not found.\n";
        continue;
    }
    $content = file_get_contents($f);
    
    // Check if we already injected
    if (strpos($content, '<!-- Add Modal Trigger Logic -->') !== false) {
        echo "Already injected $f\n";
        continue;
    }

    $type = (strpos($f, 'hydrant') !== false) ? 'hydrant' : 'apar';

    $modalHtml = "
        // <!-- Add Modal Trigger Logic -->
        $('#btn-add-{$type}').on('click', function() {
            $('#modal-add-{$type}').modal('show');
            $('#form-add-{$type}')[0].reset();
            $('#add-{$type}-area').val('<?php echo \$area; ?>').trigger('change');
            if ('<?php echo \$area; ?>' === 'Office') {
                $('#add-{$type}-code').prop('readonly', false);
            }
        });
    });
</script>

<?php include(__DIR__ . '/../create.php'); ?>
";

    // Replaces the generic tail pattern
    $content = str_replace("    });\n</script>", $modalHtml, $content);

    // There's a case in Disa index.php that already got a partial modal injection that failed
    if (strpos($content, "function()") && strpos($content, "$('#modal-add-apar').modal('show');") !== false && strpos($content, '<!-- Add Modal Trigger Logic -->') === false) {
        // Just cleanly replace the whole tail if it's messed up
        $pattern = "/\/\/ Trigger Add Modal\s*\$\('\#btn-add-apar'\)\.on\('click', function\(\) \{\s*\$\('\#modal-add-apar'\)\.modal\('show'\);\s*\n/s";
        $content = preg_replace($pattern, "    });\n</script>", $content);
        $content = str_replace("    });\n</script>", $modalHtml, $content);
    }

    file_put_contents($f, $content);
    echo "Successfully injected $f\n";
}

