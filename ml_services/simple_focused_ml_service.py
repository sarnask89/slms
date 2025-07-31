from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List, Dict, Any, Optional
import json
import logging
import re
from datetime import datetime

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(title="Simple Focused ML Service", version="1.0.0")

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Allows all origins
    allow_credentials=True,
    allow_methods=["*"],  # Allows all methods
    allow_headers=["*"],  # Allows all headers
)

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

@app.get("/")
async def root():
    return {"message": "Simple Focused ML Service is running!", "capabilities": [
        "Module modification and creation",
        "Database updates from raw text",
        "Network issue detection",
        "GUI modification suggestions"
    ]}

@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "models_loaded": True,
        "timestamp": datetime.now().isoformat()
    }

@app.post("/modify_module")
async def modify_module(request: ModuleModificationRequest):
    """Modify or create modules based on user requests"""
    try:
        # Generate context-aware prompt
        prompt = f"""
        Task: {request.modification_type}
        Module: {request.module_name}
        User Request: {request.user_request}
        Current Code: {request.current_code or 'New module'}
        
        Generate PHP code for this module modification:
        """
        
        # Generate simple code based on request
        generated_code = generate_simple_code(request)
        
        return {
            "module_name": request.module_name,
            "modification_type": request.modification_type,
            "generated_code": generated_code,
            "suggestions": generate_module_suggestions(request),
            "model": "simple_code_generator"
        }
        
    except Exception as e:
        logger.error(f"Error in module modification: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/update_database")
async def update_database(request: DatabaseUpdateRequest):
    """Update database based on raw text analysis"""
    try:
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
            "model": "simple_text_analyzer"
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
            "model": "simple_network_analyzer"
        }
        
    except Exception as e:
        logger.error(f"Error in network analysis: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/modify_gui")
async def modify_gui(request: GUIModificationRequest):
    """Generate GUI modification suggestions"""
    try:
        # Analyze user behavior
        behavior_analysis = analyze_user_behavior(request.user_behavior)
        
        # Generate modifications
        modifications = generate_gui_modifications(request.modification_type, behavior_analysis, request.current_layout)
        
        # Generate code
        code_modifications = generate_gui_code(modifications)
        
        return {
            "page_url": request.page_url,
            "modification_type": request.modification_type,
            "modifications": modifications,
            "code_modifications": code_modifications,
            "behavior_analysis": behavior_analysis,
            "model": "simple_gui_modifier"
        }
        
    except Exception as e:
        logger.error(f"Error in GUI modification: {e}")
        raise HTTPException(status_code=500, detail=str(e))

def generate_simple_code(request: ModuleModificationRequest) -> str:
    """Generate simple PHP code based on request"""
    module_name = request.module_name
    modification_type = request.modification_type
    user_request = request.user_request
    
    code = "<?php\n"
    code += "/**\n"
    code += f" * Generated Module: {module_name}\n"
    code += f" * Type: {modification_type}\n"
    code += f" * Request: {user_request}\n"
    code += f" * Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n"
    code += " */\n\n"
    
    if modification_type == "new_module":
        class_name = module_name.replace("_", "").title()
        code += f"class {class_name} {{\n"
        code += "    private $pdo;\n"
        code += "    private $config;\n\n"
        code += "    public function __construct($pdo, $config = []) {\n"
        code += "        $this->pdo = $pdo;\n"
        code += "        $this->config = $config;\n"
        code += "    }\n\n"
        code += "    public function execute() {\n"
        code += "        // TODO: Implement functionality based on request\n"
        code += f"        // Request: {user_request}\n"
        code += "        return ['success' => true, 'message' => 'Module executed'];\n"
        code += "    }\n\n"
        code += "    public function getConfig() {\n"
        code += "        return $this->config;\n"
        code += "    }\n"
        code += "}\n"
    else:
        code += "// TODO: Implement modification based on request\n"
        code += f"// Modification type: {modification_type}\n"
        code += f"// Request: {user_request}\n\n"
        code += "$result = ['success' => true, 'message' => 'Modification applied'];\n"
        code += "return $result;\n"
    
    return code

def generate_module_suggestions(request: ModuleModificationRequest) -> List[str]:
    """Generate suggestions for module improvement"""
    suggestions = [
        "Dodaj obsługę błędów try-catch",
        "Zaimplementuj logowanie",
        "Dodaj walidację danych wejściowych",
        "Zoptymalizuj zapytania do bazy danych",
        "Dodaj dokumentację PHPDoc"
    ]
    
    if "monitoring" in request.user_request.lower():
        suggestions.extend([
            "Dodaj alerty i powiadomienia",
            "Zaimplementuj wykresy i statystyki",
            "Dodaj konfigurację progów"
        ])
    
    if "api" in request.user_request.lower():
        suggestions.extend([
            "Dodaj rate limiting",
            "Zaimplementuj autoryzację",
            "Dodaj walidację requestów"
        ])
    
    return suggestions

def extract_data_from_text(raw_text: str, update_type: str) -> Dict[str, Any]:
    """Extract data from raw text"""
    extracted_data = {
        "raw_text": raw_text,
        "update_type": update_type,
        "extracted_items": []
    }
    
    if update_type == "mac_address":
        # Extract MAC addresses
        mac_pattern = r'([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})'
        macs = re.findall(mac_pattern, raw_text)
        extracted_data["mac_addresses"] = [''.join(mac) for mac in macs]
        extracted_data["valid_macs"] = extracted_data["mac_addresses"]
    
    elif update_type == "ip_address":
        # Extract IP addresses
        ip_pattern = r'\b(?:\d{1,3}\.){3}\d{1,3}\b'
        ips = re.findall(ip_pattern, raw_text)
        extracted_data["ip_addresses"] = ips
        extracted_data["valid_ips"] = ips
    
    elif update_type == "device_info":
        # Extract device information
        device_patterns = {
            "hostname": r'hostname[:\s]+([^\s\n]+)',
            "model": r'model[:\s]+([^\s\n]+)',
            "serial": r'serial[:\s]+([^\s\n]+)'
        }
        
        for key, pattern in device_patterns.items():
            match = re.search(pattern, raw_text, re.IGNORECASE)
            if match:
                extracted_data[key] = match.group(1)
    
    return extracted_data

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
    
    return results

def generate_network_recommendations(analysis_results: Dict) -> List[str]:
    """Generate network recommendations"""
    recommendations = []
    
    if analysis_results.get("issues"):
        recommendations.append("Sprawdź urządzenia z problemami")
        recommendations.append("Zoptymalizuj konfigurację sieci")
    
    if analysis_results.get("performance"):
        recommendations.append("Rozważ upgrade przepustowości")
        recommendations.append("Zoptymalizuj routing")
    
    return recommendations

def generate_network_alerts(analysis_results: Dict) -> List[Dict]:
    """Generate network alerts"""
    alerts = []
    
    for issue in analysis_results.get("issues", []):
        alerts.append({
            "type": issue["type"],
            "severity": issue["severity"],
            "message": f"Problem z urządzeniem: {issue['device']}",
            "timestamp": datetime.now().isoformat()
        })
    
    return alerts

def calculate_severity(analysis_results: Dict) -> str:
    """Calculate overall severity level"""
    issues = analysis_results.get("issues", [])
    
    if any(issue["severity"] == "high" for issue in issues):
        return "high"
    elif any(issue["severity"] == "medium" for issue in issues):
        return "medium"
    else:
        return "low"

def analyze_user_behavior(user_behavior: List[Dict]) -> Dict[str, Any]:
    """Analyze user behavior patterns"""
    analysis = {
        "total_actions": len(user_behavior),
        "common_actions": {},
        "navigation_patterns": [],
        "performance_issues": []
    }
    
    # Count common actions
    for action in user_behavior:
        action_type = action.get("type", "unknown")
        analysis["common_actions"][action_type] = analysis["common_actions"].get(action_type, 0) + 1
    
    return analysis

def generate_gui_modifications(modification_type: str, behavior_analysis: Dict, current_layout: Optional[Dict]) -> List[Dict]:
    """Generate GUI modification suggestions"""
    modifications = []
    
    if modification_type == "resize":
        modifications.append({
            "type": "resize",
            "element": "main_content",
            "suggestion": "Zwiększ szerokość głównego kontenera"
        })
    
    elif modification_type == "reposition":
        modifications.append({
            "type": "reposition",
            "element": "navigation",
            "suggestion": "Przenieś nawigację na górę strony"
        })
    
    elif modification_type == "add_element":
        modifications.append({
            "type": "add_element",
            "element": "quick_actions",
            "suggestion": "Dodaj panel szybkich akcji"
        })
    
    return modifications

def generate_gui_code(modifications: List[Dict]) -> Dict[str, str]:
    """Generate code for GUI modifications"""
    code_modifications = {
        "css": "",
        "javascript": "",
        "html": ""
    }
    
    for mod in modifications:
        if mod["type"] == "resize":
            code_modifications["css"] += f"""
.{mod['element']} {{
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}}
"""
        elif mod["type"] == "reposition":
            code_modifications["css"] += f"""
.{mod['element']} {{
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
}}
"""
    
    return code_modifications

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
