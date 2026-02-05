        </main>
    </div>

    <?php if (isLoggedIn()): ?>
    <!-- Tutorial de Ayuda -->
    <?php include __DIR__ . '/tutorial.php'; ?>

    <!-- Chat TuBi Flotante -->
    <div class="chat-box" id="chatBox">
        <button class="chat-toggle" id="chatToggle" title="Chat TuBi">
            💬
        </button>
        <div class="chat-window" id="chatWindow" style="display: none;">
            <div class="chat-header">
                <span>🚲 TuBi Chat</span>
                <button class="chat-close" id="chatClose">×</button>
            </div>
            <div class="chat-messages" id="chatMessages">
                <!-- Mensajes se cargan dinámicamente -->
            </div>
            <div class="chat-input-container">
                <input type="text" class="chat-input" id="chatInput" placeholder="Escribí tu mensaje..." autocomplete="off">
                <button class="chat-send" id="chatSend">➤</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="<?= BASE_URL ?>assets/js/chat.js"></script>
    <script>
    // Sistema de tema claro/oscuro global
    (function() {
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const savedTheme = localStorage.getItem('tubi-theme') || 'dark';

        // Aplicar tema guardado
        body.setAttribute('data-theme', savedTheme);

        // Toggle de tema
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                const currentTheme = body.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                body.setAttribute('data-theme', newTheme);
                localStorage.setItem('tubi-theme', newTheme);
            });
        }
    })();
    </script>
</body>
</html>
