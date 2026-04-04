            </div><!-- /.admin-content -->
        </main>
    </div><!-- /.admin-wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <?php if (!empty($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?= e($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
