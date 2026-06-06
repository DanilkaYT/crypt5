import json
import base64
import urllib.request
import urllib.error
from urllib.parse import parse_qs

HAPP_API = "https://crypto.happ.su/api-v2.php"

def decode_base64_url(encoded: str) -> str:
    """Декодирует base64 в URL"""
    padded = encoded.strip()
    missing_padding = len(padded) % 4
    if missing_padding:
        padded += "=" * (4 - missing_padding)
    return base64.b64decode(padded).decode("utf-8")

def encrypt_to_happ(url: str) -> str:
    """Шифрует URL через happ API и возвращает happ:// ссылку"""
    payload = json.dumps({"url": url}).encode("utf-8")
    req = urllib.request.Request(
        HAPP_API,
        data=payload,
        headers={
            "Content-Type": "application/json",
            "User-Agent": "happ-redirect/1.0"
        },
        method="POST",
    )
    with urllib.request.urlopen(req, timeout=9) as resp:
        return resp.read().decode("utf-8").strip()

def handler(request: dict, context: dict) -> dict:
    """
    Vercel serverless function handler.
    GET: возвращает HTML с автоматическим переходом на happ://
    POST: возвращает JSON с полем happ_link (для API)
    """
    method = request.get("method", "GET")
    b64_input = None

    # Получаем b64 параметр
    if method == "GET":
        query = request.get("query", {})
        b64_input = query.get("b64")
    elif method == "POST":
        body = request.get("body", "")
        try:
            data = json.loads(body)
            b64_input = data.get("b64")
        except:
            b64_input = None

    if not b64_input:
        if method == "GET":
            return html_response(400, "Ошибка: отсутствует параметр 'b64' в запросе. Пример: ?b64=...")
        else:
            return json_response(400, {"error": "Missing 'b64' parameter"})

    try:
        decoded_url = decode_base64_url(b64_input)
        happ_link = encrypt_to_happ(decoded_url)

        if "happ://" not in happ_link:
            raise Exception("API вернул не happ:// ссылку")

        # Успех: для GET возвращаем HTML с автоматическим переходом
        if method == "GET":
            return html_redirect(happ_link)
        else:
            return json_response(200, {"happ_link": happ_link})

    except Exception as e:
        error_msg = str(e)
        if method == "GET":
            return html_response(500, f"Ошибка: {error_msg}")
        else:
            return json_response(500, {"error": error_msg})

def html_redirect(happ_link: str) -> dict:
    """Возвращает HTML-страницу, которая сразу перенаправляет на happ://"""
    html_content = f"""<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Перенаправление в приложение</title>
    <script>
        // Пытаемся открыть deeplink
        window.location.href = "{happ_link}";
        // Fallback: если не открылось через 2 секунды, показываем кнопку
        setTimeout(function() {{
            document.getElementById('manual').style.display = 'block';
            document.getElementById('loading').style.display = 'none';
        }}, 2000);
    </script>
    <style>
        body {{
            font-family: sans-serif;
            text-align: center;
            padding: 2rem;
        }}
        .manual {{
            display: none;
            margin-top: 2rem;
            padding: 1rem;
            background: #f0f0f0;
            border-radius: 8px;
        }}
        button {{
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            background: #0070f3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }}
    </style>
</head>
<body>
    <div id="loading">
        <p>Перенаправление в приложение...</p>
        <p>Если ничего не происходит, нажмите кнопку ниже.</p>
    </div>
    <div id="manual" class="manual">
        <p>Не удалось автоматически открыть приложение.</p>
        <button onclick="window.location.href='{happ_link}'">Открыть вручную</button>
        <p><small>Ссылка: <a href="{happ_link}">{happ_link}</a></small></p>
    </div>
</body>
</html>"""
    return {
        "statusCode": 200,
        "headers": {"Content-Type": "text/html; charset=utf-8"},
        "body": html_content
    }

def html_response(status_code: int, message: str) -> dict:
    """Возвращает HTML-страницу с ошибкой"""
    html = f"""<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Ошибка</title></head>
<body style="font-family:sans-serif;text-align:center;padding:2rem">
    <h1>Ошибка {status_code}</h1>
    <p>{message}</p>
    <hr>
    <small>happ-redirect на Vercel</small>
</body>
</html>"""
    return {
        "statusCode": status_code,
        "headers": {"Content-Type": "text/html; charset=utf-8"},
        "body": html
    }

def json_response(status_code: int, data: dict) -> dict:
    """Возвращает JSON-ответ (для POST)"""
    return {
        "statusCode": status_code,
        "headers": {"Content-Type": "application/json"},
        "body": json.dumps(data, ensure_ascii=False)
    }
