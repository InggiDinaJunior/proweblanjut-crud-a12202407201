<?php ?>

    </div>

    <footer style="
        margin-left: var(--sidebar-w);
        background: var(--maroon-dark);
        color: rgba(255,255,255,0.45);
        font-size: 0.75rem;
        padding: 0.65rem 1.5rem;
        border-top: 2px solid var(--red);
    ">
        &copy; <?= date('Y') ?> Inggi Dina &mdash; Sistem Manajemen Inventaris
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <script>
        
        function confirmDelete(formId, nama) {
            if (confirm('Hapus "' + nama + '"?\nTindakan ini tidak dapat dibatalkan.')) {
                document.getElementById(formId).submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alert-dismiss').forEach(function (el) {
                setTimeout(function () {
                    el.style.display = 'none';
                }, 4000);
            });
        });
    </script>

    <?php if (isset($_SESSION['flash'])):
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
    ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:999;">
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismiss shadow-sm mb-0"
             style="font-size:0.85rem; min-width:280px;">
            <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'x-circle' ?> me-1"></i>
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>