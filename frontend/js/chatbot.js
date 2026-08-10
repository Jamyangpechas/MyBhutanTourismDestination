/**
 * Official Bhutan Travel AI Chatbot Client Script
 * Location: frontend/js/chatbot.js
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Select Chatbot Widget DOM Elements
    const chatWidget = document.getElementById('bhutan-chat-widget') || document.querySelector('.chat-widget');
    const chatToggleBtn = document.getElementById('chat-toggle-btn') || document.querySelector('.chat-toggle-btn');
    const chatForm = document.getElementById('chat-form') || document.querySelector('.chat-form');
    const chatInput = document.getElementById('chat-input') || (chatForm ? chatForm.querySelector('input') : null);
    const chatSubmitBtn = document.getElementById('chat-submit-btn') || (chatForm ? chatForm.querySelector('button') : null);
    const chatMessages = document.getElementById('chat-messages') || document.querySelector('.chat-messages');

    if (!chatWidget || !chatToggleBtn || !chatForm || !chatInput || !chatMessages) {
        return;
    }

    const chatHistory = [];
    let isRequestPending = false;

    /**
     * Resolves absolute endpoint path regardless of current routing depth
     */
    const getApiEndpoint = () => {
        const origin = window.location.origin;
        const pathSegments = window.location.pathname.split('/').filter(Boolean);
        
        if (pathSegments.length > 0 && pathSegments[pathSegments.length - 1].endsWith('.php')) {
            pathSegments.pop();
        }

        const basePath = pathSegments.length > 0 ? `/${pathSegments.join('/')}` : '';
        return `${origin}${basePath}/chatbot.php`;
    };

    const scrollToBottom = () => {
        chatMessages.scrollTo({
            top: chatMessages.scrollHeight,
            behavior: 'smooth'
        });
    };

    /**
     * Parses markdown text (bolding, lists) and returns formatted HTML string
     */
    const parseMarkdown = (text) => {
        if (!text) return '';

        let safeText = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        safeText = safeText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        const lines = safeText.split('\n');
        let htmlOutput = '';
        let inList = false;

        lines.forEach(line => {
            const trimmedLine = line.trim();

            if (trimmedLine.startsWith('* ') || trimmedLine.startsWith('- ')) {
                if (!inList) {
                    htmlOutput += '<ul>';
                    inList = true;
                }
                htmlOutput += `<li>${trimmedLine.substring(2)}</li>`;
            } else {
                if (inList) {
                    htmlOutput += '</ul>';
                    inList = false;
                }
                if (trimmedLine.length > 0) {
                    htmlOutput += `<p>${trimmedLine}</p>`;
                }
            }
        });

        if (inList) {
            htmlOutput += '</ul>';
        }

        return htmlOutput;
    };

    const appendChatMessage = (text, className) => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${className}`;

        if (className.includes('bot-message') && !className.includes('typing')) {
            messageDiv.innerHTML = parseMarkdown(text);
        } else {
            const p = document.createElement('p');
            p.textContent = text;
            messageDiv.appendChild(p);
        }

        chatMessages.appendChild(messageDiv);
        scrollToBottom();
        return messageDiv;
    };

    // 2. Widget Toggle & Escape Key Close
    chatToggleBtn.addEventListener('click', () => {
        const isActive = chatWidget.classList.toggle('active');
        chatToggleBtn.setAttribute('aria-expanded', isActive.toString());
        if (isActive) {
            chatInput.focus();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && chatWidget.classList.contains('active')) {
            chatWidget.classList.remove('active');
            chatToggleBtn.setAttribute('aria-expanded', 'false');
            chatToggleBtn.focus();
        }
    });

    // 3. Form Submission & AJAX Logic
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const userQuery = chatInput.value.trim();
        if (!userQuery || isRequestPending) return;

        appendChatMessage(userQuery, 'user-message');
        chatInput.value = '';

        isRequestPending = true;
        chatInput.disabled = true;
        if (chatSubmitBtn) chatSubmitBtn.disabled = true;

        const typingIndicator = appendChatMessage('Assistant is thinking...', 'bot-message typing');

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 15000);

        try {
            const apiEndpoint = getApiEndpoint();

            const response = await fetch(apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                signal: controller.signal,
                body: JSON.stringify({
                    message: userQuery,
                    history: chatHistory
                })
            });

            clearTimeout(timeoutId);

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const rawText = await response.text();
                console.error('Non-JSON Response Payload Received:', rawText);
                throw new Error(`Server returned HTTP ${response.status} with unexpected content-type.`);
            }

            const data = await response.json();
            typingIndicator.remove();

            if (response.ok && data.status === 'success') {
                appendChatMessage(data.reply, 'bot-message');
                chatHistory.push({ role: 'user', text: userQuery });
                chatHistory.push({ role: 'model', text: data.reply });
            } else {
                appendChatMessage(data.message || 'Error communicating with AI assistant.', 'bot-message error');
            }
        } catch (error) {
            clearTimeout(timeoutId);
            typingIndicator.remove();

            if (error.name === 'AbortError') {
                appendChatMessage('Request timed out. Please check your connection.', 'bot-message error');
            } else {
                appendChatMessage('Network error. Unable to reach server.', 'bot-message error');
            }
            console.error('Chatbot AJAX Error Details:', {
                name: error.name,
                message: error.message,
                stack: error.stack
            });
        } finally {
            isRequestPending = false;
            chatInput.disabled = false;
            if (chatSubmitBtn) chatSubmitBtn.disabled = false;
            chatInput.focus();
        }
    });
});