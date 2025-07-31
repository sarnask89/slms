/**
 * Simple Stats.js implementation for performance monitoring
 * Based on mrdoob's Stats.js but simplified for our needs
 */

class Stats {
    constructor() {
        this.mode = 0;
        this.beginTime = (performance || Date).now();
        this.prevTime = this.beginTime;
        this.frames = 0;
        this.fps = 0;
        this.ms = 0;
        
        this.dom = document.createElement('div');
        this.dom.style.cssText = 'position:fixed;top:0;left:0;cursor:pointer;opacity:0.9;z-index:10000';
        this.dom.addEventListener('click', (event) => {
            event.preventDefault();
            this.showPanel(++this.mode % this.dom.children.length);
        });
        
        this.addPanel(this.createPanel('FPS', '#0ff', '#002'));
        this.addPanel(this.createPanel('MS', '#0f0', '#020'));
        this.addPanel(this.createPanel('MB', '#f08', '#201'));
        
        this.showPanel(0);
    }
    
    createPanel(name, fg, bg) {
        const canvas = document.createElement('canvas');
        canvas.width = 80;
        canvas.height = 48;
        canvas.style.cssText = 'width:80px;height:48px';
        
        const context = canvas.getContext('2d');
        context.font = 'bold 9px Helvetica,Arial,sans-serif';
        context.fillStyle = bg;
        context.fillRect(0, 0, 80, 48);
        
        context.fillStyle = fg;
        context.fillText(name, 3, 12);
        
        return {
            canvas: canvas,
            context: context,
            name: name,
            fg: fg,
            bg: bg,
            min: Infinity,
            max: 0
        };
    }
    
    addPanel(panel) {
        this.dom.appendChild(panel.canvas);
        return panel;
    }
    
    showPanel(id) {
        for (let i = 0; i < this.dom.children.length; i++) {
            this.dom.children[i].style.display = i === id ? 'block' : 'none';
        }
        this.mode = id;
    }
    
    begin() {
        this.beginTime = (performance || Date).now();
    }
    
    end() {
        this.frames++;
        const time = (performance || Date).now();
        
        this.ms = time - this.beginTime;
        this.fps = Math.round(1000 / this.ms);
        
        this.updatePanel(0, this.fps, 100);
        this.updatePanel(1, this.ms, 20);
        
        this.prevTime = time;
    }
    
    update() {
        this.beginTime = this.end();
    }
    
    updatePanel(panelId, value, maxValue) {
        const panel = this.dom.children[panelId];
        const context = panel.getContext('2d');
        
        context.fillStyle = panel.bg;
        context.fillRect(0, 0, 80, 48);
        
        context.fillStyle = panel.fg;
        context.fillText(Math.round(value) + ' ' + panel.name, 3, 12);
        
        context.fillRect(3, 15, 74 * (value / maxValue), 30);
    }
    
    getFPS() {
        return this.fps;
    }
    
    getMS() {
        return this.ms;
    }
}
