/**
 * Google Apps Script for importing individual hours data to the API
 *
 * Usage:
 * 1. Open your Google Sheet
 * 2. Click Extensions > Apps Script
 * 3. Paste this code into the editor
 * 4. Update CONFIG with your API endpoint and sheet details
 * 5. Run importIndividualHours() or use the custom menu
 */

// ========== CONFIGURATION ==========
const CONFIG = {
  // API endpoint for importing individual hours
  API_URL: 'http://happy-village.org/sn-progress/api.php/api/import-individual-hours',

  // Google Sheet ID (copy from the URL)
  SHEET_ID: SpreadsheetApp.getActiveSpreadsheet().getId(),

  // Sheet name containing the data
  SHEET_NAME: 'Individual Hours',

  // Header row number (usually 1)
  HEADER_ROW: 1,

  // First data row (usually 2)
  DATA_START_ROW: 2,

  // Column mappings (adjust these to match your sheet columns)
  // Key = JSON field name, Value = Column letter or header name
  COLUMN_MAP: {
    'participant_name': 'A',         // Optional if participant_id is provided
    'participant_age': 'G',          // Optional
    'participant_gender': 'D',       // Optional
    'session_date': 'D',             // Required: YYYY-MM-DD
    'start_time': 'J',               // Required: HH:MM:SS
    'duration_minutes': 'I',         // Required: 30-960, multiple of 30
    'branch_id': 'E',                // Required
    'volunteer_id': 'K'              // Required
  },

  // Optional: Add authentication headers if needed
  HEADERS: {
    'Content-Type': 'application/json'
    // Add any other headers like:
    // 'Authorization': 'Bearer YOUR_TOKEN'
  }
};

// ========== MAIN FUNCTIONS ==========

/**
 * Create a custom menu in the Google Sheet
 */
function onOpen() {
  const ui = SpreadsheetApp.getUi();
  ui.createMenu('Individual Hours')
    .addItem('Import Hours Data', 'importIndividualHours')
    .addItem('Preview Data', 'previewData')
    .addSeparator()
    .addItem('Clear Status Column', 'clearStatusColumn')
    .addToUi();
}

/**
 * Main function to import individual hours data from sheet to API
 */
function importIndividualHours() {
  try {
    Logger.log('Starting import process...');

    // Get sheet and data
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(CONFIG.SHEET_NAME);
    if (!sheet) {
      throw new Error(`Sheet "${CONFIG.SHEET_NAME}" not found`);
    }

    // Get all data
    const lastRow = sheet.getLastRow();
    if (lastRow < CONFIG.DATA_START_ROW) {
      throw new Error('No data found in sheet');
    }

    const range = sheet.getRange(CONFIG.DATA_START_ROW, 1, lastRow - CONFIG.DATA_START_ROW + 1, sheet.getLastColumn());
    const values = range.getValues();

    Logger.log(`Found ${values.length} rows of data`);

    // Convert rows to JSON objects
    const jsonData = [];
    const statusColumn = sheet.getLastColumn() + 1; // Column for status messages
    const statusRange = sheet.getRange(CONFIG.DATA_START_ROW, statusColumn, values.length, 1);
    const statusValues = [];

    for (let i = 0; i < values.length; i++) {
      const row = values[i];
      const jsonObject = rowToJson(row);

      // Validate required fields
      const validation = validateRecord(jsonObject, i + CONFIG.DATA_START_ROW);
      if (!validation.valid) {
        statusValues.push([validation.error]);
        continue;
      }

      jsonData.push(jsonObject);
      statusValues.push(['PENDING']);
    }

    if (jsonData.length === 0) {
      SpreadsheetApp.getUi().alert('No valid records to import');
      return;
    }

    Logger.log(`Prepared ${jsonData.length} valid records for import`);
    Logger.log('Payload: ' + JSON.stringify(jsonData));

    // Call the API
    const response = sendToApi(jsonData);

    Logger.log('API Response: ' + JSON.stringify(response));

    // Process response and update status column
    updateStatusColumn(sheet, response, jsonData);

    // Show summary
    showSummary(response);

  } catch (error) {
    Logger.log('Error: ' + error.toString());
    SpreadsheetApp.getUi().alert('Error: ' + error.toString());
  }
}

/**
 * Preview the data that will be sent to the API
 */
function previewData() {
  try {
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(CONFIG.SHEET_NAME);
    if (!sheet) {
      throw new Error(`Sheet "${CONFIG.SHEET_NAME}" not found`);
    }

    const lastRow = sheet.getLastRow();
    if (lastRow < CONFIG.DATA_START_ROW) {
      throw new Error('No data found in sheet');
    }

    const range = sheet.getRange(CONFIG.DATA_START_ROW, 1, lastRow - CONFIG.DATA_START_ROW + 1, sheet.getLastColumn());
    const values = range.getValues();

    const jsonData = [];
    for (let i = 0; i < Math.min(values.length, 5); i++) {
      const jsonObject = rowToJson(values[i]);
      const validation = validateRecord(jsonObject, i + CONFIG.DATA_START_ROW);
      if (validation.valid) {
        jsonData.push(jsonObject);
      }
    }

    const preview = JSON.stringify(jsonData, null, 2);
    Logger.log('Preview of first 5 valid records:\n' + preview);
    SpreadsheetApp.getUi().alert('Preview:\n' + preview.substring(0, 1000) + '\n... (see logs for full preview)');

  } catch (error) {
    SpreadsheetApp.getUi().alert('Error: ' + error.toString());
  }
}

/**
 * Clear the status column
 */
function clearStatusColumn() {
  try {
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(CONFIG.SHEET_NAME);
    const lastRow = sheet.getLastRow();
    if (lastRow >= CONFIG.DATA_START_ROW) {
      const statusColumn = sheet.getLastColumn() + 1;
      sheet.getRange(CONFIG.DATA_START_ROW, statusColumn, lastRow - CONFIG.DATA_START_ROW + 1, 1).clearContent();
      SpreadsheetApp.getUi().alert('Status column cleared');
    }
  } catch (error) {
    SpreadsheetApp.getUi().alert('Error: ' + error.toString());
  }
}

// ========== HELPER FUNCTIONS ==========

/**
 * Convert a sheet row to JSON object based on column mapping
 */
function rowToJson(row) {
  const jsonObject = {};

  for (const [jsonKey, colLetter] of Object.entries(CONFIG.COLUMN_MAP)) {
    const colIndex = colLetter.charCodeAt(0) - 65; // Convert A->0, B->1, etc.
    const value = row[colIndex];

    if (value !== null && value !== undefined && value !== '') {
      // Handle different types
      if (jsonKey === 'participant_id' || jsonKey === 'participant_age' || jsonKey === 'branch_id' || jsonKey === 'duration_minutes' || jsonKey === 'volunteer_id') {
        jsonObject[jsonKey] = Number(value);
      } else {
        jsonObject[jsonKey] = String(value).trim();
      }
    }
  }

  return jsonObject;
}

/**
 * Validate a single record
 */
function validateRecord(record, rowNumber) {
  // Check for required fields
  if (!record.session_date) {
    return { valid: false, error: 'Missing session_date' };
  }

  if (!record.start_time) {
    return { valid: false, error: 'Missing start_time' };
  }

  if (!record.duration_minutes && record.duration_minutes !== 0) {
    return { valid: false, error: 'Missing duration_minutes' };
  }

  if (!record.branch_id) {
    return { valid: false, error: 'Missing branch_id' };
  }

  if (!record.volunteer_id) {
    return { valid: false, error: 'Missing volunteer_id' };
  }

  // Check for participant_id OR participant_name
  if (!record.participant_id && !record.participant_name) {
    return { valid: false, error: 'Missing participant_id or participant_name' };
  }

  // Validate session_date format
  if (!/^\d{4}-\d{2}-\d{2}$/.test(record.session_date)) {
    return { valid: false, error: 'Invalid session_date format (use YYYY-MM-DD)' };
  }

  // Validate start_time format
  if (!/^\d{2}:\d{2}:\d{2}$/.test(record.start_time)) {
    return { valid: false, error: 'Invalid start_time format (use HH:MM:SS)' };
  }

  // Validate duration_minutes (30-960, multiple of 30)
  if (record.duration_minutes < 30 || record.duration_minutes > 960) {
    return { valid: false, error: 'Duration minutes must be between 30 and 960' };
  }

  if (record.duration_minutes % 30 !== 0) {
    return { valid: false, error: 'Duration minutes must be a multiple of 30' };
  }

  return { valid: true };
}

/**
 * Send JSON data to the API
 */
function sendToApi(jsonData) {
  const payload = JSON.stringify(jsonData);

  const options = {
    method: 'post',
    headers: CONFIG.HEADERS,
    payload: payload,
    muteHttpExceptions: true
  };

  Logger.log('Sending to API: ' + CONFIG.API_URL);
  const response = UrlFetchApp.fetch(CONFIG.API_URL, options);
  const responseCode = response.getResponseCode();
  const responseText = response.getContentText();

  Logger.log('Response Code: ' + responseCode);
  Logger.log('Response Text: ' + responseText);

  if (responseCode === 200) {
    return JSON.parse(responseText);
  } else {
    throw new Error(`API returned status ${responseCode}: ${responseText}`);
  }
}

/**
 * Update the status column with API response results
 */
function updateStatusColumn(sheet, response, jsonData) {
  const statusColumn = sheet.getLastColumn() + 1;
  const lastRow = sheet.getLastRow();
  const statusValues = [];

  // Build status messages
  for (let i = CONFIG.DATA_START_ROW; i <= lastRow; i++) {
    let status = '✓ OK';
    if (response.errors && response.errors.length > 0) {
      const recordError = response.errors.find(err => err.includes(`#${i - CONFIG.DATA_START_ROW}`));
      if (recordError) {
        status = '✗ ' + recordError;
      }
    }
    statusValues.push([status]);
  }

  // Write status to sheet
  sheet.getRange(CONFIG.DATA_START_ROW, statusColumn, statusValues.length, 1).setValues(statusValues);
}

/**
 * Show a summary dialog
 */
function showSummary(response) {
  const summary = `
Import Complete!

Processed: ${response.processed}
Inserted: ${response.inserted}
Updated: ${response.updated}
Errors: ${response.errors ? response.errors.length : 0}

${response.success ? '✓ All records processed successfully' : '⚠ Some records had errors'}
  `;

  SpreadsheetApp.getUi().alert(summary);
}
