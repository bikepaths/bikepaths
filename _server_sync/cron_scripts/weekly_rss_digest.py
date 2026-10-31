#!/usr/bin/env python3
"""
Weekly RSS Newsletter Digest Dispatcher for BikePaths.
Dispatches a curated weekly summary of posts from https://bikepaths.org/blog/feed/rss.
Runs every Saturday at 09:00 AM.
Enforces a 5-second trickle delay between recipients to protect sender reputation.
"""
import os
import sys
import re
import time
import hmac
import hashlib
import smtplib
import configparser
import urllib.request
import urllib.parse
import xml.etree.ElementTree as ET
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.utils import formatdate, parsedate_to_datetime
from datetime import datetime, timezone, timedelta

RSS_URL = "https://bikepaths.org/blog/feed/rss"
SECRET_FILE = "/home/user0/files/personal/email/newsletter_secret.key"
SUBSCRIBER_FILE = "/home/user0/files/personal/email/subscriber-email-list.txt"
TELEGRAM_INI = "/home/user0/files/personal/email/telegram.ini"
CONTENT_DIR = "/var/www/bikepaths/html/blog/content/chas/blog"
FROM_ADDR = "BikePaths <newsletter@bikepaths.org>"
REPLY_TO = "newsletter@bikepaths.org"
SMTP_HOST = "localhost"
SMTP_PORT = 25
TRICKLE_DELAY_SECONDS = 5
LOOKBACK_DAYS = 7

def get_secret():
    if not os.path.exists(SECRET_FILE):
        return "default-insecure-secret"
    with open(SECRET_FILE, "r", encoding="utf-8") as f:
        return f.read().strip()

def generate_unsubscribe_token(email: str, secret: str) -> str:
    return hmac.new(secret.encode("utf-8"), email.lower().strip().encode("utf-8"), hashlib.sha256).hexdigest()

def send_telegram_alert(text: str):
    if not os.path.exists(TELEGRAM_INI):
        return
    try:
        config = configparser.ConfigParser()
        config.read(TELEGRAM_INI)
        token = config.get("telegram", "token", fallback=None)
        chat_id = config.get("telegram", "chat_id", fallback=None)
        if token:
            token = token.strip().strip('"').strip("'")
        if chat_id:
            chat_id = chat_id.strip().strip('"').strip("'")

        if token and chat_id:
            url = f"https://api.telegram.org/bot{token}/sendMessage?chat_id={chat_id}&text=" + urllib.parse.quote(text)
            urllib.request.urlopen(url, timeout=5)
    except Exception as e:
        print(f"Telegram notification warning: {e}")

def get_metadescription_from_file(slug: str, fallback: str) -> str:
    """Extracts exact <!--d ... d--> metadata from published markdown file."""
    if not slug or not os.path.exists(CONTENT_DIR):
        return fallback

    for root, _, files in os.walk(CONTENT_DIR):
        for f in files:
            if f.endswith(f"_{slug}.md") or f.endswith(f"{slug}.md"):
                filepath = os.path.join(root, f)
                try:
                    with open(filepath, "r", encoding="utf-8") as md_file:
                        for line in md_file:
                            m = re.search(r'<!--d\s+(.*?)\s+d-->', line)
                            if m:
                                return m.group(1).strip()
                except Exception:
                    pass
    return fallback

def fetch_recent_posts(days=LOOKBACK_DAYS):
    req = urllib.request.Request(RSS_URL, headers={"User-Agent": "BikePaths-Digest/1.0"})
    with urllib.request.urlopen(req, timeout=15) as resp:
        xml_data = resp.read()

    root = ET.fromstring(xml_data)
    channel = root.find("channel")
    if channel is None:
        return []

    cutoff = datetime.now(timezone.utc) - timedelta(days=days)
    posts = []

    for item in channel.findall("item"):
        title_el = item.find("title")
        link_el = item.find("link")
        desc_el = item.find("description")
        pub_el = item.find("pubDate")
        cat_el = item.find("category")

        if title_el is None or link_el is None or pub_el is None:
            continue

        title = title_el.text or "Untitled"
        link = link_el.text or ""
        desc = (desc_el.text or "").strip()
        category = cat_el.text if cat_el is not None else "General"

        try:
            pub_date = parsedate_to_datetime(pub_el.text)
            if pub_date.tzinfo is None:
                pub_date = pub_date.replace(tzinfo=timezone.utc)
        except Exception:
            continue

        if pub_date >= cutoff:
            # Extract slug from link
            slug = link.rstrip("/").split("/")[-1]
            metadesc = get_metadescription_from_file(slug, desc)
            if not metadesc:
                metadesc = " ".join(desc.split())[:200]

            posts.append({
                "title": title,
                "link": link,
                "description": metadesc,
                "category": category,
                "pub_date": pub_date.strftime("%B %d, %Y")
            })

    return posts

def build_email_content(posts, recipient_email, unsub_token):
    unsub_url = f"https://bikepaths.org/blog/unsubscribe.php?email={urllib.parse.quote(recipient_email)}&token={unsub_token}"
    date_str = datetime.now().strftime("%B %d, %Y")
    year_str = datetime.now().strftime("%Y")

    # Plain text version
    text_lines = [
        "BIKEPATHS.ORG",
        f"Weekly Digest — {date_str}",
        f"To unsubscribe from this weekly digest: {unsub_url}",
        "",
        "==================================================",
        ""
    ]
    for idx, p in enumerate(posts):
        text_lines.append(f"[{p['category'].upper()}] {p['pub_date']}")
        text_lines.append(p['title'])
        text_lines.append(p['description'])
        text_lines.append(p['link'])
        if idx < len(posts) - 1:
            text_lines.append("")
            text_lines.append("-" * 50)
            text_lines.append("")

    text_lines.extend([
        "",
        "==================================================",
        f"To unsubscribe from this weekly digest: {unsub_url}",
        "",
        f"(c) {year_str} bikepaths.org"
    ])
    plain_text = "\n".join(text_lines)

    # HTML Version
    items_html = ""
    for idx, p in enumerate(posts):
        divider = '<hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 24px 0;">' if idx < len(posts) - 1 else ''
        items_html += f'''
        <div style="margin-bottom: 20px;">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 4px;">
                {p['category']} &bull; {p['pub_date']}
            </div>
            <h2 style="font-size: 18px; font-weight: 700; margin: 4px 0 8px 0; line-height: 1.35;">
                <a href="{p['link']}" style="color: #1d4ed8; text-decoration: none;">{p['title']}</a>
            </h2>
            <p style="font-size: 14px; line-height: 1.6; color: #334155; margin: 0;">
                {p['description']}
            </p>
        </div>
        {divider}'''

    html = f'''<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BikePaths Weekly Digest</title>
</head>
<body style="margin: 0; padding: 20px 10px; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <div style="max-width: 580px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 25px 30px;">

        <!-- Top Unsubscribe Notice — centered, above masthead -->
        <div style="font-size: 13px; color: #475569; text-align: center; padding-bottom: 14px;">
            To unsubscribe from this weekly digest, <a href="{unsub_url}" style="color: #2563eb; font-weight: 600; text-decoration: underline;">click here</a>.
        </div>
        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 0 0 22px 0;">

        <!-- Masthead: circular mandala logo + bikepaths.org + date -->
        <div style="margin-bottom: 25px; padding-bottom: 16px; border-bottom: 2px solid #0f172a;">
            <table cellpadding="0" cellspacing="0" border="0" style="vertical-align: middle; width: 100%;">
                <tr>
                    <td style="vertical-align: middle; width: 48px; padding-right: 12px;">
                        <a href="https://bikepaths.org" style="text-decoration: none;">
                            <img src="https://bikepaths.org/blog/themes/blog/images/logo.png" alt="bikepaths.org" style="height: 38px; width: 38px; border-radius: 50%; display: block; border: 0;">
                        </a>
                    </td>
                    <td style="vertical-align: middle;">
                        <a href="https://bikepaths.org" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 21px; font-weight: 700; color: #0f172a; text-decoration: none; letter-spacing: -0.3px;">bikepaths.org</a>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px; font-weight: 500;">Weekly Digest &bull; {date_str}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Content -->
        <div>
            {items_html}
        </div>

        <!-- Footer: centered unsubscribe above copyright -->
        <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; text-align: center;">
            <div style="font-size: 13px; color: #475569; margin-bottom: 10px;">
                To unsubscribe from this weekly digest, <a href="{unsub_url}" style="color: #2563eb; font-weight: 600; text-decoration: underline;">click here</a>.
            </div>
            <div style="font-size: 12px; color: #94a3b8;">
                &copy; {year_str} <a href="https://bikepaths.org" style="color: #94a3b8; text-decoration: none;">bikepaths.org</a>
            </div>
        </div>
    </div>
</body>
</html>'''

    return plain_text, html, unsub_url

def dispatch_digest(dry_run=False, output_sample=False, test_recipient=None):
    posts = fetch_recent_posts(days=LOOKBACK_DAYS)
    if not posts:
        posts = fetch_recent_posts(days=14)

    if not posts:
        print("No recent posts found in RSS feed. Exiting.")
        return

    secret = get_secret()

    if test_recipient:
        subscribers = [test_recipient.lower().strip()]
    else:
        subscribers = []
        if os.path.exists(SUBSCRIBER_FILE):
            with open(SUBSCRIBER_FILE, "r", encoding="utf-8") as f:
                subscribers = [line.strip().lower() for line in f if line.strip() and "@" in line]

    sample_email = subscribers[0] if subscribers else "subscriber@example.com"
    sample_token = generate_unsubscribe_token(sample_email, secret)
    plain_text, html, unsub_url = build_email_content(posts, sample_email, sample_token)

    if output_sample:
        print("--- EMAIL HTML PREVIEW DUMP ---")
        print(html)
        return

    print(f"Loaded {len(posts)} recent posts with verified metadescriptions.")
    print(f"Target recipients: {len(subscribers)}")

    if dry_run:
        print("\n[DRY RUN MODE ACTIVE - NO EMAILS WILL BE TRANSMITTED]")
        for idx, sub in enumerate(subscribers, 1):
            tok = generate_unsubscribe_token(sub, secret)
            print(f"  {idx}. Would dispatch to: {sub} (Unsub token: {tok[:12]}...)")
        return

    if not subscribers:
        print("No active subscribers in list. Exiting.")
        return

    sent_count = 0
    date_str = datetime.now().strftime("%B %d, %Y")

    for sub in subscribers:
        tok = generate_unsubscribe_token(sub, secret)
        body_text, body_html, unsub = build_email_content(posts, sub, tok)

        msg = MIMEMultipart("alternative")
        msg["Subject"] = f"bikepaths.org Weekly Digest - {date_str}"
        msg["From"] = FROM_ADDR
        msg["To"] = sub
        msg["Reply-To"] = REPLY_TO
        msg["Date"] = formatdate(localtime=True)
        msg["List-Unsubscribe"] = f"<{unsub}>"
        msg["List-Unsubscribe-Post"] = "List-Unsubscribe=One-Click"

        part1 = MIMEText(body_text, "plain", "utf-8")
        part2 = MIMEText(body_html, "html", "utf-8")
        msg.attach(part1)
        msg.attach(part2)

        try:
            with smtplib.SMTP(SMTP_HOST, SMTP_PORT, timeout=10) as server:
                server.sendmail(REPLY_TO, [sub], msg.as_string())
            sent_count += 1
            print(f"Dispatched digest to: {sub} ({sent_count}/{len(subscribers)})")
        except Exception as e:
            print(f"Failed sending to {sub}: {e}")

        if sent_count < len(subscribers):
            time.sleep(TRICKLE_DELAY_SECONDS)

    summary = f"Weekly RSS Digest dispatched to {sent_count} recipients."
    print(summary)
    send_telegram_alert(summary)

if __name__ == "__main__":
    is_dry = "--dry-run" in sys.argv
    is_sample = "--sample-html" in sys.argv
    test_recip = None
    for arg in sys.argv[1:]:
        if arg.startswith("--test-to="):
            test_recip = arg.split("=", 1)[1]
    dispatch_digest(dry_run=is_dry, output_sample=is_sample, test_recipient=test_recip)
