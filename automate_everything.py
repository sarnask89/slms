#!/usr/bin/env python3
"""
AI Assistant & ML Model Automation System
Comprehensive automation for testing, deployment, and monitoring
"""

import subprocess
import requests
import json
import time
import os
import sys
import threading
import schedule
import logging
from datetime import datetime
from typing import Dict, List, Any, Optional

class AIAssistantAutomation:
    def __init__(self):
        self.setup_logging()
        self.test_results = {}
        self.services_status = {}
        self.automation_config = {
            'auto_start_services': True,
            'continuous_monitoring': True,
            'auto_restart_failed_services': True,
            'performance_thresholds': {
                'response_time_ms': 1000,
                'error_rate_percent': 5,
                'uptime_percent': 95
            },
            'monitoring_interval_seconds': 60,
            'test_interval_minutes': 15
        }
        
    def setup_logging(self):
        """Setup comprehensive logging"""
        logging.basicConfig(
            level=logging.INFO,
            format='%(asctime)s - %(levelname)s - %(message)s',
            handlers=[
                logging.FileHandler('/var/www/html/automation.log'),
                logging.StreamHandler(sys.stdout)
            ]
        )
        self.logger = logging.getLogger(__name__)
        
    def log_action(self, action: str, details: str = "", level: str = "INFO"):
        """Log automation actions"""
        message = f"🤖 AUTOMATION: {action}"
        if details:
            message += f" - {details}"
        
        if level == "ERROR":
            self.logger.error(message)
        elif level == "WARNING":
            self.logger.warning(message)
        else:
            self.logger.info(message)
            
    def run_command(self, command: str, description: str = "") -> Dict[str, Any]:
        """Execute shell command with error handling"""
        try:
            self.log_action(f"Executing: {description or command}")
            result = subprocess.run(
                command, 
                shell=True, 
                capture_output=True, 
                text=True, 
                timeout=300
            )
            
            success = result.returncode == 0
            self.log_action(
                f"Command {'completed' if success else 'failed'}", 
                f"Return code: {result.returncode}",
                "ERROR" if not success else "INFO"
            )
            
            return {
                'success': success,
                'stdout': result.stdout,
                'stderr': result.stderr,
                'returncode': result.returncode
            }
        except subprocess.TimeoutExpired:
            self.log_action(f"Command timed out: {command}", level="ERROR")
            return {'success': False, 'error': 'Timeout'}
        except Exception as e:
            self.log_action(f"Command error: {str(e)}", level="ERROR")
            return {'success': False, 'error': str(e)}
    
    def check_service_health(self, service_name: str, url: str) -> bool:
        """Check if a service is healthy"""
        try:
            response = requests.get(url, timeout=10)
            is_healthy = response.status_code == 200
            self.services_status[service_name] = {
                'healthy': is_healthy,
                'status_code': response.status_code,
                'last_check': datetime.now().isoformat()
            }
            return is_healthy
        except Exception as e:
            self.services_status[service_name] = {
                'healthy': False,
                'error': str(e),
                'last_check': datetime.now().isoformat()
            }
            return False
    
    def start_service(self, service_name: str, setup_script: str) -> bool:
        """Start a service using its setup script"""
        self.log_action(f"Starting {service_name}")
        
        if not os.path.exists(setup_script):
            self.log_action(f"Setup script not found: {setup_script}", level="ERROR")
            return False
            
        result = self.run_command(f"sudo {setup_script}", f"Start {service_name}")
        
        if result['success']:
            self.log_action(f"{service_name} started successfully")
            return True
        else:
            self.log_action(f"Failed to start {service_name}", level="ERROR")
            return False
    
    def automated_service_management(self):
        """Automated service management and monitoring"""
        self.log_action("Starting automated service management")
        
        services = {
            'localai': {
                'setup_script': '/var/www/html/setup_localai.sh',
                'health_url': 'http://localhost:8080/v1/models',
                'port': 8080
            },
            'focused_ml': {
                'setup_script': '/var/www/html/setup_focused_ml_models.sh',
                'health_url': 'http://localhost:8000/health',
                'port': 8000
            },
            'apache': {
                'setup_script': 'systemctl restart apache2',
                'health_url': 'http://localhost/ai_assistant_api.php?action=model_status',
                'port': 80
            }
        }
        
        # Check and start services
        for service_name, config in services.items():
            if not self.check_service_health(service_name, config['health_url']):
                self.log_action(f"Service {service_name} is down, attempting restart")
                if self.automation_config['auto_restart_failed_services']:
                    self.start_service(service_name, config['setup_script'])
                    time.sleep(30)  # Wait for service to start
                    self.check_service_health(service_name, config['health_url'])
    
    def run_automated_tests(self):
        """Run comprehensive automated tests"""
        self.log_action("Running automated test suite")
        
        test_suite = [
            self.test_ai_assistant_basic,
            self.test_ai_assistant_chat,
            self.test_adaptive_ai_behavior,
            self.test_adaptive_ai_patterns,
            self.test_database_connection,
            self.test_ml_services,
            self.test_performance_metrics
        ]
        
        for test_func in test_suite:
            try:
                test_name = test_func.__name__
                self.log_action(f"Running test: {test_name}")
                result = test_func()
                self.test_results[test_name] = result
                
                if result['success']:
                    self.log_action(f"Test {test_name} passed")
                else:
                    self.log_action(f"Test {test_name} failed: {result.get('error', 'Unknown error')}", level="ERROR")
                    
            except Exception as e:
                self.log_action(f"Test {test_func.__name__} crashed: {str(e)}", level="ERROR")
                self.test_results[test_func.__name__] = {'success': False, 'error': str(e)}
    
    def test_ai_assistant_basic(self) -> Dict[str, Any]:
        """Test basic AI assistant functionality"""
        try:
            response = requests.get("http://localhost/ai_assistant_api.php?action=model_status", timeout=10)
            if response.status_code == 200:
                data = response.json()
                return {
                    'success': data.get('success', False),
                    'model_type': data.get('data', {}).get('model_type', 'unknown'),
                    'response_time_ms': response.elapsed.total_seconds() * 1000
                }
            else:
                return {'success': False, 'error': f"HTTP {response.status_code}"}
        except Exception as e:
            return {'success': False, 'error': str(e)}
    
    def test_ai_assistant_chat(self) -> Dict[str, Any]:
        """Test AI assistant chat functionality"""
        try:
            payload = {
                "action": "chat",
                "message": "Automated test message",
                "context": json.dumps({"url": "http://localhost/automation"})
            }
            
            response = requests.post(
                "http://localhost/ai_assistant_api.php",
                json=payload,
                timeout=30
            )
            
            if response.status_code == 200:
                data = response.json()
                return {
                    'success': data.get('success', False),
                    'response_length': len(data.get('data', {}).get('response', '')),
                    'response_time_ms': response.elapsed.total_seconds() * 1000
                }
            else:
                return {'success': False, 'error': f"HTTP {response.status_code}"}
        except Exception as e:
            return {'success': False, 'error': str(e)}
    
    def test_adaptive_ai_behavior(self) -> Dict[str, Any]:
        """Test adaptive AI behavior tracking"""
        try:
            payload = {
                "action": "track_behavior",
                "action_type": "automated_test",
                "element_id": "test-element",
                "element_type": "button",
                "coordinates": {"x": 100, "y": 100}
            }
            
            response = requests.post(
                "http://localhost/adaptive_ai_api.php",
                json=payload,
                timeout=10
            )
            
            if response.status_code == 200:
                data = response.json()
                return {
                    'success': data.get('success', False),
                    'tracked': data.get('data', {}).get('tracked', False)
                }
            else:
                return {'success': False, 'error': f"HTTP {response.status_code}"}
        except Exception as e:
            return {'success': False, 'error': str(e)}
    
    def test_adaptive_ai_patterns(self) -> Dict[str, Any]:
        """Test adaptive AI pattern analysis"""
        try:
            response = requests.get("http://localhost/adaptive_ai_api.php?action=analyze_patterns", timeout=10)
            
            if response.status_code == 200:
                data = response.json()
                patterns = data.get('data', {}).get('patterns', [])
                return {
                    'success': data.get('success', False),
                    'patterns_count': len(patterns),
                    'response_time_ms': response.elapsed.total_seconds() * 1000
                }
            else:
                return {'success': False, 'error': f"HTTP {response.status_code}"}
        except Exception as e:
            return {'success': False, 'error': str(e)}
    
    def test_database_connection(self) -> Dict[str, Any]:
        """Test database connectivity"""
        try:
            response = requests.get("http://localhost/ai_assistant_api.php?action=model_status", timeout=10)
            if response.status_code == 200:
                data = response.json()
                db_connected = data.get('data', {}).get('database_connected', False)
                return {
                    'success': db_connected,
                    'database_status': 'Connected' if db_connected else 'Disconnected'
                }
            else:
                return {'success': False, 'error': f"HTTP {response.status_code}"}
        except Exception as e:
            return {'success': False, 'error': str(e)}
    
    def test_ml_services(self) -> Dict[str, Any]:
        """Test ML services (LocalAI and Focused ML)"""
        results = {}
        
        # Test LocalAI
        try:
            response = requests.get("http://localhost:8080/v1/models", timeout=10)
            if response.status_code == 200:
                models = response.json()
                results['localai'] = {
                    'success': True,
                    'models_count': len(models.get('data', [])),
                    'response_time_ms': response.elapsed.total_seconds() * 1000
                }
            else:
                results['localai'] = {'success': False, 'error': f"HTTP {response.status_code}"}
        except Exception as e:
            results['localai'] = {'success': False, 'error': str(e)}
        
        # Test Focused ML Service
        try:
            response = requests.get("http://localhost:8000/health", timeout=10)
            if response.status_code == 200:
                health_data = response.json()
                results['focused_ml'] = {
                    'success': True,
                    'health_status': health_data,
                    'response_time_ms': response.elapsed.total_seconds() * 1000
                }
            else:
                results['focused_ml'] = {'success': False, 'error': f"HTTP {response.status_code}"}
        except Exception as e:
            results['focused_ml'] = {'success': False, 'error': str(e)}
        
        return results
    
    def test_performance_metrics(self) -> Dict[str, Any]:
        """Test performance metrics"""
        metrics = {}
        
        # Test response times
        endpoints = [
            ("ai_assistant_status", "http://localhost/ai_assistant_api.php?action=model_status"),
            ("adaptive_ai_suggestions", "http://localhost/adaptive_ai_api.php?action=suggest_improvements"),
            ("ai_assistant_chat", "http://localhost/ai_assistant_api.php")
        ]
        
        for name, url in endpoints:
            try:
                start_time = time.time()
                if name == "ai_assistant_chat":
                    payload = {"action": "chat", "message": "Performance test"}
                    response = requests.post(url, json=payload, timeout=30)
                else:
                    response = requests.get(url, timeout=10)
                
                response_time = (time.time() - start_time) * 1000
                metrics[name] = {
                    'success': response.status_code == 200,
                    'response_time_ms': response_time,
                    'status_code': response.status_code
                }
            except Exception as e:
                metrics[name] = {
                    'success': False,
                    'error': str(e),
                    'response_time_ms': None
                }
        
        return metrics
    
    def generate_automation_report(self):
        """Generate comprehensive automation report"""
        self.log_action("Generating automation report")
        
        report = {
            'timestamp': datetime.now().isoformat(),
            'automation_config': self.automation_config,
            'services_status': self.services_status,
            'test_results': self.test_results,
            'summary': self.calculate_summary()
        }
        
        # Save report to file
        with open('/var/www/html/automation_report.json', 'w') as f:
            json.dump(report, f, indent=2)
        
        # Generate HTML report
        self.generate_html_report(report)
        
        self.log_action("Automation report generated")
        return report
    
    def calculate_summary(self) -> Dict[str, Any]:
        """Calculate test summary statistics"""
        total_tests = len(self.test_results)
        passed_tests = sum(1 for result in self.test_results.values() 
                          if isinstance(result, dict) and result.get('success', False))
        
        # Calculate average response times
        response_times = []
        for result in self.test_results.values():
            if isinstance(result, dict) and 'response_time_ms' in result:
                response_times.append(result['response_time_ms'])
        
        avg_response_time = sum(response_times) / len(response_times) if response_times else 0
        
        return {
            'total_tests': total_tests,
            'passed_tests': passed_tests,
            'failed_tests': total_tests - passed_tests,
            'success_rate': (passed_tests / total_tests * 100) if total_tests > 0 else 0,
            'average_response_time_ms': avg_response_time,
            'services_healthy': sum(1 for service in self.services_status.values() 
                                  if service.get('healthy', False)),
            'total_services': len(self.services_status)
        }
    
    def generate_html_report(self, report: Dict[str, Any]):
        """Generate HTML automation report"""
        html_content = f"""
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant Automation Report</title>
    <style>
        body {{ font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }}
        .container {{ background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }}
        .header {{ background-color: #007bff; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; }}
        .status-success {{ color: green; font-weight: bold; }}
        .status-error {{ color: red; font-weight: bold; }}
        .status-warning {{ color: orange; font-weight: bold; }}
        .metric {{ background-color: #f8f9fa; padding: 10px; margin: 10px 0; border-radius: 5px; }}
        .service-status {{ display: inline-block; padding: 5px 10px; margin: 5px; border-radius: 3px; }}
        .service-healthy {{ background-color: #d4edda; color: #155724; }}
        .service-unhealthy {{ background-color: #f8d7da; color: #721c24; }}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 AI Assistant Automation Report</h1>
            <p>Generated: {report['timestamp']}</p>
        </div>
        
        <h2>📊 Summary</h2>
        <div class="metric">
            <p><strong>Success Rate:</strong> <span class="status-{'success' if report['summary']['success_rate'] >= 80 else 'warning' if report['summary']['success_rate'] >= 60 else 'error'}">{report['summary']['success_rate']:.1f}%</span></p>
            <p><strong>Tests Passed:</strong> {report['summary']['passed_tests']}/{report['summary']['total_tests']}</p>
            <p><strong>Average Response Time:</strong> {report['summary']['average_response_time_ms']:.1f}ms</p>
            <p><strong>Services Healthy:</strong> {report['summary']['services_healthy']}/{report['summary']['total_services']}</p>
        </div>
        
        <h2>🔧 Services Status</h2>
        <div>
"""
        
        for service_name, status in report['services_status'].items():
            status_class = 'service-healthy' if status.get('healthy', False) else 'service-unhealthy'
            html_content += f"""
            <span class="service-status {status_class}">
                {service_name}: {'✅ Healthy' if status.get('healthy', False) else '❌ Unhealthy'}
            </span>
"""
        
        html_content += """
        </div>
        
        <h2>🧪 Test Results</h2>
        <div>
"""
        
        for test_name, result in report['test_results'].items():
            if isinstance(result, dict):
                status_class = 'status-success' if result.get('success', False) else 'status-error'
                html_content += f"""
            <div class="metric">
                <h3>{test_name}</h3>
                <p class="{status_class}">Status: {'✅ Passed' if result.get('success', False) else '❌ Failed'}</p>
                {f'<p>Response Time: {result.get("response_time_ms", 0):.1f}ms</p>' if 'response_time_ms' in result else ''}
                {f'<p>Error: {result.get("error", "")}</p>' if not result.get('success', False) and 'error' in result else ''}
            </div>
"""
        
        html_content += """
        </div>
        
        <h2>⚙️ Automation Configuration</h2>
        <div class="metric">
"""
        
        for key, value in report['automation_config'].items():
            html_content += f"            <p><strong>{key}:</strong> {value}</p>\n"
        
        html_content += """
        </div>
    </div>
</body>
</html>
"""
        
        with open('/var/www/html/automation_report.html', 'w') as f:
            f.write(html_content)
    
    def start_continuous_monitoring(self):
        """Start continuous monitoring and automation"""
        self.log_action("Starting continuous monitoring")
        
        # Schedule regular tasks
        schedule.every(self.automation_config['monitoring_interval_seconds']).seconds.do(self.automated_service_management)
        schedule.every(self.automation_config['test_interval_minutes']).minutes.do(self.run_automated_tests)
        schedule.every().hour.do(self.generate_automation_report)
        
        # Run initial tasks
        self.automated_service_management()
        self.run_automated_tests()
        self.generate_automation_report()
        
        # Start monitoring loop
        while self.automation_config['continuous_monitoring']:
            schedule.run_pending()
            time.sleep(1)
    
    def run_full_automation(self):
        """Run complete automation sequence"""
        self.log_action("🚀 Starting full automation sequence")
        
        try:
            # Initial setup
            self.log_action("Phase 1: Service Management")
            self.automated_service_management()
            
            # Wait for services to stabilize
            time.sleep(30)
            
            # Run comprehensive tests
            self.log_action("Phase 2: Comprehensive Testing")
            self.run_automated_tests()
            
            # Generate report
            self.log_action("Phase 3: Report Generation")
            report = self.generate_automation_report()
            
            # Display summary
            summary = report['summary']
            self.log_action(f"Automation Complete! Success Rate: {summary['success_rate']:.1f}%")
            
            # Start continuous monitoring if enabled
            if self.automation_config['continuous_monitoring']:
                self.log_action("Starting continuous monitoring...")
                self.start_continuous_monitoring()
            
        except KeyboardInterrupt:
            self.log_action("Automation stopped by user")
        except Exception as e:
            self.log_action(f"Automation error: {str(e)}", level="ERROR")

def main():
    """Main automation entry point"""
    print("🤖 AI Assistant & ML Model Automation System")
    print("=" * 60)
    
    automation = AIAssistantAutomation()
    
    print("\n🔧 Automation Options:")
    print("1. Run full automation (setup + test + monitor)")
    print("2. Run tests only")
    print("3. Start services only")
    print("4. Generate report only")
    print("5. Continuous monitoring")
    
    choice = input("\nEnter your choice (1-5): ").strip()
    
    if choice == "1":
        automation.run_full_automation()
    elif choice == "2":
        automation.run_automated_tests()
        automation.generate_automation_report()
    elif choice == "3":
        automation.automated_service_management()
    elif choice == "4":
        automation.generate_automation_report()
    elif choice == "5":
        automation.start_continuous_monitoring()
    else:
        print("Invalid choice. Running full automation...")
        automation.run_full_automation()

if __name__ == "__main__":
    main() 