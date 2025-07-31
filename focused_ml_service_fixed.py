#!/usr/bin/env python3
"""
Fixed Focused ML Service for AI Assistant
Handles module modification, database updates, network analysis, and GUI adaptation
"""

import os
import sys
import json
import logging
from typing import Dict, List, Any, Optional
from datetime import datetime
import traceback

# Add the virtual environment to the path
sys.path.insert(0, '/var/www/html/ml_env/venv/lib/python3.13/site-packages')

try:
    from flask import Flask, request, jsonify
    from transformers import pipeline, AutoTokenizer, AutoModel
    from sentence_transformers import SentenceTransformer
    import torch
    import nltk
    import spacy
    from fuzzywuzzy import fuzz
    import re
    import netaddr
except ImportError as e:
    print(f"Import error: {e}")
    sys.exit(1)

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = Flask(__name__)

class FocusedMLService:
    def __init__(self):
        self.models = {}
        self.nlp = None
        self.embedding_model = None
        self.text_generator = None
        self.initialize_models()
    
    def initialize_models(self):
        """Initialize ML models for specific use cases"""
        try:
            logger.info("Initializing ML models...")
            
            # Load spaCy for text processing
            self.nlp = spacy.load("en_core_web_sm")
            
            # Load sentence transformer for embeddings
            self.embedding_model = SentenceTransformer('all-MiniLM-L6-v2')
            
            # Load text generation model
            self.text_generator = pipeline("text-generation", model="microsoft/DialoGPT-medium")
            
            logger.info("ML models initialized successfully")
            
        except Exception as e:
            logger.error(f"Error initializing models: {e}")
            logger.error(traceback.format_exc())
    
    def analyze_text(self, text: str) -> Dict[str, Any]:
        """Analyze text for entities, sentiment, and patterns"""
        try:
            doc = self.nlp(text)
            
            # Extract entities
            entities = [(ent.text, ent.label_) for ent in doc.ents]
            
            # Extract MAC addresses
            mac_pattern = r'([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})'
            mac_addresses = re.findall(mac_pattern, text)
            
            # Extract IP addresses
            ip_pattern = r'\b(?:\d{1,3}\.){3}\d{1,3}\b'
            ip_addresses = re.findall(ip_pattern, text)
            
            return {
                "entities": entities,
                "mac_addresses": [''.join(mac) for mac in mac_addresses],
                "ip_addresses": ip_addresses,
                "sentiment": "neutral",  # Placeholder
                "tokens": len(doc)
            }
        except Exception as e:
            logger.error(f"Error analyzing text: {e}")
            return {"error": str(e)}
    
    def generate_code(self, prompt: str) -> str:
        """Generate code based on user request"""
        try:
            response = self.text_generator(prompt, max_length=200, num_return_sequences=1)
            return response[0]['generated_text']
        except Exception as e:
            logger.error(f"Error generating code: {e}")
            return f"# Error generating code: {e}"
    
    def find_similar_text(self, query: str, texts: List[str]) -> List[Dict[str, Any]]:
        """Find similar texts using embeddings"""
        try:
            query_embedding = self.embedding_model.encode(query)
            text_embeddings = self.embedding_model.encode(texts)
            
            similarities = []
            for i, text in enumerate(texts):
                similarity = torch.cosine_similarity(
                    torch.tensor(query_embedding).unsqueeze(0),
                    torch.tensor(text_embeddings[i]).unsqueeze(0)
                ).item()
                similarities.append({
                    "text": text,
                    "similarity": similarity,
                    "index": i
                })
            
            return sorted(similarities, key=lambda x: x["similarity"], reverse=True)
        except Exception as e:
            logger.error(f"Error finding similar text: {e}")
            return []
    
    def analyze_network_issues(self, log_data: str) -> List[Dict[str, Any]]:
        """Analyze network logs for issues (LibreNMS style)"""
        try:
            issues = []
            
            # Common network issue patterns
            patterns = {
                "high_latency": r"latency.*>.*\d+ms",
                "packet_loss": r"packet.*loss.*>.*\d+%",
                "interface_down": r"interface.*down|link.*down",
                "authentication_failure": r"auth.*fail|authentication.*failed",
                "dhcp_timeout": r"dhcp.*timeout|dhcp.*failed",
                "dns_error": r"dns.*error|dns.*failed"
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
        except Exception as e:
            logger.error(f"Error analyzing network issues: {e}")
            return []
    
    def suggest_gui_improvements(self, user_behavior: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """Suggest GUI improvements based on user behavior"""
        try:
            suggestions = []
            
            # Analyze click patterns
            click_counts = {}
            for behavior in user_behavior:
                element_id = behavior.get("element_id", "unknown")
                click_counts[element_id] = click_counts.get(element_id, 0) + 1
            
            # Suggest improvements based on patterns
            for element_id, count in click_counts.items():
                if count > 5:
                    suggestions.append({
                        "type": "shortcut",
                        "element_id": element_id,
                        "description": f"Add keyboard shortcut for {element_id} (clicked {count} times)",
                        "priority": "high" if count > 10 else "medium"
                    })
            
            return suggestions
        except Exception as e:
            logger.error(f"Error suggesting GUI improvements: {e}")
            return []

# Initialize the service
ml_service = FocusedMLService()

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        "status": "healthy",
        "timestamp": datetime.now().isoformat(),
        "models_loaded": bool(ml_service.nlp and ml_service.embedding_model)
    })

@app.route('/analyze', methods=['POST'])
def analyze_text():
    """Analyze text for entities and patterns"""
    try:
        data = request.get_json()
        text = data.get('text', '')
        
        if not text:
            return jsonify({"error": "No text provided"}), 400
        
        result = ml_service.analyze_text(text)
        return jsonify(result)
    except Exception as e:
        logger.error(f"Error in analyze endpoint: {e}")
        return jsonify({"error": str(e)}), 500

@app.route('/generate', methods=['POST'])
def generate_code():
    """Generate code based on prompt"""
    try:
        data = request.get_json()
        prompt = data.get('prompt', '')
        
        if not prompt:
            return jsonify({"error": "No prompt provided"}), 400
        
        result = ml_service.generate_code(prompt)
        return jsonify({"generated_code": result})
    except Exception as e:
        logger.error(f"Error in generate endpoint: {e}")
        return jsonify({"error": str(e)}), 500

@app.route('/network_analysis', methods=['POST'])
def analyze_network():
    """Analyze network logs for issues"""
    try:
        data = request.get_json()
        log_data = data.get('log_data', '')
        
        if not log_data:
            return jsonify({"error": "No log data provided"}), 400
        
        issues = ml_service.analyze_network_issues(log_data)
        return jsonify({"issues": issues})
    except Exception as e:
        logger.error(f"Error in network analysis endpoint: {e}")
        return jsonify({"error": str(e)}), 500

@app.route('/gui_suggestions', methods=['POST'])
def suggest_gui_improvements():
    """Suggest GUI improvements based on user behavior"""
    try:
        data = request.get_json()
        user_behavior = data.get('user_behavior', [])
        
        suggestions = ml_service.suggest_gui_improvements(user_behavior)
        return jsonify({"suggestions": suggestions})
    except Exception as e:
        logger.error(f"Error in GUI suggestions endpoint: {e}")
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    logger.info("Starting Focused ML Service...")
    app.run(host='0.0.0.0', port=8000, debug=False)
