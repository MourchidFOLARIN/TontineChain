#!/bin/bash

# Script de test automatique pour l'API TontineChain
# Utilisation: ./test_api.sh [email_test]

set -e

# Configuration
BASE_URL="https://tonnine-benin-backend.onrender.com/api/v1"
TEST_EMAIL="${1:-test@example.com}"
TEST_PHONE="+22997000000"

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction de logging
log() {
    echo -e "${BLUE}[$(date +'%H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Fonction pour faire des requêtes HTTP
make_request() {
    local method=$1
    local url=$2
    local data=$3
    local auth=$4

    local curl_cmd="curl -s -w '\nHTTP_STATUS:%{http_code}'"

    if [ "$method" = "POST" ] || [ "$method" = "PATCH" ]; then
        curl_cmd="$curl_cmd -X $method -H 'Content-Type: application/json'"
        if [ -n "$data" ]; then
            curl_cmd="$curl_cmd -d '$data'"
        fi
    elif [ "$method" = "GET" ]; then
        curl_cmd="$curl_cmd -X GET"
    fi

    if [ -n "$auth" ]; then
        curl_cmd="$curl_cmd -H 'Authorization: Bearer $auth'"
    fi

    curl_cmd="$curl_cmd '$url'"

    eval "$curl_cmd"
}

# Fonction pour extraire le status HTTP
get_status() {
    echo "$1" | grep "HTTP_STATUS:" | cut -d: -f2
}

# Fonction pour extraire le body JSON
get_body() {
    echo "$1" | sed '/HTTP_STATUS:/d'
}

# Fonction pour tester un endpoint
test_endpoint() {
    local test_name=$1
    local method=$2
    local endpoint=$3
    local data=$4
    local expected_status=$5
    local auth=${6:-""}

    log "Testing: $test_name"

    local response=$(make_request "$method" "$BASE_URL$endpoint" "$data" "$auth")
    local status=$(get_status "$response")
    local body=$(get_body "$response")

    if [ "$status" = "$expected_status" ]; then
        success "$test_name - Status: $status"
        return 0
    else
        error "$test_name - Expected: $expected_status, Got: $status"
        echo "Response: $body"
        return 1
    fi
}

# Variables globales
ACCESS_TOKEN=""
GROUP_ID=""

# Fonction principale
main() {
    log "🚀 Starting TontineChain API Tests"
    log "Base URL: $BASE_URL"
    log "Test Email: $TEST_EMAIL"
    echo

    # Test 1: Health Check
    if test_endpoint "Health Check" "GET" "/health" "" "200"; then
        success "API is healthy"
    else
        error "API health check failed"
        exit 1
    fi

    echo

    # Test 2: Request OTP
    warning "Requesting OTP for $TEST_EMAIL"
    if test_endpoint "Request OTP" "POST" "/auth/request-otp" "{\"email\":\"$TEST_EMAIL\",\"phone\":\"$TEST_PHONE\",\"locale\":\"fr\"}" "200"; then
        success "OTP request successful - Check email for code"
    else
        error "OTP request failed"
        exit 1
    fi

    echo

    # Test 3: Verify OTP (nécessite intervention manuelle)
    warning "⚠️  MANUAL STEP REQUIRED ⚠️"
    echo "Please check your email ($TEST_EMAIL) for the OTP code"
    echo "Enter the 6-digit code:"
    read -r otp_code

    if [ ${#otp_code} -ne 6 ]; then
        error "OTP code must be 6 digits"
        exit 1
    fi

    log "Verifying OTP: $otp_code"
    response=$(make_request "POST" "$BASE_URL/auth/verify-otp" "{\"email\":\"$TEST_EMAIL\",\"code\":\"$otp_code\"}" "")
    status=$(get_status "$response")
    body=$(get_body "$response")

    if [ "$status" = "200" ]; then
        success "OTP verification successful"
        ACCESS_TOKEN=$(echo "$body" | jq -r '.access_token')
        if [ "$ACCESS_TOKEN" != "null" ] && [ -n "$ACCESS_TOKEN" ]; then
            success "Access token obtained: ${ACCESS_TOKEN:0:20}..."
        else
            error "No access token in response"
            exit 1
        fi
    else
        error "OTP verification failed - Status: $status"
        echo "Response: $body"
        exit 1
    fi

    echo

    # Test 4: Get User Profile
    if test_endpoint "Get User Profile" "GET" "/users/me" "" "200" "$ACCESS_TOKEN"; then
        success "User profile retrieved"
    fi

    echo

    # Test 5: Create Group
    log "Creating test group..."
    response=$(make_request "POST" "$BASE_URL/groups" "{\"name\":\"Test Group\",\"contribution_amount\":5000,\"max_members\":5,\"frequency\":\"monthly\",\"payout_method\":\"sequential\",\"insurance_opt_in\":true}" "$ACCESS_TOKEN")
    status=$(get_status "$response")
    body=$(get_body "$response")

    if [ "$status" = "201" ] || [ "$status" = "200" ]; then
        success "Group creation successful"
        GROUP_ID=$(echo "$body" | jq -r '.id')
        if [ "$GROUP_ID" != "null" ] && [ -n "$GROUP_ID" ]; then
            success "Group ID: $GROUP_ID"
        fi
    else
        error "Group creation failed - Status: $status"
        echo "Response: $body"
    fi

    echo

    # Test 6: Read Group
    if [ -n "$GROUP_ID" ]; then
        if test_endpoint "Read Group" "GET" "/groups/$GROUP_ID" "" "200" "$ACCESS_TOKEN"; then
            success "Group details retrieved"
        fi
    else
        warning "Skipping group read test - no group ID"
    fi

    echo

    # Test 7: Update Profile
    if test_endpoint "Update Profile" "PATCH" "/users/me" "{\"full_name\":\"Test User\",\"phone\":\"$TEST_PHONE\"}" "200" "$ACCESS_TOKEN"; then
        success "Profile update successful"
    fi

    echo

    # Test 8: AI Chat
    if test_endpoint "AI Chat" "POST" "/ai/chat" "{\"message\":\"Bonjour\",\"locale\":\"fr\"}" "200" "$ACCESS_TOKEN"; then
        success "AI chat working"
    fi

    echo

    # Test 9: Unauthorized Access
    if test_endpoint "Unauthorized Access" "GET" "/users/me" "" "401" ""; then
        success "Authentication protection working"
    fi

    echo

    # Test 10: Invalid Email
    if test_endpoint "Invalid Email" "POST" "/auth/request-otp" "{\"email\":\"invalid-email\"}" "422" ""; then
        success "Email validation working"
    fi

    echo

    # Résumé final
    log "🎉 Test Summary"
    success "All automated tests completed"
    warning "Manual OTP verification was required"
    log "Access Token: ${ACCESS_TOKEN:0:20}..."
    if [ -n "$GROUP_ID" ]; then
        log "Test Group ID: $GROUP_ID"
    fi

    echo
    log "📊 Next steps:"
    echo "1. Run performance tests with: ab -n 10 -c 2 $BASE_URL/health"
    echo "2. Import the Postman collection for more detailed testing"
    echo "3. Check the TEST_GUIDE.md for complete testing procedures"
}

# Vérifier les dépendances
check_dependencies() {
    if ! command -v curl &> /dev/null; then
        error "curl is required but not installed"
        exit 1
    fi

    if ! command -v jq &> /dev/null; then
        warning "jq is recommended for JSON parsing (install with: apt install jq)"
    fi
}

# Point d'entrée
check_dependencies
main "$@"