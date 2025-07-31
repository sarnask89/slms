# 🧪 Local ML Model Testing Guide

## **📊 Current Test Results**

### **✅ Working Components**
- ✅ **AI Assistant API**: Fully functional with local rule-based responses
- ✅ **Adaptive AI API**: Behavior tracking and pattern analysis working
- ✅ **Chat Functionality**: Interactive chat with contextual responses
- ✅ **Database Integration**: All tables and queries working correctly

### **⚠️ ML Services Not Running**
- ❌ **LocalAI** (Port 8080): Not accessible - needs to be started
- ❌ **Focused ML Service** (Port 8000): Not accessible - needs to be started

---

## **🚀 Quick Start Testing**

### **1. Test Current Functionality**
```bash
# Quick test of working components
python3 quick_ml_test.py
```

### **2. Comprehensive Testing**
```bash
# Full testing framework with service management
python3 test_local_ml_models.py
```

### **3. Browser Testing**
- **Debug Page**: `http://localhost/debug_ai_integration.html`
- **AI Assistant Demo**: `http://localhost/ai_assistant_demo.html`
- **Adaptive AI Demo**: `http://localhost/adaptive_ai_demo.html`

---

## **🔧 Starting ML Services**

### **Option 1: Start LocalAI (Enhanced AI Capabilities)**
```bash
# Install and start LocalAI with GPT4All-J model
sudo ./setup_localai.sh
```

**Benefits:**
- More sophisticated AI responses
- Better context understanding
- Enhanced conversation capabilities

### **Option 2: Start Focused ML Service (Specialized Tasks)**
```bash
# Install and start Focused ML Service
sudo ./setup_focused_ml_models.sh
```

**Benefits:**
- Specialized network analysis
- GUI modification capabilities
- Database update assistance
- Module content analysis

### **Option 3: Start Both Services**
```bash
# Start both services for full functionality
sudo ./setup_localai.sh
sudo ./setup_focused_ml_models.sh
```

---

## **🧪 Testing Framework**

### **Test Scripts Available**

#### **1. Quick Test (`quick_ml_test.py`)**
- **Purpose**: Fast check of all components
- **Duration**: ~30 seconds
- **Output**: Simple pass/fail status

#### **2. Comprehensive Test (`test_local_ml_models.py`)**
- **Purpose**: Detailed testing with service management
- **Duration**: ~2-5 minutes
- **Features**:
  - Service health checks
  - Model functionality testing
  - Integration testing
  - Performance metrics
  - Automatic service startup

### **Manual Testing Commands**

#### **Test AI Assistant**
```bash
# Test model status
curl "http://localhost/ai_assistant_api.php?action=model_status"

# Test chat functionality
curl -X POST "http://localhost/ai_assistant_api.php" \
  -H "Content-Type: application/json" \
  -d '{"action":"chat","message":"Hello, test message"}'
```

#### **Test Adaptive AI**
```bash
# Test behavior tracking
curl -X POST "http://localhost/adaptive_ai_api.php" \
  -H "Content-Type: application/json" \
  -d '{"action":"track_behavior","action_type":"click","element_id":"test"}'

# Test pattern analysis
curl "http://localhost/adaptive_ai_api.php?action=analyze_patterns"
```

#### **Test LocalAI (when running)**
```bash
# Check available models
curl "http://localhost:8080/v1/models"

# Test model completion
curl -X POST "http://localhost:8080/v1/completions" \
  -H "Content-Type: application/json" \
  -d '{"model":"gpt4all-j","prompt":"Hello","max_tokens":50}'
```

#### **Test Focused ML Service (when running)**
```bash
# Check service health
curl "http://localhost:8000/health"

# Test text generation
curl -X POST "http://localhost:8000/generate" \
  -H "Content-Type: application/json" \
  -d '{"text":"Test message","max_length":50}'
```

---

## **📈 Performance Testing**

### **Response Time Testing**
```bash
# Test API response times
time curl "http://localhost/ai_assistant_api.php?action=model_status"
time curl -X POST "http://localhost/ai_assistant_api.php" \
  -H "Content-Type: application/json" \
  -d '{"action":"chat","message":"Test"}'
```

### **Load Testing**
```bash
# Simple load test (10 concurrent requests)
for i in {1..10}; do
  curl -X POST "http://localhost/ai_assistant_api.php" \
    -H "Content-Type: application/json" \
    -d '{"action":"chat","message":"Load test '$i'"}' &
done
wait
```

---

## **🔍 Troubleshooting**

### **Common Issues**

#### **1. Port 8080 Not Accessible**
```bash
# Check if LocalAI is running
sudo systemctl status localai
# or
docker ps | grep localai

# Start LocalAI if not running
sudo ./setup_localai.sh
```

#### **2. Port 8000 Not Accessible**
```bash
# Check if Focused ML Service is running
sudo systemctl status focused-ml-service
# or
ps aux | grep python | grep focused

# Start Focused ML Service if not running
sudo ./setup_focused_ml_models.sh
```

#### **3. Database Connection Issues**
```bash
# Check database connection
mysql -u slms -p slmsdb -e "SELECT 1;"

# Check PHP database connection
curl "http://localhost/ai_assistant_api.php?action=model_status"
```

#### **4. Permission Issues**
```bash
# Fix script permissions
chmod +x *.sh
chmod +x *.py

# Check file ownership
ls -la *.sh *.py
```

### **Service Logs**
```bash
# Check Apache logs
tail -f /var/log/apache2/error.log

# Check LocalAI logs (if using Docker)
docker logs localai

# Check Focused ML Service logs
sudo journalctl -u focused-ml-service -f
```

---

## **🎯 Testing Scenarios**

### **Scenario 1: Basic AI Assistant**
1. **Start**: AI Assistant is working with rule-based responses
2. **Test**: Chat functionality, context awareness
3. **Expected**: Smooth conversation with helpful responses

### **Scenario 2: Enhanced AI Assistant (with LocalAI)**
1. **Start**: LocalAI service running on port 8080
2. **Test**: More sophisticated responses, better context
3. **Expected**: Enhanced conversation quality

### **Scenario 3: Adaptive AI System**
1. **Start**: Adaptive AI tracking user behavior
2. **Test**: GUI modifications, pattern analysis
3. **Expected**: System learns and adapts to user preferences

### **Scenario 4: Full ML Integration**
1. **Start**: All services running (LocalAI + Focused ML)
2. **Test**: Complete functionality with specialized tasks
3. **Expected**: Advanced AI capabilities with specialized features

---

## **📊 Expected Test Results**

### **Without ML Services**
- ✅ AI Assistant: Working (rule-based)
- ✅ Adaptive AI: Working (basic functionality)
- ❌ LocalAI: Not accessible
- ❌ Focused ML: Not accessible
- **Overall**: 50% functionality

### **With LocalAI Only**
- ✅ AI Assistant: Working (enhanced)
- ✅ Adaptive AI: Working
- ✅ LocalAI: Working
- ❌ Focused ML: Not accessible
- **Overall**: 75% functionality

### **With Focused ML Only**
- ✅ AI Assistant: Working (rule-based)
- ✅ Adaptive AI: Working (enhanced)
- ❌ LocalAI: Not accessible
- ✅ Focused ML: Working
- **Overall**: 75% functionality

### **With All Services**
- ✅ AI Assistant: Working (enhanced)
- ✅ Adaptive AI: Working (enhanced)
- ✅ LocalAI: Working
- ✅ Focused ML: Working
- **Overall**: 100% functionality

---

## **🚀 Next Steps**

### **Immediate Actions**
1. **Test Current Setup**: Run `python3 quick_ml_test.py`
2. **Choose Enhancement**: Decide which ML service to start
3. **Start Services**: Run appropriate setup script
4. **Verify Integration**: Test enhanced functionality

### **Advanced Testing**
1. **Performance Testing**: Measure response times
2. **Load Testing**: Test concurrent usage
3. **Integration Testing**: Test with real user scenarios
4. **Monitoring**: Set up logging and monitoring

---

## **📋 Test Checklist**

- [ ] **Basic AI Assistant**: Chat functionality working
- [ ] **Adaptive AI**: Behavior tracking working
- [ ] **Database**: All tables and queries working
- [ ] **LocalAI**: Service running and accessible (optional)
- [ ] **Focused ML**: Service running and accessible (optional)
- [ ] **Integration**: All components working together
- [ ] **Performance**: Response times acceptable
- [ ] **Error Handling**: Graceful error recovery

---

**🎯 Goal**: Achieve 100% functionality with all ML services running for the best user experience! 