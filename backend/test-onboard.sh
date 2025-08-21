#!/bin/bash

# Test script for user onboarding API
# Usage: ./test-onboard.sh

echo "🧘‍♂️ Testing User Onboarding API"
echo "==============================="

# Test 1: Create new user
echo -e "\n📝 Test 1: Creating new user..."
curl -X POST http://localhost:8080/api.php/onboard \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "mobile": "9876543210",
    "password": "testpass123",
    "branch_name": "Chennai Central",
    "email": "test@example.com"
  }' | jq '.'

echo -e "\n---"

# Test 2: Update existing user (add new branch access)
echo -e "\n📝 Test 2: Updating existing user with new branch access..."
curl -X POST http://localhost:8080/api.php/onboard \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User Updated",
    "mobile": "9876543210",
    "password": "newpass123",
    "branch_name": "Chennai South"
  }' | jq '.'

echo -e "\n---"

# Test 3: Invalid branch name
echo -e "\n📝 Test 3: Testing with invalid branch name..."
curl -X POST http://localhost:8080/api.php/onboard \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Another User",
    "mobile": "9123456789",
    "password": "password123",
    "branch_name": "Non-Existent Branch"
  }' | jq '.'

echo -e "\n---"

# Test 4: Missing required fields
echo -e "\n📝 Test 4: Testing with missing required fields..."
curl -X POST http://localhost:8080/api.php/onboard \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Incomplete User",
    "mobile": "9111111111"
  }' | jq '.'

echo -e "\n---"

# Test 5: Invalid mobile format
echo -e "\n📝 Test 5: Testing with invalid mobile format..."
curl -X POST http://localhost:8080/api.php/onboard \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Invalid Mobile User",
    "mobile": "12345",
    "password": "password123",
    "branch_name": "Chennai Central"
  }' | jq '.'

echo -e "\n✅ Testing completed!"