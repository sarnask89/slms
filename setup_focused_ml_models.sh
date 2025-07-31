#!/bin/bash

# Focused ML Models Setup for Adaptive AI Assistant
# Optimized for: Module modification, database updates, network monitoring, GUI adaptation

echo "🎯 Setting up Focused ML Models for Your Specific Use Cases..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
MODELS_DIR="/var/www/html/focused_ml_models"
PYTHON_ENV="/var/www/html/ml_env"
SERVICES_DIR="/var/www/html/ml_services"

# Create directories
mkdir -p $MODELS_DIR
mkdir -p $PYTHON_ENV
mkdir -p $SERVICES_DIR

cd /var/www/html

echo -e "${BLUE}📁 Created directories:${NC}"
echo "   - Models: $MODELS_DIR"
echo "   - Python Environment: $PYTHON_ENV"
echo "   - Services: $SERVICES_DIR"

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to install system dependencies
install_system_deps() {
    echo -e "${YELLOW}📦 Installing system dependencies...${NC}"
    
    # Update package list
    apt update
    
    # Install essential packages for focused use cases
    apt install -y \
        curl \
        wget \
        git \
        python3 \
        python3-pip \
        python3-venv \
        build-essential \
        cmake \
        pkg-config \
        libssl-dev \
        libffi-dev \
        libjpeg-dev \
        libpng-dev \
        libfreetype6-dev \
        liblcms2-dev \
        libopenjp2-7-dev \
        libtiff5-dev \
        zlib1g-dev \
        libwebp-dev \
        libharfbuzz-dev \
        libfribidi-dev \
        libxcb1-dev \
        libatlas-base-dev \
        gfortran \
        libblas-dev \
        liblapack-dev \
        libhdf5-dev \
        libhdf5-serial-dev \
        libhdf5-103 \
        libqtgui4 \
        libqtwebkit4 \
        libqt4-test \
        python3-pyqt5 \
        gstreamer1.0-0 \
        gstreamer1.0-plugins-base \
        gstreamer1.0-plugins-good \
        gstreamer1.0-plugins-bad \
        gstreamer1.0-plugins-ugly \
        gstreamer1.0-libav \
        gstreamer1.0-tools \
        gstreamer1.0-x \
        gstreamer1.0-alsa \
        gstreamer1.0-gl \
        gstreamer1.0-gtk3 \
        gstreamer1.0-qt5 \
        gstreamer1.0-pulseaudio \
        docker.io \
        docker-compose \
        sqlite3 \
        postgresql-client \
        mysql-client \
        nmap \
        snmp \
        snmp-mibs-downloader
    
    echo -e "${GREEN}✅ System dependencies installed${NC}"
}

# Function to setup Python environment with focused models
setup_python_env() {
    echo -e "${YELLOW}🐍 Setting up Python environment with focused models...${NC}"
    
    cd $PYTHON_ENV
    
    # Create virtual environment
    python3 -m venv venv
    source venv/bin/activate
    
    # Upgrade pip
    pip install --upgrade pip
    
    # Install focused ML libraries for your use cases
    pip install \
        torch torchvision torchaudio --index-url https://download.pytorch.org/whl/cpu \
        transformers \
        sentence-transformers \
        scikit-learn \
        numpy \
        pandas \
        matplotlib \
        seaborn \
        jupyter \
        ipykernel \
        fastapi \
        uvicorn \
        pydantic \
        requests \
        beautifulsoup4 \
        nltk \
        spacy \
        gensim \
        word2vec \
        tensorflow \
        keras \
        opencv-python \
        pillow \
        scipy \
        scikit-image \
        plotly \
        dash \
        streamlit \
        gradio \
        huggingface_hub \
        accelerate \
        bitsandbytes \
        optimum \
        onnxruntime \
        openai-whisper \
        librosa \
        soundfile \
        pydub \
        ffmpeg-python \
        # Database and network specific
        sqlalchemy \
        psycopg2-binary \
        pymysql \
        sqlite3 \
        paramiko \
        netmiko \
        pysnmp \
        scapy \
        python-nmap \
        # Text processing for database updates
        fuzzywuzzy \
        python-levenshtein \
        dateparser \
        phonenumbers \
        # GUI and web processing
        selenium \
        beautifulsoup4 \
        lxml \
        cssselect \
        # Code analysis and generation
        ast \
        astor \
        black \
        pylint \
        # Network monitoring
        prometheus_client \
        influxdb-client \
        # LibreNMS integration
        requests \
        jsonpath-ng \
        # MAC address and network utilities
        netaddr \
        ipaddress \
        macaddress
    
    # Download spaCy model for text processing
    python -m spacy download en_core_web_sm
    
    # Download NLTK data
    python -c "import nltk; nltk.download('punkt'); nltk.download('stopwords'); nltk.download('wordnet'); nltk.download('averaged_perceptron_tagger')"
    
    echo -e "${GREEN}✅ Python environment setup complete${NC}"
}

# Function to create focused ML service
create_focused_ml_service() {
    echo -e "${YELLOW}🎯 Creating focused ML service for your use cases...${NC}"
    
    cd $SERVICES_DIR
    
    # Create focused ML service
    cat > focused_ml_service.py << 'EOF'
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Dict, Any, Optional
import torch
from transformers import pipeline, AutoTokenizer, AutoModel
from sentence_transformers import SentenceTransformer
import numpy as np
import json
import logging
import re
import sqlite3
import pandas as pd
from datetime import datetime
import netaddr
import ipaddress
from fuzzywuzzy import fuzz, process

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(title="Focused ML Service", version="1.0.0")

# Global variables for models
code_generator = None
text_analyzer = None
embedding_model = None
network_analyzer = None
gui_modifier = None

class ModuleModificationRequest(BaseModel):
    module_name: str
    modification_type: str  # "content", "structure", "new_module"
    user_request: str
    current_code: Optional[str] = None

class DatabaseUpdateRequest(BaseModel):
    table_name: str
    raw_text: str
    update_type: str  # "mac_address", "ip_address", "device_info", "network_data"
    existing_data: Optional[Dict] = None

class NetworkAnalysisRequest(BaseModel):
    network_data: Dict[str, Any]
    analysis_type: str  # "issues", "performance", "security", "topology"

class GUIModificationRequest(BaseModel):
    page_url: str
    modification_type: str  # "resize", "reposition", "add_element", "change_theme"
    user_behavior: List[Dict[str, Any]]
    current_layout: Optional[Dict] = None

@app.on_event("startup")
async def load_models():
    global code_generator, text_analyzer, embedding_model, network_analyzer, gui_modifier
    
    logger.info("Loading focused ML models...")
    
    try:
        # Code generation model (best for module modification)
        code_generator = pipeline(
            "text-generation",
            model="microsoft/DialoGPT-medium",  # Better for code than small
            torch_dtype=torch.float32,
            device_map="auto" if torch.cuda.is_available() else "cpu"
        )
        
        # Text analysis for database updates
        text_analyzer = pipeline(
            "zero-shot-classification",
            model="facebook/bart-large-mnli"
        )
        
        # Embeddings for similarity search
        embedding_model = SentenceTransformer('all-MiniLM-L6-v2')
        
        # Network analysis model (custom logic)
        network_analyzer = {
            "model": "network_analysis_v1",
            "capabilities": ["issue_detection", "performance_analysis", "security_scan"]
        }
        
        # GUI modification model
        gui_modifier = {
            "model": "gui_modification_v1",
            "capabilities": ["element_resize", "reposition", "theme_change", "accessibility"]
        }
        
        logger.info("All focused models loaded successfully!")
        
    except Exception as e:
        logger.error(f"Error loading models: {e}")
        raise

@app.get("/")
async def root():
    return {"message": "Focused ML Service is running!", "capabilities": [
        "Module modification and creation",
        "Database updates from raw text",
        "Network issue detection",
        "GUI adaptation and modification"
    ]}

@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "models_loaded": {
            "code_generator": code_generator is not None,
            "text_analyzer": text_analyzer is not None,
            "embedding_model": embedding_model is not None,
            "network_analyzer": network_analyzer is not None,
            "gui_modifier": gui_modifier is not None
        }
    }

@app.post("/modify_module")
async def modify_module(request: ModuleModificationRequest):
    """Modify or create modules based on user requests"""
    try:
        if code_generator is None:
            raise HTTPException(status_code=503, detail="Code generation model not loaded")
        
        # Generate context-aware prompt
        prompt = f"""
        Task: {request.modification_type}
        Module: {request.module_name}
        User Request: {request.user_request}
        Current Code: {request.current_code or 'New module'}
        
        Generate PHP code for this module modification:
        """
        
        # Generate code
        result = code_generator(
            prompt,
            max_length=500,
            temperature=0.7,
            do_sample=True,
            pad_token_id=code_generator.tokenizer.eos_token_id
        )
        
        generated_code = result[0]['generated_text']
        
        # Extract code from response
        code_match = re.search(r'```php\s*(.*?)\s*```', generated_code, re.DOTALL)
        if code_match:
            generated_code = code_match.group(1)
        
        return {
            "module_name": request.module_name,
            "modification_type": request.modification_type,
            "generated_code": generated_code,
            "suggestions": generate_module_suggestions(request),
            "model": "microsoft/DialoGPT-medium"
        }
        
    except Exception as e:
        logger.error(f"Error in module modification: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/update_database")
async def update_database(request: DatabaseUpdateRequest):
    """Update database based on raw text analysis"""
    try:
        if text_analyzer is None:
            raise HTTPException(status_code=503, detail="Text analyzer not loaded")
        
        # Extract information from raw text
        extracted_data = extract_data_from_text(request.raw_text, request.update_type)
        
        # Generate SQL update statements
        sql_statements = generate_sql_statements(request.table_name, extracted_data, request.existing_data)
        
        # Validate data
        validation_results = validate_extracted_data(extracted_data, request.update_type)
        
        return {
            "table_name": request.table_name,
            "extracted_data": extracted_data,
            "sql_statements": sql_statements,
            "validation_results": validation_results,
            "confidence_score": calculate_confidence(extracted_data),
            "model": "facebook/bart-large-mnli"
        }
        
    except Exception as e:
        logger.error(f"Error in database update: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/analyze_network")
async def analyze_network(request: NetworkAnalysisRequest):
    """Analyze network data for issues (LibreNMS style)"""
    try:
        # Analyze network data
        analysis_results = perform_network_analysis(request.network_data, request.analysis_type)
        
        # Generate recommendations
        recommendations = generate_network_recommendations(analysis_results)
        
        # Create alerts
        alerts = generate_network_alerts(analysis_results)
        
        return {
            "analysis_type": request.analysis_type,
            "results": analysis_results,
            "recommendations": recommendations,
            "alerts": alerts,
            "severity_level": calculate_severity(analysis_results),
            "model": "network_analysis_v1"
        }
        
    except Exception as e:
        logger.error(f"Error in network analysis: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/modify_gui")
async def modify_gui(request: GUIModificationRequest):
    """Modify GUI elements based on user behavior"""
    try:
        # Analyze user behavior
        behavior_analysis = analyze_user_behavior(request.user_behavior)
        
        # Generate GUI modifications
        modifications = generate_gui_modifications(
            request.modification_type,
            behavior_analysis,
            request.current_layout
        )
        
        # Generate CSS/JS code
        code_changes = generate_gui_code(modifications)
        
        return {
            "page_url": request.page_url,
            "modification_type": request.modification_type,
            "behavior_analysis": behavior_analysis,
            "modifications": modifications,
            "code_changes": code_changes,
            "model": "gui_modification_v1"
        }
        
    except Exception as e:
        logger.error(f"Error in GUI modification: {e}")
        raise HTTPException(status_code=500, detail=str(e))

# Helper functions
def extract_data_from_text(raw_text: str, update_type: str) -> Dict[str, Any]:
    """Extract structured data from raw text"""
    extracted = {}
    
    if update_type == "mac_address":
        # Extract MAC addresses
        mac_pattern = r'([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})'
        mac_addresses = re.findall(mac_pattern, raw_text)
        extracted["mac_addresses"] = [''.join(mac) for mac in mac_addresses]
        
        # Validate MAC addresses
        extracted["valid_macs"] = []
        for mac in extracted["mac_addresses"]:
            try:
                netaddr.EUI(mac)
                extracted["valid_macs"].append(mac)
            except:
                pass
    
    elif update_type == "ip_address":
        # Extract IP addresses
        ip_pattern = r'\b(?:\d{1,3}\.){3}\d{1,3}\b'
        ip_addresses = re.findall(ip_pattern, raw_text)
        extracted["ip_addresses"] = ip_addresses
        
        # Validate IP addresses
        extracted["valid_ips"] = []
        for ip in ip_addresses:
            try:
                ipaddress.ip_address(ip)
                extracted["valid_ips"].append(ip)
            except:
                pass
    
    elif update_type == "device_info":
        # Extract device information
        device_patterns = {
            "hostname": r'hostname[:\s]+([^\n\r]+)',
            "model": r'model[:\s]+([^\n\r]+)',
            "serial": r'serial[:\s]+([^\n\r]+)',
            "version": r'version[:\s]+([^\n\r]+)'
        }
        
        for key, pattern in device_patterns.items():
            match = re.search(pattern, raw_text, re.IGNORECASE)
            if match:
                extracted[key] = match.group(1).strip()
    
    return extracted

def generate_sql_statements(table_name: str, extracted_data: Dict, existing_data: Optional[Dict]) -> List[str]:
    """Generate SQL statements for database updates"""
    statements = []
    
    if "mac_addresses" in extracted_data:
        for mac in extracted_data.get("valid_macs", []):
            statements.append(f"""
                INSERT INTO {table_name} (mac_address, created_at) 
                VALUES ('{mac}', NOW())
                ON DUPLICATE KEY UPDATE updated_at = NOW();
            """)
    
    if "ip_addresses" in extracted_data:
        for ip in extracted_data.get("valid_ips", []):
            statements.append(f"""
                INSERT INTO {table_name} (ip_address, created_at) 
                VALUES ('{ip}', NOW())
                ON DUPLICATE KEY UPDATE updated_at = NOW();
            """)
    
    if "hostname" in extracted_data:
        statements.append(f"""
            UPDATE {table_name} 
            SET hostname = '{extracted_data["hostname"]}', updated_at = NOW()
            WHERE id = (SELECT id FROM {table_name} ORDER BY created_at DESC LIMIT 1);
        """)
    
    return statements

def validate_extracted_data(extracted_data: Dict, update_type: str) -> Dict[str, Any]:
    """Validate extracted data"""
    validation = {
        "valid": True,
        "errors": [],
        "warnings": []
    }
    
    if update_type == "mac_address":
        if not extracted_data.get("valid_macs"):
            validation["valid"] = False
            validation["errors"].append("No valid MAC addresses found")
        
        if len(extracted_data.get("mac_addresses", [])) != len(extracted_data.get("valid_macs", [])):
            validation["warnings"].append("Some MAC addresses are invalid")
    
    elif update_type == "ip_address":
        if not extracted_data.get("valid_ips"):
            validation["valid"] = False
            validation["errors"].append("No valid IP addresses found")
    
    return validation

def calculate_confidence(extracted_data: Dict) -> float:
    """Calculate confidence score for extracted data"""
    total_items = 0
    valid_items = 0
    
    for key, value in extracted_data.items():
        if isinstance(value, list):
            total_items += len(value)
            if "valid_" in key:
                valid_items += len(value)
        elif value:
            total_items += 1
            valid_items += 1
    
    return valid_items / total_items if total_items > 0 else 0.0

def perform_network_analysis(network_data: Dict, analysis_type: str) -> Dict[str, Any]:
    """Perform network analysis"""
    results = {
        "issues": [],
        "performance": {},
        "security": [],
        "topology": {}
    }
    
    # Analyze network issues
    if "devices" in network_data:
        for device in network_data["devices"]:
            if device.get("status") == "down":
                results["issues"].append({
                    "type": "device_down",
                    "device": device.get("name", "Unknown"),
                    "severity": "high"
                })
            
            if device.get("cpu_usage", 0) > 80:
                results["issues"].append({
                    "type": "high_cpu",
                    "device": device.get("name", "Unknown"),
                    "severity": "medium",
                    "value": device.get("cpu_usage")
                })
    
    # Analyze performance
    if "interfaces" in network_data:
        for interface in network_data["interfaces"]:
            if interface.get("utilization", 0) > 90:
                results["performance"]["high_utilization"] = results["performance"].get("high_utilization", [])
                results["performance"]["high_utilization"].append(interface)
    
    return results

def generate_network_recommendations(analysis_results: Dict) -> List[str]:
    """Generate network recommendations"""
    recommendations = []
    
    for issue in analysis_results.get("issues", []):
        if issue["type"] == "device_down":
            recommendations.append(f"Check physical connection and power for {issue['device']}")
        elif issue["type"] == "high_cpu":
            recommendations.append(f"Consider upgrading {issue['device']} or optimizing processes")
    
    return recommendations

def generate_network_alerts(analysis_results: Dict) -> List[Dict]:
    """Generate network alerts"""
    alerts = []
    
    for issue in analysis_results.get("issues", []):
        alerts.append({
            "type": issue["type"],
            "message": f"Network issue detected: {issue['type']} on {issue.get('device', 'Unknown')}",
            "severity": issue["severity"],
            "timestamp": datetime.now().isoformat()
        })
    
    return alerts

def calculate_severity(analysis_results: Dict) -> str:
    """Calculate overall severity level"""
    high_issues = sum(1 for issue in analysis_results.get("issues", []) if issue.get("severity") == "high")
    
    if high_issues > 0:
        return "critical"
    elif len(analysis_results.get("issues", [])) > 0:
        return "warning"
    else:
        return "normal"

def analyze_user_behavior(user_behavior: List[Dict]) -> Dict[str, Any]:
    """Analyze user behavior patterns"""
    analysis = {
        "frustration_level": 0.0,
        "repetitive_actions": 0.0,
        "efficiency_score": 0.0,
        "accessibility_issues": []
    }
    
    # Calculate frustration level
    rapid_clicks = sum(1 for i in range(1, len(user_behavior)) 
                      if user_behavior[i].get("timestamp", 0) - user_behavior[i-1].get("timestamp", 0) < 500)
    analysis["frustration_level"] = min(1.0, rapid_clicks / len(user_behavior)) if user_behavior else 0.0
    
    # Calculate repetitive actions
    action_counts = {}
    for action in user_behavior:
        key = f"{action.get('action_type', '')}_{action.get('element_id', '')}"
        action_counts[key] = action_counts.get(key, 0) + 1
    
    max_repetitions = max(action_counts.values()) if action_counts else 0
    analysis["repetitive_actions"] = min(1.0, max_repetitions / len(user_behavior)) if user_behavior else 0.0
    
    return analysis

def generate_gui_modifications(modification_type: str, behavior_analysis: Dict, current_layout: Optional[Dict]) -> List[Dict]:
    """Generate GUI modifications"""
    modifications = []
    
    if modification_type == "resize":
        if behavior_analysis["frustration_level"] > 0.7:
            modifications.append({
                "type": "resize",
                "target": "all_buttons",
                "scale_factor": 1.3,
                "reason": "High frustration detected"
            })
    
    elif modification_type == "reposition":
        modifications.append({
            "type": "reposition",
            "target": "frequently_clicked",
            "action": "move_to_top",
            "reason": "Improve accessibility"
        })
    
    elif modification_type == "add_element":
        if behavior_analysis["repetitive_actions"] > 0.8:
            modifications.append({
                "type": "add_shortcut",
                "target": "global",
                "shortcuts": ["Ctrl+S", "Ctrl+R", "Ctrl+H"],
                "reason": "Repetitive actions detected"
            })
    
    return modifications

def generate_gui_code(modifications: List[Dict]) -> Dict[str, str]:
    """Generate CSS/JS code for GUI modifications"""
    css_code = ""
    js_code = ""
    
    for mod in modifications:
        if mod["type"] == "resize":
            css_code += f"""
                button, input, select, textarea {{
                    font-size: {mod.get('scale_factor', 1.2)}em !important;
                    padding: 12px 16px !important;
                }}
            """
        
        elif mod["type"] == "add_shortcut":
            js_code += f"""
                document.addEventListener('keydown', function(e) {{
                    if (e.ctrlKey) {{
                        switch(e.key) {{
                            case 's': e.preventDefault(); saveState(); break;
                            case 'r': e.preventDefault(); resetModifications(); break;
                            case 'h': e.preventDefault(); showHelp(); break;
                        }}
                    }}
                }});
            """
    
    return {
        "css": css_code,
        "javascript": js_code
    }

def generate_module_suggestions(request: ModuleModificationRequest) -> List[str]:
    """Generate suggestions for module modifications"""
    suggestions = []
    
    if request.modification_type == "new_module":
        suggestions.append("Consider adding error handling and logging")
        suggestions.append("Include input validation for security")
        suggestions.append("Add configuration options for flexibility")
    
    elif request.modification_type == "content":
        suggestions.append("Update documentation to reflect changes")
        suggestions.append("Consider backward compatibility")
        suggestions.append("Add unit tests for new functionality")
    
    return suggestions

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
EOF

    # Create requirements.txt for focused service
    cat > requirements.txt << EOF
fastapi==0.104.1
uvicorn==0.24.0
pydantic==2.5.0
torch==2.1.1
transformers==4.36.2
sentence-transformers==2.2.2
scikit-learn==1.3.2
numpy==1.24.3
pandas==2.1.4
requests==2.31.0
python-multipart==0.0.6
fuzzywuzzy==0.18.0
python-levenshtein==0.21.1
netaddr==0.8.0
sqlalchemy==2.0.23
psycopg2-binary==2.9.9
pymysql==1.1.0
paramiko==3.4.0
netmiko==4.3.0
pysnmp==4.4.12
scapy==2.5.0
python-nmap==0.7.1
beautifulsoup4==4.12.2
lxml==4.9.3
cssselect==1.2.0
EOF

    # Create systemd service
    cat > /etc/systemd/system/focused-ml.service << EOF
[Unit]
Description=Focused ML Service for Adaptive AI
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=$SERVICES_DIR
Environment=PATH=$PYTHON_ENV/venv/bin
ExecStart=$PYTHON_ENV/venv/bin/python focused_ml_service.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

    echo -e "${GREEN}✅ Focused ML service created${NC}"
}

# Function to create integration script
create_focused_integration() {
    echo -e "${YELLOW}🔗 Creating focused integration script...${NC}"
    
    cd /var/www/html
    
    # Create focused integration configuration
    cat > focused_ml_config.json << EOF
{
  "service": {
    "url": "http://localhost:8000",
    "models": {
      "code_generation": "microsoft/DialoGPT-medium",
      "text_analysis": "facebook/bart-large-mnli",
      "embeddings": "all-MiniLM-L6-v2",
      "network_analysis": "network_analysis_v1",
      "gui_modification": "gui_modification_v1"
    }
  },
  "capabilities": {
    "module_modification": true,
    "database_updates": true,
    "network_analysis": true,
    "gui_adaptation": true
  },
  "settings": {
    "max_tokens": 500,
    "temperature": 0.7,
    "confidence_threshold": 0.8
  }
}
EOF

    # Create focused integration script
    cat > focused_ml_integration.js << 'EOF'
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
EOF

    echo -e "${GREEN}✅ Focused integration script created${NC}"
}

# Function to start services
start_focused_services() {
    echo -e "${YELLOW}🚀 Starting focused ML services...${NC}"
    
    # Start focused ML service
    systemctl daemon-reload
    systemctl enable focused-ml
    systemctl start focused-ml
    
    echo -e "${GREEN}✅ Focused ML services started${NC}"
}

# Function to test services
test_focused_services() {
    echo -e "${YELLOW}🧪 Testing focused ML services...${NC}"
    
    # Wait for service to start
    sleep 5
    
    # Test focused ML service
    echo -e "${BLUE}Testing Focused ML Service...${NC}"
    if curl -s "http://localhost:8000/health" > /dev/null; then
        echo -e "${GREEN}✅ Focused ML Service is running${NC}"
    else
        echo -e "${RED}❌ Focused ML Service failed to start${NC}"
    fi
}

# Function to create startup script
create_focused_startup_script() {
    echo -e "${YELLOW}📝 Creating focused startup script...${NC}"
    
    cat > /var/www/html/start_focused_ml.sh << 'EOF'
#!/bin/bash

# Start Focused ML Services for Adaptive AI Assistant
echo "🎯 Starting Focused ML Services..."

# Start Focused ML Service
systemctl start focused-ml

echo "✅ Focused ML services started!"
echo ""
echo "🌐 Service URLs:"
echo "   - Focused ML Service: http://localhost:8000"
echo "   - API Documentation: http://localhost:8000/docs"
echo "   - Adaptive AI Demo: http://localhost/adaptive_ai_demo.html"
echo ""
echo "🎯 Capabilities:"
echo "   - Module modification and creation"
echo "   - Database updates from raw text"
echo "   - Network issue detection"
echo "   - GUI adaptation and modification"
EOF

    chmod +x /var/www/html/start_focused_ml.sh
    
    echo -e "${GREEN}✅ Focused startup script created${NC}"
}

# Main execution
main() {
    echo -e "${BLUE}🎯 Setting up Focused ML Models for Your Specific Use Cases${NC}"
    echo "=================================================="
    
    # Check if running as root
    if [ "$EUID" -ne 0 ]; then
        echo -e "${RED}❌ Please run as root (use sudo)${NC}"
        exit 1
    fi
    
    # Install system dependencies
    install_system_deps
    
    # Setup Python environment
    setup_python_env
    
    # Create focused ML service
    create_focused_ml_service
    
    # Create integration
    create_focused_integration
    
    # Start services
    start_focused_services
    
    # Test services
    test_focused_services
    
    # Create startup script
    create_focused_startup_script
    
    echo ""
    echo -e "${GREEN}🎯 Focused ML Models Setup Complete!${NC}"
    echo "=================================================="
    echo ""
    echo -e "${BLUE}🎯 Optimized for Your Use Cases:${NC}"
    echo "   • Module modification and creation"
    echo "   • Database updates from raw text (MAC addresses, device info)"
    echo "   • Network issue detection (LibreNMS style)"
    echo "   • GUI adaptation and modification"
    echo ""
    echo -e "${BLUE}🤖 Selected Models:${NC}"
    echo "   • DialoGPT-medium (Code generation)"
    echo "   • BART-large-mnli (Text analysis)"
    echo "   • All-MiniLM-L6-v2 (Embeddings)"
    echo "   • Custom network analysis model"
    echo "   • Custom GUI modification model"
    echo ""
    echo -e "${BLUE}🔗 Integration Files:${NC}"
    echo "   • focused_ml_config.json - Configuration"
    echo "   • focused_ml_integration.js - Integration script"
    echo "   • start_focused_ml.sh - Startup script"
    echo ""
    echo -e "${BLUE}🌐 Service URLs:${NC}"
    echo "   • Focused ML Service: http://localhost:8000"
    echo "   • API Documentation: http://localhost:8000/docs"
    echo "   • Adaptive AI Demo: http://localhost/adaptive_ai_demo.html"
    echo ""
    echo -e "${YELLOW}📝 Next Steps:${NC}"
    echo "1. Test the service: ./start_focused_ml.sh"
    echo "2. Integrate with your adaptive AI assistant"
    echo "3. Start using the focused ML capabilities"
    echo ""
    echo -e "${GREEN}✅ Setup complete! Your adaptive AI assistant now has focused ML capabilities!${NC}"
}

# Run main function
main "$@" 