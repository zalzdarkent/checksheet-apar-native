<?php
$tail = "\n\n        // Open Add Hydrant Modal\n        \$('#btn-add-hydrant').on('click', function() {\n            var myModal = new bootstrap.Modal(document.getElementById('modal-add-hydrant'));\n            \$('#form-add-hydrant')[0].reset();\n            \$('#add-hydrant-area').trigger('change');\n            myModal.show();\n        });\n    });\n</script>\n\n<?php include(__DIR__ . '/../create.php'); ?>\n";

$files = [
    'module/hydrant/ace/index.php',
    'module/hydrant/disa/index.php',
    'module/hydrant/machining/index.php',
    'module/hydrant/office/index.php',
];

foreach ($files as $f) {
    $c = file_get_contents($f);
    // Try CRLF first
    $needle = "    });\r\n</script>";
    $pos = strrpos($c, $needle);
    if ($pos !== false) {
        $c = substr($c, 0, $pos) . $tail;
        file_put_contents($f, $c);
        echo "Done (CRLF): $f\n";
    } else {
        $needle = "    });\n</script>";
        $pos = strrpos($c, $needle);
        if ($pos !== false) {
            $c = substr($c, 0, $pos) . $tail;
            file_put_contents($f, $c);
            echo "Done (LF): $f\n";
        } else {
            echo "NOT MATCHED: $f — last 80 chars hex: " . bin2hex(substr($c, -80)) . "\n";
        }
    }
}
echo "All done.\n";
