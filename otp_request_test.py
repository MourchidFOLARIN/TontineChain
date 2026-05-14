import json
import urllib.request
import urllib.error

data = json.dumps({'email': 'mourchidolawale@gmail.com', 'locale': 'fr'}).encode('utf-8')
request = urllib.request.Request(
    'https://tonnine-benin-backend.onrender.com/api/v1/auth/request-otp',
    data=data,
    headers={'Content-Type': 'application/json', 'Accept': 'application/json'},
    method='POST'
)

print('START')
print('DATA', data)
try:
    response = urllib.request.urlopen(request, timeout=30)
    print('STATUS', response.status)
    body = response.read().decode('utf-8')
    print('BODY', body)
except urllib.error.HTTPError as e:
    print('HTTP_ERROR', e.code)
    print(e.read().decode('utf-8'))
except Exception as e:
    print('EXCEPTION', type(e).__name__, e)
