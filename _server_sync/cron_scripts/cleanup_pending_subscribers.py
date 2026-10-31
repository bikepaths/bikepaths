#!/usr/bin/env python3
"""
Daily pending subscriber hygiene script.
Purges unconfirmed tokens older than 72 hours from pending-subscribers.json.
"""
import os
import json
import time

PENDING_FILE = "/home/user0/files/personal/email/pending-subscribers.json"
EXPIRATION_SECONDS = 72 * 3600  # 72 hours

def prune_expired():
    if not os.path.exists(PENDING_FILE):
        return

    try:
        with open(PENDING_FILE, "r", encoding="utf-8") as f:
            data = json.load(f)
    except Exception as e:
        print(f"Error reading {PENDING_FILE}: {e}")
        return

    now = int(time.time())
    cleaned = {}
    purged_count = 0

    if isinstance(data, dict):
        for token, record in data.items():
            created_at = record.get("created_at", 0)
            if (now - created_at) <= EXPIRATION_SECONDS:
                cleaned[token] = record
            else:
                purged_count += 1

    with open(PENDING_FILE, "w", encoding="utf-8") as f:
        json.dump(cleaned, f, indent=2)

    print(f"Cleaned pending subscribers: {purged_count} expired entries removed. Remaining: {len(cleaned)}")

if __name__ == "__main__":
    prune_expired()
