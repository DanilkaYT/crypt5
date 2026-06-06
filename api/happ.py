import json
import base64
import urllib.request
import urllib.error

HAPP_API = "https://crypto.happ.su/api-v2.php"

def decode_base64_url(encoded: str) -> str:
    padded = encoded.strip()
    missing_padding = len(padded) % 4
    if missing_padding:
        padded += "=" * (4 - missing_padding)
    return base64.b64decode(padded).decode("utf-8")

def encrypt_to_happ(url: str) -> str:
    payload = json.dumps({"url": url}).encode("utf-8")
    req = urllib.request.Request(
        HAPP_API,
        data=payload,
        headers={"Content-Type": "application/json", "User-Agent": "happ-redirect/1.0"},
        method="POST",
    )
    with urllib.request.urlopen(req, timeout=9) as resp:
        return resp.read().decode("utf-8").strip()

def handler(request: dict, context: dict) -> dict:
    method = request.get("method", "GET")
    b64_input = None

    if method == "GET":
        b64_input = request.get("query", {}).get("b64")
    elif method == "POST":
        body = request.get("body", "")
        try:
            data = json.loads(body)
            b64_input = data.get("b64")
        except:
            b64_input = None

    if not b64_input:
        if method == "GET":
            return html_response(400, "Ошибка: отсутствует параметр 'b64'")
        return json_response(400, {"error": "Missing 'b64'"})

    try:
        decoded_url = decode_base64_url(b64_input)
        happ_link = encrypt_to_happ(decoded_url)
        if "happ://" not in happ_link:
            raise Exception("API вернул не happ:// ссылку")
        if method == "GET":
            return html_redirect(happ_link)
        return json_response(200, {"happ_link": happ_link})
    except Exception as e:
        if method == "GET":
            return html_response(500, f"Ошибка: {str(e)}")
        return json_response(500, {"error": str(e)})

def html_redirect(happ_link: str) -> dict:
    html = f"""<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Перенаправление в приложение</title>
<script>window.location.href="{happ_link}";
setTimeout(function(){{document.getElementById('manual').style.display='block';document.getElementById('loading').style.display='none';}},2000);
</script>
<style>.manual{{display:none;margin-top:2rem;}}</style>
</head>
<body>
<div id="loading"><p>Перенаправление в приложение...</p></div>
<div id="manual" class="manual"><p>Не удалось автоматически открыть приложение.</p>
<button onclick="window.location.href='{happ_link}'">Открыть вручную</button><br>
<a href="{happ_link}">{happ_link}</a></div>
</body>
</html>"""
    return {"statusCode": 200, "headers": {"Content-Type": "text/html"}, "body": html}

def html_response(code: int, msg: str) -> dict:
    html = f"<html><body><h1>Ошибка {code}</h1><p>{msg}</p></body></html>"
    return {"statusCode": code, "headers": {"Content-Type": "text/html"}, "body": html}

def json_response(code: int, data: dict) -> dict:
    return {"statusCode": code, "headers": {"Content-Type": "application/json"}, "body": json.dumps(data)}
