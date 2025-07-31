#!/usr/bin/env python3
"""
Local ML Model Testing Framework
Comprehensive testing for LocalAI, Focused ML Service, and AI Assistant integration
"""

import requests
import json
import time
import subprocess
import sys
import os
from typing import Dict, Any, List, Optional

class LocalMLModelTester:
    def __init__(self):
        self.test_results = {}
        self.services = {
            'localai': {
                'url': 'http://localhost:8080',
                'health_endpoint': '/v1/models',
                'status': False
            },
            'focused_ml': {
                'url': 'http://localhost:8000',
                'health_endpoint': '/health',
                'status': False
            },
            'ai_assistant': {
                'url': 'http://localhost',
                'health_endpoint': '/ai_assistant_api.php?action=model_status',
                'status': False
            }
        }
    
    def print_header(self, title: str):
        """Print a formatted header"""
        print(f"\n{'='*60}")
        print(f"🧪 {title}")
        print(f"{'='*60}")
    
    def print_result(self, test_name: str, success: bool, details: str = ""):
        """Print test result with formatting"""
        status = "✅ PASS" if success else "❌ FAIL"
        print(f"{status} {test_name}")
        if details:
            print(f"   📝 {details}")
        self.test_results[test_name] = success
    
    def check_service_health(self, service_name: str) -> bool:
        """Check if a service is running and healthy"""
        service = self.services[service_name]
        try:
            response = requests.get(f"{service['url']}{service['health_endpoint']}", 
                                  timeout=5)
            if response.status_code == 200:
                service['status'] = True
                return True
            else:
                return False
        except requests.exceptions.RequestException:
            service['status'] = False
            return False
    
    def test_localai_service(self):
        """Test LocalAI service functionality"""
        self.print_header("Testing LocalAI Service")
        
        # Check if service is running
        if not self.check_service_health('localai'):
            self.print_result("LocalAI Service Running", False, 
                            "Service not accessible on localhost:8080")
            return
        
        self.print_result("LocalAI Service Running", True)
        
        try:
            # Test models endpoint
            response = requests.get(f"{self.services['localai']['url']}/v1/models")
            if response.status_code == 200:
                models = response.json()
                model_count = len(models.get('data', []))
                self.print_result("LocalAI Models Available", True, 
                                f"Found {model_count} models")
                
                # Test specific model if available
                if model_count > 0:
                    model_name = models['data'][0]['id']
                    self.test_localai_model(model_name)
            else:
                self.print_result("LocalAI Models Available", False, 
                                f"HTTP {response.status_code}")
                
        except Exception as e:
            self.print_result("LocalAI Models Available", False, str(e))
    
    def test_localai_model(self, model_name: str):
        """Test a specific LocalAI model"""
        try:
            # Test model completion
            payload = {
                "model": model_name,
                "prompt": "Hello, this is a test message. Please respond with 'Test successful'.",
                "max_tokens": 50
            }
            
            response = requests.post(f"{self.services['localai']['url']}/v1/completions", 
                                   json=payload, timeout=30)
            
            if response.status_code == 200:
                result = response.json()
                self.print_result(f"LocalAI Model {model_name} Response", True,
                                f"Generated response: {result.get('choices', [{}])[0].get('text', '')[:50]}...")
            else:
                self.print_result(f"LocalAI Model {model_name} Response", False,
                                f"HTTP {response.status_code}")
                
        except Exception as e:
            self.print_result(f"LocalAI Model {model_name} Response", False, str(e))
    
    def test_focused_ml_service(self):
        """Test Focused ML Service functionality"""
        self.print_header("Testing Focused ML Service")
        
        # Check if service is running
        if not self.check_service_health('focused_ml'):
            self.print_result("Focused ML Service Running", False,
                            "Service not accessible on localhost:8000")
            return
        
        self.print_result("Focused ML Service Running", True)
        
        try:
            # Test health endpoint
            response = requests.get(f"{self.services['focused_ml']['url']}/health")
            if response.status_code == 200:
                health_data = response.json()
                self.print_result("Focused ML Health Check", True,
                                f"Service healthy: {health_data}")
                
                # Test text generation
                self.test_focused_ml_generation()
                
                # Test sentiment analysis
                self.test_focused_ml_sentiment()
                
                # Test embeddings
                self.test_focused_ml_embeddings()
                
            else:
                self.print_result("Focused ML Health Check", False,
                                f"HTTP {response.status_code}")
                
        except Exception as e:
            self.print_result("Focused ML Health Check", False, str(e))
    
    def test_focused_ml_generation(self):
        """Test text generation with Focused ML Service"""
        try:
            payload = {
                "text": "Hello, this is a test message.",
                "max_length": 50
            }
            
            response = requests.post(f"{self.services['focused_ml']['url']}/generate", 
                                   json=payload, timeout=30)
            
            if response.status_code == 200:
                result = response.json()
                self.print_result("Focused ML Text Generation", True,
                                f"Generated: {result.get('generated_text', '')[:50]}...")
            else:
                self.print_result("Focused ML Text Generation", False,
                                f"HTTP {response.status_code}")
                
        except Exception as e:
            self.print_result("Focused ML Text Generation", False, str(e))
    
    def test_focused_ml_sentiment(self):
        """Test sentiment analysis with Focused ML Service"""
        try:
            payload = {
                "text": "I love this amazing product! It's fantastic."
            }
            
            response = requests.post(f"{self.services['focused_ml']['url']}/sentiment", 
                                   json=payload, timeout=10)
            
            if response.status_code == 200:
                result = response.json()
                self.print_result("Focused ML Sentiment Analysis", True,
                                f"Sentiment: {result.get('sentiment', 'unknown')}")
            else:
                self.print_result("Focused ML Sentiment Analysis", False,
                                f"HTTP {response.status_code}")
                
        except Exception as e:
            self.print_result("Focused ML Sentiment Analysis", False, str(e))
    
    def test_focused_ml_embeddings(self):
        """Test embeddings with Focused ML Service"""
        try:
            payload = {
                "text": "This is a test sentence for embeddings."
            }
            
            response = requests.post(f"{self.services['focused_ml']['url']}/embed", 
                                   json=payload, timeout=10)
            
            if response.status_code == 200:
                result = response.json()
                embedding_length = len(result.get('embedding', []))
                self.print_result("Focused ML Embeddings", True,
                                f"Embedding length: {embedding_length}")
            else:
                self.print_result("Focused ML Embeddings", False,
                                f"HTTP {response.status_code}")
                
        except Exception as e:
            self.print_result("Focused ML Embeddings", False, str(e))
    
    def test_ai_assistant_integration(self):
        """Test AI Assistant integration with ML models"""
        self.print_header("Testing AI Assistant Integration")
        
        # Check if AI Assistant API is accessible
        if not self.check_service_health('ai_assistant'):
            self.print_result("AI Assistant API Accessible", False,
                            "API not accessible")
            return
        
        self.print_result("AI Assistant API Accessible", True)
        
        try:
            # Test model status
            response = requests.get(f"{self.services['ai_assistant']['url']}/ai_assistant_api.php?action=model_status")
            if response.status_code == 200:
                status_data = response.json()
                self.print_result("AI Assistant Model Status", True,
                                f"Model type: {status_data.get('data', {}).get('model_type', 'unknown')}")
                
                # Test chat functionality
                self.test_ai_assistant_chat()
                
            else:
                self.print_result("AI Assistant Model Status", False,
                                f"HTTP {response.status_code}")
                
        except Exception as e:
            self.print_result("AI Assistant Model Status", False, str(e))
    
    def test_ai_assistant_chat(self):
        """Test AI Assistant chat functionality"""
        try:
            payload = {
                "action": "chat",
                "message": "Hello, I want to test the ML model integration. Can you help me?",
                "context": json.dumps({"url": "http://localhost/test"})
            }
            
            response = requests.post(f"{self.services['ai_assistant']['url']}/ai_assistant_api.php", 
                                   json=payload, timeout=30)
            
            if response.status_code == 200:
                result = response.json()
                if result.get('success'):
                    self.print_result("AI Assistant Chat", True,
                                    f"Response: {result.get('data', {}).get('response', '')[:100]}...")
                else:
                    self.print_result("AI Assistant Chat", False,
                                    f"Error: {result.get('error', 'Unknown error')}")
            else:
                self.print_result("AI Assistant Chat", False,
                                f"HTTP {response.status_code}")
                
        except Exception as e:
            self.print_result("AI Assistant Chat", False, str(e))
    
    def test_adaptive_ai_integration(self):
        """Test Adaptive AI integration"""
        self.print_header("Testing Adaptive AI Integration")
        
        try:
            # Test behavior tracking
            payload = {
                "action": "track_behavior",
                "action_type": "click",
                "element_id": "test-button",
                "element_type": "button",
                "coordinates": {"x": 100, "y": 100}
            }
            
            response = requests.post(f"{self.services['ai_assistant']['url']}/adaptive_ai_api.php", 
                                   json=payload, timeout=10)
            
            if response.status_code == 200:
                result = response.json()
                if result.get('success'):
                    self.print_result("Adaptive AI Behavior Tracking", True,
                                    "Behavior tracked successfully")
                else:
                    self.print_result("Adaptive AI Behavior Tracking", False,
                                    f"Error: {result.get('error', 'Unknown error')}")
            else:
                self.print_result("Adaptive AI Behavior Tracking", False,
                                f"HTTP {response.status_code}")
                
        except Exception as e:
            self.print_result("Adaptive AI Behavior Tracking", False, str(e))
        
        try:
            # Test pattern analysis
            response = requests.get(f"{self.services['ai_assistant']['url']}/adaptive_ai_api.php?action=analyze_patterns")
            
            if response.status_code == 200:
                result = response.json()
                if result.get('success'):
                    patterns = result.get('data', {}).get('patterns', [])
                    self.print_result("Adaptive AI Pattern Analysis", True,
                                    f"Found {len(patterns)} patterns")
                else:
                    self.print_result("Adaptive AI Pattern Analysis", False,
                                    f"Error: {result.get('error', 'Unknown error')}")
            else:
                self.print_result("Adaptive AI Pattern Analysis", False,
                                f"HTTP {response.status_code}")
                
        except Exception as e:
            self.print_result("Adaptive AI Pattern Analysis", False, str(e))
    
    def start_ml_services(self):
        """Start ML services if they're not running"""
        self.print_header("Starting ML Services")
        
        # Check if services are already running
        localai_running = self.check_service_health('localai')
        focused_ml_running = self.check_service_health('focused_ml')
        
        if not localai_running:
            print("🚀 Starting LocalAI...")
            try:
                # Check if setup script exists
                if os.path.exists('setup_localai.sh'):
                    subprocess.run(['sudo', './setup_localai.sh'], 
                                 capture_output=True, text=True)
                    time.sleep(10)  # Wait for service to start
                    if self.check_service_health('localai'):
                        self.print_result("LocalAI Started", True)
                    else:
                        self.print_result("LocalAI Started", False, "Service failed to start")
                else:
                    self.print_result("LocalAI Setup Script", False, "setup_localai.sh not found")
            except Exception as e:
                self.print_result("LocalAI Started", False, str(e))
        else:
            self.print_result("LocalAI Already Running", True)
        
        if not focused_ml_running:
            print("🚀 Starting Focused ML Service...")
            try:
                # Check if setup script exists
                if os.path.exists('setup_focused_ml_models.sh'):
                    subprocess.run(['sudo', './setup_focused_ml_models.sh'], 
                                 capture_output=True, text=True)
                    time.sleep(10)  # Wait for service to start
                    if self.check_service_health('focused_ml'):
                        self.print_result("Focused ML Service Started", True)
                    else:
                        self.print_result("Focused ML Service Started", False, "Service failed to start")
                else:
                    self.print_result("Focused ML Setup Script", False, "setup_focused_ml_models.sh not found")
            except Exception as e:
                self.print_result("Focused ML Service Started", False, str(e))
        else:
            self.print_result("Focused ML Service Already Running", True)
    
    def run_comprehensive_test(self):
        """Run all tests"""
        self.print_header("Local ML Model Testing Framework")
        print("This framework will test your local ML models and AI assistant integration.")
        
        # Ask user if they want to start services
        print("\n🔧 Service Management:")
        print("1. Start ML services (LocalAI, Focused ML)")
        print("2. Test existing services only")
        print("3. Exit")
        
        choice = input("\nEnter your choice (1-3): ").strip()
        
        if choice == "1":
            self.start_ml_services()
            print("\n⏳ Waiting for services to be ready...")
            time.sleep(5)
        elif choice == "3":
            print("Exiting...")
            return
        
        # Run all tests
        self.test_localai_service()
        self.test_focused_ml_service()
        self.test_ai_assistant_integration()
        self.test_adaptive_ai_integration()
        
        # Print summary
        self.print_test_summary()
    
    def print_test_summary(self):
        """Print test results summary"""
        self.print_header("Test Results Summary")
        
        total_tests = len(self.test_results)
        passed_tests = sum(1 for result in self.test_results.values() if result)
        failed_tests = total_tests - passed_tests
        
        print(f"📊 Total Tests: {total_tests}")
        print(f"✅ Passed: {passed_tests}")
        print(f"❌ Failed: {failed_tests}")
        print(f"📈 Success Rate: {(passed_tests/total_tests*100):.1f}%" if total_tests > 0 else "N/A")
        
        if failed_tests > 0:
            print("\n❌ Failed Tests:")
            for test_name, result in self.test_results.items():
                if not result:
                    print(f"   - {test_name}")
        
        print("\n🎯 Recommendations:")
        if failed_tests == 0:
            print("   🎉 All tests passed! Your ML models are working perfectly.")
        else:
            print("   🔧 Some tests failed. Check the service status and configuration.")
            print("   📖 Review the error messages above for specific issues.")
            print("   🚀 Consider running the setup scripts to install missing services.")

if __name__ == "__main__":
    tester = LocalMLModelTester()
    tester.run_comprehensive_test() 