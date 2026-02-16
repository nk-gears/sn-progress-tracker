/**
 * One-time script to make all existing event files publicly accessible
 * Run this once to fix existing files, then delete this script
 */

// Replace with your actual folder ID
const MAIN_FOLDER_ID = '1MRS3Y48-33lIGqTIidekxN2i1roYTuZA';

function makeAllFilesPublic() {
  try {
    const mainFolder = DriveApp.getFolderById(MAIN_FOLDER_ID);
    let fileCount = 0;
    let folderCount = 0;

    Logger.log('Starting to make files public...');

    // Process all branch folders
    const branchFolders = mainFolder.getFolders();
    while (branchFolders.hasNext()) {
      const branchFolder = branchFolders.next();
      Logger.log(`Processing branch: ${branchFolder.getName()}`);
      folderCount++;

      // Make branch folder accessible
      branchFolder.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);

      // Process all event folders within branch
      const eventFolders = branchFolder.getFolders();
      while (eventFolders.hasNext()) {
        const eventFolder = eventFolders.next();
        Logger.log(`  Processing event: ${eventFolder.getName()}`);
        folderCount++;

        // Make event folder accessible
        eventFolder.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);

        // Make all files in event folder public
        const files = eventFolder.getFiles();
        while (files.hasNext()) {
          const file = files.next();
          file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
          fileCount++;
          Logger.log(`    Made public: ${file.getName()}`);
        }
      }
    }

    Logger.log(`\nCompleted!`);
    Logger.log(`Folders processed: ${folderCount}`);
    Logger.log(`Files made public: ${fileCount}`);

    return {
      success: true,
      message: `Made ${fileCount} files in ${folderCount} folders publicly accessible`,
      filesProcessed: fileCount,
      foldersProcessed: folderCount
    };

  } catch (error) {
    Logger.log('Error: ' + error.toString());
    return {
      success: false,
      message: error.toString()
    };
  }
}

/**
 * Test function - makes just one folder public to verify it works
 */
function testMakeFolderPublic() {
  try {
    const mainFolder = DriveApp.getFolderById(MAIN_FOLDER_ID);
    const branchFolders = mainFolder.getFolders();

    if (branchFolders.hasNext()) {
      const firstBranch = branchFolders.next();
      Logger.log(`Testing with branch: ${firstBranch.getName()}`);

      firstBranch.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);

      const eventFolders = firstBranch.getFolders();
      if (eventFolders.hasNext()) {
        const firstEvent = eventFolders.next();
        Logger.log(`Testing with event: ${firstEvent.getName()}`);

        firstEvent.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);

        const files = firstEvent.getFiles();
        let count = 0;
        while (files.hasNext()) {
          const file = files.next();
          file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
          count++;
          Logger.log(`Made public: ${file.getName()}`);
        }

        Logger.log(`Test completed. Made ${count} files public.`);
        return { success: true, message: `Test successful. ${count} files made public.` };
      }
    }

    Logger.log('No folders found to test');
    return { success: false, message: 'No folders found' };

  } catch (error) {
    Logger.log('Error: ' + error.toString());
    return { success: false, message: error.toString() };
  }
}
