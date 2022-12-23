<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Payroll | {{$title}}</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{asset('/')}}assets/vendors/iconfonts/mdi/font/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="{{asset('/')}}assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="{{asset('/')}}assets/vendors/css/vendor.bundle.addons.css">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="{{asset('/')}}assets/css/vertical-layout-light/style.css">
    <link rel="stylesheet" href="{{asset('/')}}assets/css/sweetalert2.min.css">
    <link rel="stylesheet" href="{{asset('/')}}assets/css/custom.css">
    <link href="{{ asset('/') }}assets/vendors/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="{{ asset('/') }}assets/vendors/timepicker/timepicker.min.css" rel="stylesheet">
    <link href="{{ asset('/') }}assets/vendors/timepicker/timepicker.min.css" rel="stylesheet">
    <link href="{{ asset('/') }}assets/vendors/daterangepicker/daterangepicker.css" rel="stylesheet">

    <!-- endinject -->
    <!-- <link rel="shortcut icon" href="{{asset('/')}}assets/images/favicon.png" /> -->
    @stack('css-app')
</head>
<body>

@yield('content-app')

<script src="{{ asset('/') }}assets/js/jquery.min.js"></script>

<script src="{{asset('/')}}assets/vendors/js/vendor.bundle.base.js"></script>
<script src="{{asset('/')}}assets/vendors/js/vendor.bundle.addons.js"></script>
<!-- endinject -->
<script src="{{ asset('/')}}assets/vendors/datatables/jquery.dataTables.min.js"></script>
<script src="{{ asset('/')}}assets/vendors/datatables/dataTables.bootstrap4.min.js"></script>
<!-- inject:js -->
<script src="{{asset('/')}}assets/js/off-canvas.js"></script>
<script src="{{asset('/')}}assets/js/hoverable-collapse.js"></script>
<script src="{{asset('/')}}assets/js/template.js"></script>
<script src="{{asset('/')}}assets/js/settings.js"></script>
<script src="{{asset('/')}}assets/js/todolist.js"></script>
<script src="{{asset('/')}}assets/js/sweetalert2.min.js"></script>
<script src="{{asset('/')}}assets/js/autoNumeric.js"></script>
<script src="{{asset('/')}}assets/vendors/datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="{{asset('/')}}assets/vendors/timepicker/timepicker.min.js"></script>
<script src="{{asset('/')}}assets/vendors/js/moment.js"></script>
<script src="{{asset('/')}}assets/js/custom.js"></script>
<script src="{{asset('/')}}assets/js/file-upload.js"></script>
<script src="{{asset('/')}}assets/js/tooltips.js"></script>
<script src="{{asset('/')}}assets/js/popover.js"></script>
<script type="text/javascript">
    $(".numeric").autoNumeric('init', {
        aPad: false,
        aDec: ',',
        aSep: '.'
    });
    function numericFormat(x) {
        return x.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ".");
    }
    function numberFormat(x) {
        return parseFloat(x.replace(/\./g, ""));
    }
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
<!-- endinject -->
@stack('js-app')

</body>

</html>