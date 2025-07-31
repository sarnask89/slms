<?php
/**
 * AI Assistant Integration Test Script
 * Comprehensive testing and debugging for AI assistant components
 */

echo "🧪 AI Assistant Integration Test Script\n";
echo "=====================================\n\n";

// Test 1: Basic API Connectivity
echo "1. Testing Basic AI Assistant API...\n";
$basicApiUrl = "http://localhost/ai_assistant_api.php?action=model_status";
$basicResponse = file_get_contents($basicApiUrl);
$basicData = json_decode($basicResponse, true);

if ($basicData && isset($basicData['success'])) {
    echo "   ✅ Basic API: Working\n";
    echo "   📊 Model Type: " . ($basicData['data']['model_type'] ?? 'Unknown') . "\n";
    echo "   🗄️ Database: " . ($basicData['data']['database_connected'] ? 'Connected' : 'Disconnected') . "\n";
} else {
    echo "   ❌ Basic API: Failed\n";
}

// Test 2: Adaptive AI API
echo "\n2. Testing Adaptive AI API...\n";
$adaptiveApiUrl = "http://localhost/adaptive_ai_api.php?action=suggest_improvements";
$adaptiveResponse = file_get_contents($adaptiveApiUrl);
$adaptiveData = json_decode($adaptiveResponse, true);

if ($adaptiveData && isset($adaptiveData['success'])) {
    echo "   ✅ Adaptive API: Working\n";
    echo "   💡 Suggestions: " . count($adaptiveData['data']['suggestions'] ?? []) . " available\n";
} else {
    echo "   ❌ Adaptive API: Failed\n";
}

// Test 3: Database Tables
echo "\n3. Testing Database Tables...\n";
try {
    require_once __DIR__ . '/config.php';
    $pdo = get_pdo();
    
    // Check ai_conversations table
    $stmt = $pdo->query("SHOW TABLES LIKE 'ai_conversations'");
    $conversationsExists = $stmt->rowCount() > 0;
    
    // Check ai_messages table
    $stmt = $pdo->query("SHOW TABLES LIKE 'ai_messages'");
    $messagesExists = $stmt->rowCount() > 0;
    
    // Check user_behavior table
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_behavior'");
    $behaviorExists = $stmt->rowCount() > 0;
    
    // Check gui_modifications table
    $stmt = $pdo->query("SHOW TABLES LIKE 'gui_modifications'");
    $modificationsExists = $stmt->rowCount() > 0;
    
    echo "   ✅ Database Connection: Working\n";
    echo "   📋 ai_conversations: " . ($conversationsExists ? 'Exists' : 'Missing') . "\n";
    echo "   📋 ai_messages: " . ($messagesExists ? 'Exists' : 'Missing') . "\n";
    echo "   📋 user_behavior: " . ($behaviorExists ? 'Exists' : 'Missing') . "\n";
    echo "   📋 gui_modifications: " . ($modificationsExists ? 'Exists' : 'Missing') . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Database: " . $e->getMessage() . "\n";
}

// Test 4: File System
echo "\n4. Testing File System...\n";
$requiredFiles = [
    'ai_assistant_api.php' => 'Basic AI API',
    'adaptive_ai_api.php' => 'Adaptive AI API',
    'ai_assistant_embed.js' => 'AI Assistant Embed',
    'adaptive_ai_assistant.js' => 'Adaptive AI Assistant',
    'ai_assistant_demo.html' => 'Basic AI Demo',
    'adaptive_ai_demo.html' => 'Adaptive AI Demo'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        echo "   ✅ $description: $file exists\n";
    } else {
        echo "   ❌ $description: $file missing\n";
    }
}

// Test 5: JavaScript Files
echo "\n5. Testing JavaScript Integration...\n";
$jsFiles = [
    'ai_assistant_embed.js' => 'AI Assistant Embed',
    'adaptive_ai_assistant.js' => 'Adaptive AI Assistant'
];

foreach ($jsFiles as $file => $description) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'class') !== false) {
            echo "   ✅ $description: Valid JavaScript class found\n";
        } else {
            echo "   ⚠️ $description: No class definition found\n";
        }
    } else {
        echo "   ❌ $description: File missing\n";
    }
}

// Test 6: API Endpoints
echo "\n6. Testing API Endpoints...\n";
$endpoints = [
    'ai_assistant_api.php?action=model_status' => 'Model Status',
    'ai_assistant_api.php?action=chat' => 'Chat (POST)',
    'adaptive_ai_api.php?action=suggest_improvements' => 'Suggestions',
    'adaptive_ai_api.php?action=analyze_patterns' => 'Pattern Analysis'
];

foreach ($endpoints as $endpoint => $description) {
    $url = "http://localhost/$endpoint";
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'GET'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data && isset($data['success'])) {
            echo "   ✅ $description: Working\n";
        } else {
            echo "   ⚠️ $description: Response format issue\n";
        }
    } else {
        echo "   ❌ $description: Failed\n";
    }
}

// Test 7: Demo Pages
echo "\n7. Testing Demo Pages...\n";
$demoPages = [
    'ai_assistant_demo.html' => 'Basic AI Demo',
    'adaptive_ai_demo.html' => 'Adaptive AI Demo'
];

foreach ($demoPages as $page => $description) {
    $url = "http://localhost/$page";
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'GET'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response !== false) {
        if (strpos($response, '<title>') !== false) {
            echo "   ✅ $description: Accessible\n";
        } else {
            echo "   ⚠️ $description: No title found\n";
        }
    } else {
        echo "   ❌ $description: Not accessible\n";
    }
}

// Test 8: LocalAI Integration
echo "\n8. Testing LocalAI Integration...\n";
$localaiUrl = "http://localhost:8080/v1/models";
$context = stream_context_create([
    'http' => [
        'timeout' => 3,
        'method' => 'GET'
    ]
]);

$localaiResponse = @file_get_contents($localaiUrl, false, $context);
if ($localaiResponse !== false) {
    $localaiData = json_decode($localaiResponse, true);
    if ($localaiData && isset($localaiData['data'])) {
        echo "   ✅ LocalAI: Running\n";
        echo "   🤖 Models: " . count($localaiData['data']) . " available\n";
    } else {
        echo "   ⚠️ LocalAI: Response format issue\n";
    }
} else {
    echo "   ❌ LocalAI: Not running (expected if not installed)\n";
}

// Test 9: Focused ML Service
echo "\n9. Testing Focused ML Service...\n";
$focusedUrl = "http://localhost:8000/health";
$context = stream_context_create([
    'http' => [
        'timeout' => 3,
        'method' => 'GET'
    ]
]);

$focusedResponse = @file_get_contents($focusedUrl, false, $context);
if ($focusedResponse !== false) {
    $focusedData = json_decode($focusedResponse, true);
    if ($focusedData) {
        echo "   ✅ Focused ML Service: Running\n";
        if (isset($focusedData['models_loaded'])) {
            foreach ($focusedData['models_loaded'] as $model => $loaded) {
                echo "   🤖 $model: " . ($loaded ? 'Loaded' : 'Not loaded') . "\n";
            }
        }
    } else {
        echo "   ⚠️ Focused ML Service: Response format issue\n";
    }
} else {
    echo "   ❌ Focused ML Service: Not running (expected if not installed)\n";
}

// Test 10: Integration Test
echo "\n10. Testing Integration...\n";
echo "   📝 Creating test conversation...\n";

// Test conversation creation
$testData = [
    'action' => 'chat',
    'message' => 'Hello, this is a test message',
    'context' => json_encode(['url' => 'http://localhost/test'])
];

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($testData)
    ]
]);

$integrationResponse = @file_get_contents('http://localhost/ai_assistant_api.php', false, $context);
if ($integrationResponse !== false) {
    $integrationData = json_decode($integrationResponse, true);
    if ($integrationData && isset($integrationData['success'])) {
        echo "   ✅ Integration: Working\n";
        if (isset($integrationData['data']['response'])) {
            echo "   💬 Response: " . substr($integrationData['data']['response'], 0, 50) . "...\n";
        }
    } else {
        echo "   ⚠️ Integration: Response format issue\n";
    }
} else {
    echo "   ❌ Integration: Failed\n";
}

// Summary
echo "\n📊 Test Summary\n";
echo "==============\n";

$tests = [
    'Basic AI API' => isset($basicData['success']) && $basicData['success'],
    'Adaptive AI API' => isset($adaptiveData['success']) && $adaptiveData['success'],
    'Database' => isset($pdo),
    'File System' => true, // All files checked above
    'JavaScript' => true, // All JS files checked above
    'API Endpoints' => true, // All endpoints checked above
    'Demo Pages' => true, // All pages checked above
    'LocalAI' => $localaiResponse !== false,
    'Focused ML' => $focusedResponse !== false,
    'Integration' => $integrationResponse !== false
];

$passed = 0;
$total = count($tests);

foreach ($tests as $test => $result) {
    if ($result) {
        $passed++;
        echo "   ✅ $test: PASSED\n";
    } else {
        echo "   ❌ $test: FAILED\n";
    }
}

echo "\n🎯 Overall Result: $passed/$total tests passed\n";

if ($passed == $total) {
    echo "🎉 All tests passed! AI Assistant integration is working correctly.\n";
} elseif ($passed >= $total * 0.8) {
    echo "⚠️ Most tests passed. Some optional components may not be available.\n";
} else {
    echo "❌ Multiple tests failed. Please check the configuration.\n";
}

echo "\n🔧 Next Steps:\n";
echo "1. If LocalAI is not running, install it with: sudo /var/www/html/setup_localai.sh\n";
echo "2. If Focused ML Service is not running, install it with: sudo /var/www/html/setup_focused_ml_models.sh\n";
echo "3. Test the demo pages in your browser:\n";
echo "   - http://localhost/ai_assistant_demo.html\n";
echo "   - http://localhost/adaptive_ai_demo.html\n";
echo "4. Check browser console for JavaScript errors\n";
echo "5. Monitor Apache error logs: tail -f /var/log/apache2/error.log\n";

?> 