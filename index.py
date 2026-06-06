import sys
import base64
import json
import urllib.request
import urllib.error
HAPP_API = "https://crypto.happ.su/api-v2.php"
def decode_base64_url(encoded: str) -> str:
    """Декодирует base64 строку в оригинальный URL."""
    # Добавляем паддинг если нужно (base64 требует кратность 4)
    padded = encoded.strip()
    missing_padding = len(padded) % 4
    if missing_padding:
        padded += "=" * (4 - missing_padding)
    try:
        decoded = base64.b64decode(padded).decode("utf-8")
        return decoded
    except Exception as e:
        raise ValueError(f"Не удалось декодировать base64: {e}")
def encrypt_to_happ(url: str) -> str:
    """
    Отправляет URL на crypto.happ.su/api-v2.php
    и возвращает happ:// deeplink.
    """
    payload = json.dumps({"url": url}).encode("utf-8")
    req = urllib.request.Request(
        HAPP_API,
        data=payload,
        headers={
            "Content-Type": "application/json",
            "User-Agent": "Mozilla/5.0 (compatible; crypt5-script/1.0)",
        },
        method="POST",
    )
    try:
        with urllib.request.urlopen(req, timeout=15) as response:
            raw = response.read().decode("utf-8").strip()
    except urllib.error.HTTPError as e:
        raise ConnectionError(f"HTTP ошибка {e.code}: {e.reason}")
    except urllib.error.URLError as e:
        raise ConnectionError(f"Ошибка соединения: {e.reason}")
    return raw
def main():
    # Получаем входные данные
    if len(sys.argv) > 1:
        b64_input = sys.argv[1].strip()
    else:
        print("=" * 55)
        print("  base64 URL → happ5crypt deeplink")
        print("=" * 55)
        b64_input = input("Введи base64 строку: ").strip()
    if not b64_input:
        print("[ОШИБКА] Пустой ввод. Выход.")
        sys.exit(1)
    print(f"\n[1] Входная строка (base64):\n    {b64_input}")
    # Шаг 1: Декодируем base64
    try:
        decoded_url = decode_base64_url(b64_input)
    except ValueError as e:
        print(f"\n[ОШИБКА] {e}")
        sys.exit(1)
    print(f"\n[2] Декодированный URL:\n    {decoded_url}")
    # Шаг 2: Шифруем через happ5crypt API
    print(f"\n[3] Отправляем на {HAPP_API} ...")
    try:
        happ_link = encrypt_to_happ(decoded_url)
    except ConnectionError as e:
        print(f"\n[ОШИБКА] {e}")
        sys.exit(1)
    # Проверка что ответ содержит валидный happ deeplink
    if "happ://" in happ_link:
        print(f"\n[✓] Готово! happ:// deeplink:\n")
        print(f"    {happ_link}")
        print()
    else:
        print(f"\n[!] API вернул неожиданный ответ:")
        print(f"    {happ_link}")
        print("\n    Возможно ссылка невалидная или API временно недоступен.")
if __name__ == "__main__":
    main()
