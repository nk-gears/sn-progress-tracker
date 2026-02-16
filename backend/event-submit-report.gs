// Replace with your actual folder ID
const MAIN_FOLDER_ID = '1MRS3Y48-33lIGqTIidekxN2i1roYTuZA';
const SS_ID='1UJqUMbG48Xye5WA0kUlB0VGZKEyxHz0MQz7LHk8cxd8'

function doGet(e) {
  try {
    const branch = e.parameter.branch;

    if (!branch) {
      return createJsonResponse(false, 'Branch parameter is required');
    }

    const events = getEventsByBranch(branch);

    return createJsonResponse(true, 'Events fetched successfully', events);

  } catch (error) {
    return createJsonResponse(false, error.toString());
  }
}

function doPost(e) {
  try {
    // Parse JSON data from request body
    let params = {};
    if (e.postData && e.postData.contents) {
      params = JSON.parse(e.postData.contents);
    } else {
      params = e.parameter;
    }

    // Validate required fields
    if (!params.branch) {
      return createJsonResponse(false, 'Branch name is required');
    }
    if (!params.event_title) {
      return createJsonResponse(false, 'Event title is required');
    }
    if (!params.event_date) {
      return createJsonResponse(false, 'Event date is required');
    }

    // Get or create branch folder
    const mainFolder = DriveApp.getFolderById(MAIN_FOLDER_ID);
    const branchName = params.branch;
    let branchFolder = getBranchFolder(mainFolder, branchName);

    // Create event folder with date and title
    const eventDate = params.event_date;
    const eventTitle = params.event_title;
    const folderName = `${eventDate}_${eventTitle}`;
    const eventFolder = branchFolder.createFolder(folderName);

    // Save photos
    const photoUrls = [];
    const photoNames = [];
    for (let i = 1; i <= 5; i++) {
      if (params[`photo${i}`]) {
        const photoData = Utilities.base64Decode(params[`photo${i}`].split(',')[1]);
        const photoFileName = `photo_${i}.jpg`;
        const photoBlob = Utilities.newBlob(photoData, params[`photo${i}_type`], photoFileName);
        const photoFile = eventFolder.createFile(photoBlob);

        // Make file publicly accessible
        photoFile.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);

        photoUrls.push(photoFile.getUrl());
        photoNames.push(photoFileName);
      }
    }

    // Save video
    let videoUrl = '';
    let videoName = '';
    if (params.video) {
      const videoData = Utilities.base64Decode(params.video.split(',')[1]);
      const videoFileName = 'event_video.mp4';
      const videoBlob = Utilities.newBlob(videoData, params.video_type, videoFileName);
      const videoFile = eventFolder.createFile(videoBlob);

      // Make file publicly accessible
      videoFile.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);

      videoUrl = videoFile.getUrl();
      videoName = videoFileName;
    }

    // Save to Google Sheet
    saveToSheet({
      branch: branchName,
      title: eventTitle,
      description: params.description,
      event_date: eventDate,
      event_time: params.event_time || '',
      participants: params.participants,
      folder_url: eventFolder.getUrl(),
      photo_urls: photoUrls.join(', '),
      photo_names: photoNames.join(', '),
      video_url: videoUrl,
      video_name: videoName,
      submitted_at: new Date()
    });

    return createJsonResponse(true, 'Event submitted successfully!', {
      folder_url: eventFolder.getUrl()
    });

  } catch (error) {
    return createJsonResponse(false, error.toString());
  }
}

function createJsonResponse(success, message, data = null) {
  const response = {
    success: success,
    message: message,
    data: data
  };

  return ContentService.createTextOutput(JSON.stringify(response))
    .setMimeType(ContentService.MimeType.JSON);
}

function getBranchFolder(mainFolder, branchName) {
  const folders = mainFolder.getFoldersByName(branchName);
  if (folders.hasNext()) {
    return folders.next();
  }
  return mainFolder.createFolder(branchName);
}

function saveToSheet(data) {
  const ss = SpreadsheetApp.openById(SS_ID); // Create a sheet and paste ID
  const sheet = ss.getSheetByName('Submissions') || ss.insertSheet('Submissions');

  if (sheet.getLastRow() === 0) {
    sheet.appendRow(['Branch', 'Title', 'Description', 'Event Date', 'Event Time', 'Participants', 'Folder URL', 'Photos URLs', 'Photo Names', 'Video URL', 'Video Name', 'Submitted At']);
  }

  sheet.appendRow([
    data.branch,
    data.title,
    data.description,
    data.event_date,
    data.event_time,
    data.participants,
    data.folder_url,
    data.photo_urls,
    data.photo_names,
    data.video_url,
    data.video_name,
    data.submitted_at
  ]);
}

function getEventsByBranch(branch) {
  const ss = SpreadsheetApp.openById(SS_ID);
  const sheet = ss.getSheetByName('Submissions');

  if (!sheet || sheet.getLastRow() === 0) {
    return [];
  }

  const data = sheet.getDataRange().getValues();
  const headers = data[0];
  const rows = data.slice(1);

  // Filter by branch and convert to objects
  return rows
    .filter(row => row[0] === branch) // Column 0 is Branch
    .map(row => {
      const event = {};
      headers.forEach((header, i) => {
        const key = header.toLowerCase().replace(/\s+/g, '_');
        event[key] = row[i];
      });
      return event;
    });
}