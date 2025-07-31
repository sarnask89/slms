/**
 * Focused ML Integration for Adaptive AI Assistant
 * Optimized for: Module modification, database updates, network monitoring, GUI adaptation
 */

class FocusedMLIntegration {
    constructor(config = {}) {
        this.config = {
            serviceUrl: config.serviceUrl || 'http://localhost:8000',
            maxTokens: config.maxTokens || 500,
            temperature: config.temperature || 0.7,
            confidenceThreshold: config.confidenceThreshold || 0.8,
            ...config
        };
        
        this.capabilities = {
            moduleModification: true,
            databaseUpdates: true,
            networkAnalysis: true,
            guiAdaptation: true
        };
    }
    
    async modifyModule(moduleName, modificationType, userRequest, currentCode = null) {
        try {
            const response = await fetch(`${this.config.serviceUrl}/modify_module`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    module_name: moduleName,
                    modification_type: modificationType,
                    user_request: userRequest,
                    current_code: currentCode
                })
            });
            
            if (!response.ok) throw new Error('Module modification failed');
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Module modification error:', error);
            throw error;
        }
    }
    
    async updateDatabase(tableName, rawText, updateType, existingData = null) {
        try {
            const response = await fetch(`${this.config.serviceUrl}/update_database`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    table_name: tableName,
                    raw_text: rawText,
                    update_type: updateType,
                    existing_data: existingData
                })
            });
            
            if (!response.ok) throw new Error('Database update failed');
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Database update error:', error);
            throw error;
        }
    }
    
    async analyzeNetwork(networkData, analysisType = 'issues') {
        try {
            const response = await fetch(`${this.config.serviceUrl}/analyze_network`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    network_data: networkData,
                    analysis_type: analysisType
                })
            });
            
            if (!response.ok) throw new Error('Network analysis failed');
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Network analysis error:', error);
            throw error;
        }
    }
    
    async modifyGUI(pageUrl, modificationType, userBehavior, currentLayout = null) {
        try {
            const response = await fetch(`${this.config.serviceUrl}/modify_gui`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    page_url: pageUrl,
                    modification_type: modificationType,
                    user_behavior: userBehavior,
                    current_layout: currentLayout
                })
            });
            
            if (!response.ok) throw new Error('GUI modification failed');
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('GUI modification error:', error);
            throw error;
        }
    }
    
    // High-level convenience methods
    async createNewModule(moduleName, userRequest) {
        return await this.modifyModule(moduleName, 'new_module', userRequest);
    }
    
    async updateModuleContent(moduleName, userRequest, currentCode) {
        return await this.modifyModule(moduleName, 'content', userRequest, currentCode);
    }
    
    async extractMacAddresses(rawText) {
        return await this.updateDatabase('devices', rawText, 'mac_address');
    }
    
    async extractDeviceInfo(rawText) {
        return await this.updateDatabase('devices', rawText, 'device_info');
    }
    
    async detectNetworkIssues(networkData) {
        return await this.analyzeNetwork(networkData, 'issues');
    }
    
    async resizeGUIElements(userBehavior) {
        return await this.modifyGUI(window.location.href, 'resize', userBehavior);
    }
    
    async addGUIShortcuts(userBehavior) {
        return await this.modifyGUI(window.location.href, 'add_element', userBehavior);
    }
    
    // Integration with adaptive AI assistant
    integrateWithAdaptiveAI(adaptiveAI) {
        // Extend adaptive AI with focused ML capabilities
        adaptiveAI.focusedML = this;
        
        // Override behavior analysis to include ML insights
        const originalAnalyzeBehavior = adaptiveAI.analyzeUserBehavior;
        adaptiveAI.analyzeUserBehavior = async function() {
            const basicAnalysis = originalAnalyzeBehavior.call(this);
            
            // Enhance with ML analysis
            try {
                const mlAnalysis = await this.focusedML.analyzeNetwork(
                    this.userBehavior, 'issues'
                );
                
                return {
                    ...basicAnalysis,
                    ml_insights: mlAnalysis
                };
            } catch (error) {
                console.warn('ML analysis failed, using basic analysis:', error);
                return basicAnalysis;
            }
        };
        
        // Add ML-powered modification suggestions
        adaptiveAI.suggestMLImprovements = async function() {
            try {
                const suggestions = await this.focusedML.modifyGUI(
                    window.location.href,
                    'suggest',
                    this.userBehavior
                );
                
                return suggestions.modifications;
            } catch (error) {
                console.warn('ML suggestions failed:', error);
                return [];
            }
        };
    }
    
    async getServiceStatus() {
        try {
            const response = await fetch(`${this.config.serviceUrl}/health`);
            return response.ok;
        } catch (error) {
            return false;
        }
    }
}

// Export for use
window.FocusedMLIntegration = FocusedMLIntegration;
