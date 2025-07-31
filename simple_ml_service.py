#!/usr/bin/env python3
"""
Simple ML Service for AI Assistant
Basic functionality without heavy dependencies
"""

import json
import re
from datetime import datetime
from flask import Flask, request, jsonify

app = Flask(__name__)

class SimpleMLService:
    def __init__(self):
        self.patterns = {
            "mac_address": r'([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})',
            "ip_address": r'\b(?:\d{1,3}\.){3}\d{1,3}\b',
            "email": r'\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b',
            "url": r'https?://(?:[-\w.])+(?:[:\d]+)?(?:/(?:[\w/_.])*(?:\?(?:[\w&=%.])*)?(?:#(?:[\w.])*)?)?'
        }
    
    def analyze_text(self, text):
        """Analyze text for patterns and entities"""
        result = {
            "mac_addresses": re.findall(self.patterns["mac_address"], text),
            "ip_addresses": re.findall(self.patterns["ip_address"], text),
            "emails": re.findall(self.patterns["email"], text),
            "urls": re.findall(self.patterns["url"], text),
            "word_count": len(text.split()),
            "char_count": len(text)
        }
        return result
    
    def analyze_network_issues(self, log_data):
        """Analyze network logs for common issues"""
        issues = []
        
        # Common network issue patterns
        patterns = {
            "high_latency": r"latency.*>.*\d+ms|delay.*>.*\d+ms",
            "packet_loss": r"packet.*loss.*>.*\d+%|loss.*>.*\d+%",
            "interface_down": r"interface.*down|link.*down|port.*down",
            "authentication_failure": r"auth.*fail|authentication.*failed|login.*failed",
            "dhcp_timeout": r"dhcp.*timeout|dhcp.*failed|dhcp.*error",
            "dns_error": r"dns.*error|dns.*failed|dns.*timeout"
        }
        
        for issue_type, pattern in patterns.items():
            matches = re.findall(pattern, log_data, re.IGNORECASE)
            if matches:
                issues.append({
                    "type": issue_type,
                    "matches": matches,
                    "severity": "high" if issue_type in ["interface_down", "authentication_failure"] else "medium"
                })
        
        return issues
    
    def suggest_gui_improvements(self, user_behavior):
        """Suggest GUI improvements based on user behavior"""
        suggestions = []
        
        # Analyze click patterns
        click_counts = {}
        for behavior in user_behavior:
            element_id = behavior.get("element_id", "unknown")
            click_counts[element_id] = click_counts.get(element_id, 0) + 1
        
        # Suggest improvements
        for element_id, count in click_counts.items():
            if count > 3:
                suggestions.append({
                    "type": "shortcut",
                    "element_id": element_id,
                    "description": f"Add keyboard shortcut for {element_id} (clicked {count} times)",
                    "priority": "high" if count > 5 else "medium"
                })
        
        return suggestions

# Initialize service
ml_service = SimpleMLService()

@app.route('/health', methods=['GET'])
def health_check():
    return jsonify({
        "status": "healthy",
        "timestamp": datetime.now().isoformat(),
        "service": "simple_ml_service"
    })

@app.route('/analyze', methods=['POST'])
def analyze_text():
    try:
        data = request.get_json()
        text = data.get('text', '')
        
        if not text:
            return jsonify({"error": "No text provided"}), 400
        
        result = ml_service.analyze_text(text)
        return jsonify(result)
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/network_analysis', methods=['POST'])
def analyze_network():
    try:
        data = request.get_json()
        log_data = data.get('log_data', '')
        
        if not log_data:
            return jsonify({"error": "No log data provided"}), 400
        
        issues = ml_service.analyze_network_issues(log_data)
        return jsonify({"issues": issues})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/gui_suggestions', methods=['POST'])
def suggest_gui_improvements():
    try:
        data = request.get_json()
        user_behavior = data.get('user_behavior', [])
        
        suggestions = ml_service.suggest_gui_improvements(user_behavior)
        return jsonify({"suggestions": suggestions})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    print("Starting Simple ML Service on port 8000...")
    app.run(host='0.0.0.0', port=8000, debug=False)
