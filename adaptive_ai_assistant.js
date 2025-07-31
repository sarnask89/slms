/**
 * Adaptive AI Assistant
 * Can modify and expand GUI functionality based on user behavior and requests
 */

class AdaptiveAIAssistant {
    constructor(config = {}) {
        this.config = {
            observationMode: true,
            autoModify: true,
            learningEnabled: true,
            ...config
        };
        
        this.userBehavior = {
            clicks: [],
            scrolls: [],
            timeOnPage: 0,
            errors: [],
            frustrations: []
        };
        
        this.modifications = [];
        this.observers = [];
        this.isActive = false;
        
        this.initialize();
    }
    
    initialize() {
        this.startBehaviorTracking();
        this.createAdaptiveInterface();
        this.loadUserPreferences();
        this.startPeriodicAnalysis();
    }
    
    startBehaviorTracking() {
        // Track user clicks
        document.addEventListener('click', (e) => {
            this.recordUserAction('click', {
                element: e.target.tagName,
                id: e.target.id,
                class: e.target.className,
                text: e.target.textContent?.substring(0, 50),
                timestamp: Date.now()
            });
        });
        
        // Track scrolling patterns
        let scrollTimeout;
        document.addEventListener('scroll', (e) => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                this.recordUserAction('scroll', {
                    scrollY: window.scrollY,
                    scrollX: window.scrollX,
                    timestamp: Date.now()
                });
            }, 100);
        });
        
        // Track form interactions
        document.addEventListener('input', (e) => {
            this.recordUserAction('input', {
                element: e.target.tagName,
                id: e.target.id,
                type: e.target.type,
                timestamp: Date.now()
            });
        });
        
        // Track errors and frustrations
        window.addEventListener('error', (e) => {
            this.recordUserAction('error', {
                message: e.message,
                filename: e.filename,
                lineno: e.lineno,
                timestamp: Date.now()
            });
        });
        
        // Track time on page
        setInterval(() => {
            this.userBehavior.timeOnPage += 1;
        }, 1000);
    }
    
    recordUserAction(type, data) {
        this.userBehavior[type + 's'].push(data);
        
        // Keep only last 100 actions
        if (this.userBehavior[type + 's'].length > 100) {
            this.userBehavior[type + 's'].shift();
        }
        
        // Analyze for patterns
        this.analyzeUserBehavior();
    }
    
    analyzeUserBehavior() {
        const recentActions = this.userBehavior.clicks.slice(-10);
        const patterns = this.detectPatterns(recentActions);
        
        if (patterns.frustration > 0.7) {
            this.suggestImprovements();
        }
        
        if (patterns.repetitive > 0.8) {
            this.autoOptimize();
        }
    }
    
    detectPatterns(actions) {
        const patterns = {
            frustration: 0,
            repetitive: 0,
            efficiency: 0
        };
        
        // Detect repetitive clicks
        const clickCounts = {};
        actions.forEach(action => {
            const key = `${action.element}-${action.id}`;
            clickCounts[key] = (clickCounts[key] || 0) + 1;
        });
        
        patterns.repetitive = Math.max(...Object.values(clickCounts)) / actions.length;
        
        // Detect frustration (rapid clicking, errors)
        const rapidClicks = actions.filter((action, index) => {
            if (index === 0) return false;
            return action.timestamp - actions[index - 1].timestamp < 500;
        }).length;
        
        patterns.frustration = rapidClicks / actions.length;
        
        return patterns;
    }
    
    createAdaptiveInterface() {
        // Create the adaptive assistant UI
        this.createAssistantUI();
        this.createModificationPanel();
        this.createLearningPanel();
    }
    
    createAssistantUI() {
        const assistant = document.createElement('div');
        assistant.id = 'adaptive-ai-assistant';
        assistant.innerHTML = `
            <div class="adaptive-assistant-toggle" id="adaptiveToggle">
                <div class="adaptive-icon">🤖</div>
                <div class="adaptive-status" id="adaptiveStatus">Learning...</div>
            </div>
            
            <div class="adaptive-assistant-panel" id="adaptivePanel" style="display: none;">
                <div class="adaptive-header">
                    <h3>Adaptive AI Assistant</h3>
                    <button class="adaptive-close" id="adaptiveClose">×</button>
                </div>
                
                <div class="adaptive-tabs">
                    <button class="adaptive-tab active" data-tab="chat">💬 Chat</button>
                    <button class="adaptive-tab" data-tab="modifications">🔧 Modifications</button>
                    <button class="adaptive-tab" data-tab="learning">🧠 Learning</button>
                    <button class="adaptive-tab" data-tab="suggestions">💡 Suggestions</button>
                </div>
                
                <div class="adaptive-content">
                    <div class="adaptive-tab-content active" id="chatTab">
                        <div class="adaptive-messages" id="adaptiveMessages"></div>
                        <div class="adaptive-input">
                            <input type="text" id="adaptiveInput" placeholder="Ask me to modify the interface...">
                            <button id="adaptiveSend">Send</button>
                        </div>
                    </div>
                    
                    <div class="adaptive-tab-content" id="modificationsTab">
                        <div class="modifications-list" id="modificationsList"></div>
                        <button class="adaptive-button" id="addModification">Add New Modification</button>
                    </div>
                    
                    <div class="adaptive-tab-content" id="learningTab">
                        <div class="learning-stats" id="learningStats"></div>
                        <div class="behavior-patterns" id="behaviorPatterns"></div>
                    </div>
                    
                    <div class="adaptive-tab-content" id="suggestionsTab">
                        <div class="suggestions-list" id="suggestionsList"></div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(assistant);
        this.addAdaptiveStyles();
        this.bindAdaptiveEvents();
    }
    
    addAdaptiveStyles() {
        const styles = document.createElement('style');
        styles.textContent = `
            #adaptive-ai-assistant {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 10000;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            
            .adaptive-assistant-toggle {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                cursor: pointer;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: white;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                transition: all 0.3s ease;
            }
            
            .adaptive-assistant-toggle:hover {
                transform: scale(1.1);
            }
            
            .adaptive-icon {
                font-size: 24px;
                margin-bottom: 2px;
            }
            
            .adaptive-status {
                font-size: 8px;
                opacity: 0.8;
            }
            
            .adaptive-assistant-panel {
                position: absolute;
                bottom: 80px;
                right: 0;
                width: 400px;
                height: 600px;
                background: white;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }
            
            .adaptive-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 15px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .adaptive-header h3 {
                margin: 0;
                font-size: 16px;
            }
            
            .adaptive-close {
                background: none;
                border: none;
                color: white;
                font-size: 18px;
                cursor: pointer;
                padding: 0;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .adaptive-tabs {
                display: flex;
                background: #f8f9fa;
                border-bottom: 1px solid #e1e8ed;
            }
            
            .adaptive-tab {
                flex: 1;
                padding: 10px;
                border: none;
                background: none;
                cursor: pointer;
                font-size: 12px;
                transition: background 0.2s;
            }
            
            .adaptive-tab.active {
                background: white;
                border-bottom: 2px solid #667eea;
            }
            
            .adaptive-content {
                flex: 1;
                overflow: hidden;
            }
            
            .adaptive-tab-content {
                display: none;
                height: 100%;
                overflow-y: auto;
                padding: 15px;
            }
            
            .adaptive-tab-content.active {
                display: block;
            }
            
            .adaptive-messages {
                flex: 1;
                overflow-y: auto;
                margin-bottom: 15px;
            }
            
            .adaptive-input {
                display: flex;
                gap: 10px;
            }
            
            .adaptive-input input {
                flex: 1;
                padding: 8px 12px;
                border: 1px solid #e1e8ed;
                border-radius: 20px;
                outline: none;
            }
            
            .adaptive-input button {
                padding: 8px 16px;
                background: #667eea;
                color: white;
                border: none;
                border-radius: 20px;
                cursor: pointer;
            }
            
            .modifications-list {
                margin-bottom: 15px;
            }
            
            .modification-item {
                background: #f8f9fa;
                padding: 10px;
                margin-bottom: 10px;
                border-radius: 8px;
                border-left: 4px solid #667eea;
            }
            
            .adaptive-button {
                width: 100%;
                padding: 10px;
                background: #667eea;
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
            }
            
            .learning-stats {
                background: #e3f2fd;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 15px;
            }
            
            .behavior-patterns {
                background: #f3e5f5;
                padding: 15px;
                border-radius: 8px;
            }
            
            .suggestions-list {
                margin-bottom: 15px;
            }
            
            .suggestion-item {
                background: #fff3e0;
                padding: 10px;
                margin-bottom: 10px;
                border-radius: 8px;
                border-left: 4px solid #ff9800;
                cursor: pointer;
            }
        `;
        document.head.appendChild(styles);
    }
    
    bindAdaptiveEvents() {
        const toggle = document.getElementById('adaptiveToggle');
        const panel = document.getElementById('adaptivePanel');
        const close = document.getElementById('adaptiveClose');
        const input = document.getElementById('adaptiveInput');
        const send = document.getElementById('adaptiveSend');
        const tabs = document.querySelectorAll('.adaptive-tab');
        
        toggle.addEventListener('click', () => {
            panel.style.display = panel.style.display === 'none' ? 'flex' : 'none';
        });
        
        close.addEventListener('click', () => {
            panel.style.display = 'none';
        });
        
        send.addEventListener('click', () => {
            this.handleUserRequest(input.value);
            input.value = '';
        });
        
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.handleUserRequest(input.value);
                input.value = '';
            }
        });
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                const tabContents = document.querySelectorAll('.adaptive-tab-content');
                tabContents.forEach(content => content.classList.remove('active'));
                
                const targetTab = document.getElementById(tab.dataset.tab + 'Tab');
                targetTab.classList.add('active');
                
                this.updateTabContent(tab.dataset.tab);
            });
        });
        
        // Initial tab content
        this.updateTabContent('chat');
    }
    
    updateTabContent(tabName) {
        switch(tabName) {
            case 'modifications':
                this.updateModificationsTab();
                break;
            case 'learning':
                this.updateLearningTab();
                break;
            case 'suggestions':
                this.updateSuggestionsTab();
                break;
        }
    }
    
    updateModificationsTab() {
        const list = document.getElementById('modificationsList');
        list.innerHTML = this.modifications.map(mod => `
            <div class="modification-item">
                <strong>${mod.type}</strong>: ${mod.description}
                <br><small>Applied: ${new Date(mod.timestamp).toLocaleString()}</small>
                <button onclick="adaptiveAI.removeModification('${mod.id}')" style="float: right; background: #ff4757; color: white; border: none; padding: 2px 8px; border-radius: 4px; cursor: pointer;">Remove</button>
            </div>
        `).join('');
    }
    
    updateLearningTab() {
        const stats = document.getElementById('learningStats');
        const patterns = document.getElementById('behaviorPatterns');
        
        stats.innerHTML = `
            <h4>User Behavior Statistics</h4>
            <p>Time on page: ${Math.floor(this.userBehavior.timeOnPage / 60)}m ${this.userBehavior.timeOnPage % 60}s</p>
            <p>Total clicks: ${this.userBehavior.clicks.length}</p>
            <p>Total scrolls: ${this.userBehavior.scrolls.length}</p>
            <p>Errors encountered: ${this.userBehavior.errors.length}</p>
        `;
        
        const recentPatterns = this.detectPatterns(this.userBehavior.clicks.slice(-20));
        patterns.innerHTML = `
            <h4>Detected Patterns</h4>
            <p>Frustration level: ${(recentPatterns.frustration * 100).toFixed(1)}%</p>
            <p>Repetitive actions: ${(recentPatterns.repetitive * 100).toFixed(1)}%</p>
            <p>Efficiency score: ${(recentPatterns.efficiency * 100).toFixed(1)}%</p>
        `;
    }
    
    updateSuggestionsTab() {
        const list = document.getElementById('suggestionsList');
        const suggestions = this.generateSuggestions();
        
        list.innerHTML = suggestions.map(suggestion => `
            <div class="suggestion-item" onclick="adaptiveAI.applySuggestion('${suggestion.id}')">
                <strong>${suggestion.title}</strong>
                <br>${suggestion.description}
            </div>
        `).join('');
    }
    
    async handleUserRequest(request) {
        this.addMessage('user', request);
        
        // Analyze request for modification intent
        const modification = this.analyzeModificationRequest(request);
        
        if (modification) {
            await this.applyModification(modification);
            this.addMessage('assistant', `I've ${modification.description}. The interface has been updated to make your experience better!`);
        } else {
            // Generate contextual response
            const response = await this.generateResponse(request);
            this.addMessage('assistant', response);
        }
    }
    
    analyzeModificationRequest(request) {
        const lowerRequest = request.toLowerCase();
        
        // UI modifications
        if (lowerRequest.includes('make') && lowerRequest.includes('bigger')) {
            return {
                type: 'resize',
                target: this.findRelevantElement(),
                description: 'increased the size of the interface elements',
                action: () => this.resizeElements()
            };
        }
        
        if (lowerRequest.includes('add') && lowerRequest.includes('button')) {
            return {
                type: 'add_button',
                target: 'navigation',
                description: 'added a new shortcut button',
                action: () => this.addShortcutButton()
            };
        }
        
        if (lowerRequest.includes('move') || lowerRequest.includes('reposition')) {
            return {
                type: 'reposition',
                target: this.findRelevantElement(),
                description: 'repositioned elements for better accessibility',
                action: () => this.repositionElements()
            };
        }
        
        if (lowerRequest.includes('color') || lowerRequest.includes('theme')) {
            return {
                type: 'theme',
                target: 'global',
                description: 'changed the color theme',
                action: () => this.changeTheme()
            };
        }
        
        if (lowerRequest.includes('shortcut') || lowerRequest.includes('keyboard')) {
            return {
                type: 'shortcut',
                target: 'global',
                description: 'added keyboard shortcuts',
                action: () => this.addKeyboardShortcuts()
            };
        }
        
        return null;
    }
    
    async applyModification(modification) {
        try {
            await modification.action();
            
            // Record the modification
            const modRecord = {
                id: Date.now().toString(),
                type: modification.type,
                description: modification.description,
                timestamp: Date.now(),
                target: modification.target
            };
            
            this.modifications.push(modRecord);
            this.saveModifications();
            
            // Update UI
            this.updateModificationsTab();
            
        } catch (error) {
            console.error('Failed to apply modification:', error);
            this.addMessage('assistant', 'Sorry, I encountered an error while trying to modify the interface.');
        }
    }
    
    resizeElements() {
        const elements = document.querySelectorAll('button, input, select, textarea');
        elements.forEach(element => {
            const currentSize = parseFloat(getComputedStyle(element).fontSize);
            element.style.fontSize = (currentSize * 1.2) + 'px';
            element.style.padding = '12px 16px';
        });
    }
    
    addShortcutButton() {
        const button = document.createElement('button');
        button.textContent = '🚀 Quick Action';
        button.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 20px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        `;
        
        button.addEventListener('click', () => {
            this.performQuickAction();
        });
        
        document.body.appendChild(button);
    }
    
    repositionElements() {
        // Find elements that might be hard to reach
        const elements = document.querySelectorAll('button, input, a');
        elements.forEach(element => {
            const rect = element.getBoundingClientRect();
            if (rect.top > window.innerHeight * 0.8) {
                // Move to more accessible position
                element.style.position = 'relative';
                element.style.top = '-50px';
            }
        });
    }
    
    changeTheme() {
        const themes = [
            { primary: '#667eea', secondary: '#764ba2' },
            { primary: '#ff6b6b', secondary: '#4ecdc4' },
            { primary: '#a8e6cf', secondary: '#dcedc1' },
            { primary: '#ffd93d', secondary: '#ff6b6b' }
        ];
        
        const randomTheme = themes[Math.floor(Math.random() * themes.length)];
        
        document.documentElement.style.setProperty('--primary-color', randomTheme.primary);
        document.documentElement.style.setProperty('--secondary-color', randomTheme.secondary);
    }
    
    addKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'h':
                        e.preventDefault();
                        this.showHelp();
                        break;
                    case 's':
                        e.preventDefault();
                        this.saveCurrentState();
                        break;
                    case 'r':
                        e.preventDefault();
                        this.resetModifications();
                        break;
                }
            }
        });
    }
    
    findRelevantElement() {
        // Find the most recently clicked element or element under cursor
        const recentClick = this.userBehavior.clicks[this.userBehavior.clicks.length - 1];
        if (recentClick && recentClick.id) {
            return document.getElementById(recentClick.id);
        }
        return null;
    }
    
    generateSuggestions() {
        const suggestions = [];
        
        // Based on user behavior patterns
        if (this.userBehavior.clicks.length > 10) {
            suggestions.push({
                id: 'shortcuts',
                title: 'Add Keyboard Shortcuts',
                description: 'I noticed you click frequently. Would you like keyboard shortcuts?'
            });
        }
        
        if (this.userBehavior.scrolls.length > 5) {
            suggestions.push({
                id: 'navigation',
                title: 'Improve Navigation',
                description: 'I see you scroll a lot. Should I add a navigation menu?'
            });
        }
        
        if (this.userBehavior.errors.length > 0) {
            suggestions.push({
                id: 'error_handling',
                title: 'Better Error Handling',
                description: 'I detected some errors. Should I add better error messages?'
            });
        }
        
        return suggestions;
    }
    
    applySuggestion(suggestionId) {
        switch(suggestionId) {
            case 'shortcuts':
                this.addKeyboardShortcuts();
                break;
            case 'navigation':
                this.addNavigationMenu();
                break;
            case 'error_handling':
                this.improveErrorHandling();
                break;
        }
        
        this.updateSuggestionsTab();
    }
    
    addMessage(type, content) {
        const messages = document.getElementById('adaptiveMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `adaptive-message ${type}`;
        messageDiv.innerHTML = `
            <div class="message-content">
                <strong>${type === 'user' ? 'You' : 'AI'}:</strong> ${content}
            </div>
        `;
        messages.appendChild(messageDiv);
        messages.scrollTop = messages.scrollHeight;
    }
    
    async generateResponse(request) {
        // This would integrate with your AI model
        return `I understand you want to modify the interface. I can help you with that! Try asking me to "make buttons bigger", "add a shortcut button", "change the theme", or "add keyboard shortcuts".`;
    }
    
    saveModifications() {
        localStorage.setItem('adaptiveAI_modifications', JSON.stringify(this.modifications));
    }
    
    loadUserPreferences() {
        const saved = localStorage.getItem('adaptiveAI_modifications');
        if (saved) {
            this.modifications = JSON.parse(saved);
        }
    }
    
    startPeriodicAnalysis() {
        setInterval(() => {
            this.analyzeUserBehavior();
            this.updateLearningTab();
        }, 30000); // Every 30 seconds
    }
    
    // Public API methods
    removeModification(id) {
        this.modifications = this.modifications.filter(mod => mod.id !== id);
        this.saveModifications();
        this.updateModificationsTab();
    }
    
    resetModifications() {
        this.modifications = [];
        this.saveModifications();
        location.reload();
    }
    
    showHelp() {
        alert(`Adaptive AI Assistant Help:
        
Ctrl+H: Show this help
Ctrl+S: Save current state
Ctrl+R: Reset modifications

You can also ask me to:
- Make elements bigger/smaller
- Add buttons or shortcuts
- Change colors or themes
- Reposition elements
- Add keyboard shortcuts`);
    }
    
    saveCurrentState() {
        const state = {
            modifications: this.modifications,
            userBehavior: this.userBehavior,
            timestamp: Date.now()
        };
        localStorage.setItem('adaptiveAI_state', JSON.stringify(state));
        alert('Current state saved!');
    }
}

// Initialize the adaptive AI assistant
window.adaptiveAI = new AdaptiveAIAssistant(); 