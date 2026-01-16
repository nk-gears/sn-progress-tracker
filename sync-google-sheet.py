#!/usr/bin/env python3
"""
Script to sync center addresses from Google Sheet to the database via API.
Reads from Google Sheet and POSTs to the /api/center-addresses endpoint.
"""

import json
import requests
import sys
from typing import List, Dict, Any

# Configuration
GOOGLE_SHEET_ID = "YOUR_SHEET_ID_HERE"  # Replace with your Google Sheet ID
GOOGLE_SHEET_RANGE = "Sheet1!A1:M1000"  # Adjust range as needed
API_ENDPOINT = "http://localhost/backend/api.php/center-addresses"  # Adjust if needed

# Google Sheets API setup (using public sheet or with API key)
# For public sheets, you can use this URL directly
GOOGLE_SHEETS_URL = f"https://docs.google.com/spreadsheets/d/{GOOGLE_SHEET_ID}/export?format=csv"

# Column mapping from Google Sheet to database
COLUMN_MAPPING = {
    'Centre code': 'center_code',
    'State': 'state',
    'District': 'district',
    'Locality': 'locality',
    'Address': 'address',
    'Contact No': 'contact_no',
    'Address & contact no. verified?': 'address_contact_verified',
    'Latitude & Longitude': 'latitude_longitude',
    'Lat/Long verified?': 'lat_long_verified',
    'URL': 'url',
    'Verified': 'verified'
}


def fetch_google_sheet_csv() -> str:
    """
    Fetch CSV data from Google Sheet.
    For public sheets or sheets shared with 'Anyone with the link can view'
    """
    print(f"Fetching Google Sheet from: {GOOGLE_SHEETS_URL}")
    try:
        response = requests.get(GOOGLE_SHEETS_URL, timeout=30)
        response.raise_for_status()
        return response.text
    except requests.exceptions.RequestException as e:
        print(f"Error fetching Google Sheet: {e}")
        sys.exit(1)


def parse_csv_data(csv_text: str) -> List[Dict[str, str]]:
    """
    Parse CSV text and return list of dictionaries.
    """
    import csv
    from io import StringIO

    csv_file = StringIO(csv_text)
    reader = csv.DictReader(csv_file)

    records = []
    for row in reader:
        if row and any(row.values()):  # Skip empty rows
            records.append(row)

    return records


def transform_record(sheet_row: Dict[str, str]) -> Dict[str, Any]:
    """
    Transform Google Sheet row to API payload format.
    """
    api_record = {}

    for sheet_col, db_col in COLUMN_MAPPING.items():
        if sheet_col in sheet_row:
            value = sheet_row[sheet_col].strip()
            if value:  # Only include non-empty values
                api_record[db_col] = value

    return api_record


def sync_centers(records: List[Dict[str, Any]]) -> None:
    """
    Send center records to API endpoint.
    """
    if not records:
        print("No records to sync.")
        return

    print(f"\nSyncing {len(records)} center(s) to API...")
    print(f"API Endpoint: {API_ENDPOINT}")

    try:
        response = requests.post(
            API_ENDPOINT,
            json=records,
            headers={'Content-Type': 'application/json'},
            timeout=60
        )

        response.raise_for_status()
        result = response.json()

        print("\n✅ Sync Complete!")
        print(f"Response: {json.dumps(result, indent=2)}")

        if result.get('success'):
            print(f"\n📊 Summary:")
            print(f"   Total Processed: {result.get('processed', 0)}")
            print(f"   Inserted: {result.get('inserted', 0)}")
            print(f"   Updated: {result.get('updated', 0)}")
            if result.get('errors'):
                print(f"   Errors: {len(result.get('errors', []))}")
                for error in result.get('errors', [])[:5]:  # Show first 5 errors
                    print(f"     - {error}")

    except requests.exceptions.RequestException as e:
        print(f"❌ Error syncing to API: {e}")
        sys.exit(1)


def main():
    """
    Main execution function.
    """
    print("=" * 60)
    print("Google Sheet Center Sync Script")
    print("=" * 60)

    # Step 1: Fetch data from Google Sheet
    print("\n1️⃣  Fetching data from Google Sheet...")
    csv_data = fetch_google_sheet_csv()
    print("   ✓ Data fetched successfully")

    # Step 2: Parse CSV
    print("\n2️⃣  Parsing CSV data...")
    sheet_records = parse_csv_data(csv_data)
    print(f"   ✓ Found {len(sheet_records)} records")

    if len(sheet_records) == 0:
        print("   ⚠️  No records found in sheet!")
        sys.exit(1)

    # Step 3: Transform records
    print("\n3️⃣  Transforming records...")
    api_records = [transform_record(row) for row in sheet_records]

    # Show sample
    if api_records:
        print(f"   Sample record:")
        print(f"   {json.dumps(api_records[0], indent=6)}")

    # Step 4: Sync to API
    print("\n4️⃣  Syncing to API...")
    sync_centers(api_records)

    print("\n" + "=" * 60)
    print("Done! ✨")
    print("=" * 60)


if __name__ == "__main__":
    main()
