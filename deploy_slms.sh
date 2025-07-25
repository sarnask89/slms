#!/bin/bash

# === CONFIGURATION ===
DOCKERHUB_USER="sarnask82"   # <-- CHANGE THIS!
IMAGE_NAME="slms"
IMAGE_TAG="latest"
K8S_DEPLOYMENT="slms-deployment.yaml"
APP_DIR="$(pwd)"

# === 1. Create Dockerfile ===
cat > Dockerfile <<EOF
FROM php:8.1-apache
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
RUN a2enmod rewrite
EOF

echo "Dockerfile created."

# === 2. Build Docker image ===
docker build -t $DOCKERHUB_USER/$IMAGE_NAME:$IMAGE_TAG .
if [ $? -ne 0 ]; then
  echo "Docker build failed!"
  exit 1
fi

# === 3. Push Docker image to Docker Hub ===
docker push $DOCKERHUB_USER/$IMAGE_NAME:$IMAGE_TAG
if [ $? -ne 0 ]; then
  echo "Docker push failed!"
  exit 1
fi

# === 4. Generate Kubernetes deployment YAML ===
cat > $K8S_DEPLOYMENT <<EOF
apiVersion: apps/v1
kind: Deployment
metadata:
  name: slms-web
spec:
  replicas: 1
  selector:
    matchLabels:
      app: slms-web
  template:
    metadata:
      labels:
        app: slms-web
    spec:
      containers:
      - name: slms-web
        image: $DOCKERHUB_USER/$IMAGE_NAME:$IMAGE_TAG
        ports:
        - containerPort: 80
---
apiVersion: v1
kind: Service
metadata:
  name: slms-service
spec:
  type: NodePort
  selector:
    app: slms-web
  ports:
    - protocol: TCP
      port: 80
      targetPort: 80
      nodePort: 30080
EOF

echo "$K8S_DEPLOYMENT created."

# === 5. Deploy to Kubernetes ===
kubectl apply -f $K8S_DEPLOYMENT

# === 6. Wait for pod to be ready ===
echo "Waiting for pod to be ready..."
kubectl wait --for=condition=ready pod -l app=slms-web --timeout=120s

# === 7. Verify files in the pod ===
POD_NAME=$(kubectl get pods -l app=slms-web -o jsonpath="{.items[0].metadata.name}")
echo "Listing files in /var/www/html inside the pod:"
kubectl exec -it $POD_NAME -- ls -l /var/www/html

# === 8. Print access instructions ===
NODE_IP=$(kubectl get nodes -o jsonpath="{.items[0].status.addresses[?(@.type=='InternalIP')].address}")
echo "If you see your files above, your app is deployed!"
echo "Access your app at: http://$NODE_IP:30080"

echo "Done."
