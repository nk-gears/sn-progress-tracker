/**
 * Google Apps Script to sync event registration data from backup.php to Google Sheet
 *
 * Setup Instructions:
 * 1. Create a new Google Sheet
 * 2. Go to Extensions > Apps Script
 * 3. Paste this entire script into the editor
 * 4. Update the CONFIG object with your details
 * 5. Run syncDataToSheet() function
 * 6. (Optional) Set up a time-based trigger to run automatically
 */

// ============================================================================
// CONFIGURATION - UPDATE THESE VALUES
// ============================================================================

const CONFIG = {
    // URL to your backup.php export endpoint (without centre_id for all centres)
    EXPORT_URL: 'http://your-domain.com/backend/backup.php',

    // Name of the sheet to sync data to (will be created if doesn't exist)
    SHEET_NAME: 'Event Registrations',

    // Email to send notification on sync (leave empty to skip)
    NOTIFY_EMAIL: ''
};

// ============================================================================
// MAIN SYNC FUNCTION
// ============================================================================

/**
 * Syncs data from backup.php export URL to Google Sheet
 */
function syncDataToSheet() {
    try {
        Logger.log('Starting data sync...');

        // Fetch CSV data from export URL
        const csvData = fetchExportData(CONFIG.EXPORT_URL);

        if (!csvData) {
            throw new Error('Failed to fetch data from export URL');
        }

        Logger.log('Data fetched successfully');

        // Parse CSV data
        const rows = parseCSV(csvData);

        if (rows.length === 0) {
            throw new Error('No data found in export');
        }

        Logger.log(`Parsed ${rows.length} rows`);

        // Get or create sheet
        const sheet = getOrCreateSheet(CONFIG.SHEET_NAME);

        // Clear existing data (keep headers if first row is headers)
        const lastRow = sheet.getLastRow();
        if (lastRow > 1) {
            sheet.deleteRows(2, lastRow - 1);
        }

        // Write data to sheet
        writeDataToSheet(sheet, rows);

        Logger.log('Data sync completed successfully');

        // Send notification if configured
        if (CONFIG.NOTIFY_EMAIL) {
            sendNotification(CONFIG.NOTIFY_EMAIL, rows.length);
        }

        return {
            success: true,
            message: `Synced ${rows.length - 1} records to ${CONFIG.SHEET_NAME}`,
            timestamp: new Date()
        };

    } catch (error) {
        Logger.log('Error: ' + error.message);
        throw error;
    }
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Fetches CSV data from the export URL
 */
function fetchExportData(url) {
    try {
        const options = {
            method: 'get',
            muteHttpExceptions: true,
            timeout: 30
        };

        const response = UrlFetchApp.fetch(url, options);
        const responseCode = response.getResponseCode();

        if (responseCode !== 200) {
            throw new Error(`HTTP ${responseCode}: ${response.getContentText()}`);
        }

        return response.getContentText('utf-8');

    } catch (error) {
        Logger.log('Fetch error: ' + error.message);
        throw new Error('Failed to fetch data: ' + error.message);
    }
}

/**
 * Parses CSV string into array of arrays
 */
function parseCSV(csvData) {
    const rows = [];
    let currentRow = [];
    let currentField = '';
    let insideQuotes = false;

    for (let i = 0; i < csvData.length; i++) {
        const char = csvData[i];
        const nextChar = csvData[i + 1];

        if (char === '"') {
            if (insideQuotes && nextChar === '"') {
                // Escaped quote
                currentField += '"';
                i++; // Skip next quote
            } else {
                // Toggle quote state
                insideQuotes = !insideQuotes;
            }
        } else if (char === ',' && !insideQuotes) {
            // End of field
            currentRow.push(currentField.trim());
            currentField = '';
        } else if ((char === '\n' || char === '\r') && !insideQuotes) {
            // End of row
            if (currentField || currentRow.length > 0) {
                currentRow.push(currentField.trim());
                if (currentRow.some(field => field.length > 0)) {
                    rows.push(currentRow);
                }
                currentRow = [];
                currentField = '';
            }
            // Skip \r\n combination
            if (char === '\r' && nextChar === '\n') {
                i++;
            }
        } else {
            currentField += char;
        }
    }

    // Add last field and row if any
    if (currentField || currentRow.length > 0) {
        currentRow.push(currentField.trim());
        if (currentRow.some(field => field.length > 0)) {
            rows.push(currentRow);
        }
    }

    return rows;
}

/**
 * Gets existing sheet or creates new one
 */
function getOrCreateSheet(sheetName) {
    const spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
    let sheet = spreadsheet.getSheetByName(sheetName);

    if (!sheet) {
        Logger.log(`Creating new sheet: ${sheetName}`);
        sheet = spreadsheet.insertSheet(sheetName);
    }

    return sheet;
}

/**
 * Writes data array to sheet
 */
function writeDataToSheet(sheet, rows) {
    if (rows.length === 0) {
        return;
    }

    // Get the number of columns
    const maxColumns = Math.max(...rows.map(row => row.length));

    // Clear all existing data
    const lastRow = sheet.getLastRow();
    const lastCol = sheet.getLastColumn();
    if (lastRow > 0 && lastCol > 0) {
        sheet.getRange(1, 1, lastRow, lastCol).clearContent();
    }

    // Write all rows at once
    sheet.getRange(1, 1, rows.length, maxColumns).setValues(rows);

    // Format header row (first row)
    if (rows.length > 0) {
        const headerRange = sheet.getRange(1, 1, 1, maxColumns);
        headerRange.setFontWeight('bold');
        headerRange.setBackground('#4472C4');
        headerRange.setFontColor('#FFFFFF');
    }

    // Auto-resize columns
    for (let i = 1; i <= maxColumns; i++) {
        sheet.autoResizeColumn(i);
    }

    Logger.log(`Wrote ${rows.length} rows to sheet`);
}

/**
 * Sends email notification of sync completion
 */
function sendNotification(email, recordCount) {
    try {
        const subject = 'Event Registration Data Sync Completed';
        const message = `
Data sync completed successfully!

Details:
- Total Records: ${recordCount - 1} (excluding header)
- Sheet: ${CONFIG.SHEET_NAME}
- Sync Time: ${new Date().toLocaleString()}
- Export URL: ${CONFIG.EXPORT_URL}
        `;

        GmailApp.sendEmail(email, subject, message);
        Logger.log('Notification sent to ' + email);

    } catch (error) {
        Logger.log('Failed to send notification: ' + error.message);
    }
}

// ============================================================================
// TRIGGER SETUP (Manual)
// ============================================================================

/**
 * Create a time-based trigger to run sync automatically
 * This function creates a trigger that runs syncDataToSheet every hour
 * Run this function once, then delete it
 */
function setupHourlyTrigger() {
    // Remove existing triggers to avoid duplicates
    ScriptApp.getProjectTriggers().forEach(trigger => {
        if (trigger.getHandlerFunction() === 'syncDataToSheet') {
            ScriptApp.deleteTrigger(trigger);
        }
    });

    // Create new trigger (runs every hour)
    ScriptApp.newTrigger('syncDataToSheet')
        .timeBased()
        .everyHours(1)
        .create();

    Logger.log('Hourly trigger created for syncDataToSheet');
}

/**
 * Remove the scheduled trigger
 * Run this to stop automatic syncing
 */
function removeTrigger() {
    ScriptApp.getProjectTriggers().forEach(trigger => {
        if (trigger.getHandlerFunction() === 'syncDataToSheet') {
            ScriptApp.deleteTrigger(trigger);
            Logger.log('Trigger removed');
        }
    });
}

/**
 * View all active triggers
 */
function viewTriggers() {
    const triggers = ScriptApp.getProjectTriggers();

    if (triggers.length === 0) {
        Logger.log('No triggers found');
        return;
    }

    Logger.log('Active Triggers:');
    triggers.forEach((trigger, index) => {
        Logger.log(`${index + 1}. ${trigger.getHandlerFunction()} (${trigger.getTriggerSource()})`);
    });
}
