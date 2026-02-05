/**
 * TUBI 2026 - Chat TuBi con Google Gemini
 */

document.addEventListener('DOMContentLoaded', function() {
    const chatBox = document.getElementById('chatBox');
    const chatToggle = document.getElementById('chatToggle');
    const chatWindow = document.getElementById('chatWindow');
    const chatClose = document.getElementById('chatClose');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const chatSend = document.getElementById('chatSend');

    if (!chatBox) return;

    let conversationHistory = [];
    let isOpen = false;
    let welcomeShown = false;

    // Toggle chat window
    chatToggle.addEventListener('click', function() {
        isOpen = !isOpen;
        chatWindow.style.display = isOpen ? 'flex' : 'none';
        chatToggle.style.display = isOpen ? 'none' : 'flex';

        if (isOpen && !welcomeShown) {
            loadWelcomeMessage();
            welcomeShown = true;
        }

        if (isOpen) {
            chatInput.focus();
        }
    });

    // Close chat
    chatClose.addEventListener('click', function() {
        isOpen = false;
        chatWindow.style.display = 'none';
        chatToggle.style.display = 'flex';
    });

    // Send message on button click
    chatSend.addEventListener('click', sendMessage);

    // Send message on Enter
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Load welcome message
    function loadWelcomeMessage() {
        fetch(getBaseUrl() + 'api/chat.php?action=welcome')
            .then(response => response.json())
            .then(data => {
                if (data.content) {
                    addMessage(data.content, 'assistant');
                }
            })
            .catch(error => {
                addMessage('¡Hola! Soy TuBi, tu asistente. ¿En qué puedo ayudarte?', 'assistant');
            });
    }

    // Send message to API with retry logic
    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;

        // Add user message to chat
        addMessage(message, 'user');
        chatInput.value = '';
        chatInput.disabled = true;
        chatSend.disabled = true;

        // Show typing indicator
        const typingId = showTyping();

        // Retry configuration
        const maxRetries = 2;
        let retryCount = 0;

        function attemptSend() {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 25000); // 25 second timeout

            fetch(getBaseUrl() + 'api/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    history: conversationHistory.slice(-10)
                }),
                signal: controller.signal
            })
            .then(response => {
                clearTimeout(timeoutId);
                if (!response.ok) {
                    throw new Error('Server error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                removeTyping(typingId);
                chatInput.disabled = false;
                chatSend.disabled = false;
                chatInput.focus();

                if (data.content) {
                    addMessage(data.content, 'assistant');
                    conversationHistory.push({ role: 'user', content: message });
                    conversationHistory.push({ role: 'assistant', content: data.content });
                } else if (data.error) {
                    addMessage('Disculpá, no pude procesar tu mensaje. ¿Podés reformularlo?', 'assistant');
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);

                if (error.name === 'AbortError' || retryCount < maxRetries) {
                    retryCount++;
                    console.log('Reintentando... (' + retryCount + '/' + maxRetries + ')');
                    setTimeout(attemptSend, 1000);
                    return;
                }

                removeTyping(typingId);
                chatInput.disabled = false;
                chatSend.disabled = false;
                chatInput.focus();

                // Mensaje de error amigable
                addMessage('Parece que hay problemas con la conexión. Por ahora, te cuento que puedo ayudarte con:\n\n• Cuidado de tu bicicleta\n• Seguridad vial y uso del casco\n• Retos y puntos del programa\n• Información general de TuBi\n\n¡Intentá de nuevo en unos segundos!', 'assistant');
            });
        }

        attemptSend();
    }

    // Add message to chat
    function addMessage(content, role) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${role}`;

        // Parse markdown-like formatting
        content = formatMessage(content);

        messageDiv.innerHTML = content;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Format message with basic markdown
    function formatMessage(text) {
        // Convert line breaks
        text = text.replace(/\n/g, '<br>');

        // Bold text **text**
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Bullet points
        text = text.replace(/• /g, '&bull; ');
        text = text.replace(/- /g, '&bull; ');

        return text;
    }

    // Show typing indicator
    function showTyping() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chat-message assistant typing';
        typingDiv.id = 'typing-' + Date.now();
        typingDiv.innerHTML = '<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>';
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return typingDiv.id;
    }

    // Remove typing indicator
    function removeTyping(typingId) {
        const typingDiv = document.getElementById(typingId);
        if (typingDiv) {
            typingDiv.remove();
        }
    }

    // Get base URL - detecta automáticamente
    function getBaseUrl() {
        // Buscar si hay un elemento con data-base-url
        const baseEl = document.querySelector('[data-base-url]');
        if (baseEl) return baseEl.dataset.baseUrl;

        // Detectar desde la URL actual
        const path = window.location.pathname;

        // Si estamos en la raíz
        if (path === '/' || path === '/index.php' || path === '/selector.php' || path === '/login.php') {
            return '/';
        }

        // Si estamos en un subdirectorio como /tubi-php/
        const match = path.match(/^(\/[^\/]+\/)/);
        if (match && match[1] !== '/pages/') {
            return match[1];
        }

        // Default para localhost
        return '/';
    }
});
