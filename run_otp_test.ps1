$body = @{ email = 'mourchid200523@gmail.com'; locale = 'fr' } | ConvertTo-Json
try {
    $response = Invoke-RestMethod -Uri 'https://tonnine-benin-backend.onrender.com/api/v1/auth/request-otp' -Method Post -Body $body -ContentType 'application/json' -Headers @{ Accept = 'application/json' } -ErrorAction Stop
    Write-Host 'STATUS: 200'
    Write-Host 'RESPONSE:'
    $response | ConvertTo-Json -Depth 5
} catch {
    Write-Host 'ERROR:' $_.Exception.Message
    if ($_.Exception.Response) {
        $status = $_.Exception.Response.StatusCode.value__
        Write-Host "STATUS: $status"
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $body = $reader.ReadToEnd()
        Write-Host 'BODY:'
        Write-Host $body
    }
}
