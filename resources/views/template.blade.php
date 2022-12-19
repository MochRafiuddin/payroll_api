@extends('app',['title'=>$title])
@section('content-app')
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    @include('partial.navbar')
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        @include('partial.sidebar')
        <!-- partial -->
        @yield('content')
        <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
</div>
@endsection
@push('js-app')
    @stack('js')
    <script>
        $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    </script>
@endpush