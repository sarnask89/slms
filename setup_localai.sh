#!/bin/bash

# LocalAI Setup Script
# This script installs LocalAI and downloads free models

echo "🤖 Setting up LocalAI for AI Assistant..."

# Create directories
mkdir -p /var/www/html/ai_models
mkdir -p /var/www/html/localai

cd /var/www/html/localai

# Check if LocalAI is already installed
if [ ! -d "LocalAI" ]; then
    echo "📥 Cloning LocalAI repository..."
    git clone https://github.com/go-skynet/LocalAI
    cd LocalAI
else
    echo "✅ LocalAI already exists, updating..."
    cd LocalAI
    git pull
fi

# Create models directory
mkdir -p models

# Download free models
echo "📥 Downloading free models..."

# GPT4All-J model (text generation)
if [ ! -f "models/ggml-gpt4all-j" ]; then
    echo "Downloading GPT4All-J model..."
    wget https://gpt4all.io/models/ggml-gpt4all-j.bin -O models/ggml-gpt4all-j
fi

# All-MiniLM-L6-v2 model (embeddings)
if [ ! -f "models/bert" ]; then
    echo "Downloading All-MiniLM-L6-v2 model..."
    wget https://huggingface.co/skeskinen/ggml/resolve/main/all-MiniLM-L6-v2/ggml-model-q4_0.bin -O models/bert
fi

# Create model configuration files
echo "⚙️ Creating model configurations..."

# GPT4All-J configuration
cat > models/gpt-3.5-turbo.yaml << EOF
name: gpt-3.5-turbo
backend: gpt4all
parameters:
  model: ggml-gpt4all-j
  temperature: 0.7
  top_p: 0.9
  top_k: 40
  repeat_penalty: 1.1
  max_tokens: 2048
context_size: 4096
EOF

# Embeddings configuration
cat > models/embeddings.yaml << EOF
name: text-embedding-ada-002
backend: gpt4all-embeddings
parameters:
  model: bert
EOF

# Create .env file
if [ ! -f ".env" ]; then
    echo "📝 Creating .env configuration..."
    cat > .env << EOF
THREADS=4
MODELS_PATH=/models
DEBUG=true
EOF
fi

# Check if Docker is available
if command -v docker &> /dev/null; then
    echo "🐳 Docker found, starting LocalAI with Docker..."
    
    # Create docker-compose.yml if it doesn't exist
    if [ ! -f "docker-compose.yml" ]; then
        cat > docker-compose.yml << EOF
version: '3.8'
services:
  localai:
    image: quay.io/go-skynet/local-ai:latest
    ports:
      - "8080:8080"
    volumes:
      - ./models:/models
    environment:
      - THREADS=4
      - MODELS_PATH=/models
      - DEBUG=true
    restart: unless-stopped
EOF
    fi
    
    # Start LocalAI
    docker compose up -d --build
    
    echo "✅ LocalAI started with Docker!"
    echo "🌐 Access LocalAI at: http://localhost:8080"
    echo "📋 Check models at: http://localhost:8080/v1/models"
    
else
    echo "🐳 Docker not found, starting LocalAI directly..."
    
    # Download LocalAI binary
    if [ ! -f "localai" ]; then
        echo "📥 Downloading LocalAI binary..."
        wget https://github.com/go-skynet/LocalAI/releases/latest/download/localai-linux-amd64 -O localai
        chmod +x localai
    fi
    
    # Start LocalAI
    echo "🚀 Starting LocalAI..."
    ./localai &
    LOCALAI_PID=$!
    echo $LOCALAI_PID > localai.pid
    
    echo "✅ LocalAI started with PID: $LOCALAI_PID"
    echo "🌐 Access LocalAI at: http://localhost:8080"
fi

# Update AI Assistant configuration
echo "🔧 Updating AI Assistant configuration..."

# Create a configuration file for the AI assistant
cat > /var/www/html/ai_assistant_config.json << EOF
{
  "localai_url": "http://localhost:8080",
  "models": {
    "text_generation": "gpt-3.5-turbo",
    "embeddings": "text-embedding-ada-002"
  },
  "settings": {
    "temperature": 0.7,
    "max_tokens": 2048,
    "context_size": 4096
  }
}
EOF

echo "✅ Setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Test LocalAI: curl http://localhost:8080/v1/models"
echo "2. Update your AI Assistant to use LocalAI models"
echo "3. Test the assistant with: curl http://localhost/ai_assistant_api.php?action=model_status"
echo ""
echo "🔗 Useful URLs:"
echo "- LocalAI API: http://localhost:8080"
echo "- AI Assistant Demo: http://localhost/ai_assistant_demo.html"
echo "- AI Assistant API: http://localhost/ai_assistant_api.php" 