# 🤖 AI Assistant & ML Models Documentation

## Overview

The sLMS AI Assistant provides intelligent, context-aware assistance throughout the system. It leverages machine learning models and natural language processing to help users navigate the system, automate tasks, and provide intelligent recommendations.

## Features

### 🧠 Core Capabilities
- **Natural Language Understanding** - Process user queries in natural language
- **Context-Aware Responses** - Understand current user context and permissions
- **Action Automation** - Execute system actions through conversational interface
- **Intelligent Recommendations** - ML-powered suggestions based on patterns
- **Multi-language Support** - Communicate in multiple languages

### 🔧 Integration Points
- **API Endpoints** - RESTful API for assistant interactions
- **WebSocket Support** - Real-time communication (planned)
- **Module Integration** - Deep integration with all system modules
- **External AI Services** - Support for OpenAI, Google AI, custom models

## API Endpoints

### 1. Ask Assistant
**Endpoint**: `POST /api/assistant/ask`

Send natural language queries to the assistant.

**Request**:
```json
{
  "question": "How many active clients do we have?",
  "context": {
    "module": "clients",
    "user_id": 123
  }
}
```

**Response**:
```json
{
  "answer": "You currently have 156 active clients in the system.",
  "data": {
    "total_clients": 156,
    "active": 156,
    "inactive": 23
  },
  "suggestions": [
    "View client list",
    "Generate client report",
    "Add new client"
  ]
}
```

### 2. Execute Action
**Endpoint**: `POST /api/assistant/action`

Execute system actions through the assistant.

**Request**:
```json
{
  "action": "create_client",
  "params": {
    "company_name": "Example Corp",
    "email": "contact@example.com",
    "phone": "+1234567890"
  },
  "confirmation": true
}
```

**Response**:
```json
{
  "status": "success",
  "message": "Client 'Example Corp' created successfully",
  "data": {
    "client_id": 789,
    "created_at": "2024-12-20T10:30:00Z"
  },
  "next_actions": [
    "Add services to client",
    "Create invoice",
    "Assign devices"
  ]
}
```

### 3. Get Context
**Endpoint**: `GET /api/assistant/context`

Retrieve current user context for the assistant.

**Request**:
```
GET /api/assistant/context?module=devices&user_id=123
```

**Response**:
```json
{
  "context": {
    "user_id": 123,
    "username": "admin",
    "current_module": "devices",
    "permissions": [
      "clients.read",
      "clients.write",
      "devices.read",
      "devices.write",
      "network.read",
      "financial.read"
    ],
    "recent_actions": [
      "viewed_device_list",
      "edited_device_192.168.1.1",
      "added_new_network"
    ],
    "preferences": {
      "language": "en",
      "timezone": "UTC",
      "date_format": "Y-m-d"
    }
  }
}
```

## ML Models

### 1. Intent Classification Model
Classifies user intents from natural language queries.

**Supported Intents**:
- `query.count` - Counting entities (clients, devices, etc.)
- `query.list` - Listing entities
- `query.search` - Searching for specific items
- `action.create` - Creating new entities
- `action.update` - Updating existing entities
- `action.delete` - Deleting entities
- `report.generate` - Generating reports
- `help.general` - General help requests
- `navigation.goto` - Navigation requests

### 2. Entity Recognition Model
Extracts entities from user queries.

**Recognized Entities**:
- `client` - Client names or IDs
- `device` - Device names or IP addresses
- `network` - Network names or subnets
- `service` - Service types or packages
- `date` - Date and time references
- `number` - Numeric values
- `status` - Status values (active, inactive, etc.)

### 3. Recommendation Engine
Provides intelligent suggestions based on user behavior.

**Recommendation Types**:
- **Next Actions** - Suggest logical next steps
- **Similar Items** - Find similar clients/devices
- **Optimization Tips** - System optimization suggestions
- **Predictive Alerts** - Predict potential issues

## Integration Examples

### JavaScript Integration
```javascript
// Initialize AI Assistant
const assistant = new SLMSAssistant({
  apiUrl: '/api/assistant',
  userId: currentUser.id,
  language: 'en'
});

// Ask a question
async function askAssistant(question) {
  try {
    const response = await assistant.ask(question);
    console.log('Answer:', response.answer);
    
    // Display suggestions
    response.suggestions.forEach(suggestion => {
      console.log('Suggestion:', suggestion);
    });
  } catch (error) {
    console.error('Assistant error:', error);
  }
}

// Execute an action
async function executeAction(action, params) {
  try {
    const response = await assistant.executeAction(action, params);
    console.log('Status:', response.status);
    console.log('Message:', response.message);
  } catch (error) {
    console.error('Action error:', error);
  }
}
```

### PHP Integration
```php
<?php
// Include assistant client
require_once 'api/AssistantClient.php';

$assistant = new AssistantClient([
    'api_url' => '/api/assistant',
    'user_id' => $_SESSION['user_id']
]);

// Ask a question
$response = $assistant->ask("How many devices are offline?");
echo "Answer: " . $response['answer'] . "\n";

// Execute action
$result = $assistant->executeAction('create_client', [
    'company_name' => 'New Client Corp',
    'email' => 'new@client.com'
]);
```

## Configuration

### Environment Variables
```env
# AI Service Configuration
AI_SERVICE_PROVIDER=openai
AI_API_KEY=your-api-key-here
AI_MODEL=gpt-4
AI_MAX_TOKENS=500
AI_TEMPERATURE=0.7

# ML Model Settings
ML_INTENT_MODEL_PATH=/models/intent_classifier.pkl
ML_ENTITY_MODEL_PATH=/models/entity_recognizer.pkl
ML_RECOMMENDATION_ENGINE=enabled

# Assistant Settings
ASSISTANT_RESPONSE_TIMEOUT=30
ASSISTANT_CACHE_TTL=300
ASSISTANT_MAX_CONTEXT_SIZE=10
```

### Configuration File
```php
// config/assistant.php
return [
    'providers' => [
        'openai' => [
            'api_key' => env('AI_API_KEY'),
            'model' => env('AI_MODEL', 'gpt-4'),
            'max_tokens' => env('AI_MAX_TOKENS', 500),
            'temperature' => env('AI_TEMPERATURE', 0.7)
        ],
        'local' => [
            'model_path' => env('ML_LOCAL_MODEL_PATH'),
            'use_gpu' => env('ML_USE_GPU', false)
        ]
    ],
    'intents' => [
        'enabled' => true,
        'confidence_threshold' => 0.7
    ],
    'context' => [
        'max_history' => 10,
        'include_permissions' => true,
        'include_preferences' => true
    ]
];
```

## Advanced Features

### 1. Custom Actions
Define custom actions for the assistant:

```php
// api/assistant/actions/CustomAction.php
class CustomAction extends BaseAction {
    public function execute($params) {
        // Validate parameters
        $this->validate($params, [
            'field1' => 'required|string',
            'field2' => 'numeric'
        ]);
        
        // Execute action
        $result = $this->performAction($params);
        
        // Return response
        return [
            'status' => 'success',
            'data' => $result
        ];
    }
}
```

### 2. Context Providers
Add custom context providers:

```php
// api/assistant/context/NetworkContextProvider.php
class NetworkContextProvider implements ContextProvider {
    public function getContext($userId, $module) {
        return [
            'active_networks' => Network::where('status', 'active')->count(),
            'recent_alerts' => Alert::recent()->limit(5)->get(),
            'network_health' => $this->calculateNetworkHealth()
        ];
    }
}
```

### 3. ML Model Training
Train custom models for your specific use case:

```python
# scripts/train_intent_model.py
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
import joblib

# Load training data
X_train, y_train = load_training_data()

# Train model
model = RandomForestClassifier(n_estimators=100)
model.fit(X_train, y_train)

# Save model
joblib.dump(model, 'models/intent_classifier.pkl')
```

## Security Considerations

### Authentication & Authorization
- All API endpoints require authentication
- Actions are validated against user permissions
- Sensitive data is filtered based on user role

### Input Validation
- All user inputs are sanitized
- SQL injection protection
- XSS prevention

### Rate Limiting
- API calls are rate-limited per user
- Burst protection for heavy queries
- Graceful degradation under load

## Performance Optimization

### Caching Strategy
- Response caching for frequent queries
- Context caching per session
- Model predictions cached

### Async Processing
- Long-running actions processed asynchronously
- WebSocket notifications for completion
- Queue-based task processing

## Troubleshooting

### Common Issues

1. **Assistant not responding**
   - Check API endpoint configuration
   - Verify AI service credentials
   - Check rate limits

2. **Incorrect responses**
   - Update context providers
   - Retrain ML models
   - Check intent mappings

3. **Performance issues**
   - Enable caching
   - Optimize database queries
   - Use async processing

## Future Enhancements

### Planned Features
1. **Voice Interface** - Voice commands and responses
2. **Proactive Assistance** - Predictive suggestions
3. **Multi-modal Input** - Image and document processing
4. **Advanced Analytics** - Deep learning insights
5. **Workflow Automation** - Complex task automation

### Roadmap
- Q1 2025: WebSocket real-time updates
- Q2 2025: Voice interface beta
- Q3 2025: Advanced ML models
- Q4 2025: Full automation suite

## Support

For AI Assistant support:
- **Documentation**: This guide
- **API Issues**: Check [API troubleshooting](../api-reference/troubleshooting.md)
- **Feature Requests**: Submit via feedback system
- **Community**: Join our AI/ML discussion forum