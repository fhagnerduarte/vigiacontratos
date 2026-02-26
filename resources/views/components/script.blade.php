    <!-- jQuery library js -->
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <!-- Bootstrap js -->
    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
    <!-- Apex Chart js -->
    <script src="{{ asset('assets/js/lib/apexcharts.min.js') }}"></script>
    <!-- Data Table js -->
    <script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script>
    <!-- Iconify Icon: carregado no head.blade.php -->
    <!-- jQuery UI js -->
    <script src="{{ asset('assets/js/lib/jquery-ui.min.js') }}"></script>
    <!-- Vector Map js -->
    <script src="{{ asset('assets/js/lib/jquery-jvectormap-2.0.5.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/jquery-jvectormap-world-mill-en.js') }}"></script>
    <!-- Popup js -->
    <script src="{{ asset('assets/js/lib/magnifc-popup.min.js') }}"></script>
    <!-- Slick Slider js -->
    <script src="{{ asset('assets/js/lib/slick.min.js') }}"></script>
    <!-- prism js -->
    <script src="{{ asset('assets/js/lib/prism.js') }}"></script>
    <!-- file upload js -->
    <script src="{{ asset('assets/js/lib/file-upload.js') }}"></script>
    <!-- audioplayer js -->
    <script src="{{ asset('assets/js/lib/audioplayer.js') }}"></script>
    <!-- Select2 js -->
    <script src="{{ asset('assets/js/lib/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/select2-pt-BR.js') }}"></script>
    <!-- main js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <!-- input masks -->
    <script src="{{ asset('assets/js/input-masks.js') }}"></script>
    <!-- Select2 init -->
    <script src="{{ asset('assets/js/select2-init.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="{{ asset('assets/js/lib/sweetalert2.all.min.js') }}"></script>
    <!-- Toast init -->
    <script src="{{ asset('assets/js/toast-init.js') }}"></script>
    <!-- Confirm dialog (SweetAlert2) -->
    <script src="{{ asset('assets/js/confirm-dialog.js') }}"></script>
    <!-- Global search -->
    <script src="{{ asset('assets/js/global-search.js') }}"></script>
    <!-- Keyboard shortcuts -->
    <script src="{{ asset('assets/js/keyboard-shortcuts.js') }}"></script>

    {!! isset($script) ? $script : '' !!}
    @stack('scripts')
