#!/bin/bash
# sLMS Kubernetes Deploy, Restart, and Debug Script
# Usage: ./deploy_and_debug.sh

set -e

NAMESPACE="default" # Change if using a different namespace
DEPLOYMENT="slms-web" # Change to your deployment name
YAML="slms-deployment.yaml"
DEBUG_SCRIPT="debug_system.php"

# 1. Apply the deployment
echo "\n=== [1/5] Applying Kubernetes deployment: $YAML ==="
kubectl apply -f "$YAML" --namespace "$NAMESPACE"

# 2. Restart the deployment
echo "\n=== [2/5] Restarting deployment: $DEPLOYMENT ==="
kubectl rollout restart deployment "$DEPLOYMENT" --namespace "$NAMESPACE"

# 3. Wait for pods to be ready
echo "\n=== [3/5] Waiting for pods to be ready... ==="
kubectl rollout status deployment "$DEPLOYMENT" --namespace "$NAMESPACE"

# 4. Find a running pod
POD=$(kubectl get pods --namespace "$NAMESPACE" -l app=$DEPLOYMENT -o jsonpath='{.items[0].metadata.name}')
if [ -z "$POD" ]; then
  echo "Error: No pod found for deployment $DEPLOYMENT in namespace $NAMESPACE" >&2
  exit 1
fi

echo "\n=== [4/5] Running debug script in pod: $POD ==="
kubectl exec -it "$POD" -- php "$DEBUG_SCRIPT"

# 5. Done
echo "\n=== [5/5] Deployment, restart, and debug complete ===" 