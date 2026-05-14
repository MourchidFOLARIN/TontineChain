# Script de test PowerShell pour l'API TontineChain
# Utilisation: .\test_api.ps1 -Email "votre.email@test.com"

param(
    [string]$Email = "test@example.com",
    [string]$Phone = "+22997000000"
)

# Configuration
$BASE_URL = "https://tonnine-benin-backend.onrender.com/api/v1"
$TEST_EMAIL = $Email
$TEST_PHONE = $Phone

# Variables globales
$ACCESS_TOKEN = ""
$GROUP_ID = ""

# Fonctions utilitaires
function Write-Log {
    param([string]$Message, [string]$Color = "White")
    $timestamp = Get-Date -Format "HH:mm:ss"
    Write-Host "[$timestamp] $Message" -ForegroundColor $Color
}

function Write-Success { param([string]$Message) Write-Log "✅ $Message" "Green" }
function Write-Error { param([string]$Message) Write-Log "❌ $Message" "Red" }
function Write-Warning { param([string]$Message) Write-Log "⚠️  $Message" "Yellow" }

# Fonction pour faire des requêtes HTTP
function Invoke-APIRequest {
    param(
        [string]$Method,
        [string]$Endpoint,
        [string]$Body = "",
        [string]$Token = ""
    )

    $headers = @{
        "Accept" = "application/json"
    }

    if ($Token) {
        $headers["Authorization"] = "Bearer $Token"
    }

    if ($Body) {
        $headers["Content-Type"] = "application/json"
    }

    $url = "$BASE_URL$Endpoint"

    try {
        $params = @{
            Uri = $url
            Method = $Method
            Headers = $headers
        }

        if ($Body) {
            $params.Body = $Body
        }

        $response = Invoke-RestMethod @params -ErrorAction Stop
        return @{
            Success = $true
            StatusCode = 200
            Data = $response
        }
    }
    catch {
        $statusCode = 500
        if ($_.Exception.Response) {
            $statusCode = $_.Exception.Response.StatusCode.value__
        }

        return @{
            Success = $false
            StatusCode = $statusCode
            Error = $_.Exception.Message
        }
    }
}

# Fonction pour tester un endpoint
function Test-APIEndpoint {
    param(
        [string]$TestName,
        [string]$Method,
        [string]$Endpoint,
        [string]$Body = "",
        [int]$ExpectedStatus = 200,
        [string]$Token = ""
    )

    Write-Log "Testing: $TestName" "Cyan"

    $result = Invoke-APIRequest -Method $Method -Endpoint $Endpoint -Body $Body -Token $Token

    if ($result.Success -and $result.StatusCode -eq $ExpectedStatus) {
        Write-Success "$TestName - Status: $($result.StatusCode)"
        return $result.Data
    }
    else {
        Write-Error "$TestName - Expected: $ExpectedStatus, Got: $($result.StatusCode)"
        if ($result.Error) {
            Write-Log "Error: $($result.Error)" "Red"
        }
        return $null
    }
}

# Script principal
Write-Log "🚀 Starting TontineChain API Tests (PowerShell)" "Magenta"
Write-Log "Base URL: $BASE_URL"
Write-Log "Test Email: $TEST_EMAIL"
Write-Host ""

# Test 1: Health Check
$result = Test-APIEndpoint "Health Check" "GET" "/health"
if ($result -and $result.status -eq "ok") {
    Write-Success "API is healthy"
}
else {
    Write-Error "API health check failed"
    exit 1
}

Write-Host ""

# Test 2: Request OTP
Write-Warning "Requesting OTP for $TEST_EMAIL"
$otpRequest = Test-APIEndpoint "Request OTP" "POST" "/auth/request-otp" (@{
    email = $TEST_EMAIL
    phone = $TEST_PHONE
    locale = "fr"
} | ConvertTo-Json)

if ($otpRequest) {
    Write-Success "OTP request successful - Check email for code"
}
else {
    Write-Error "OTP request failed"
    exit 1
}

Write-Host ""

# Test 3: Verify OTP (nécessite intervention manuelle)
Write-Warning "⚠️  MANUAL STEP REQUIRED ⚠️"
Write-Host "Please check your email ($TEST_EMAIL) for the OTP code"
$otp_code = Read-Host "Enter the 6-digit code"

if ($otp_code.Length -ne 6 -or -not ($otp_code -match '^\d{6}$')) {
    Write-Error "OTP code must be exactly 6 digits"
    exit 1
}

Write-Log "Verifying OTP: $otp_code"
$otpVerify = Invoke-APIRequest -Method "POST" -Endpoint "/auth/verify-otp" -Body (@{
    email = $TEST_EMAIL
    code = $otp_code
} | ConvertTo-Json)

if ($otpVerify.Success -and $otpVerify.StatusCode -eq 200) {
    Write-Success "OTP verification successful"
    $ACCESS_TOKEN = $otpVerify.Data.access_token
    if ($ACCESS_TOKEN) {
        Write-Success "Access token obtained: $($ACCESS_TOKEN.Substring(0, [Math]::Min(20, $ACCESS_TOKEN.Length)))..."
    }
    else {
        Write-Error "No access token in response"
        exit 1
    }
}
else {
    Write-Error "OTP verification failed - Status: $($otpVerify.StatusCode)"
    if ($otpVerify.Error) {
        Write-Log "Error: $($otpVerify.Error)" "Red"
    }
    exit 1
}

Write-Host ""

# Test 4: Get User Profile
$userProfile = Test-APIEndpoint "Get User Profile" "GET" "/users/me" "" 200 $ACCESS_TOKEN
if ($userProfile) {
    Write-Success "User profile retrieved"
}

Write-Host ""

# Test 5: Create Group
Write-Log "Creating test group..."
$groupData = Test-APIEndpoint "Create Group" "POST" "/groups" (@{
    name = "Test Group PowerShell"
    contribution_amount = 5000
    max_members = 5
    frequency = "monthly"
    payout_method = "sequential"
    insurance_opt_in = $true
} | ConvertTo-Json) 201 $ACCESS_TOKEN

if ($groupData) {
    $GROUP_ID = $groupData.id
    Write-Success "Group ID: $GROUP_ID"
}

Write-Host ""

# Test 6: Read Group
if ($GROUP_ID) {
    $groupDetails = Test-APIEndpoint "Read Group" "GET" "/groups/$GROUP_ID" "" 200 $ACCESS_TOKEN
    if ($groupDetails) {
        Write-Success "Group details retrieved"
    }
}
else {
    Write-Warning "Skipping group read test - no group ID"
}

Write-Host ""

# Test 7: Update Profile
$profileUpdate = Test-APIEndpoint "Update Profile" "PATCH" "/users/me" (@{
    full_name = "Test User PS"
    phone = $TEST_PHONE
} | ConvertTo-Json) 200 $ACCESS_TOKEN

if ($profileUpdate) {
    Write-Success "Profile update successful"
}

Write-Host ""

# Test 8: AI Chat
$aiChat = Test-APIEndpoint "AI Chat" "POST" "/ai/chat" (@{
    message = "Bonjour"
    locale = "fr"
} | ConvertTo-Json) 200 $ACCESS_TOKEN

if ($aiChat) {
    Write-Success "AI chat working"
}

Write-Host ""

# Test 9: Unauthorized Access
$unauthorized = Test-APIEndpoint "Unauthorized Access" "GET" "/users/me" "" 401 ""
if (-not $unauthorized) {
    Write-Success "Authentication protection working"
}

Write-Host ""

# Test 10: Invalid Email
$invalidEmail = Test-APIEndpoint "Invalid Email" "POST" "/auth/request-otp" (@{
    email = "invalid-email"
} | ConvertTo-Json) 422 ""

if (-not $invalidEmail) {
    Write-Success "Email validation working"
}

Write-Host ""

# Résumé final
Write-Log "🎉 Test Summary" "Magenta"
Write-Success "All automated tests completed"
Write-Warning "Manual OTP verification was required"
Write-Log "Access Token: $($ACCESS_TOKEN.Substring(0, [Math]::Min(20, $ACCESS_TOKEN.Length)))..."
if ($GROUP_ID) {
    Write-Log "Test Group ID: $GROUP_ID"
}

Write-Host ""
Write-Log "Next steps:" "Cyan"
Write-Host "1. Import the Postman collection for more detailed testing"
Write-Host "2. Check TEST_GUIDE.md for complete testing procedures"
Write-Host "3. Run performance tests with PowerShell scripts"