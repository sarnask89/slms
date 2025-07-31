#!/bin/bash

# Local ML Models Setup Script for Adaptive AI Assistant
# Installs and configures the best free local ML models

echo "🧠 Setting up Local ML Models for Adaptive AI Assistant..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
MODELS_DIR="/var/www/html/local_ml_models"
OLLAMA_DIR="/opt/ollama"
LOCALAI_DIR="/var/www/html/localai"
PYTHON_ENV="/var/www/html/ml_env"

# Create directories
mkdir -p $MODELS_DIR
mkdir -p $LOCALAI_DIR
mkdir -p $PYTHON_ENV

cd /var/www/html

echo -e "${BLUE}📁 Created directories:${NC}"
echo "   - Models: $MODELS_DIR"
echo "   - LocalAI: $LOCALAI_DIR"
echo "   - Python Environment: $PYTHON_ENV"

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to install system dependencies
install_system_deps() {
    echo -e "${YELLOW}📦 Installing system dependencies...${NC}"
    
    # Update package list
    apt update
    
    # Install essential packages
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
        libgstreamer1.0-0 \
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
        docker-compose
    
    echo -e "${GREEN}✅ System dependencies installed${NC}"
}

# Function to setup Python environment
setup_python_env() {
    echo -e "${YELLOW}🐍 Setting up Python environment...${NC}"
    
    cd $PYTHON_ENV
    
    # Create virtual environment
    python3 -m venv venv
    source venv/bin/activate
    
    # Upgrade pip
    pip install --upgrade pip
    
    # Install ML libraries
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
        ffmpeg-python
    
    # Download spaCy model
    python -m spacy download en_core_web_sm
    
    # Download NLTK data
    python -c "import nltk; nltk.download('punkt'); nltk.download('stopwords'); nltk.download('wordnet')"
    
    echo -e "${GREEN}✅ Python environment setup complete${NC}"
}

# Function to install Ollama
install_ollama() {
    echo -e "${YELLOW}🦙 Installing Ollama...${NC}"
    
    # Download and install Ollama
    curl -fsSL https://ollama.ai/install.sh | sh
    
    # Start Ollama service
    systemctl enable ollama
    systemctl start ollama
    
    # Wait for Ollama to start
    sleep 5
    
    echo -e "${GREEN}✅ Ollama installed and started${NC}"
}

# Function to install LocalAI
install_localai() {
    echo -e "${YELLOW}🤖 Installing LocalAI...${NC}"
    
    cd $LOCALAI_DIR
    
    # Clone LocalAI repository
    if [ ! -d "LocalAI" ]; then
        git clone https://github.com/go-skynet/LocalAI
        cd LocalAI
    else
        cd LocalAI
        git pull
    fi
    
    # Create models directory
    mkdir -p models
    
    # Download lightweight models
    echo -e "${YELLOW}📥 Downloading lightweight models...${NC}"
    
    # Text generation model (GPT4All)
    if [ ! -f "models/ggml-gpt4all-j" ]; then
        wget https://gpt4all.io/models/ggml-gpt4all-j.bin -O models/ggml-gpt4all-j
    fi
    
    # Embeddings model
    if [ ! -f "models/bert" ]; then
        wget https://huggingface.co/skeskinen/ggml/resolve/main/all-MiniLM-L6-v2/ggml-model-q4_0.bin -O models/bert
    fi
    
    # Create model configurations
    echo -e "${YELLOW}⚙️ Creating model configurations...${NC}"
    
    # GPT4All configuration
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
    cat > .env << EOF
THREADS=4
MODELS_PATH=/models
DEBUG=true
EOF

    # Create docker-compose.yml
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

    echo -e "${GREEN}✅ LocalAI installed${NC}"
}

# Function to download Ollama models
download_ollama_models() {
    echo -e "${YELLOW}📥 Downloading Ollama models...${NC}"
    
    # Wait for Ollama to be ready
    sleep 10
    
    # Download lightweight models
    ollama pull llama2:7b
    ollama pull mistral:7b
    ollama pull codellama:7b
    ollama pull neural-chat:7b
    ollama pull phi:2.7b
    ollama pull tinyllama:1.1b
    
    echo -e "${GREEN}✅ Ollama models downloaded${NC}"
}

# Function to create Python ML service
create_ml_service() {
    echo -e "${YELLOW}🐍 Creating Python ML service...${NC}"
    
    cd $MODELS_DIR
    
    # Create FastAPI service
    cat > ml_service.py << 'EOF'
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List, Dict, Any
import torch
from transformers import pipeline, AutoTokenizer, AutoModel
from sentence_transformers import SentenceTransformer
import numpy as np
import json
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(title="Adaptive AI ML Service", version="1.0.0")

# Global variables for models
text_generator = None
embedding_model = None
sentiment_analyzer = None
text_classifier = None

class TextRequest(BaseModel):
    text: str
    max_length: int = 100
    temperature: float = 0.7

class EmbeddingRequest(BaseModel):
    texts: List[str]

class AnalysisRequest(BaseModel):
    text: str
    task: str = "sentiment"  # sentiment, classification, summarization

class BehaviorAnalysisRequest(BaseModel):
    user_actions: List[Dict[str, Any]]
    page_context: Dict[str, Any]

@app.on_event("startup")
async def load_models():
    global text_generator, embedding_model, sentiment_analyzer, text_classifier
    
    logger.info("Loading ML models...")
    
    try {
        # Load text generation model (smaller model for faster inference)
        text_generator = pipeline(
            "text-generation",
            model="microsoft/DialoGPT-small",
            torch_dtype=torch.float32,
            device_map="auto" if torch.cuda.is_available() else "cpu"
        )
        
        # Load embedding model
        embedding_model = SentenceTransformer('all-MiniLM-L6-v2')
        
        # Load sentiment analyzer
        sentiment_analyzer = pipeline(
            "sentiment-analysis",
            model="cardiffnlp/twitter-roberta-base-sentiment-latest"
        )
        
        # Load text classifier
        text_classifier = pipeline(
            "zero-shot-classification",
            model="facebook/bart-large-mnli"
        )
        
        logger.info("All models loaded successfully!")
        
    except Exception as e:
        logger.error(f"Error loading models: {e}")
        raise

@app.get("/")
async def root():
    return {"message": "Adaptive AI ML Service is running!"}

@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "models_loaded": {
            "text_generator": text_generator is not None,
            "embedding_model": embedding_model is not None,
            "sentiment_analyzer": sentiment_analyzer is not None,
            "text_classifier": text_classifier is not None
        }
    }

@app.post("/generate")
async def generate_text(request: TextRequest):
    try:
        if text_generator is None:
            raise HTTPException(status_code=503, detail="Text generation model not loaded")
        
        # Generate text
        result = text_generator(
            request.text,
            max_length=request.max_length,
            temperature=request.temperature,
            do_sample=True,
            pad_token_id=text_generator.tokenizer.eos_token_id
        )
        
        generated_text = result[0]['generated_text']
        
        return {
            "generated_text": generated_text,
            "input_text": request.text,
            "model": "microsoft/DialoGPT-small"
        }
        
    except Exception as e:
        logger.error(f"Error in text generation: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/embeddings")
async def get_embeddings(request: EmbeddingRequest):
    try:
        if embedding_model is None:
            raise HTTPException(status_code=503, detail="Embedding model not loaded")
        
        # Generate embeddings
        embeddings = embedding_model.encode(request.texts)
        
        return {
            "embeddings": embeddings.tolist(),
            "model": "all-MiniLM-L6-v2",
            "dimension": embeddings.shape[1]
        }
        
    except Exception as e:
        logger.error(f"Error in embedding generation: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/analyze")
async def analyze_text(request: AnalysisRequest):
    try:
        if request.task == "sentiment":
            if sentiment_analyzer is None:
                raise HTTPException(status_code=503, detail="Sentiment analyzer not loaded")
            
            result = sentiment_analyzer(request.text)
            return {
                "task": "sentiment",
                "text": request.text,
                "result": result[0],
                "model": "cardiffnlp/twitter-roberta-base-sentiment-latest"
            }
        
        elif request.task == "classification":
            if text_classifier is None:
                raise HTTPException(status_code=503, detail="Text classifier not loaded")
            
            # Define candidate labels for classification
            candidate_labels = [
                "user frustration",
                "efficiency issue", 
                "accessibility problem",
                "navigation difficulty",
                "form completion issue",
                "general question"
            ]
            
            result = text_classifier(request.text, candidate_labels)
            return {
                "task": "classification",
                "text": request.text,
                "result": result,
                "model": "facebook/bart-large-mnli"
            }
        
        else:
            raise HTTPException(status_code=400, detail="Unsupported task")
            
    except Exception as e:
        logger.error(f"Error in text analysis: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/analyze_behavior")
async def analyze_user_behavior(request: BehaviorAnalysisRequest):
    try:
        # Analyze user behavior patterns
        actions = request.user_actions
        context = request.page_context
        
        # Extract patterns
        patterns = {
            "frustration_level": 0.0,
            "repetitive_actions": 0.0,
            "efficiency_score": 0.0,
            "accessibility_issues": [],
            "suggestions": []
        }
        
        # Analyze click patterns
        click_actions = [a for a in actions if a.get('action_type') == 'click']
        if len(click_actions) > 10:
            # Check for rapid clicking (frustration indicator)
            rapid_clicks = 0
            for i in range(1, len(click_actions)):
                time_diff = click_actions[i].get('timestamp', 0) - click_actions[i-1].get('timestamp', 0)
                if time_diff < 500:  # Less than 500ms between clicks
                    rapid_clicks += 1
            
            patterns["frustration_level"] = min(1.0, rapid_clicks / len(click_actions))
        
        # Analyze repetitive actions
        element_counts = {}
        for action in actions:
            element_key = f"{action.get('element_type', '')}_{action.get('element_id', '')}"
            element_counts[element_key] = element_counts.get(element_key, 0) + 1
        
        max_repetitions = max(element_counts.values()) if element_counts else 0
        patterns["repetitive_actions"] = min(1.0, max_repetitions / len(actions))
        
        # Generate suggestions based on patterns
        if patterns["frustration_level"] > 0.7:
            patterns["suggestions"].append({
                "type": "shortcut",
                "title": "Add Keyboard Shortcuts",
                "description": "High frustration detected. Adding keyboard shortcuts may help.",
                "priority": "high"
            })
        
        if patterns["repetitive_actions"] > 0.8:
            patterns["suggestions"].append({
                "type": "automation",
                "title": "Automate Repetitive Actions",
                "description": "Many repetitive actions detected. Consider automation.",
                "priority": "medium"
            })
        
        # Check for accessibility issues
        small_elements = [a for a in actions if a.get('element_size', {}).get('width', 100) < 44]
        if small_elements:
            patterns["accessibility_issues"].append("small_elements")
            patterns["suggestions"].append({
                "type": "resize",
                "title": "Increase Element Sizes",
                "description": "Small elements detected. Increasing sizes may improve accessibility.",
                "priority": "high"
            })
        
        return {
            "patterns": patterns,
            "analysis_timestamp": "2025-07-30T17:00:00Z",
            "model": "behavior_analysis_v1"
        }
        
    except Exception as e:
        logger.error(f"Error in behavior analysis: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/models")
async def list_models():
    return {
        "available_models": {
            "text_generation": "microsoft/DialoGPT-small",
            "embeddings": "all-MiniLM-L6-v2",
            "sentiment_analysis": "cardiffnlp/twitter-roberta-base-sentiment-latest",
            "zero_shot_classification": "facebook/bart-large-mnli"
        },
        "model_status": {
            "text_generator": text_generator is not None,
            "embedding_model": embedding_model is not None,
            "sentiment_analyzer": sentiment_analyzer is not None,
            "text_classifier": text_classifier is not None
        }
    }

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
EOF

    # Create requirements.txt
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
EOF

    # Create systemd service
    cat > /etc/systemd/system/adaptive-ml.service << EOF
[Unit]
Description=Adaptive AI ML Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=$MODELS_DIR
Environment=PATH=$PYTHON_ENV/venv/bin
ExecStart=$PYTHON_ENV/venv/bin/python ml_service.py
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

    echo -e "${GREEN}✅ Python ML service created${NC}"
}

# Function to start services
start_services() {
    echo -e "${YELLOW}🚀 Starting services...${NC}"
    
    # Start LocalAI with Docker
    cd $LOCALAI_DIR/LocalAI
    docker-compose up -d
    
    # Start Python ML service
    systemctl daemon-reload
    systemctl enable adaptive-ml
    systemctl start adaptive-ml
    
    # Start Ollama (if not already running)
    systemctl start ollama
    
    echo -e "${GREEN}✅ All services started${NC}"
}

# Function to create integration script
create_integration() {
    echo -e "${YELLOW}🔗 Creating integration script...${NC}"
    
    cd /var/www/html
    
    # Create integration configuration
    cat > ml_integration_config.json << EOF
{
  "services": {
    "localai": {
      "url": "http://localhost:8080",
      "models": {
        "text_generation": "gpt-3.5-turbo",
        "embeddings": "text-embedding-ada-002"
      }
    },
    "ollama": {
      "url": "http://localhost:11434",
      "models": [
        "llama2:7b",
        "mistral:7b",
        "codellama:7b",
        "neural-chat:7b",
        "phi:2.7b",
        "tinyllama:1.1b"
      ]
    },
    "python_ml": {
      "url": "http://localhost:8000",
      "models": {
        "text_generation": "microsoft/DialoGPT-small",
        "embeddings": "all-MiniLM-L6-v2",
        "sentiment_analysis": "cardiffnlp/twitter-roberta-base-sentiment-latest",
        "classification": "facebook/bart-large-mnli"
      }
    }
  },
  "settings": {
    "default_service": "python_ml",
    "fallback_service": "localai",
    "max_tokens": 2048,
    "temperature": 0.7,
    "enable_behavior_analysis": true,
    "enable_sentiment_analysis": true
  }
}
EOF

    # Create integration script
    cat > integrate_ml_models.js << 'EOF'
/**
 * ML Models Integration for Adaptive AI Assistant
 * Connects to multiple local ML services
 */

class MLModelsIntegration {
    constructor(config = {}) {
        this.config = {
            localaiUrl: config.localaiUrl || 'http://localhost:8080',
            ollamaUrl: config.ollamaUrl || 'http://localhost:11434',
            pythonMLUrl: config.pythonMLUrl || 'http://localhost:8000',
            defaultService: config.defaultService || 'python_ml',
            ...config
        };
        
        this.services = {
            localai: this.createLocalAIService(),
            ollama: this.createOllamaService(),
            python_ml: this.createPythonMLService()
        };
        
        this.currentService = this.services[this.config.defaultService];
    }
    
    createLocalAIService() {
        return {
            name: 'LocalAI',
            url: this.config.localaiUrl,
            
            async generateText(prompt, options = {}) {
                try {
                    const response = await fetch(`${this.config.localaiUrl}/v1/chat/completions`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            model: 'gpt-3.5-turbo',
                            messages: [{ role: 'user', content: prompt }],
                            temperature: options.temperature || 0.7,
                            max_tokens: options.maxTokens || 2048
                        })
                    });
                    
                    if (!response.ok) throw new Error('LocalAI request failed');
                    
                    const data = await response.json();
                    return data.choices[0].message.content;
                } catch (error) {
                    console.error('LocalAI error:', error);
                    throw error;
                }
            },
            
            async getEmbeddings(texts) {
                try {
                    const response = await fetch(`${this.config.localaiUrl}/v1/embeddings`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            model: 'text-embedding-ada-002',
                            input: texts
                        })
                    });
                    
                    if (!response.ok) throw new Error('LocalAI embeddings failed');
                    
                    const data = await response.json();
                    return data.data.map(item => item.embedding);
                } catch (error) {
                    console.error('LocalAI embeddings error:', error);
                    throw error;
                }
            }
        };
    }
    
    createOllamaService() {
        return {
            name: 'Ollama',
            url: this.config.ollamaUrl,
            
            async generateText(prompt, options = {}) {
                try {
                    const response = await fetch(`${this.config.ollamaUrl}/api/generate`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            model: options.model || 'llama2:7b',
                            prompt: prompt,
                            stream: false,
                            options: {
                                temperature: options.temperature || 0.7,
                                num_predict: options.maxTokens || 2048
                            }
                        })
                    });
                    
                    if (!response.ok) throw new Error('Ollama request failed');
                    
                    const data = await response.json();
                    return data.response;
                } catch (error) {
                    console.error('Ollama error:', error);
                    throw error;
                }
            },
            
            async listModels() {
                try {
                    const response = await fetch(`${this.config.ollamaUrl}/api/tags`);
                    if (!response.ok) throw new Error('Ollama models request failed');
                    
                    const data = await response.json();
                    return data.models.map(model => model.name);
                } catch (error) {
                    console.error('Ollama models error:', error);
                    return [];
                }
            }
        };
    }
    
    createPythonMLService() {
        return {
            name: 'Python ML',
            url: this.config.pythonMLUrl,
            
            async generateText(prompt, options = {}) {
                try {
                    const response = await fetch(`${this.config.pythonMLUrl}/generate`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            text: prompt,
                            max_length: options.maxTokens || 100,
                            temperature: options.temperature || 0.7
                        })
                    });
                    
                    if (!response.ok) throw new Error('Python ML request failed');
                    
                    const data = await response.json();
                    return data.generated_text;
                } catch (error) {
                    console.error('Python ML error:', error);
                    throw error;
                }
            },
            
            async getEmbeddings(texts) {
                try {
                    const response = await fetch(`${this.config.pythonMLUrl}/embeddings`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ texts: texts })
                    });
                    
                    if (!response.ok) throw new Error('Python ML embeddings failed');
                    
                    const data = await response.json();
                    return data.embeddings;
                } catch (error) {
                    console.error('Python ML embeddings error:', error);
                    throw error;
                }
            },
            
            async analyzeSentiment(text) {
                try {
                    const response = await fetch(`${this.config.pythonMLUrl}/analyze`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            text: text,
                            task: 'sentiment'
                        })
                    });
                    
                    if (!response.ok) throw new Error('Python ML sentiment analysis failed');
                    
                    const data = await response.json();
                    return data.result;
                } catch (error) {
                    console.error('Python ML sentiment error:', error);
                    throw error;
                }
            },
            
            async analyzeBehavior(userActions, pageContext) {
                try {
                    const response = await fetch(`${this.config.pythonMLUrl}/analyze_behavior`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            user_actions: userActions,
                            page_context: pageContext
                        })
                    });
                    
                    if (!response.ok) throw new Error('Python ML behavior analysis failed');
                    
                    const data = await response.json();
                    return data.patterns;
                } catch (error) {
                    console.error('Python ML behavior analysis error:', error);
                    throw error;
                }
            }
        };
    }
    
    async generateResponse(prompt, options = {}) {
        // Try primary service first
        try {
            return await this.currentService.generateText(prompt, options);
        } catch (error) {
            console.warn(`Primary service failed, trying fallback...`);
            
            // Try fallback services
            for (const [name, service] of Object.entries(this.services)) {
                if (service === this.currentService) continue;
                
                try {
                    return await service.generateText(prompt, options);
                } catch (fallbackError) {
                    console.warn(`${name} fallback failed:`, fallbackError);
                }
            }
            
            throw new Error('All ML services failed');
        }
    }
    
    async getEmbeddings(texts) {
        try {
            return await this.currentService.getEmbeddings(texts);
        } catch (error) {
            // Try fallback
            for (const [name, service] of Object.entries(this.services)) {
                if (service === this.currentService) continue;
                
                try {
                    return await service.getEmbeddings(texts);
                } catch (fallbackError) {
                    console.warn(`${name} embeddings fallback failed:`, fallbackError);
                }
            }
            
            throw new Error('All embedding services failed');
        }
    }
    
    async analyzeSentiment(text) {
        if (this.currentService.analyzeSentiment) {
            try {
                return await this.currentService.analyzeSentiment(text);
            } catch (error) {
                console.error('Sentiment analysis failed:', error);
                return { label: 'neutral', score: 0.5 };
            }
        }
        return { label: 'neutral', score: 0.5 };
    }
    
    async analyzeBehavior(userActions, pageContext) {
        if (this.currentService.analyzeBehavior) {
            try {
                return await this.currentService.analyzeBehavior(userActions, pageContext);
            } catch (error) {
                console.error('Behavior analysis failed:', error);
                return {
                    frustration_level: 0.0,
                    repetitive_actions: 0.0,
                    efficiency_score: 0.0,
                    accessibility_issues: [],
                    suggestions: []
                };
            }
        }
        return {
            frustration_level: 0.0,
            repetitive_actions: 0.0,
            efficiency_score: 0.0,
            accessibility_issues: [],
            suggestions: []
        };
    }
    
    async getServiceStatus() {
        const status = {};
        
        for (const [name, service] of Object.entries(this.services)) {
            try {
                const response = await fetch(`${service.url}/health`, { 
                    method: 'GET',
                    timeout: 5000 
                });
                status[name] = response.ok;
            } catch (error) {
                status[name] = false;
            }
        }
        
        return status;
    }
}

// Export for use
window.MLModelsIntegration = MLModelsIntegration;
EOF

    echo -e "${GREEN}✅ Integration script created${NC}"
}

# Function to test services
test_services() {
    echo -e "${YELLOW}🧪 Testing services...${NC}"
    
    # Wait for services to start
    sleep 10
    
    # Test LocalAI
    echo -e "${BLUE}Testing LocalAI...${NC}"
    if curl -s "http://localhost:8080/v1/models" > /dev/null; then
        echo -e "${GREEN}✅ LocalAI is running${NC}"
    else
        echo -e "${RED}❌ LocalAI failed to start${NC}"
    fi
    
    # Test Ollama
    echo -e "${BLUE}Testing Ollama...${NC}"
    if curl -s "http://localhost:11434/api/tags" > /dev/null; then
        echo -e "${GREEN}✅ Ollama is running${NC}"
    else
        echo -e "${RED}❌ Ollama failed to start${NC}"
    fi
    
    # Test Python ML Service
    echo -e "${BLUE}Testing Python ML Service...${NC}"
    if curl -s "http://localhost:8000/health" > /dev/null; then
        echo -e "${GREEN}✅ Python ML Service is running${NC}"
    else
        echo -e "${RED}❌ Python ML Service failed to start${NC}"
    fi
}

# Function to create startup script
create_startup_script() {
    echo -e "${YELLOW}📝 Creating startup script...${NC}"
    
    cat > /var/www/html/start_ml_services.sh << 'EOF'
#!/bin/bash

# Start ML Services for Adaptive AI Assistant
echo "🚀 Starting ML Services..."

# Start LocalAI
cd /var/www/html/localai/LocalAI
docker-compose up -d

# Start Python ML Service
systemctl start adaptive-ml

# Start Ollama
systemctl start ollama

echo "✅ All ML services started!"
echo ""
echo "🌐 Service URLs:"
echo "   - LocalAI: http://localhost:8080"
echo "   - Ollama: http://localhost:11434"
echo "   - Python ML: http://localhost:8000"
echo "   - Adaptive AI Demo: http://localhost/adaptive_ai_demo.html"
EOF

    chmod +x /var/www/html/start_ml_services.sh
    
    echo -e "${GREEN}✅ Startup script created${NC}"
}

# Main execution
main() {
    echo -e "${BLUE}🧠 Setting up Local ML Models for Adaptive AI Assistant${NC}"
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
    
    # Install Ollama
    install_ollama
    
    # Install LocalAI
    install_localai
    
    # Download Ollama models
    download_ollama_models
    
    # Create Python ML service
    create_ml_service
    
    # Create integration
    create_integration
    
    # Start services
    start_services
    
    # Test services
    test_services
    
    # Create startup script
    create_startup_script
    
    echo ""
    echo -e "${GREEN}🎉 Local ML Models Setup Complete!${NC}"
    echo "=================================================="
    echo ""
    echo -e "${BLUE}📋 Installed Services:${NC}"
    echo "   • LocalAI (Docker) - http://localhost:8080"
    echo "   • Ollama - http://localhost:11434"
    echo "   • Python ML Service - http://localhost:8000"
    echo ""
    echo -e "${BLUE}🤖 Available Models:${NC}"
    echo "   • GPT4All-J (Text Generation)"
    echo "   • All-MiniLM-L6-v2 (Embeddings)"
    echo "   • Llama2:7b, Mistral:7b, CodeLlama:7b"
    echo "   • Neural-Chat:7b, Phi:2.7b, TinyLlama:1.1b"
    echo "   • DialoGPT-small, BERT, RoBERTa"
    echo ""
    echo -e "${BLUE}🔗 Integration Files:${NC}"
    echo "   • ml_integration_config.json - Configuration"
    echo "   • integrate_ml_models.js - Integration script"
    echo "   • start_ml_services.sh - Startup script"
    echo ""
    echo -e "${BLUE}🌐 Demo Pages:${NC}"
    echo "   • Adaptive AI Demo: http://localhost/adaptive_ai_demo.html"
    echo "   • Basic AI Demo: http://localhost/ai_assistant_demo.html"
    echo ""
    echo -e "${YELLOW}📝 Next Steps:${NC}"
    echo "1. Test the services: ./start_ml_services.sh"
    echo "2. Integrate with your adaptive AI assistant"
    echo "3. Customize models and configurations"
    echo ""
    echo -e "${GREEN}✅ Setup complete! Your adaptive AI assistant now has powerful local ML capabilities!${NC}"
}

# Run main function
main "$@" 