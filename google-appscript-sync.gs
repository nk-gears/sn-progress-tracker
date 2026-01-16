/**
 * Google Apps Script to sync center addresses from Google Sheet to API
 * Add this code to your Google Sheet's script editor (Extensions > Apps Script)
 */

// Configuration - Change these to match your environment
const CONFIG = {
  // API Configuration
  API_BASE_URL: "http://localhost/backend", // Base URL of your backend (e.g., http://localhost/backend or https://api.example.com/backend)
  API_ENDPOINT: "api.php/center-addresses", // API endpoint path

  // Sheet Configuration
  SHEET_NAME: "Sheet1", // Name of the sheet to read from
  HEADER_ROW: 1, // Row number containing headers (1-indexed)

  // Production Configuration Examples:
  // For localhost: "http://localhost/backend"
  // For direct URL: "http://api.example.com"
  // For subdirectory: "http://example.com/api/v1"
};

// Construct full endpoint URL
const API_ENDPOINT = CONFIG.API_BASE_URL + "/" + CONFIG.API_ENDPOINT.replace(/^\//, '');

// Column mapping from Sheet to API
const COLUMN_MAPPING = {
  'Centre code': 'center_code',
  'State': 'state',
  'District': 'district',
  'Locality': 'locality',
  'Address': 'address',
  'Contact No': 'contact_no',
  'Contact No 2': 'contact_no_2',
  'Address & contact no. verified?': 'address_contact_verified',
  'Latitude & Longitude': 'latitude_longitude',
  'Lat/Long verified?': 'lat_long_verified',
  'URL': 'url',
  'Verified': 'verified',
  'Other': 'other'
};

/**
 * Main function to sync centers from Google Sheet to API
 */
function syncCentersToAPI() {
  try {
    Logger.log("🔄 Starting sync...");

    // Get sheet and data
    const sheet = SpreadsheetApp.getActiveSheet();
    const data = sheet.getDataRange().getValues();

    if (data.length < 2) {
      showAlert("❌ Error", "Sheet is empty or has no headers");
      return;
    }

    // Get headers from first row
    const headers = data[CONFIG.HEADER_ROW - 1];
    Logger.log("Raw Headers: " + JSON.stringify(headers));

    // Debug: Show what we detected
    Logger.log("📋 Detected " + headers.length + " columns");
    for (let i = 0; i < headers.length; i++) {
      Logger.log("  Column " + (i+1) + ": '" + headers[i] + "'");
    }

    // Parse data rows
    const centers = [];
    for (let i = CONFIG.HEADER_ROW; i < data.length; i++) {
      const row = data[i];

      // Skip empty rows
      if (!row.some(cell => cell !== "")) {
        continue;
      }

      // Transform row to center object
      const center = transformRow(headers, row);
      if (center && center.center_code) { // Only add if center_code exists
        centers.push(center);
      }
    }

    Logger.log("📊 Found " + centers.length + " centers to sync");

    if (centers.length === 0) {
      showAlert("⚠️ Warning", "No valid centers found to sync");
      return;
    }

    // Show sample data
    Logger.log("Sample center (1st row): " + JSON.stringify(centers[0]));
    if (centers.length > 1) {
      Logger.log("Sample center (2nd row): " + JSON.stringify(centers[1]));
    }

    // Send to API
    const result = sendToAPI(centers);

    // Show result
    showSyncResult(result, centers.length);

  } catch (error) {
    Logger.log("❌ Error: " + error);
    showAlert("❌ Error", error.toString());
  }
}

/**
 * Transform a sheet row into a center object
 */
function transformRow(headers, row) {
  const center = {};

  for (let i = 0; i < headers.length; i++) {
    let header = String(headers[i]).trim(); // Trim whitespace
    const value = row[i];

    // Try exact match first
    let dbColumn = COLUMN_MAPPING[header];

    // If no exact match, try case-insensitive match
    if (!dbColumn) {
      for (const [key, col] of Object.entries(COLUMN_MAPPING)) {
        if (key.toLowerCase() === header.toLowerCase()) {
          dbColumn = col;
          Logger.log("⚠️ Column matched (case-insensitive): '" + header + "' → '" + key + "'");
          break;
        }
      }
    }

    if (dbColumn && value !== "") {
      center[dbColumn] = String(value).trim();
    }
  }

  return center;
}

/**
 * Send centers array to API endpoint
 */
function sendToAPI(centers) {
  const payload = JSON.stringify(centers);

  const options = {
    method: 'post',
    contentType: 'application/json',
    payload: payload,
    muteHttpExceptions: true,
    timeout: 60
  };

  Logger.log("📤 Sending to API: " + API_ENDPOINT);
  Logger.log("Payload size: " + payload.length + " bytes");
  Logger.log("Number of centers: " + centers.length);

  try {
    const response = UrlFetchApp.fetch(API_ENDPOINT, options);
    const result = JSON.parse(response.getContentText());

    Logger.log("✅ API Response: " + JSON.stringify(result));
    return result;

  } catch (error) {
    Logger.log("❌ API Error: " + error);
    return {
      success: false,
      message: error.toString()
    };
  }
}

/**
 * Display sync results in a dialog
 */
function showSyncResult(result, totalCenters) {
  let message = "";

  if (result.success) {
    message = `
      <h2>✅ Sync Successful!</h2>
      <p><strong>Summary:</strong></p>
      <ul>
        <li>Total Centers: ${totalCenters}</li>
        <li>Processed: ${result.processed || 0}</li>
        <li>Inserted: ${result.inserted || 0}</li>
        <li>Updated: ${result.updated || 0}</li>
      </ul>
      ${result.message ? `<p><strong>Message:</strong> ${result.message}</p>` : ""}
    `;

    if (result.errors && result.errors.length > 0) {
      message += `
        <p><strong>⚠️ Errors (${result.errors.length}):</strong></p>
        <ul>
          ${result.errors.slice(0, 5).map(err => `<li>${err}</li>`).join("")}
          ${result.errors.length > 5 ? `<li>... and ${result.errors.length - 5} more</li>` : ""}
        </ul>
      `;
    }
  } else {
    message = `
      <h2>❌ Sync Failed!</h2>
      <p><strong>Error:</strong> ${result.message || "Unknown error"}</p>
    `;
  }

  const htmlOutput = HtmlService.createHtmlOutput(message)
    .setWidth(500)
    .setHeight(400);

  SpreadsheetApp.getUi().showModalDialog(htmlOutput, "Sync Result");
}

/**
 * Simple alert dialog
 */
function showAlert(title, message) {
  SpreadsheetApp.getUi().alert(title + "\n\n" + message);
}

/**
 * Add a menu to Google Sheet for easy access
 */
function onOpen() {
  const ui = SpreadsheetApp.getUi();
  ui.createMenu('🔄 Sync Centers')
    .addItem('Sync to API', 'syncCentersToAPI')
    .addSeparator()
    .addItem('Debug Column Mapping', 'debugColumnMapping')
    .addItem('View Logs', 'showLogs')
    .addToUi();
}

/**
 * Show execution logs
 */
function showLogs() {
  const ui = SpreadsheetApp.getUi();
  ui.alert("Check the execution logs in:\nExtensions > Apps Script > Execution > Show execution log");
}

/**
 * Test function to verify setup
 */
function testSync() {
  Logger.log("Testing sync setup...");
  Logger.log("API Endpoint: " + API_ENDPOINT);
  Logger.log("Sheet Name: " + SHEET_NAME);
  Logger.log("Column Mapping: " + JSON.stringify(COLUMN_MAPPING));
  Logger.log("✓ Test complete. Check logs above.");
}

/**
 * Debug function to show column mapping issues
 */
function debugColumnMapping() {
  Logger.log("🔍 Debugging column mapping...\n");

  const sheet = SpreadsheetApp.getActiveSheet();
  const data = sheet.getDataRange().getValues();

  if (data.length < 1) {
    Logger.log("❌ Sheet is empty!");
    return;
  }

  const headers = data[CONFIG.HEADER_ROW - 1];

  Logger.log("📋 Your Sheet Columns vs Expected Mapping:\n");

  // Show all expected columns
  Logger.log("Expected Columns in COLUMN_MAPPING:");
  for (const [sheetCol, dbCol] of Object.entries(COLUMN_MAPPING)) {
    Logger.log("  '" + sheetCol + "' → '" + dbCol + "'");
  }

  Logger.log("\n📊 Your Sheet Columns:");
  for (let i = 0; i < headers.length; i++) {
    const header = String(headers[i]).trim();
    const match = COLUMN_MAPPING[header];
    const status = match ? "✓ Matched" : "❌ NOT matched";
    Logger.log("  [" + (i+1) + "] '" + header + "' → " + status);
  }

  Logger.log("\n⚠️ Unmapped Columns:");
  let hasUnmapped = false;
  for (let i = 0; i < headers.length; i++) {
    const header = String(headers[i]).trim();
    if (!COLUMN_MAPPING[header]) {
      Logger.log("  Column " + (i+1) + ": '" + header + "'");
      hasUnmapped = true;
    }
  }

  if (!hasUnmapped) {
    Logger.log("  None - all columns are mapped!");
  }

  // Show first row as sample
  if (data.length > 1) {
    Logger.log("\n📌 First Data Row Sample:");
    const firstRow = data[1];
    for (let i = 0; i < headers.length && i < firstRow.length; i++) {
      Logger.log("  '" + String(headers[i]).trim() + "': '" + String(firstRow[i]).trim() + "'");
    }
  }
}
