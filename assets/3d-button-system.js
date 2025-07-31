/**
 * SLMS 3D Button System
 * Advanced 3D button framework with interactive effects
 * Based on Unity VR Menu and Godot 3D UI patterns
 */

class Button3D {
    constructor(options = {}) {
        this.id = options.id || 'btn-' + Math.random().toString(36).substr(2, 9);
        this.text = options.text || 'Button';
        this.icon = options.icon || '';
        this.onClick = options.onClick || (() => {});
        this.position = options.position || { x: 0, y: 0, z: 0 };
        this.size = options.size || { width: 200, height: 60, depth: 10 };
        this.color = options.color || '#00d4ff';
        this.hoverColor = options.hoverColor || '#00ff88';
        this.activeColor = options.activeColor || '#ff6b35';
        this.isHovered = false;
        this.isPressed = false;
        this.animationSpeed = options.animationSpeed || 0.3;
        
        this.element = null;
        this.createButton();
    }

    createButton() {
        // Create button container
        this.element = document.createElement('div');
        this.element.className = 'btn-3d-advanced';
        this.element.id = this.id;
        this.element.style.cssText = `
            position: relative;
            width: ${this.size.width}px;
            height: ${this.size.height}px;
            background: linear-gradient(145deg, #2a2a2a, #1a1a1a);
            border: 2px solid ${this.color};
            border-radius: 15px;
            color: #ffffff;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all ${this.animationSpeed}s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
            perspective: 1000px;
            box-shadow: 
                0 0 15px ${this.color}40,
                inset 0 1px 0 rgba(255, 255, 255, 0.1),
                0 4px 8px rgba(0, 0, 0, 0.3);
            user-select: none;
            overflow: hidden;
        `;

        // Create button content
        const content = document.createElement('div');
        content.className = 'btn-content';
        content.style.cssText = `
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            transform: translateZ(5px);
            transition: transform ${this.animationSpeed}s ease;
        `;

        // Add icon if provided
        if (this.icon) {
            const iconElement = document.createElement('i');
            iconElement.className = `bi ${this.icon}`;
            iconElement.style.cssText = `
                font-size: 24px;
                margin-bottom: 8px;
                color: ${this.color};
                transition: color ${this.animationSpeed}s ease;
            `;
            content.appendChild(iconElement);
        }

        // Add text
        const textElement = document.createElement('span');
        textElement.textContent = this.text;
        textElement.style.cssText = `
            font-size: 14px;
            text-align: center;
            line-height: 1.2;
        `;
        content.appendChild(textElement);

        this.element.appendChild(content);

        // Add 3D effect layers
        this.add3DEffects();

        // Add event listeners
        this.addEventListeners();
    }

    add3DEffects() {
        // Create 3D depth effect
        const depthLayer = document.createElement('div');
        depthLayer.className = 'btn-depth';
        depthLayer.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(145deg, #1a1a1a, #0a0a0a);
            border-radius: 13px;
            transform: translateZ(-5px);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5);
        `;
        this.element.appendChild(depthLayer);

        // Create highlight effect
        const highlight = document.createElement('div');
        highlight.className = 'btn-highlight';
        highlight.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 50%;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.1), transparent);
            border-radius: 15px 15px 0 0;
            pointer-events: none;
            transition: opacity ${this.animationSpeed}s ease;
        `;
        this.element.appendChild(highlight);

        // Create glow effect
        const glow = document.createElement('div');
        glow.className = 'btn-glow';
        glow.style.cssText = `
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: ${this.color};
            border-radius: 17px;
            opacity: 0;
            filter: blur(10px);
            transition: opacity ${this.animationSpeed}s ease;
            pointer-events: none;
            z-index: -1;
        `;
        this.element.appendChild(glow);
    }

    addEventListeners() {
        // Mouse events
        this.element.addEventListener('mouseenter', () => this.onHoverStart());
        this.element.addEventListener('mouseleave', () => this.onHoverEnd());
        this.element.addEventListener('mousedown', () => this.onPressStart());
        this.element.addEventListener('mouseup', () => this.onPressEnd());
        this.element.addEventListener('click', (e) => this.onClick(e));

        // Touch events for mobile
        this.element.addEventListener('touchstart', (e) => {
            e.preventDefault();
            this.onPressStart();
        });
        this.element.addEventListener('touchend', (e) => {
            e.preventDefault();
            this.onPressEnd();
            this.onClick(e);
        });

        // Keyboard events
        this.element.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.onPressStart();
            }
        });
        this.element.addEventListener('keyup', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.onPressEnd();
                this.onClick(e);
            }
        });

        // Make button focusable
        this.element.setAttribute('tabindex', '0');
    }

    onHoverStart() {
        this.isHovered = true;
        this.element.style.transform = 'translateY(-5px) scale(1.05) rotateX(5deg)';
        this.element.style.borderColor = this.hoverColor;
        this.element.style.boxShadow = `
            0 10px 25px ${this.hoverColor}60,
            inset 0 1px 0 rgba(255, 255, 255, 0.2),
            0 8px 16px rgba(0, 0, 0, 0.4)
        `;
        
        // Animate glow
        const glow = this.element.querySelector('.btn-glow');
        if (glow) {
            glow.style.opacity = '0.6';
            glow.style.background = this.hoverColor;
        }

        // Animate content
        const content = this.element.querySelector('.btn-content');
        if (content) {
            content.style.transform = 'translateZ(10px)';
        }

        // Animate icon color
        const icon = this.element.querySelector('i');
        if (icon) {
            icon.style.color = this.hoverColor;
        }
    }

    onHoverEnd() {
        this.isHovered = false;
        this.element.style.transform = 'translateY(0) scale(1) rotateX(0deg)';
        this.element.style.borderColor = this.color;
        this.element.style.boxShadow = `
            0 0 15px ${this.color}40,
            inset 0 1px 0 rgba(255, 255, 255, 0.1),
            0 4px 8px rgba(0, 0, 0, 0.3)
        `;
        
        // Reset glow
        const glow = this.element.querySelector('.btn-glow');
        if (glow) {
            glow.style.opacity = '0';
        }

        // Reset content
        const content = this.element.querySelector('.btn-content');
        if (content) {
            content.style.transform = 'translateZ(5px)';
        }

        // Reset icon color
        const icon = this.element.querySelector('i');
        if (icon) {
            icon.style.color = this.color;
        }
    }

    onPressStart() {
        this.isPressed = true;
        this.element.style.transform = 'translateY(-2px) scale(0.98) rotateX(10deg)';
        this.element.style.borderColor = this.activeColor;
        this.element.style.boxShadow = `
            0 5px 15px ${this.activeColor}80,
            inset 0 2px 4px rgba(0, 0, 0, 0.3),
            0 2px 4px rgba(0, 0, 0, 0.5)
        `;
        
        // Animate glow
        const glow = this.element.querySelector('.btn-glow');
        if (glow) {
            glow.style.opacity = '0.8';
            glow.style.background = this.activeColor;
        }

        // Animate content
        const content = this.element.querySelector('.btn-content');
        if (content) {
            content.style.transform = 'translateZ(2px)';
        }
    }

    onPressEnd() {
        this.isPressed = false;
        if (this.isHovered) {
            this.onHoverStart();
        } else {
            this.onHoverEnd();
        }
    }

    // Public methods
    setText(text) {
        this.text = text;
        const textElement = this.element.querySelector('span');
        if (textElement) {
            textElement.textContent = text;
        }
    }

    setIcon(icon) {
        this.icon = icon;
        const iconElement = this.element.querySelector('i');
        if (iconElement) {
            iconElement.className = `bi ${icon}`;
        }
    }

    setColor(color) {
        this.color = color;
        this.element.style.borderColor = color;
        const icon = this.element.querySelector('i');
        if (icon) {
            icon.style.color = color;
        }
    }

    setEnabled(enabled) {
        this.element.style.opacity = enabled ? '1' : '0.5';
        this.element.style.pointerEvents = enabled ? 'auto' : 'none';
    }

    destroy() {
        if (this.element && this.element.parentNode) {
            this.element.parentNode.removeChild(this.element);
        }
    }
}

// Button Grid System
class ButtonGrid3D {
    constructor(container, options = {}) {
        this.container = container;
        this.buttons = [];
        this.columns = options.columns || 2;
        this.gap = options.gap || 20;
        this.autoArrange = options.autoArrange !== false;
        
        this.createGrid();
    }

    createGrid() {
        this.container.style.cssText = `
            display: grid;
            grid-template-columns: repeat(${this.columns}, 1fr);
            gap: ${this.gap}px;
            width: 100%;
        `;
    }

    addButton(buttonOptions) {
        const button = new Button3D(buttonOptions);
        this.container.appendChild(button.element);
        this.buttons.push(button);
        return button;
    }

    removeButton(button) {
        const index = this.buttons.indexOf(button);
        if (index > -1) {
            this.buttons.splice(index, 1);
            button.destroy();
        }
    }

    clear() {
        this.buttons.forEach(button => button.destroy());
        this.buttons = [];
    }

    setColumns(columns) {
        this.columns = columns;
        this.container.style.gridTemplateColumns = `repeat(${columns}, 1fr)`;
    }
}

// Menu Panel System
class MenuPanel3D {
    constructor(options = {}) {
        this.id = options.id || 'menu-panel-' + Math.random().toString(36).substr(2, 9);
        this.title = options.title || 'Menu Panel';
        this.position = options.position || 'center';
        this.size = options.size || { width: 500, height: 'auto' };
        this.buttons = [];
        
        this.element = null;
        this.createPanel();
    }

    createPanel() {
        this.element = document.createElement('div');
        this.element.className = 'menu-panel-3d-advanced';
        this.element.id = this.id;
        this.element.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(15px);
            border: 2px solid #00d4ff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.6);
            min-width: ${this.size.width}px;
            max-width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
        `;

        // Create title
        const title = document.createElement('div');
        title.className = 'menu-title';
        title.textContent = this.title;
        title.style.cssText = `
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: #00d4ff;
            margin-bottom: 30px;
            text-shadow: 0 0 15px #00d4ff;
        `;
        this.element.appendChild(title);

        // Create button container
        this.buttonContainer = document.createElement('div');
        this.buttonContainer.className = 'button-container';
        this.element.appendChild(this.buttonContainer);
    }

    addButton(buttonOptions) {
        const button = new Button3D(buttonOptions);
        this.buttonContainer.appendChild(button.element);
        this.buttons.push(button);
        return button;
    }

    show() {
        document.body.appendChild(this.element);
        this.element.style.opacity = '0';
        this.element.style.transform = 'translate(-50%, -50%) scale(0.8)';
        
        setTimeout(() => {
            this.element.style.opacity = '1';
            this.element.style.transform = 'translate(-50%, -50%) scale(1)';
        }, 10);
    }

    hide() {
        this.element.style.opacity = '0';
        this.element.style.transform = 'translate(-50%, -50%) scale(0.8)';
        
        setTimeout(() => {
            if (this.element.parentNode) {
                this.element.parentNode.removeChild(this.element);
            }
        }, 300);
    }

    destroy() {
        this.hide();
    }
}

// Global utility functions
window.Button3D = Button3D;
window.ButtonGrid3D = ButtonGrid3D;
window.MenuPanel3D = MenuPanel3D;

// Auto-initialize button system
document.addEventListener('DOMContentLoaded', () => {
    // Add global styles
    const style = document.createElement('style');
    style.textContent = `
        .btn-3d-advanced:focus {
            outline: none;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.8) !important;
        }
        
        .btn-3d-advanced:focus-visible {
            outline: 2px solid #00d4ff;
            outline-offset: 2px;
        }
        
        @media (max-width: 768px) {
            .btn-3d-advanced {
                width: 100% !important;
                height: 80px !important;
            }
        }
    `;
    document.head.appendChild(style);
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { Button3D, ButtonGrid3D, MenuPanel3D };
} 