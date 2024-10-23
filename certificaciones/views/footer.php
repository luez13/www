<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <span class="text-muted">© <?php echo date("Y"); ?> Sistema de gestión de cursos y certificaciones</span>
    </div>
</footer>

<!-- Bootstrap core JavaScript-->
<script src="../public/assets/vendor/jquery/jquery.min.js"></script>
<script src="../public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="../public/assets/vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="../public/assets/js/sb-admin-2.min.js"></script>

<script>
    // Asegurarse de que el contenido principal no sea tapado por el footer
    $(document).ready(function() {
        var footerHeight = $('footer').outerHeight();
        $('body').css('padding-bottom', footerHeight + 'px');
    });
</script>
</body>
</html>