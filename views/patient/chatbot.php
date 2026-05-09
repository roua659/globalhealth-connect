<!-- Chatbot Component -->
<style>
    /* Variables de couleurs pour coller au thème médical */
    :root {
        --chat-primary: #1e3a8a; /* Bleu foncé médical */
        --chat-secondary: #3b82f6; /* Bleu plus clair */
        --chat-bg: #f3f4f6;
        --chat-text: #1f2937;
        --chat-white: #ffffff;
    }

    #chatbot-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: var(--chat-primary);
        color: var(--chat-white);
        border: none;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        transition: transform 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #chatbot-btn:hover {
        transform: scale(1.1);
        background-color: var(--chat-secondary);
    }

    #chatbot-window {
        position: fixed;
        bottom: 90px;
        right: 20px;
        width: 350px;
        height: 500px;
        background-color: var(--chat-white);
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        display: flex;
        flex-direction: column;
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transform: translateY(20px);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    #chatbot-window.active {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
    }

    .chat-header {
        background-color: var(--chat-primary);
        color: var(--chat-white);
        padding: 15px;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-header button {
        background: none;
        border: none;
        color: var(--chat-white);
        font-size: 20px;
        cursor: pointer;
    }

    .chat-body {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background-color: var(--chat-bg);
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .message {
        max-width: 80%;
        padding: 10px 15px;
        border-radius: 15px;
        font-size: 14px;
        line-height: 1.4;
    }

    .message.bot {
        align-self: flex-start;
        background-color: var(--chat-white);
        color: var(--chat-text);
        border: 1px solid #e5e7eb;
        border-bottom-left-radius: 2px;
    }

    .message.user {
        align-self: flex-end;
        background-color: var(--chat-primary);
        color: var(--chat-white);
        border-bottom-right-radius: 2px;
    }

    .chat-options {
        display: flex;
        flex-direction: column;
        gap: 5px;
        margin-top: 5px;
    }

    .chat-option-btn {
        background-color: var(--chat-white);
        border: 1px solid var(--chat-secondary);
        color: var(--chat-secondary);
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
    }

    .chat-option-btn:hover {
        background-color: var(--chat-secondary);
        color: var(--chat-white);
    }

    .chat-footer {
        padding: 15px;
        background-color: var(--chat-white);
        border-top: 1px solid #e5e7eb;
    }

    #custom-message-form {
        display: none; /* Caché par défaut, affiché si question complexe */
        flex-direction: column;
        gap: 10px;
    }

    #custom-message-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        resize: none;
        font-family: inherit;
        font-size: 14px;
    }

    #send-msg-btn {
        background-color: var(--chat-primary);
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }

    #send-msg-btn:hover {
        background-color: var(--chat-secondary);
    }
</style>

<button id="chatbot-btn" onclick="toggleChat()">
    <i class="fas fa-comment-medical"></i> <!-- Assurez-vous d'avoir FontAwesome -->
</button>

<div id="chatbot-window">
    <div class="chat-header">
        <span><i class="fas fa-robot"></i> Assistant Médical</span>
        <button onclick="toggleChat()">&times;</button>
    </div>
    <div class="chat-body" id="chat-body">
        <div class="message bot">
            Bonjour ! Je suis votre assistant virtuel. Comment puis-je vous aider aujourd'hui ?
        </div>
        <div class="chat-options" id="initial-options">
            <button class="chat-option-btn" onclick="handleOption('rdv')">Comment prendre un rendez-vous ?</button>
            <button class="chat-option-btn" onclick="handleOption('suivis')">Où consulter mes suivis médicaux ?</button>
            <button class="chat-option-btn" onclick="handleOption('complexe')">Autre question / Contacter le médecin</button>
        </div>
    </div>
    <div class="chat-footer">
        <div id="custom-message-form">
            <textarea id="custom-message-input" rows="2" placeholder="Tapez votre message pour le médecin..."></textarea>
            <button id="send-msg-btn" onclick="sendToDoctor()">Envoyer au médecin</button>
        </div>
    </div>
</div>

<script>
    function toggleChat() {
        const chatWindow = document.getElementById('chatbot-window');
        chatWindow.classList.toggle('active');
    }

    function addMessage(text, sender) {
        const chatBody = document.getElementById('chat-body');
        const msgDiv = document.createElement('div');
        msgDiv.className = `message ${sender}`;
        msgDiv.innerText = text;
        chatBody.appendChild(msgDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function handleOption(type) {
        const initialOptions = document.getElementById('initial-options');
        if (initialOptions) initialOptions.style.display = 'none';

        if (type === 'rdv') {
            addMessage("Comment prendre un rendez-vous ?", 'user');
            setTimeout(() => {
                addMessage("Pour prendre un rendez-vous, veuillez contacter le secrétariat par téléphone ou utiliser la plateforme de prise de RDV de la clinique.", 'bot');
                resetOptions();
            }, 1000);
        } else if (type === 'suivis') {
            addMessage("Où consulter mes suivis médicaux ?", 'user');
            setTimeout(() => {
                addMessage("Vos suivis sont accessibles dans le menu 'Mes Suivis' à gauche de votre tableau de bord. Vous pouvez également y mettre à jour votre poids et votre tension.", 'bot');
                resetOptions();
            }, 1000);
        } else if (type === 'complexe') {
            addMessage("J'ai une autre question.", 'user');
            setTimeout(() => {
                addMessage("Veuillez taper votre question ci-dessous. Elle sera envoyée directement à votre médecin traitant.", 'bot');
                document.getElementById('custom-message-form').style.display = 'flex';
            }, 1000);
        }
    }

    function resetOptions() {
        setTimeout(() => {
            const chatBody = document.getElementById('chat-body');
            const optionsDiv = document.createElement('div');
            optionsDiv.className = 'chat-options';
            optionsDiv.innerHTML = `
                <button class="chat-option-btn" onclick="handleOption('rdv')">Comment prendre un rendez-vous ?</button>
                <button class="chat-option-btn" onclick="handleOption('suivis')">Où consulter mes suivis médicaux ?</button>
                <button class="chat-option-btn" onclick="handleOption('complexe')">Autre question / Contacter le médecin</button>
            `;
            chatBody.appendChild(optionsDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }, 1500);
    }

    function sendToDoctor() {
        const input = document.getElementById('custom-message-input');
        const message = input.value.trim();

        if (message === '') return;

        addMessage(message, 'user');
        input.value = '';
        document.getElementById('custom-message-form').style.display = 'none';

        // Envoi AJAX
        fetch('index.php?controller=patient&action=sendMessage', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `message=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                setTimeout(() => {
                    addMessage("✅ " + data.message, 'bot');
                    resetOptions();
                }, 1000);
            } else {
                setTimeout(() => {
                    addMessage("❌ " + data.message, 'bot');
                    document.getElementById('custom-message-form').style.display = 'flex';
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            setTimeout(() => {
                addMessage("❌ Une erreur est survenue lors de l'envoi de votre message.", 'bot');
                document.getElementById('custom-message-form').style.display = 'flex';
            }, 1000);
        });
    }
</script>
