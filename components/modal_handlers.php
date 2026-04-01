<?php

?>
<script>
$(document).ready(function() {
    $('#btn-add-<?php echo $type; ?>').on('click', function() {
        $('#modal-add-<?php echo $type; ?>').modal('show');
        $('#form-add-<?php echo $type; ?>')[0].reset();
        $('#add-<?php echo $type; ?>-area').val('<?php echo $area; ?>').trigger('change');
        
        if ('<?php echo $area; ?>' === 'Office') {
            $('#add-<?php echo $type; ?>-code').prop('readonly', false);
        } else {
            $('#add-<?php echo $type; ?>-code').prop('readonly', true);
        }
    });
});
</script>
