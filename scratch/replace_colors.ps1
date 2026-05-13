$f = 'c:\xampp\htdocs\TontineChain-Frontend\src\components\LandingPage.jsx'
$c = Get-Content $f -Raw -Encoding UTF8
$c = $c -replace 'text-amber-500', 'text-blue-500'
$c = $c -replace 'bg-amber-500', 'bg-blue-500'
$c = $c -replace 'bg-amber-50', 'bg-blue-50'
$c = $c -replace 'bg-amber-100', 'bg-blue-100'
$c = $c -replace 'bg-amber-400', 'bg-blue-400'
$c = $c -replace 'text-amber-600', 'text-blue-600'
$c = $c -replace 'border-amber-200', 'border-blue-200'
$c = $c -replace 'text-amber-200', 'text-blue-200'
$c = $c -replace 'text-amber-900', 'text-blue-900'
$c = $c -replace 'text-amber-800', 'text-blue-800'
$c = $c -replace 'text-amber-100', 'text-blue-100'
$c = $c -replace 'hover:bg-amber-50', 'hover:bg-blue-50'
$c = $c -replace 'hover:bg-amber-600', 'hover:bg-blue-600'
$c = $c -replace 'hover:bg-amber-300', 'hover:bg-blue-300'
$c = $c -replace 'focus:border-amber-400', 'focus:border-blue-400'
$c | Set-Content $f -NoNewline -Encoding UTF8
Write-Host "Done!"
