#!/usr/bin/env python3
"""
Quick ML Model Test
Simple testing for local ML models
"""

import requests
import json
import time

def test_service(url, name):
    """Test if a service is running"""
    try:
        response = requests.get(url, timeout=5)
        if response.status_code == 200:
            print(f"✅ {name}: Running")
            return True
        else:
            print(f"❌ {name}: HTTP {response.status_code}")
            return False
    except requests.exceptions.RequestException as e:
        print(f"❌ {name}: Not accessible ({e})")
        return False

def test_ai_assistant():
    """Test AI Assistant functionality"""
    print("\n🧪 Testing AI Assistant...")
    
    # Test model status
    if test_service("http://localhost/ai_assistant_api.php?action=model_status", "AI Assistant API"):
        try:
            response = requests.get("http://localhost/ai_assistant_api.php?action=model_status")
            data = response.json()
            if data.get('success'):
                model_type = data.get('data', {}).get('model_type', 'unknown')
                print(f"   📊 Model Type: {model_type}")
                
                # Test chat
                chat_payload = {
                    "action": "chat",
                    "message": "Hello, test message"
                }
                chat_response = requests.post("http://localhost/ai_assistant_api.php", 
                                           json=chat_payload, timeout=10)
                if chat_response.status_code == 200:
                    chat_data = chat_response.json()
                    if chat_data.get('success'):
                        print("   💬 Chat: Working")
                    else:
                        print(f"   💬 Chat: Error - {chat_data.get('error')}")
                else:
                    print(f"   💬 Chat: HTTP {chat_response.status_code}")
            else:
                print(f"   ❌ Model Status: {data.get('error')}")
        except Exception as e:
            print(f"   ❌ Error: {e}")

def test_adaptive_ai():
    """Test Adaptive AI functionality"""
    print("\n🧪 Testing Adaptive AI...")
    
    # Test suggestions
    if test_service("http://localhost/adaptive_ai_api.php?action=suggest_improvements", "Adaptive AI API"):
        try:
            # Test behavior tracking
            behavior_payload = {
                "action": "track_behavior",
                "action_type": "click",
                "element_id": "test",
                "element_type": "button"
            }
            behavior_response = requests.post("http://localhost/adaptive_ai_api.php", 
                                           json=behavior_payload, timeout=10)
            if behavior_response.status_code == 200:
                behavior_data = behavior_response.json()
                if behavior_data.get('success'):
                    print("   📊 Behavior Tracking: Working")
                else:
                    print(f"   📊 Behavior Tracking: Error - {behavior_data.get('error')}")
            else:
                print(f"   📊 Behavior Tracking: HTTP {behavior_response.status_code}")
                
            # Test pattern analysis
            pattern_response = requests.get("http://localhost/adaptive_ai_api.php?action=analyze_patterns")
            if pattern_response.status_code == 200:
                pattern_data = pattern_response.json()
                if pattern_data.get('success'):
                    patterns = pattern_data.get('data', {}).get('patterns', [])
                    print(f"   🧠 Pattern Analysis: Working ({len(patterns)} patterns)")
                else:
                    print(f"   🧠 Pattern Analysis: Error - {pattern_data.get('error')}")
            else:
                print(f"   🧠 Pattern Analysis: HTTP {pattern_response.status_code}")
                
        except Exception as e:
            print(f"   ❌ Error: {e}")

def test_ml_services():
    """Test ML services"""
    print("\n🧪 Testing ML Services...")
    
    # Test LocalAI
    localai_running = test_service("http://localhost:8080/v1/models", "LocalAI")
    if localai_running:
        try:
            response = requests.get("http://localhost:8080/v1/models")
            models = response.json()
            model_count = len(models.get('data', []))
            print(f"   🤖 Models Available: {model_count}")
            
            if model_count > 0:
                # Test model completion
                model_name = models['data'][0]['id']
                completion_payload = {
                    "model": model_name,
                    "prompt": "Test message",
                    "max_tokens": 20
                }
                completion_response = requests.post("http://localhost:8080/v1/completions", 
                                                 json=completion_payload, timeout=30)
                if completion_response.status_code == 200:
                    print("   ✨ Model Completion: Working")
                else:
                    print(f"   ✨ Model Completion: HTTP {completion_response.status_code}")
        except Exception as e:
            print(f"   ❌ LocalAI Error: {e}")
    
    # Test Focused ML Service
    focused_running = test_service("http://localhost:8000/health", "Focused ML Service")
    if focused_running:
        try:
            response = requests.get("http://localhost:8000/health")
            health_data = response.json()
            print(f"   🔬 Service Health: {health_data}")
            
            # Test generation
            gen_payload = {"text": "Test", "max_length": 20}
            gen_response = requests.post("http://localhost:8000/generate", 
                                       json=gen_payload, timeout=30)
            if gen_response.status_code == 200:
                print("   🎯 Text Generation: Working")
            else:
                print(f"   🎯 Text Generation: HTTP {gen_response.status_code}")
                
        except Exception as e:
            print(f"   ❌ Focused ML Error: {e}")

def main():
    """Main test function"""
    print("🚀 Quick ML Model Test")
    print("=" * 50)
    
    # Test AI Assistant
    test_ai_assistant()
    
    # Test Adaptive AI
    test_adaptive_ai()
    
    # Test ML Services
    test_ml_services()
    
    print("\n" + "=" * 50)
    print("🎯 Test Complete!")
    print("\n💡 If services are not running, you can start them with:")
    print("   sudo ./setup_localai.sh")
    print("   sudo ./setup_focused_ml_models.sh")

if __name__ == "__main__":
    main() 