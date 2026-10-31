#!/usr/bin/env python3
"""
One-shot re-permission dispatch for bikepaths.org historical subscribers.
Generates a 72h opt-in token for each address, stores in pending-subscribers.json,
and sends the standard confirmation email with a 5-second trickle delay.
"""
import os
import json
import time
import hashlib
import secrets
import smtplib
import urllib.request
import urllib.parse
import configparser
from datetime import datetime, timezone, timedelta
from email.mime.text import MIMEText
from email.utils import formatdate

PENDING_FILE   = "/home/user0/files/personal/email/pending-subscribers.json"
SUBSCRIBER_FILE= "/home/user0/files/personal/email/subscriber-email-list.txt"
TELEGRAM_INI   = "/home/user0/files/personal/email/telegram.ini"
FROM_ADDR      = "BikePaths <newsletter@bikepaths.org>"
REPLY_TO       = "newsletter@bikepaths.org"
SMTP_HOST      = "localhost"
SMTP_PORT      = 25
TRICKLE        = 5

ADDRESSES = [
    "hopiland@gmail.com",
    "paul@inex.ca",
    "tere_rubi_5@hotmail.com",
    "jpventasplaya@gmail.com",
    "feralgyrl@yahoo.com",
    "h.melotto@melottogroup.com",
]

def send_telegram(text):
    if not os.path.exists(TELEGRAM_INI):
        return
    try:
        config = configparser.ConfigParser()
        config.read(TELEGRAM_INI)
        token   = config.get("telegram", "token",   fallback=None)
        chat_id = config.get("telegram", "chat_id", fallback=None)
        if token:   token   = token.strip().strip('"').strip("'")
        if chat_id: chat_id = chat_id.strip().strip('"').strip("'")
        if token and chat_id:
            url = f"https://api.telegram.org/bot{token}/sendMessage?chat_id={chat_id}&text=" + urllib.parse.quote(text)
            urllib.request.urlopen(url, timeout=5)
    except Exception as e:
        print(f"  Telegram warning: {e}")

def load_active():
    if not os.path.exists(SUBSCRIBER_FILE):
        return set()
    with open(SUBSCRIBER_FILE, "r", encoding="utf-8") as f:
        return {line.strip().lower() for line in f if line.strip() and "@" in line}

def load_pending():
    if not os.path.exists(PENDING_FILE):
        return []
    with open(PENDING_FILE, "r", encoding="utf-8") as f:
        data = json.load(f)
    return data if isinstance(data, list) else []

def save_pending(pending):
    with open(PENDING_FILE, "w", encoding="utf-8") as f:
        json.dump(pending, f, indent=2)
    os.chmod(PENDING_FILE, 0o666)

def send_confirmation(email, token):
    verify_url = f"https://bikepaths.org/blog/verify.php?token={token}"
    body = (
        "Please confirm your subscription to the bikepaths.org weekly email digest "
        "by clicking the link below:\n\n"
        f"{verify_url}\n\n"
        "This link expires in 72 hours.\n\n"
        "If you did not request this subscription, no action is needed, "
        "and you will not receive any further emails.\n\n"
        "Thank you,\n"
        "https://bikepaths.org\n"
    )
    msg = MIMEText(body, "plain", "utf-8")
    msg["Subject"]  = "Confirm your subscription to bikepaths.org"
    msg["From"]     = FROM_ADDR
    msg["To"]       = email
    msg["Reply-To"] = REPLY_TO
    msg["Date"]     = formatdate(localtime=True)

    with smtplib.SMTP(SMTP_HOST, SMTP_PORT, timeout=10) as server:
        server.sendmail(REPLY_TO, [email], msg.as_string())

def main():
    active  = load_active()
    pending = load_pending()
    pending_emails = {e["email"].lower() for e in pending if "email" in e}
    expiry  = (datetime.now(timezone.utc) + timedelta(hours=72)).isoformat()

    sent = 0
    skipped = 0

    for addr in ADDRESSES:
        addr = addr.lower().strip()

        if addr in active:
            print(f"  SKIP (already active): {addr}")
            skipped += 1
            continue

        if addr in pending_emails:
            print(f"  SKIP (already pending): {addr}")
            skipped += 1
            continue

        token = secrets.token_urlsafe(32)
        pending.append({"email": addr, "token": token, "expires": expiry})
        save_pending(pending)
        pending_emails.add(addr)

        try:
            send_confirmation(addr, token)
            sent += 1
            print(f"  Sent ({sent}): {addr}")
        except Exception as e:
            print(f"  FAILED: {addr} — {e}")

        if sent < len(ADDRESSES):
            time.sleep(TRICKLE)

    summary = f"Re-permission dispatch complete: {sent} sent, {skipped} skipped."
    print(summary)
    send_telegram(summary)

if __name__ == "__main__":
    main()
