#!/usr/bin/env python3
"""
SLMS ML System MCP Server for Copilot
Model Context Protocol server to serve the SLMS Machine Learning System
"""

import asyncio
import json
import logging
import sys
import subprocess
import tempfile
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, List, Optional, Sequence
import mysql.connector
from mysql.connector import Error
import requests
from dataclasses import dataclass, asdict
import os

# MCP imports
from mcp.server import Server
from mcp.server.models import InitializationOptions
from mcp.server.stdio import stdio_server
from mcp.types import (
    CallToolRequest,
    CallToolResult,
    ListToolsRequest,
    ListToolsResult,
    Tool,
    TextContent,
    ImageContent,
    EmbeddedResource
)

class DummyNotificationOptions:
    tools_changed = False

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

@dataclass
class MLModel:
    """ML Model data structure"""
    id: int
    name: str
    type: str
    description: str
    status: str
    parameters: Dict[str, Any]
    created_at: str
    updated_at: str

@dataclass
class MLPrediction:
    """ML Prediction data structure"""
    id: int
    model_id: int
    input_data: Dict[str, Any]
    prediction_result: Dict[str, Any]
    prediction_text: str
    confidence: float
    created_at: str

@dataclass
class MLTrainingJob:
    """ML Training Job data structure"""
    id: int
    model_id: int
    status: str
    training_data: Dict[str, Any]
    created_at: str
    completed_at: Optional[str]

class SLMSMLMCPServer:
    """SLMS ML System MCP Server"""
    
    def __init__(self):
        self.server = Server("slms-ml-system")
        self.db_config = {
            'host': 'localhost',
            'user': 'slms',
            'password': 'mlss15gent001',
            'database': 'slmsdb'
        }
        self.base_url = "http://localhost"
        self.setup_handlers()
    
    def setup_handlers(self):
        """Setup MCP server handlers"""
        
        @self.server.list_tools()
        async def handle_list_tools() -> ListToolsResult:
            """List available tools"""
            tools = [
                Tool(
                    name="list_ml_models",
                    description="List all ML models in the SLMS system",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "status": {
                                "type": "string",
                                "enum": ["all", "active", "draft", "training", "inactive"],
                                "description": "Filter models by status"
                            },
                            "type": {
                                "type": "string",
                                "enum": ["neural_network", "random_forest", "linear_regression", "support_vector_machine", "decision_tree"],
                                "description": "Filter models by type"
                            }
                        }
                    }
                ),
                Tool(
                    name="get_ml_model",
                    description="Get detailed information about a specific ML model",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "model_id": {
                                "type": "integer",
                                "description": "ID of the ML model"
                            }
                        },
                        "required": ["model_id"]
                    }
                ),
                Tool(
                    name="create_ml_model",
                    description="Create a new ML model",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "name": {
                                "type": "string",
                                "description": "Name of the model"
                            },
                            "type": {
                                "type": "string",
                                "enum": ["neural_network", "random_forest", "linear_regression", "support_vector_machine", "decision_tree"],
                                "description": "Type of ML model"
                            },
                            "description": {
                                "type": "string",
                                "description": "Description of the model"
                            },
                            "parameters": {
                                "type": "object",
                                "description": "Model parameters"
                            }
                        },
                        "required": ["name", "type", "description"]
                    }
                ),
                Tool(
                    name="train_ml_model",
                    description="Train an ML model with provided data",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "model_id": {
                                "type": "integer",
                                "description": "ID of the model to train"
                            },
                            "training_data": {
                                "type": "object",
                                "description": "Training data for the model"
                            }
                        },
                        "required": ["model_id", "training_data"]
                    }
                ),
                Tool(
                    name="make_prediction",
                    description="Make a prediction using an ML model",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "model_id": {
                                "type": "integer",
                                "description": "ID of the model to use"
                            },
                            "input_data": {
                                "type": "object",
                                "description": "Input data for prediction"
                            }
                        },
                        "required": ["model_id", "input_data"]
                    }
                ),
                Tool(
                    name="list_predictions",
                    description="List recent predictions",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "model_id": {
                                "type": "integer",
                                "description": "Filter by model ID"
                            },
                            "limit": {
                                "type": "integer",
                                "description": "Number of predictions to return",
                                "default": 10
                            }
                        }
                    }
                ),
                Tool(
                    name="list_training_jobs",
                    description="List training jobs",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "status": {
                                "type": "string",
                                "enum": ["all", "pending", "running", "completed", "failed"],
                                "description": "Filter by job status"
                            },
                            "limit": {
                                "type": "integer",
                                "description": "Number of jobs to return",
                                "default": 10
                            }
                        }
                    }
                ),
                Tool(
                    name="get_system_stats",
                    description="Get ML system statistics",
                    inputSchema={
                        "type": "object",
                        "properties": {}
                    }
                ),
                Tool(
                    name="update_model_status",
                    description="Update the status of an ML model",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "model_id": {
                                "type": "integer",
                                "description": "ID of the model"
                            },
                            "status": {
                                "type": "string",
                                "enum": ["draft", "training", "active", "inactive", "archived"],
                                "description": "New status for the model"
                            }
                        },
                        "required": ["model_id", "status"]
                    }
                ),
                Tool(
                    name="delete_ml_model",
                    description="Delete an ML model",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "model_id": {
                                "type": "integer",
                                "description": "ID of the model to delete"
                            }
                        },
                        "required": ["model_id"]
                    }
                ),
                Tool(
                    name="get_model_performance",
                    description="Get performance metrics for an ML model",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "model_id": {
                                "type": "integer",
                                "description": "ID of the model"
                            }
                        },
                        "required": ["model_id"]
                    }
                ),
                Tool(
                    name="export_model",
                    description="Export an ML model to various formats",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "model_id": {
                                "type": "integer",
                                "description": "ID of the model to export"
                            },
                            "format": {
                                "type": "string",
                                "enum": ["json", "csv", "xml", "pdf"],
                                "description": "Export format",
                                "default": "json"
                            }
                        },
                        "required": ["model_id"]
                    }
                ),
                Tool(
                    name="import_model",
                    description="Import an ML model from file",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "file_path": {
                                "type": "string",
                                "description": "Path to the model file"
                            },
                            "format": {
                                "type": "string",
                                "enum": ["json", "csv", "xml"],
                                "description": "Import format",
                                "default": "json"
                            }
                        },
                        "required": ["file_path"]
                    }
                ),
                Tool(
                    name="get_ml_dashboard_url",
                    description="Get the URL for the ML system dashboard",
                    inputSchema={
                        "type": "object",
                        "properties": {}
                    }
                ),
                Tool(
                    name="test_ml_api",
                    description="Test the ML system API endpoints",
                    inputSchema={
                        "type": "object",
                        "properties": {
                            "endpoint": {
                                "type": "string",
                                "enum": ["models", "predict", "train", "stats"],
                                "description": "API endpoint to test",
                                "default": "models"
                            }
                        }
                    }
                )
            ]
            return ListToolsResult(tools=tools)
        
        @self.server.call_tool()
        async def handle_call_tool(name: str, arguments: Dict[str, Any]) -> CallToolResult:
            """Handle tool calls"""
            try:
                if name == "list_ml_models":
                    return await self.list_ml_models(arguments)
                elif name == "get_ml_model":
                    return await self.get_ml_model(arguments)
                elif name == "create_ml_model":
                    return await self.create_ml_model(arguments)
                elif name == "train_ml_model":
                    return await self.train_ml_model(arguments)
                elif name == "make_prediction":
                    return await self.make_prediction(arguments)
                elif name == "list_predictions":
                    return await self.list_predictions(arguments)
                elif name == "list_training_jobs":
                    return await self.list_training_jobs(arguments)
                elif name == "get_system_stats":
                    return await self.get_system_stats(arguments)
                elif name == "update_model_status":
                    return await self.update_model_status(arguments)
                elif name == "delete_ml_model":
                    return await self.delete_ml_model(arguments)
                elif name == "get_model_performance":
                    return await self.get_model_performance(arguments)
                elif name == "export_model":
                    return await self.export_model(arguments)
                elif name == "import_model":
                    return await self.import_model(arguments)
                elif name == "get_ml_dashboard_url":
                    return await self.get_ml_dashboard_url(arguments)
                elif name == "test_ml_api":
                    return await self.test_ml_api(arguments)
                else:
                    return CallToolResult(
                        content=[TextContent(type="text", text=f"Unknown tool: {name}")]
                    )
            except Exception as e:
                logger.error(f"Error in tool {name}: {str(e)}")
                return CallToolResult(
                    content=[TextContent(type="text", text=f"Error: {str(e)}")]
                )
    
    async def get_database_connection(self):
        """Get database connection"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except Error as e:
            logger.error(f"Database connection error: {e}")
            raise
    
    async def list_ml_models(self, args: Dict[str, Any]) -> CallToolResult:
        """List ML models"""
        try:
            conn = await self.get_database_connection()
            cursor = conn.cursor(dictionary=True)
            
            query = "SELECT * FROM ml_models WHERE 1=1"
            params = []
            
            if args.get("status") and args["status"] != "all":
                query += " AND status = %s"
                params.append(args["status"])
            
            if args.get("type"):
                query += " AND type = %s"
                params.append(args["type"])
            
            query += " ORDER BY created_at DESC"
            
            cursor.execute(query, params)
            models = cursor.fetchall()
            
            cursor.close()
            conn.close()
            
            if not models:
                return CallToolResult(
                    content=[TextContent(type="text", text="No ML models found.")]
                )
            
            # Format the output
            output = "## ML Models\n\n"
            for model in models:
                output += f"### {model['name']} (ID: {model['id']})\n"
                output += f"- **Type**: {model['type']}\n"
                output += f"- **Status**: {model['status']}\n"
                output += f"- **Description**: {model['description']}\n"
                output += f"- **Created**: {model['created_at']}\n"
                if model['parameters']:
                    params = json.loads(model['parameters']) if isinstance(model['parameters'], str) else model['parameters']
                    output += f"- **Parameters**: {json.dumps(params, indent=2)}\n"
                output += "\n"
            
            return CallToolResult(
                content=[TextContent(type="text", text=output)]
            )
            
        except Exception as e:
            logger.error(f"Error listing ML models: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error listing ML models: {str(e)}")]
            )
    
    async def get_ml_model(self, args: Dict[str, Any]) -> CallToolResult:
        """Get specific ML model details"""
        try:
            model_id = args["model_id"]
            conn = await self.get_database_connection()
            cursor = conn.cursor(dictionary=True)
            
            cursor.execute("SELECT * FROM ml_models WHERE id = %s", (model_id,))
            model = cursor.fetchone()
            
            cursor.close()
            conn.close()
            
            if not model:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"ML model with ID {model_id} not found.")]
                )
            
            # Format the output
            output = f"## ML Model Details\n\n"
            output += f"**ID**: {model['id']}\n"
            output += f"**Name**: {model['name']}\n"
            output += f"**Type**: {model['type']}\n"
            output += f"**Status**: {model['status']}\n"
            output += f"**Description**: {model['description']}\n"
            output += f"**Created**: {model['created_at']}\n"
            output += f"**Updated**: {model['updated_at']}\n"
            
            if model['parameters']:
                params = json.loads(model['parameters']) if isinstance(model['parameters'], str) else model['parameters']
                output += f"\n**Parameters**:\n```json\n{json.dumps(params, indent=2)}\n```\n"
            
            return CallToolResult(
                content=[TextContent(type="text", text=output)]
            )
            
        except Exception as e:
            logger.error(f"Error getting ML model: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error getting ML model: {str(e)}")]
            )
    
    async def create_ml_model(self, args: Dict[str, Any]) -> CallToolResult:
        """Create a new ML model"""
        try:
            # Use the API to create the model
            url = f"{self.base_url}/api/ml_api.php"
            data = {
                "action": "create_model",
                "name": args["name"],
                "type": args["type"],
                "description": args["description"],
                "parameters": args.get("parameters", {})
            }
            
            response = requests.post(url, data=data, timeout=30)
            result = response.json()
            
            if result.get("success"):
                model_id = result.get("id")
                return CallToolResult(
                    content=[TextContent(type="text", text=f"✅ ML model '{args['name']}' created successfully with ID: {model_id}")]
                )
            else:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"❌ Failed to create ML model: {result.get('error', 'Unknown error')}")]
                )
                
        except Exception as e:
            logger.error(f"Error creating ML model: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error creating ML model: {str(e)}")]
            )
    
    async def train_ml_model(self, args: Dict[str, Any]) -> CallToolResult:
        """Train an ML model"""
        try:
            url = f"{self.base_url}/api/ml_api.php"
            data = {
                "action": "train_model",
                "model_id": args["model_id"],
                "training_data": json.dumps(args["training_data"])
            }
            
            response = requests.post(url, data=data, timeout=60)
            result = response.json()
            
            if result.get("success"):
                return CallToolResult(
                    content=[TextContent(type="text", text=f"✅ Training started for model ID {args['model_id']}")]
                )
            else:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"❌ Failed to start training: {result.get('error', 'Unknown error')}")]
                )
                
        except Exception as e:
            logger.error(f"Error training ML model: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error training ML model: {str(e)}")]
            )
    
    async def make_prediction(self, args: Dict[str, Any]) -> CallToolResult:
        """Make a prediction using an ML model"""
        try:
            url = f"{self.base_url}/api/ml_api.php"
            data = {
                "action": "make_prediction",
                "model_id": args["model_id"],
                "input_data": json.dumps(args["input_data"])
            }
            
            response = requests.post(url, data=data, timeout=30)
            result = response.json()
            
            if result.get("success"):
                prediction = result.get("prediction", {})
                output = f"## Prediction Result\n\n"
                output += f"**Model ID**: {args['model_id']}\n"
                output += f"**Input Data**:\n```json\n{json.dumps(args['input_data'], indent=2)}\n```\n"
                output += f"**Prediction**:\n```json\n{json.dumps(prediction, indent=2)}\n```\n"
                
                if prediction.get("prediction_text"):
                    output += f"**Prediction Text**: {prediction['prediction_text']}\n"
                
                if prediction.get("confidence"):
                    output += f"**Confidence**: {prediction['confidence']:.2%}\n"
                
                return CallToolResult(
                    content=[TextContent(type="text", text=output)]
                )
            else:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"❌ Prediction failed: {result.get('error', 'Unknown error')}")]
                )
                
        except Exception as e:
            logger.error(f"Error making prediction: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error making prediction: {str(e)}")]
            )
    
    async def list_predictions(self, args: Dict[str, Any]) -> CallToolResult:
        """List recent predictions"""
        try:
            conn = await self.get_database_connection()
            cursor = conn.cursor(dictionary=True)
            
            query = "SELECT * FROM ml_predictions WHERE 1=1"
            params = []
            
            if args.get("model_id"):
                query += " AND model_id = %s"
                params.append(args["model_id"])
            
            limit = args.get("limit", 10)
            query += " ORDER BY created_at DESC LIMIT %s"
            params.append(limit)
            
            cursor.execute(query, params)
            predictions = cursor.fetchall()
            
            cursor.close()
            conn.close()
            
            if not predictions:
                return CallToolResult(
                    content=[TextContent(type="text", text="No predictions found.")]
                )
            
            output = "## Recent Predictions\n\n"
            for pred in predictions:
                output += f"### Prediction ID: {pred['id']}\n"
                output += f"- **Model ID**: {pred['model_id']}\n"
                output += f"- **Created**: {pred['created_at']}\n"
                output += f"- **Confidence**: {pred['confidence']:.2%}\n"
                
                if pred['prediction_text']:
                    output += f"- **Prediction**: {pred['prediction_text']}\n"
                
                if pred['input_data']:
                    input_data = json.loads(pred['input_data']) if isinstance(pred['input_data'], str) else pred['input_data']
                    output += f"- **Input Data**: {json.dumps(input_data, indent=2)}\n"
                
                output += "\n"
            
            return CallToolResult(
                content=[TextContent(type="text", text=output)]
            )
            
        except Exception as e:
            logger.error(f"Error listing predictions: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error listing predictions: {str(e)}")]
            )
    
    async def list_training_jobs(self, args: Dict[str, Any]) -> CallToolResult:
        """List training jobs"""
        try:
            conn = await self.get_database_connection()
            cursor = conn.cursor(dictionary=True)
            
            query = "SELECT * FROM ml_training_jobs WHERE 1=1"
            params = []
            
            if args.get("status") and args["status"] != "all":
                query += " AND status = %s"
                params.append(args["status"])
            
            limit = args.get("limit", 10)
            query += " ORDER BY created_at DESC LIMIT %s"
            params.append(limit)
            
            cursor.execute(query, params)
            jobs = cursor.fetchall()
            
            cursor.close()
            conn.close()
            
            if not jobs:
                return CallToolResult(
                    content=[TextContent(type="text", text="No training jobs found.")]
                )
            
            output = "## Training Jobs\n\n"
            for job in jobs:
                output += f"### Job ID: {job['id']}\n"
                output += f"- **Model ID**: {job['model_id']}\n"
                output += f"- **Status**: {job['status']}\n"
                output += f"- **Created**: {job['created_at']}\n"
                if job['completed_at']:
                    output += f"- **Completed**: {job['completed_at']}\n"
                output += "\n"
            
            return CallToolResult(
                content=[TextContent(type="text", text=output)]
            )
            
        except Exception as e:
            logger.error(f"Error listing training jobs: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error listing training jobs: {str(e)}")]
            )
    
    async def get_system_stats(self, args: Dict[str, Any]) -> CallToolResult:
        """Get ML system statistics"""
        try:
            conn = await self.get_database_connection()
            cursor = conn.cursor(dictionary=True)
            
            # Get various statistics
            stats = {}
            
            # Total models
            cursor.execute("SELECT COUNT(*) as count FROM ml_models")
            stats['total_models'] = cursor.fetchone()['count']
            
            # Active models
            cursor.execute("SELECT COUNT(*) as count FROM ml_models WHERE status = 'active'")
            stats['active_models'] = cursor.fetchone()['count']
            
            # Total predictions
            cursor.execute("SELECT COUNT(*) as count FROM ml_predictions")
            stats['total_predictions'] = cursor.fetchone()['count']
            
            # Running training jobs
            cursor.execute("SELECT COUNT(*) as count FROM ml_training_jobs WHERE status = 'running'")
            stats['running_jobs'] = cursor.fetchone()['count']
            
            # Recent predictions (last 24 hours)
            cursor.execute("SELECT COUNT(*) as count FROM ml_predictions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")
            stats['recent_predictions'] = cursor.fetchone()['count']
            
            cursor.close()
            conn.close()
            
            output = "## ML System Statistics\n\n"
            output += f"**Total Models**: {stats['total_models']}\n"
            output += f"**Active Models**: {stats['active_models']}\n"
            output += f"**Total Predictions**: {stats['total_predictions']}\n"
            output += f"**Running Training Jobs**: {stats['running_jobs']}\n"
            output += f"**Predictions (24h)**: {stats['recent_predictions']}\n"
            
            return CallToolResult(
                content=[TextContent(type="text", text=output)]
            )
            
        except Exception as e:
            logger.error(f"Error getting system stats: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error getting system stats: {str(e)}")]
            )
    
    async def update_model_status(self, args: Dict[str, Any]) -> CallToolResult:
        """Update model status"""
        try:
            url = f"{self.base_url}/api/ml_api.php"
            data = {
                "action": "update_status",
                "model_id": args["model_id"],
                "status": args["status"]
            }
            
            response = requests.post(url, data=data, timeout=30)
            result = response.json()
            
            if result.get("success"):
                return CallToolResult(
                    content=[TextContent(type="text", text=f"✅ Model {args['model_id']} status updated to '{args['status']}'")]
                )
            else:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"❌ Failed to update model status: {result.get('error', 'Unknown error')}")]
                )
                
        except Exception as e:
            logger.error(f"Error updating model status: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error updating model status: {str(e)}")]
            )
    
    async def delete_ml_model(self, args: Dict[str, Any]) -> CallToolResult:
        """Delete an ML model"""
        try:
            url = f"{self.base_url}/api/ml_api.php"
            data = {
                "action": "delete_model",
                "model_id": args["model_id"]
            }
            
            response = requests.post(url, data=data, timeout=30)
            result = response.json()
            
            if result.get("success"):
                return CallToolResult(
                    content=[TextContent(type="text", text=f"✅ Model {args['model_id']} deleted successfully")]
                )
            else:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"❌ Failed to delete model: {result.get('error', 'Unknown error')}")]
                )
                
        except Exception as e:
            logger.error(f"Error deleting ML model: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error deleting ML model: {str(e)}")]
            )
    
    async def get_model_performance(self, args: Dict[str, Any]) -> CallToolResult:
        """Get model performance metrics"""
        try:
            conn = await self.get_database_connection()
            cursor = conn.cursor(dictionary=True)
            
            model_id = args["model_id"]
            cursor.execute("""
                SELECT * FROM ml_performance_metrics 
                WHERE model_id = %s 
                ORDER BY recorded_at DESC 
                LIMIT 1
            """, (model_id,))
            
            metrics = cursor.fetchone()
            cursor.close()
            conn.close()
            
            if not metrics:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"No performance metrics found for model {model_id}")]
                )
            
            output = f"## Model Performance Metrics\n\n"
            output += f"**Model ID**: {metrics['model_id']}\n"
            output += f"**Metric Type**: {metrics['metric_type']}\n"
            output += f"**Value**: {metrics['metric_value']}\n"
            output += f"**Recorded**: {metrics['recorded_at']}\n"
            
            return CallToolResult(
                content=[TextContent(type="text", text=output)]
            )
            
        except Exception as e:
            logger.error(f"Error getting model performance: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error getting model performance: {str(e)}")]
            )
    
    async def export_model(self, args: Dict[str, Any]) -> CallToolResult:
        """Export model to file"""
        try:
            model_id = args["model_id"]
            export_format = args.get("format", "json")
            
            conn = await self.get_database_connection()
            cursor = conn.cursor(dictionary=True)
            
            cursor.execute("SELECT * FROM ml_models WHERE id = %s", (model_id,))
            model = cursor.fetchone()
            
            cursor.close()
            conn.close()
            
            if not model:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"Model {model_id} not found")]
                )
            
            # Create export file
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            filename = f"ml_model_{model_id}_{timestamp}.{export_format}"
            filepath = f"/tmp/{filename}"
            
            if export_format == "json":
                with open(filepath, 'w') as f:
                    json.dump(model, f, indent=2, default=str)
            elif export_format == "csv":
                import csv
                with open(filepath, 'w', newline='') as f:
                    writer = csv.writer(f)
                    writer.writerow(model.keys())
                    writer.writerow(model.values())
            else:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"Unsupported export format: {export_format}")]
                )
            
            return CallToolResult(
                content=[TextContent(type="text", text=f"✅ Model exported to {filepath}")]
            )
            
        except Exception as e:
            logger.error(f"Error exporting model: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error exporting model: {str(e)}")]
            )
    
    async def import_model(self, args: Dict[str, Any]) -> CallToolResult:
        """Import model from file"""
        try:
            file_path = args["file_path"]
            import_format = args.get("format", "json")
            
            if not os.path.exists(file_path):
                return CallToolResult(
                    content=[TextContent(type="text", text=f"File not found: {file_path}")]
                )
            
            if import_format == "json":
                with open(file_path, 'r') as f:
                    model_data = json.load(f)
            else:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"Unsupported import format: {import_format}")]
                )
            
            # Use API to create model
            url = f"{self.base_url}/api/ml_api.php"
            data = {
                "action": "create_model",
                "name": model_data.get("name", "Imported Model"),
                "type": model_data.get("type", "neural_network"),
                "description": model_data.get("description", "Imported from file"),
                "parameters": model_data.get("parameters", {})
            }
            
            response = requests.post(url, data=data, timeout=30)
            result = response.json()
            
            if result.get("success"):
                model_id = result.get("id")
                return CallToolResult(
                    content=[TextContent(type="text", text=f"✅ Model imported successfully with ID: {model_id}")]
                )
            else:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"❌ Failed to import model: {result.get('error', 'Unknown error')}")]
                )
                
        except Exception as e:
            logger.error(f"Error importing model: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error importing model: {str(e)}")]
            )
    
    async def get_ml_dashboard_url(self, args: Dict[str, Any]) -> CallToolResult:
        """Get ML dashboard URL"""
        dashboard_url = f"{self.base_url}/ml_model_manager.php"
        api_url = f"{self.base_url}/api/ml_api.php"
        
        output = "## ML System URLs\n\n"
        output += f"**Dashboard**: {dashboard_url}\n"
        output += f"**API Base**: {api_url}\n\n"
        output += "### Quick Links\n"
        output += f"- [ML Model Manager]({dashboard_url})\n"
        output += f"- [API Documentation]({api_url}?action=docs)\n"
        output += f"- [System Status]({dashboard_url}?action=status)\n"
        
        return CallToolResult(
            content=[TextContent(type="text", text=output)]
        )
    
    async def test_ml_api(self, args: Dict[str, Any]) -> CallToolResult:
        """Test ML API endpoints"""
        try:
            endpoint = args.get("endpoint", "models")
            url = f"{self.base_url}/api/ml_api.php"
            
            if endpoint == "models":
                response = requests.get(f"{url}?action=models", timeout=10)
            elif endpoint == "stats":
                response = requests.get(f"{url}?action=stats", timeout=10)
            else:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"Testing endpoint: {endpoint} (POST request)")]
                )
            
            if response.status_code == 200:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"✅ API endpoint '{endpoint}' is working (Status: {response.status_code})")]
                )
            else:
                return CallToolResult(
                    content=[TextContent(type="text", text=f"❌ API endpoint '{endpoint}' failed (Status: {response.status_code})")]
                )
                
        except Exception as e:
            logger.error(f"Error testing API: {e}")
            return CallToolResult(
                content=[TextContent(type="text", text=f"Error testing API: {str(e)}")]
            )
    
    async def run(self):
        """Run the MCP server"""
        async with stdio_server() as (read_stream, write_stream):
            await self.server.run(
                read_stream,
                write_stream,
                InitializationOptions(
                    server_name="slms-ml-system",
                    server_version="1.0.0",
                    capabilities=self.server.get_capabilities(
                        notification_options=DummyNotificationOptions(),
                        experimental_capabilities=None,
                    ),
                ),
            )

async def main():
    """Main function"""
    server = SLMSMLMCPServer()
    await server.run()

if __name__ == "__main__":
    asyncio.run(main()) 