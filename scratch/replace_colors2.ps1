$f = 'c:\xampp\htdocs\TontineChain-Frontend\src\components\LandingPage.jsx'
$c = Get-Content $f -Raw -Encoding UTF8
$c = $c -replace 'bg-orange-50', 'bg-blue-50'
$c = $c -replace 'bg-orange-100', 'bg-blue-100'
$c = $c -replace 'text-orange-500', 'text-blue-500'
$c = $c -replace 'text-orange-400', 'text-blue-400'
$c = $c -replace 'border-orange-200', 'border-blue-200'
$c = $c -replace 'hover:border-orange', 'hover:border-blue'
$c = $c -replace 'bg-\[#FF8C00\]', 'bg-blue-500'
$c = $c -replace '#FF8C00', '#3B7FFF'
$c = $c -replace 'rgba\(255, 140, 0', 'rgba(59, 127, 255'
$c = $c -replace 'rgba\(255,140,0', 'rgba(59,127,255'
# Fix CTA section background
$c = $c -replace 'bg-amber-500\b', 'bg-blue-500'
$c = $c -replace 'S.inscrire.*?rounded-xl', "S'inscrire" + ' rounded-xl'
$c | Set-Content $f -NoNewline -Encoding UTF8
Write-Host "Done orange replacements!"
