/**
 * sLMS Tooltip System
 * Provides comprehensive tooltip functionality for editable functions and UI elements
 * 
 * Features:
 * - Hover tooltips for editable functions
 * - Context-sensitive help
 * - Dynamic content loading
 * - Accessibility support
 * - Touch device support
 */

class SLMSTooltipSystem {
    constructor() {
        this.tooltips = new Map();
        this.activeTooltip = null;
        this.tooltipData = {};
        this.isTouchDevice = 'ontouchstart' in window;
        this.init();
    }

    /**
     * Initialize the tooltip system
     */
    init() {
        this.loadTooltipData();
        this.setupEventListeners();
        this.initializeBootstrapTooltips();
        this.setupCustomTooltips();
    }

    /**
     * Load tooltip data from server or local storage
     */
    async loadTooltipData() {
        try {
            // Try to load from server first
            const response = await fetch('/modules/tooltip_data.php');
            if (response.ok) {
                this.tooltipData = await response.json();
            } else {
                // Fallback to default tooltip data
                this.tooltipData = this.getDefaultTooltipData();
            }
        } catch (error) {
            console.warn('Failed to load tooltip data from server, using defaults:', error);
            this.tooltipData = this.getDefaultTooltipData();
        }
    }

    /**
     * Get default tooltip data for common functions
     */
    getDefaultTooltipData() {
        return {
            // Client Management
            'add-client': {
                title: 'Add New Client',
                content: 'Create a new client account with contact information, services, and billing details.',
                category: 'Client Management',
                examples: ['Basic client info', 'Service assignment', 'Billing setup']
            },
            'edit-client': {
                title: 'Edit Client',
                content: 'Modify existing client information including personal details, services, and preferences.',
                category: 'Client Management',
                examples: ['Update contact info', 'Change services', 'Modify billing']
            },
            'client-list': {
                title: 'Client List',
                content: 'View and manage all registered clients with search, filter, and bulk operations.',
                category: 'Client Management',
                examples: ['Search clients', 'Filter by status', 'Bulk actions']
            },

            // Device Management
            'add-device': {
                title: 'Add Device',
                content: 'Register a new network device with IP configuration, SNMP settings, and monitoring options.',
                category: 'Device Management',
                examples: ['IP configuration', 'SNMP setup', 'Monitoring enable']
            },
            'edit-device': {
                title: 'Edit Device',
                content: 'Modify device settings, update configuration, and adjust monitoring parameters.',
                category: 'Device Management',
                examples: ['Update IP', 'Change SNMP', 'Modify alerts']
            },
            'device-monitoring': {
                title: 'Device Monitoring',
                content: 'Real-time monitoring of device status, performance metrics, and health indicators.',
                category: 'Device Management',
                examples: ['Status check', 'Performance graphs', 'Alert management']
            },

            // Network Management
            'add-network': {
                title: 'Add Network',
                content: 'Create a new network segment with subnet configuration and routing information.',
                category: 'Network Management',
                examples: ['Subnet definition', 'Gateway setup', 'DHCP range']
            },
            'edit-network': {
                title: 'Edit Network',
                content: 'Modify network configuration, update routing, and adjust DHCP settings.',
                category: 'Network Management',
                examples: ['Update subnet', 'Change gateway', 'Modify DHCP']
            },
            'network-monitoring': {
                title: 'Network Monitoring',
                content: 'Monitor network performance, bandwidth usage, and connectivity status.',
                category: 'Network Management',
                examples: ['Bandwidth graphs', 'Traffic analysis', 'Connectivity tests']
            },

            // Services & Packages
            'add-internet-package': {
                title: 'Add Internet Package',
                content: 'Create a new internet service package with bandwidth limits and pricing.',
                category: 'Services & Packages',
                examples: ['Bandwidth limits', 'Pricing setup', 'Service terms']
            },
            'add-tv-package': {
                title: 'Add TV Package',
                content: 'Create a new television service package with channel lineup and pricing.',
                category: 'Services & Packages',
                examples: ['Channel selection', 'Package pricing', 'Service features']
            },
            'edit-service': {
                title: 'Edit Service',
                content: 'Modify existing service configurations, pricing, and feature sets.',
                category: 'Services & Packages',
                examples: ['Update pricing', 'Change features', 'Modify terms']
            },

            // Financial Management
            'add-invoice': {
                title: 'Add Invoice',
                content: 'Create a new invoice for client services with itemized billing and payment terms.',
                category: 'Financial Management',
                examples: ['Service billing', 'Payment terms', 'Tax calculation']
            },
            'add-payment': {
                title: 'Add Payment',
                content: 'Record client payments and update account balances.',
                category: 'Financial Management',
                examples: ['Payment recording', 'Balance update', 'Receipt generation']
            },
            'financial-reports': {
                title: 'Financial Reports',
                content: 'Generate financial reports including revenue, outstanding invoices, and payment history.',
                category: 'Financial Management',
                examples: ['Revenue reports', 'Invoice status', 'Payment history']
            },

            // DHCP Management
            'dhcp-clients': {
                title: 'DHCP Clients',
                content: 'View and manage DHCP client leases, IP assignments, and network configurations.',
                category: 'DHCP Management',
                examples: ['Lease management', 'IP assignments', 'Network config']
            },
            'dhcp-networks': {
                title: 'DHCP Networks',
                content: 'Configure DHCP server settings, IP ranges, and network policies.',
                category: 'DHCP Management',
                examples: ['IP range setup', 'Server config', 'Policy management']
            },

            // Network Monitoring (Cacti)
            'cacti-integration': {
                title: 'Cacti Integration',
                content: 'Integrate with Cacti monitoring system for advanced network monitoring and graphing.',
                category: 'Network Monitoring (Cacti)',
                examples: ['Device monitoring', 'Performance graphs', 'Alert integration']
            },
            'snmp-monitoring': {
                title: 'SNMP Monitoring',
                content: 'Monitor network devices using SNMP protocol for performance and status data.',
                category: 'Network Monitoring (Cacti)',
                examples: ['Device polling', 'Performance metrics', 'Status monitoring']
            },
            'interface-monitoring': {
                title: 'Interface Monitoring',
                content: 'Monitor network interface status, traffic, and performance metrics.',
                category: 'Network Monitoring (Cacti)',
                examples: ['Interface status', 'Traffic analysis', 'Performance graphs']
            },

            // System Administration
            'user-management': {
                title: 'User Management',
                content: 'Manage system users, roles, permissions, and access levels.',
                category: 'System Administration',
                examples: ['User creation', 'Role assignment', 'Permission management']
            },
            'access-level-manager': {
                title: 'Access Level Manager',
                content: 'Configure granular access levels with section and action-based permissions.',
                category: 'System Administration',
                examples: ['Level creation', 'Permission assignment', 'User assignment']
            },
            'system-status': {
                title: 'System Status',
                content: 'Monitor system health, performance metrics, and operational status.',
                category: 'System Administration',
                examples: ['Health monitoring', 'Performance metrics', 'Status alerts']
            },
            'theme-editor': {
                title: 'Theme Editor',
                content: 'Customize system appearance with color schemes, layouts, and visual preferences.',
                category: 'System Administration',
                examples: ['Color schemes', 'Layout options', 'Visual customization']
            },

            // Documentation
            'user-manual': {
                title: 'User Manual',
                content: 'Comprehensive user documentation with guides, tutorials, and best practices.',
                category: 'Documentation',
                examples: ['Quick start guide', 'Feature tutorials', 'Best practices']
            },
            'api-reference': {
                title: 'API Reference',
                content: 'Technical documentation for system APIs, endpoints, and integration methods.',
                category: 'Documentation',
                examples: ['API endpoints', 'Integration guides', 'Code examples']
            }
        };
    }

    /**
     * Setup event listeners for tooltip functionality
     */
    setupEventListeners() {
        // Handle hover events for editable functions
        document.addEventListener('mouseover', (e) => {
            const target = e.target;
            const tooltipId = this.getTooltipId(target);
            
            if (tooltipId && this.tooltipData[tooltipId]) {
                this.showTooltip(target, tooltipId);
            }
        });

        document.addEventListener('mouseout', (e) => {
            const target = e.target;
            const tooltipId = this.getTooltipId(target);
            
            if (tooltipId) {
                this.hideTooltip();
            }
        });

        // Handle touch events for mobile devices
        if (this.isTouchDevice) {
            document.addEventListener('touchstart', (e) => {
                const target = e.target;
                const tooltipId = this.getTooltipId(target);
                
                if (tooltipId && this.tooltipData[tooltipId]) {
                    e.preventDefault();
                    this.showTooltip(target, tooltipId);
                    
                    // Hide tooltip after 3 seconds on touch devices
                    setTimeout(() => {
                        this.hideTooltip();
                    }, 3000);
                }
            });
        }

        // Handle help button clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('help-btn') || e.target.closest('.help-btn')) {
                e.preventDefault();
                const target = e.target.closest('.help-btn');
                const tooltipId = target.dataset.tooltipId;
                
                if (tooltipId && this.tooltipData[tooltipId]) {
                    this.showHelpModal(tooltipId);
                }
            }
        });
    }

    /**
     * Initialize Bootstrap tooltips
     */
    initializeBootstrapTooltips() {
        if (window.bootstrap && window.bootstrap.Tooltip) {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltipTriggerList.forEach(tooltipTriggerEl => {
                new bootstrap.Tooltip(tooltipTriggerEl, {
                    placement: 'top',
                    trigger: 'hover',
                    html: true
                });
            });
        }
    }

    /**
     * Setup custom tooltips for specific elements
     */
    setupCustomTooltips() {
        // Add tooltip attributes to common elements
        this.addTooltipAttributes();
        
        // Setup help buttons
        this.setupHelpButtons();
    }

    /**
     * Add tooltip attributes to common UI elements
     */
    addTooltipAttributes() {
        // Add to navigation links
        const navLinks = document.querySelectorAll('nav a, .navbar a');
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            const tooltipId = this.getTooltipIdFromHref(href);
            
            if (tooltipId) {
                link.setAttribute('data-tooltip-id', tooltipId);
                link.classList.add('tooltip-enabled');
            }
        });

        // Add to action buttons
        const actionButtons = document.querySelectorAll('.btn[onclick], .btn[data-action]');
        actionButtons.forEach(btn => {
            const action = btn.getAttribute('data-action') || this.extractActionFromOnclick(btn.getAttribute('onclick'));
            const tooltipId = this.getTooltipIdFromAction(action);
            
            if (tooltipId) {
                btn.setAttribute('data-tooltip-id', tooltipId);
                btn.classList.add('tooltip-enabled');
            }
        });

        // Add to form fields
        const formFields = document.querySelectorAll('input[type="text"], input[type="email"], select, textarea');
        formFields.forEach(field => {
            const name = field.getAttribute('name');
            const tooltipId = this.getTooltipIdFromFieldName(name);
            
            if (tooltipId) {
                field.setAttribute('data-tooltip-id', tooltipId);
                field.classList.add('tooltip-enabled');
            }
        });
    }

    /**
     * Setup help buttons for contextual help
     */
    setupHelpButtons() {
        // Add help buttons to forms and sections
        const sections = document.querySelectorAll('.card-header, .section-header, .form-section');
        sections.forEach(section => {
            const title = section.querySelector('h1, h2, h3, h4, h5, h6');
            if (title) {
                const tooltipId = this.getTooltipIdFromTitle(title.textContent);
                if (tooltipId) {
                    const helpBtn = document.createElement('button');
                    helpBtn.className = 'btn btn-sm btn-outline-info help-btn ms-2';
                    helpBtn.innerHTML = '<i class="bi bi-question-circle"></i>';
                    helpBtn.setAttribute('data-tooltip-id', tooltipId);
                    helpBtn.title = 'Get help for this section';
                    title.appendChild(helpBtn);
                }
            }
        });
    }

    /**
     * Get tooltip ID from element
     */
    getTooltipId(element) {
        return element.getAttribute('data-tooltip-id') || 
               element.dataset.tooltipId ||
               this.getTooltipIdFromHref(element.getAttribute('href')) ||
               this.getTooltipIdFromAction(element.getAttribute('data-action'));
    }

    /**
     * Get tooltip ID from href
     */
    getTooltipIdFromHref(href) {
        if (!href) return null;
        
        const mapping = {
            'add_client.php': 'add-client',
            'edit_client.php': 'edit-client',
            'clients.php': 'client-list',
            'add_device.php': 'add-device',
            'edit_device.php': 'edit-device',
            'devices.php': 'device-monitoring',
            'add_network.php': 'add-network',
            'edit_network.php': 'edit-network',
            'networks.php': 'network-monitoring',
            'add_internet_package.php': 'add-internet-package',
            'add_tv_package.php': 'add-tv-package',
            'edit_service.php': 'edit-service',
            'add_invoice.php': 'add-invoice',
            'add_payment.php': 'add-payment',
            'dhcp_clients.php': 'dhcp-clients',
            'dhcp_networks.php': 'dhcp-networks',
            'cacti_integration.php': 'cacti-integration',
            'snmp_monitoring.php': 'snmp-monitoring',
            'interface_monitoring.php': 'interface-monitoring',
            'user_management.php': 'user-management',
            'access_level_manager.php': 'access-level-manager',
            'system_status.php': 'system-status',
            'dashboard_editor.php': 'theme-editor'
        };

        for (const [key, value] of Object.entries(mapping)) {
            if (href.includes(key)) {
                return value;
            }
        }
        
        return null;
    }

    /**
     * Get tooltip ID from action
     */
    getTooltipIdFromAction(action) {
        if (!action) return null;
        
        const mapping = {
            'add': 'add-client',
            'edit': 'edit-client',
            'delete': 'delete-client',
            'save': 'save-changes',
            'cancel': 'cancel-operation',
            'search': 'search-function',
            'filter': 'filter-function',
            'export': 'export-data',
            'import': 'import-data'
        };

        return mapping[action.toLowerCase()] || null;
    }

    /**
     * Get tooltip ID from field name
     */
    getTooltipIdFromFieldName(name) {
        if (!name) return null;
        
        const mapping = {
            'client_name': 'client-name-field',
            'email': 'email-field',
            'phone': 'phone-field',
            'ip_address': 'ip-address-field',
            'subnet': 'subnet-field',
            'gateway': 'gateway-field',
            'username': 'username-field',
            'password': 'password-field',
            'role': 'role-field'
        };

        return mapping[name] || null;
    }

    /**
     * Get tooltip ID from title
     */
    getTooltipIdFromTitle(title) {
        if (!title) return null;
        
        const mapping = {
            'client': 'client-management',
            'device': 'device-management',
            'network': 'network-management',
            'service': 'service-management',
            'invoice': 'invoice-management',
            'payment': 'payment-management',
            'dhcp': 'dhcp-management',
            'monitoring': 'network-monitoring',
            'user': 'user-management',
            'system': 'system-administration',
            'theme': 'theme-editor'
        };

        const lowerTitle = title.toLowerCase();
        for (const [key, value] of Object.entries(mapping)) {
            if (lowerTitle.includes(key)) {
                return value;
            }
        }
        
        return null;
    }

    /**
     * Extract action from onclick attribute
     */
    extractActionFromOnclick(onclick) {
        if (!onclick) return null;
        
        const actionMatch = onclick.match(/(\w+)\(/);
        return actionMatch ? actionMatch[1] : null;
    }

    /**
     * Show tooltip for element
     */
    showTooltip(element, tooltipId) {
        const tooltipData = this.tooltipData[tooltipId];
        if (!tooltipData) return;

        this.hideTooltip();

        const tooltip = document.createElement('div');
        tooltip.className = 'slms-tooltip';
        tooltip.innerHTML = this.createTooltipContent(tooltipData);

        document.body.appendChild(tooltip);

        // Position tooltip
        const rect = element.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        
        let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        let top = rect.top - tooltipRect.height - 10;

        // Adjust if tooltip goes off screen
        if (left < 10) left = 10;
        if (left + tooltipRect.width > window.innerWidth - 10) {
            left = window.innerWidth - tooltipRect.width - 10;
        }
        if (top < 10) {
            top = rect.bottom + 10;
        }

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';

        this.activeTooltip = tooltip;

        // Add show animation
        setTimeout(() => {
            tooltip.classList.add('show');
        }, 10);
    }

    /**
     * Hide active tooltip
     */
    hideTooltip() {
        if (this.activeTooltip) {
            this.activeTooltip.classList.remove('show');
            setTimeout(() => {
                if (this.activeTooltip && this.activeTooltip.parentNode) {
                    this.activeTooltip.parentNode.removeChild(this.activeTooltip);
                }
                this.activeTooltip = null;
            }, 200);
        }
    }

    /**
     * Create tooltip content HTML
     */
    createTooltipContent(data) {
        return `
            <div class="tooltip-header">
                <h6 class="tooltip-title">${data.title}</h6>
                <span class="tooltip-category">${data.category}</span>
            </div>
            <div class="tooltip-content">
                <p>${data.content}</p>
                ${data.examples ? `
                    <div class="tooltip-examples">
                        <strong>Examples:</strong>
                        <ul>
                            ${data.examples.map(example => `<li>${example}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            </div>
            <div class="tooltip-footer">
                <button class="btn btn-sm btn-outline-primary help-btn" data-tooltip-id="${data.id || ''}">
                    <i class="bi bi-question-circle"></i> More Help
                </button>
            </div>
        `;
    }

    /**
     * Show help modal with detailed information
     */
    showHelpModal(tooltipId) {
        const tooltipData = this.tooltipData[tooltipId];
        if (!tooltipData) return;

        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-question-circle text-primary"></i>
                            ${tooltipData.title}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="help-content">
                            <div class="help-description">
                                <h6>Description</h6>
                                <p>${tooltipData.content}</p>
                            </div>
                            
                            ${tooltipData.examples ? `
                                <div class="help-examples">
                                    <h6>Examples</h6>
                                    <ul>
                                        ${tooltipData.examples.map(example => `<li>${example}</li>`).join('')}
                                    </ul>
                                </div>
                            ` : ''}
                            
                            ${tooltipData.steps ? `
                                <div class="help-steps">
                                    <h6>Step-by-Step Guide</h6>
                                    <ol>
                                        ${tooltipData.steps.map(step => `<li>${step}</li>`).join('')}
                                    </ol>
                                </div>
                            ` : ''}
                            
                            ${tooltipData.tips ? `
                                <div class="help-tips">
                                    <h6>Tips & Best Practices</h6>
                                    <ul>
                                        ${tooltipData.tips.map(tip => `<li>${tip}</li>`).join('')}
                                    </ul>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="window.open('/docs/user-guide/quick-start.md', '_blank')">
                            <i class="bi bi-book"></i> View Documentation
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        
        if (window.bootstrap && window.bootstrap.Modal) {
            const bootstrapModal = new bootstrap.Modal(modal);
            bootstrapModal.show();
            
            modal.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modal);
            });
        } else {
            // Fallback for when Bootstrap is not available
            modal.style.display = 'block';
            modal.querySelector('.btn-close, .btn-secondary').addEventListener('click', () => {
                document.body.removeChild(modal);
            });
        }
    }

    /**
     * Add tooltip to specific element
     */
    addTooltip(element, tooltipId, customData = null) {
        if (customData) {
            this.tooltipData[tooltipId] = customData;
        }
        
        element.setAttribute('data-tooltip-id', tooltipId);
        element.classList.add('tooltip-enabled');
    }

    /**
     * Remove tooltip from element
     */
    removeTooltip(element) {
        element.removeAttribute('data-tooltip-id');
        element.classList.remove('tooltip-enabled');
    }

    /**
     * Update tooltip data
     */
    updateTooltipData(tooltipId, data) {
        this.tooltipData[tooltipId] = { ...this.tooltipData[tooltipId], ...data };
    }

    /**
     * Get tooltip data for specific ID
     */
    getTooltipData(tooltipId) {
        return this.tooltipData[tooltipId] || null;
    }

    /**
     * Enable/disable tooltip system
     */
    setEnabled(enabled) {
        if (enabled) {
            document.body.classList.add('tooltips-enabled');
        } else {
            document.body.classList.remove('tooltips-enabled');
            this.hideTooltip();
        }
    }

    /**
     * Check if tooltip system is enabled
     */
    isEnabled() {
        return document.body.classList.contains('tooltips-enabled');
    }
}

// Initialize tooltip system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.slmsTooltips = new SLMSTooltipSystem();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SLMSTooltipSystem;
} 