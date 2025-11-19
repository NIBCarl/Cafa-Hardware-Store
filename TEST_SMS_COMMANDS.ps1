# ========================================
# SMS Gateway Testing Commands (PowerShell)
# ========================================

# Make sure your Laravel server is running first!
# Run: php artisan serve

$baseUrl = "http://localhost:8000"  # Change if your server runs on different port

Write-Host "`n=== Test 1: Check SMS Gateway Status ===" -ForegroundColor Cyan

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/api/sms/status" -Method Get
    Write-Host "Success!" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}

Write-Host "`n=== Test 2: Send Test SMS ===" -ForegroundColor Cyan
Write-Host "Sending SMS to 09631065271..." -ForegroundColor Yellow

$body = @{
    phone = "09631065271"
    message = "Test SMS from CAFA POS via SMS-Gate.app"
    channel = "android"
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "$baseUrl/api/sms/test" `
        -Method Post `
        -ContentType "application/json" `
        -Body $body
    
    Write-Host "Success! SMS sent!" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
    Write-Host $_.Exception.Response.StatusCode -ForegroundColor Red
}

Write-Host "`n=== Done! ===" -ForegroundColor Cyan
Write-Host "Check your phone for SMS!" -ForegroundColor Yellow
