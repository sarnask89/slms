/**
 * AI Assistant Embed Script
 * Add this script to any webpage to enable AI assistance
 * 
 * Usage:
 * <script src="ai_assistant_embed.js"></script>
 * <script>
 *   AIAssistantEmbed.init({
 *     apiUrl: 'http://localhost/ai_assistant_api.php',
 *     model: 'local', // 'local' or 'api'
 *     theme: 'default' // 'default', 'dark', 'light'
 *   });
 * </script>
 */

(function() {
    'use strict';

    class AIAssistantEmbed {
        constructor(config = {}) {
            this.config = {
                apiUrl: config.apiUrl || '/ai_assistant_api.php',
                model: config.model || 'local',
                theme: config.theme || 'default',
                position: config.position || 'bottom-right',
                autoInit: config.autoInit !== false,
                ...config
            };
            
            this.isInitialized = false;
            this.container = null;
            this.chat = null;
            this.conversationHistory = [];
            this.localModel = null;
            
            if (this.config.autoInit) {
                this.init();
            }
        }

        async init() {
            if (this.isInitialized) return;
            
            try {
                await this.loadLocalModel();
                this.createAssistant();
                this.bindEvents();
                this.isInitialized = true;
                console.log('🤖 AI Assistant initialized successfully');
            } catch (error) {
                console.error('Failed to initialize AI Assistant:', error);
            }
        }

        async loadLocalModel() {
            if (this.config.model === 'local') {
                try {
                    // Try to load local AI model
                    if (typeof window.localAI !== 'undefined') {
                        this.localModel = window.localAI;
                    } else {
                        // Load local model from CDN or local file
                        await this.loadModelFromSource();
                    }
                } catch (error) {
                    console.log('Local model not available, using API fallback');
                    this.config.model = 'api';
                }
            }
        }

        async loadModelFromSource() {
            // This would load a local ML model
            // For now, we'll use a simple rule-based system
            this.localModel = {
                generateResponse: async (message, context) => {
                    return this.generateLocalResponse(message, context);
                }
            };
        }

        generateLocalResponse(message, context) {
            const lowerMessage = message.toLowerCase();
            
            // Simple rule-based responses
            if (lowerMessage.includes('hello') || lowerMessage.includes('hi')) {
                return 'Hello! I\'m your AI assistant. How can I help you today?';
            }
            
            if (lowerMessage.includes('help')) {
                return 'I can help you understand this page, answer questions, and provide assistance. What would you like to know?';
            }
            
            if (lowerMessage.includes('what can you do')) {
                return 'I can:\n• Explain page content\n• Answer questions\n• Provide summaries\n• Help with navigation\n• Assist with tasks\n\nWhat would you like help with?';
            }
            
            // Default response
            return 'I understand your question. Let me help you with that. Could you provide more specific details about what you need assistance with?';
        }

        createAssistant() {
            // Create container
            this.container = document.createElement('div');
            this.container.id = 'ai-assistant-embed';
            this.container.className = `ai-assistant-container ai-theme-${this.config.theme}`;
            this.container.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 10000;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            `;

            // Create toggle button
            const toggle = document.createElement('button');
            toggle.className = 'ai-assistant-toggle';
            toggle.innerHTML = `
                <svg class="ai-assistant-icon" viewBox="0 0 24 24" style="width: 32px; height: 32px; fill: white;">
                    <path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4M12,6A6,6 0 0,0 6,12A6,6 0 0,0 12,18A6,6 0 0,0 18,12A6,6 0 0,0 12,6M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8Z"/>
                </svg>
                <div class="ai-assistant-pulse" style="display: none;"></div>
            `;

            // Create chat interface
            this.chat = document.createElement('div');
            this.chat.className = 'ai-assistant-chat';
            this.chat.innerHTML = `
                <div class="ai-assistant-header">
                    <h3>🤖 AI Assistant</h3>
                    <button class="ai-assistant-close">×</button>
                </div>
                <div class="ai-assistant-messages"></div>
                <div class="ai-typing-indicator" style="display: none;">
                    <div class="ai-loading"></div> AI is thinking...
                </div>
                <div class="ai-suggestions">
                    <div class="ai-suggestion-chips">
                        <div class="ai-suggestion-chip">Help me with this page</div>
                        <div class="ai-suggestion-chip">Explain this content</div>
                        <div class="ai-suggestion-chip">What can you do?</div>
                    </div>
                </div>
                <div class="ai-assistant-input">
                    <div class="ai-input-container">
                        <input type="text" class="ai-input-field" placeholder="Ask me anything..." maxlength="500">
                        <button class="ai-send-button">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="white">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `;

            this.container.appendChild(toggle);
            this.container.appendChild(this.chat);
            document.body.appendChild(this.container);

            // Add styles
            this.addStyles();
        }

        addStyles() {
            if (document.getElementById('ai-assistant-styles')) return;

            const styles = document.createElement('style');
            styles.id = 'ai-assistant-styles';
            styles.textContent = `
                .ai-assistant-toggle {
                    width: 60px;
                    height: 60px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border: none;
                    cursor: pointer;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    position: relative;
                }

                .ai-assistant-toggle:hover {
                    transform: scale(1.1);
                    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
                }

                .ai-assistant-pulse {
                    position: absolute;
                    top: -5px;
                    right: -5px;
                    width: 20px;
                    height: 20px;
                    background: #ff4757;
                    border-radius: 50%;
                    animation: ai-pulse 2s infinite;
                }

                @keyframes ai-pulse {
                    0% { transform: scale(1); opacity: 1; }
                    50% { transform: scale(1.2); opacity: 0.7; }
                    100% { transform: scale(1); opacity: 1; }
                }

                .ai-assistant-chat {
                    position: absolute;
                    bottom: 80px;
                    right: 0;
                    width: 350px;
                    height: 500px;
                    background: white;
                    border-radius: 15px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                    display: none;
                    flex-direction: column;
                    overflow: hidden;
                    border: 1px solid #e1e8ed;
                }

                .ai-assistant-chat.active {
                    display: flex;
                }

                .ai-assistant-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 15px 20px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                .ai-assistant-header h3 {
                    margin: 0;
                    font-size: 16px;
                    font-weight: 600;
                }

                .ai-assistant-close {
                    background: none;
                    border: none;
                    color: white;
                    cursor: pointer;
                    font-size: 18px;
                    padding: 0;
                    width: 24px;
                    height: 24px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                    transition: background 0.2s;
                }

                .ai-assistant-close:hover {
                    background: rgba(255,255,255,0.2);
                }

                .ai-assistant-messages {
                    flex: 1;
                    padding: 15px;
                    overflow-y: auto;
                    background: #f8f9fa;
                }

                .ai-message {
                    margin-bottom: 15px;
                    display: flex;
                    align-items: flex-start;
                }

                .ai-message.user {
                    justify-content: flex-end;
                }

                .ai-message-avatar {
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    margin-right: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                    font-weight: bold;
                    color: white;
                }

                .ai-message.user .ai-message-avatar {
                    background: #667eea;
                    margin-right: 0;
                    margin-left: 10px;
                }

                .ai-message.assistant .ai-message-avatar {
                    background: #764ba2;
                }

                .ai-message-content {
                    max-width: 70%;
                    padding: 12px 16px;
                    border-radius: 18px;
                    font-size: 14px;
                    line-height: 1.4;
                    white-space: pre-wrap;
                }

                .ai-message.user .ai-message-content {
                    background: #667eea;
                    color: white;
                    border-bottom-right-radius: 4px;
                }

                .ai-message.assistant .ai-message-content {
                    background: white;
                    color: #333;
                    border: 1px solid #e1e8ed;
                    border-bottom-left-radius: 4px;
                }

                .ai-assistant-input {
                    padding: 15px;
                    border-top: 1px solid #e1e8ed;
                    background: white;
                }

                .ai-input-container {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }

                .ai-input-field {
                    flex: 1;
                    border: 1px solid #e1e8ed;
                    border-radius: 20px;
                    padding: 10px 15px;
                    font-size: 14px;
                    outline: none;
                    transition: border-color 0.2s;
                }

                .ai-input-field:focus {
                    border-color: #667eea;
                }

                .ai-send-button {
                    background: #667eea;
                    color: white;
                    border: none;
                    border-radius: 50%;
                    width: 36px;
                    height: 36px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: background 0.2s;
                }

                .ai-send-button:hover {
                    background: #5a6fd8;
                }

                .ai-send-button:disabled {
                    background: #ccc;
                    cursor: not-allowed;
                }

                .ai-typing-indicator {
                    padding: 10px 15px;
                    color: #666;
                    font-style: italic;
                    font-size: 12px;
                }

                .ai-loading {
                    display: inline-block;
                    width: 20px;
                    height: 20px;
                    border: 3px solid #f3f3f3;
                    border-top: 3px solid #667eea;
                    border-radius: 50%;
                    animation: ai-spin 1s linear infinite;
                }

                @keyframes ai-spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                .ai-suggestions {
                    padding: 10px 15px;
                    border-top: 1px solid #e1e8ed;
                    background: #f8f9fa;
                }

                .ai-suggestion-chips {
                    display: flex;
                    gap: 8px;
                    flex-wrap: wrap;
                }

                .ai-suggestion-chip {
                    background: white;
                    border: 1px solid #e1e8ed;
                    border-radius: 15px;
                    padding: 6px 12px;
                    font-size: 12px;
                    cursor: pointer;
                    transition: all 0.2s;
                }

                .ai-suggestion-chip:hover {
                    background: #667eea;
                    color: white;
                    border-color: #667eea;
                }

                @media (max-width: 480px) {
                    .ai-assistant-chat {
                        width: calc(100vw - 40px);
                        height: calc(100vh - 120px);
                        bottom: 80px;
                        right: 20px;
                    }
                }
            `;

            document.head.appendChild(styles);
        }

        bindEvents() {
            const toggle = this.container.querySelector('.ai-assistant-toggle');
            const close = this.container.querySelector('.ai-assistant-close');
            const input = this.container.querySelector('.ai-input-field');
            const send = this.container.querySelector('.ai-send-button');
            const messages = this.container.querySelector('.ai-assistant-messages');
            const suggestions = this.container.querySelectorAll('.ai-suggestion-chip');

            toggle.addEventListener('click', () => this.toggleChat());
            close.addEventListener('click', () => this.closeChat());
            send.addEventListener('click', () => this.sendMessage());
            
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            suggestions.forEach(chip => {
                chip.addEventListener('click', () => {
                    input.value = chip.textContent;
                    this.sendMessage();
                });
            });

            // Close chat when clicking outside
            document.addEventListener('click', (e) => {
                if (!this.chat.contains(e.target) && !toggle.contains(e.target)) {
                    this.closeChat();
                }
            });

            // Show welcome message
            this.showWelcomeMessage();
        }

        toggleChat() {
            const isOpen = this.chat.classList.contains('active');
            if (isOpen) {
                this.closeChat();
            } else {
                this.openChat();
            }
        }

        openChat() {
            this.chat.classList.add('active');
            this.container.querySelector('.ai-input-field').focus();
            this.hidePulse();
        }

        closeChat() {
            this.chat.classList.remove('active');
        }

        showWelcomeMessage() {
            const welcomeMessage = {
                type: 'assistant',
                content: `Hello! I'm your AI assistant. I can help you with:

• Understanding this webpage
• Answering questions about content
• Summarizing information
• Providing explanations
• Helping with tasks

How can I assist you today?`
            };
            this.addMessage(welcomeMessage);
        }

        async sendMessage() {
            const input = this.container.querySelector('.ai-input-field');
            const message = input.value.trim();
            if (!message) return;

            // Add user message
            this.addMessage({ type: 'user', content: message });
            input.value = '';

            // Show typing indicator
            this.showTyping();

            try {
                // Get AI response
                const response = await this.getAIResponse(message);
                this.addMessage({ type: 'assistant', content: response });
            } catch (error) {
                this.addMessage({ 
                    type: 'assistant', 
                    content: 'Sorry, I encountered an error. Please try again.' 
                });
            } finally {
                this.hideTyping();
            }
        }

        async getAIResponse(message) {
            const context = this.getPageContext();
            
            if (this.config.model === 'local' && this.localModel) {
                try {
                    return await this.localModel.generateResponse(message, context);
                } catch (error) {
                    console.log('Local model failed, falling back to API');
                }
            }

            // Fallback to API or local response generation
            return await this.generateResponse(message, context);
        }

        getPageContext() {
            return {
                title: document.title,
                url: window.location.href,
                content: this.extractPageContent(),
                timestamp: new Date().toISOString()
            };
        }

        extractPageContent() {
            const mainContent = document.querySelector('main, article, .content, .main, #content, #main');
            if (mainContent) {
                return mainContent.textContent.substring(0, 1000);
            }
            return document.body.textContent.substring(0, 1000);
        }

        async generateResponse(message, context) {
            // Generate contextual response
            const lowerMessage = message.toLowerCase();
            
            if (lowerMessage.includes('help') || lowerMessage.includes('what can you do')) {
                return `I'm your AI assistant! I can help you with:

• **Page Analysis**: I can explain the content on this page
• **Summarization**: I can provide brief summaries of information
• **Q&A**: Ask me questions about what you see
• **Navigation**: I can help you find specific information
• **Tasks**: I can assist with various tasks and explanations

This page appears to be about: "${context.title}"

What would you like to know?`;
            }
            
            if (lowerMessage.includes('summarize') || lowerMessage.includes('summary')) {
                return `Here's a summary of this page:

**Page Title**: ${context.title}
**URL**: ${context.url}

**Key Content**: ${context.content.substring(0, 200)}...

This appears to be a ${this.detectPageType(context)} page. Would you like me to explain any specific part in more detail?`;
            }
            
            if (lowerMessage.includes('explain') || lowerMessage.includes('what is this')) {
                return `Let me explain what I can see on this page:

**Page Type**: ${this.detectPageType(context)}
**Purpose**: ${this.detectPagePurpose(context)}

**Main Content**: ${context.content.substring(0, 300)}...

Is there something specific about this content you'd like me to clarify?`;
            }

            // Default response
            return `I understand you're asking about "${message}". 

Based on the context of this page (${context.title}), I can help you with that. Let me provide some relevant information:

${this.generateRelevantInfo(message, context)}

Is there anything specific you'd like me to focus on or explain further?`;
        }

        detectPageType(context) {
            const url = context.url.toLowerCase();
            if (url.includes('login') || url.includes('auth')) return 'authentication';
            if (url.includes('admin') || url.includes('dashboard')) return 'administrative';
            if (url.includes('product') || url.includes('shop')) return 'e-commerce';
            if (url.includes('blog') || url.includes('article')) return 'content/article';
            if (url.includes('contact') || url.includes('about')) return 'informational';
            return 'general web page';
        }

        detectPagePurpose(context) {
            const content = context.content.toLowerCase();
            if (content.includes('login') || content.includes('sign in')) return 'User authentication';
            if (content.includes('dashboard') || content.includes('admin')) return 'Administrative interface';
            if (content.includes('product') || content.includes('buy')) return 'E-commerce or sales';
            if (content.includes('contact') || content.includes('about')) return 'Information and contact';
            return 'General information and interaction';
        }

        generateRelevantInfo(message, context) {
            const keywords = message.toLowerCase().split(' ');
            const content = context.content.toLowerCase();
            
            if (keywords.some(k => content.includes(k))) {
                return 'I found some relevant information on this page that might help answer your question. ';
            }
            
            return 'The page contains various elements and information that could be useful for your query.';
        }

        addMessage(message) {
            const messages = this.container.querySelector('.ai-assistant-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `ai-message ${message.type}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'ai-message-avatar';
            avatar.textContent = message.type === 'user' ? 'U' : '🤖';
            
            const content = document.createElement('div');
            content.className = 'ai-message-content';
            content.textContent = message.content;
            
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(content);
            
            messages.appendChild(messageDiv);
            messages.scrollTop = messages.scrollHeight;
            
            this.conversationHistory.push(message);
        }

        showTyping() {
            const typing = this.container.querySelector('.ai-typing-indicator');
            const send = this.container.querySelector('.ai-send-button');
            const messages = this.container.querySelector('.ai-assistant-messages');
            
            typing.style.display = 'block';
            send.disabled = true;
            messages.scrollTop = messages.scrollHeight;
        }

        hideTyping() {
            const typing = this.container.querySelector('.ai-typing-indicator');
            const send = this.container.querySelector('.ai-send-button');
            
            typing.style.display = 'none';
            send.disabled = false;
        }

        showPulse() {
            const pulse = this.container.querySelector('.ai-assistant-pulse');
            pulse.style.display = 'block';
        }

        hidePulse() {
            const pulse = this.container.querySelector('.ai-assistant-pulse');
            pulse.style.display = 'none';
        }

        // Public API methods
        destroy() {
            if (this.container && this.container.parentNode) {
                this.container.parentNode.removeChild(this.container);
            }
            this.isInitialized = false;
        }

        getConversationHistory() {
            return this.conversationHistory;
        }

        setModel(model) {
            this.config.model = model;
        }
    }

    // Global initialization function
    window.AIAssistantEmbed = {
        init: function(config = {}) {
            return new AIAssistantEmbed(config);
        },
        
        // Pre-built configurations
        presets: {
            basic: {
                model: 'local',
                theme: 'default'
            },
            advanced: {
                model: 'api',
                theme: 'dark',
                apiUrl: '/ai_assistant_api.php'
            }
        }
    };

    // Auto-initialize if no manual init
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            if (!window.aiAssistantInstance) {
                window.aiAssistantInstance = window.AIAssistantEmbed.init();
            }
        });
    } else {
        if (!window.aiAssistantInstance) {
            window.aiAssistantInstance = window.AIAssistantEmbed.init();
        }
    }

})(); 