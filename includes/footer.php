        </main>
    </div>

    <?php if (isLoggedIn()): ?>
    <!-- Tutorial de Ayuda -->
    <?php include __DIR__ . '/tutorial.php'; ?>
    <?php endif; ?>

    <script>
    // Sistema de tema claro/oscuro global
    (function() {
        var themeToggle = document.getElementById('themeToggle');
        var body = document.body;
        var savedTheme = localStorage.getItem('tubi-theme') || 'light';

        // Aplicar tema guardado (light = sin atributo, dark = data-theme="dark")
        if (savedTheme === 'dark') {
            body.setAttribute('data-theme', 'dark');
        } else {
            body.removeAttribute('data-theme');
        }

        // Toggle claro/oscuro
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                var isDark = body.getAttribute('data-theme') === 'dark';
                if (isDark) {
                    body.removeAttribute('data-theme');
                    localStorage.setItem('tubi-theme', 'light');
                } else {
                    body.setAttribute('data-theme', 'dark');
                    localStorage.setItem('tubi-theme', 'dark');
                }
            });
        }
    })();
    </script>
</body>
</html>
