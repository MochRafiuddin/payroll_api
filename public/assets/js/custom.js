function cekFileUpload() {
    if ($('input[name=file_excel]').val() == "" || $('input[name=file_excel]').val() == null) {
        $("#priview").attr('disabled', 'disabled');
    } else {
        $("#priview").removeAttr('disabled');
    }
}