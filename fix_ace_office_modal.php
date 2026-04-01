<?php
$tail = "\n\n        // Open Add Modal\n        \$('#btn-add-apar').on('click', function() {\n            var myModal = new bootstrap.Modal(document.getElementById('modal-add-apar'));\n            \$('#form-add-apar')[0].reset();\n            \$('#add-apar-area').trigger('change');\n            myModal.show();\n        });\n    });\n</script>\n\n<?php include(__DIR__ . '/../create.php'); ?>\n";

$files = ['module/apar/ace/index.php', 'module/apar/office/index.php'];
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
        // Try LF
        $needle = "    });\n</script>";
        $pos = strrpos($c, $needle);
        if ($pos !== false) {
            $c = substr($c, 0, $pos) . $tail;
            file_put_contents($f, $c);
            echo "Done (LF): $f\n";
        } else {
            echo "NOT MATCHED: $f\n";
            echo "Last 150 chars hex: " . bin2hex(substr($c, -150)) . "\n";
        }
    }
}
echo "All done.\n";
