<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Gallery - Shivanum Naanum</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            color: #667eea;
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-align: center;
        }

        .header p {
            text-align: center;
            color: #666;
            font-size: 1.1rem;
        }

        /* Branch Selector */
        .branch-selector {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .branch-selector h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .branch-search {
            margin-bottom: 20px;
        }

        .branch-search input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .branch-search input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .branches-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            max-height: 400px;
            overflow-y: auto;
            padding: 10px 5px;
        }

        .branches-grid::-webkit-scrollbar {
            width: 8px;
        }

        .branches-grid::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .branches-grid::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }

        .branches-grid::-webkit-scrollbar-thumb:hover {
            background: #764ba2;
        }

        .branch-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.2);
        }

        .branch-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .branch-card.selected {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            box-shadow: 0 5px 20px rgba(72, 187, 120, 0.4);
        }

        .branch-card.hidden {
            display: none;
        }

        .branches-loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .branches-loading .spinner {
            width: 40px;
            height: 40px;
            margin: 0 auto 15px;
        }

        /* Loading State */
        .loading {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .event-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .event-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }

        .event-header h3 {
            font-size: 1.3rem;
            margin-bottom: 8px;
        }

        .event-date {
            opacity: 0.9;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-body {
            padding: 20px;
        }

        .event-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .event-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
            color: #666;
        }

        .participants {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Media buttons - Simple and clean */
        button {
            font-family: 'Poppins', sans-serif;
        }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            padding: 20px;
        }

        .lightbox.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lightbox-content {
            max-width: 95%;
            max-height: 95%;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .lightbox-content img,
        .lightbox-content video,
        .lightbox-content iframe {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 10px;
        }

        #lightboxMedia {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .lightbox-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            background: none;
            border: none;
            padding: 10px;
        }

        .lightbox-counter {
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            font-size: 1rem;
            background: rgba(0, 0, 0, 0.5);
            padding: 8px 16px;
            border-radius: 20px;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 20px 15px;
            font-size: 1.5rem;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .lightbox-nav:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .lightbox-nav.prev {
            left: 20px;
        }

        .lightbox-nav.next {
            right: 20px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            opacity: 0.3;
        }

        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.5rem;
        }

        .empty-state p {
            color: #666;
        }

        /* Error State */
        .error-state {
            background: #fff3f3;
            border: 2px solid #ffcdd2;
            color: #c62828;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .branches-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                max-height: 300px;
            }

            .branch-card {
                padding: 15px;
                font-size: 0.9rem;
            }

            .lightbox-nav {
                padding: 15px 10px;
                font-size: 1.2rem;
            }

            .lightbox-content {
                max-width: 95%;
                max-height: 95%;
            }

            .lightbox-content iframe {
                height: 60vh;
            }

            .lightbox-close {
                top: -35px;
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.5rem;
            }

            .header p {
                font-size: 0.95rem;
            }

            .branches-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }

            .lightbox-nav {
                padding: 10px 8px;
                font-size: 1rem;
            }

            .lightbox-nav.prev {
                left: 5px;
            }

            .lightbox-nav.next {
                right: 5px;
            }

            .lightbox-content iframe {
                height: 50vh;
            }

            /* Make media buttons full width on mobile */
            button[onclick^="openMediaPopup"] {
                width: 100% !important;
                justify-content: center !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📸 Event Gallery</h1>
            <p id="headerSubtitle">Explore branch events and activities</p>
        </div>

        <div class="branch-selector">
            <h2>Select Branch</h2>
            <div class="branch-search">
                <input
                    type="text"
                    id="branchSearch"
                    placeholder="🔍 Search branches..."
                    oninput="filterBranches()"
                >
            </div>
            <div id="branchesContainer">
                <div class="branches-loading">
                    <div class="spinner"></div>
                    <p>Loading branches...</p>
                </div>
            </div>
        </div>

        <div id="eventsContainer">
            <!-- Events will be loaded here -->
        </div>
    </div>

    <!-- Lightbox -->
    <div id="lightbox" class="lightbox">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
            <div class="lightbox-counter" id="lightboxCounter"></div>
            <button class="lightbox-nav prev" onclick="navigateLightbox(-1)">‹</button>
            <button class="lightbox-nav next" onclick="navigateLightbox(1)">›</button>
            <div id="lightboxMedia"></div>
        </div>
    </div>

    <script>
        // Configuration
        const API_BASE_URL = 'https://happy-village.org/sn-progress/api';
        const APPSCRIPT_URL = 'https://script.google.com/macros/s/AKfycbxhTxu15ZEC4opCVako9-IDOTtqrtDp8sbe2zjhV4JDqWDt6XFRDmeW0A5Myd9eS_s/exec';
        const DRIVE_PROXY_URL = 'http://localhost:5174/sn-join/drive-image-proxy.php'; // Proxy for loading Drive images

        let currentEvents = [];
        let currentGalleryItems = [];
        let currentLightboxIndex = 0;
        let allBranches = [];
        let selectedBranch = null;

        /**
         * Open media file in a popup window
         */
        function openMediaPopup(url, title) {
            // Calculate popup position (centered on screen)
            const width = Math.min(1200, window.screen.width * 0.8);
            const height = Math.min(900, window.screen.height * 0.8);
            const left = (window.screen.width - width) / 2;
            const top = (window.screen.height - height) / 2;

            // Open popup window
            const popup = window.open(
                url,
                title,
                `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes,status=yes,toolbar=no,menubar=no,location=yes`
            );

            // Focus on popup
            if (popup) {
                popup.focus();
            }
        }

        // Load branches on page load
        window.addEventListener('DOMContentLoaded', async () => {
            await loadBranches();

            // Check if branch is in URL
            const urlParams = new URLSearchParams(window.location.search);
            const branch = urlParams.get('branch');
            if (branch) {
                selectedBranch = branch;
                document.getElementById('headerSubtitle').textContent = `Showing events from: ${branch}`;

                // Update selected state after branches are loaded
                setTimeout(() => {
                    document.querySelectorAll('.branch-card').forEach(card => {
                        if (card.dataset.branch === branch) {
                            card.classList.add('selected');
                        }
                    });
                }, 100);

                loadEvents(branch);
            }
        });

        async function loadBranches() {
            try {
                const response = await fetch(`${API_BASE_URL}/branches`);
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Failed to fetch branches');
                }

                allBranches = result.branches || [];
                displayBranches(allBranches);

            } catch (error) {
                console.error('Error loading branches:', error);
                document.getElementById('branchesContainer').innerHTML = `
                    <div class="error-state">
                        <strong>Error:</strong> ${escapeHtml(error.message)}
                    </div>
                `;
            }
        }

        function displayBranches(branches) {
            const container = document.getElementById('branchesContainer');

            if (!branches || branches.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <p>No branches found</p>
                    </div>
                `;
                return;
            }

            const branchesHtml = branches.map(branch => `
                <div class="branch-card ${selectedBranch === branch.name ? 'selected' : ''}"
                     data-branch="${escapeHtml(branch.name)}"
                     onclick="selectBranch('${escapeHtml(branch.name).replace(/'/g, "\\'")}')">
                    ${escapeHtml(branch.name)}
                </div>
            `).join('');

            container.innerHTML = `<div class="branches-grid">${branchesHtml}</div>`;
        }

        function filterBranches() {
            const searchTerm = document.getElementById('branchSearch').value.toLowerCase();
            const branchCards = document.querySelectorAll('.branch-card');

            branchCards.forEach(card => {
                const branchName = card.dataset.branch.toLowerCase();
                if (branchName.includes(searchTerm)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        function selectBranch(branch) {
            selectedBranch = branch;

            // Update header subtitle
            document.getElementById('headerSubtitle').textContent = `Showing events from: ${branch}`;

            // Update selected state
            document.querySelectorAll('.branch-card').forEach(card => {
                card.classList.remove('selected');
                if (card.dataset.branch === branch) {
                    card.classList.add('selected');
                }
            });

            // Load events
            loadEvents(branch);

            // Scroll to events
            setTimeout(() => {
                document.getElementById('eventsContainer').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 100);
        }

        async function loadEvents(branch) {
            if (!branch) {
                branch = selectedBranch;
            }

            if (!branch) {
                return;
            }

            const container = document.getElementById('eventsContainer');
            container.innerHTML = `
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading events for <strong>${escapeHtml(branch)}</strong>...</p>
                </div>
            `;

            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('branch', branch);
            window.history.pushState({}, '', url);

            try {
                // Fetch directly from AppScript
                const response = await fetch(`${APPSCRIPT_URL}?branch=${encodeURIComponent(branch)}`);
                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Failed to fetch events');
                }

                currentEvents = result.data || [];
                console.log('Events loaded:', currentEvents);
                displayEvents(currentEvents, branch);

            } catch (error) {
                console.error('Error loading events:', error);
                container.innerHTML = `
                    <div class="error-state">
                        <strong>Error:</strong> ${escapeHtml(error.message)}
                    </div>
                `;
            }
        }

        function displayEvents(events, branch) {
            const container = document.getElementById('eventsContainer');

            // Debug: Log first event structure
            if (events && events.length > 0) {
                console.log('First event keys:', Object.keys(events[0]));
                console.log('First event:', events[0]);
            }

            if (!events || events.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3>No events found</h3>
                        <p>No events have been reported for <strong>${escapeHtml(branch)}</strong> yet.</p>
                    </div>
                `;
                return;
            }

            const eventsHtml = events.map((event, index) => createEventCard(event, index)).join('');
            container.innerHTML = `<div class="events-grid">${eventsHtml}</div>`;
        }

        function createEventCard(event, index) {
            const photoUrls = event.photos_urls ? event.photos_urls.split(',').map(url => url.trim()).filter(url => url) : [];
            const videoUrl = event.video_url || '';

            console.log(`Event ${index} - Photos:`, photoUrls, 'Video:', videoUrl);

            const galleryItems = [...photoUrls];
            if (videoUrl) {
                galleryItems.push(videoUrl);
            }

            // Create thumbnail gallery with proxy images
            const mediaGallery = galleryItems.map((url, i) => {
                const isVideo = isVideoUrl(url) || url === videoUrl;
                const fileId = extractFileId(url);
                const icon = isVideo ? '🎥' : '📷';
                const label = isVideo ? 'Video' : 'Photo';
                const driveUrl = fileId ? `https://drive.google.com/file/d/${fileId}/view` : url;

                console.log(`  Item ${i} - FileID: ${fileId}, Type: ${isVideo ? 'video' : 'image'}`);

                if (isVideo) {
                    // For videos, show a button with icon
                    return `
                        <button onclick="openMediaPopup('${escapeHtml(driveUrl)}', '${escapeHtml(label)} ${i + 1}')"
                                style="background: #667eea; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"
                                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.2)'"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.1)'">
                            <span style="font-size: 1.2rem;">${icon}</span>
                            <span>${label} ${i + 1}</span>
                            <span style="font-size: 0.8rem; opacity: 0.8;">⤢</span>
                        </button>
                    `;
                } else {
                    // For images, show thumbnail using proxy
                    const thumbnailUrl = `${DRIVE_PROXY_URL}?id=${fileId}`;
                    console.log(`Loading thumbnail: ${thumbnailUrl}`);

                    return `
                        <div onclick="openMediaPopup('${escapeHtml(driveUrl)}', '${escapeHtml(label)} ${i + 1}')"
                             style="width: 150px; height: 150px; border-radius: 8px; overflow: hidden; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.2s ease; position: relative;"
                             onmouseover="this.style.transform='translateY(-3px) scale(1.02)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.2)'"
                             onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'">
                            <img src="${escapeHtml(thumbnailUrl)}"
                                 alt="${label} ${i + 1}"
                                 style="width: 100%; height: 100%; object-fit: cover;"
                                 onerror="console.error('Failed to load image:', '${escapeHtml(thumbnailUrl)}'); fetch('${escapeHtml(thumbnailUrl)}').then(r => r.text()).then(text => console.error('Proxy response:', text)); this.parentElement.innerHTML='<div style=\\'width:100%;height:100%;background:#4CAF50;display:flex;flex-direction:column;align-items:center;justify-content:center;color:white;\\'><span style=\\'font-size:2rem;\\'>${icon}</span><span style=\\'font-size:0.8rem;margin-top:5px;\\'>${label} ${i + 1}</span></div>'">
                            <div style="position: absolute; top: 5px; right: 5px; background: rgba(0,0,0,0.6); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem;">
                                ${icon} ${i + 1}
                            </div>
                        </div>
                    `;
                }
            }).join('');

            const mediaSection = galleryItems.length > 0 ?
                `<div style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 15px; align-items: start;">
                    ${mediaGallery}
                </div>` :
                '<p style="text-align: center; color: #999; margin-top: 15px;">No media available</p>';

            return `
                <div class="event-card" data-event-index="${index}">
                    <div class="event-header">
                        <h3>${escapeHtml(event.title || 'Untitled Event')}</h3>
                        <div class="event-date">
                            📅 ${formatDate(event.event_date)}
                            ${event.event_time ? ` • ⏰ ${escapeHtml(event.event_time)}` : ''}
                        </div>
                    </div>
                    <div class="event-body">
                        <p class="event-description">${escapeHtml(event.description || 'No description provided')}</p>

                        ${galleryItems.length > 0 ? `
                            <div style="margin-top: 15px;">
                                <h4 style="font-size: 0.9rem; color: #666; margin-bottom: 10px; font-weight: 600;">📸 Media (${galleryItems.length} ${galleryItems.length === 1 ? 'file' : 'files'})</h4>
                                ${mediaSection}
                            </div>
                        ` : '<p style="text-align: center; color: #999; margin-top: 15px;">No media available</p>'}

                        <div class="event-meta">
                            <div class="participants">
                                <span>👥</span>
                                <span>${event.participants || 'N/A'} participants</span>
                            </div>
                            ${event.folder_url ? `<a href="${escapeHtml(event.folder_url)}" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 500;">📁 View Folder</a>` : ''}
                        </div>
                    </div>
                </div>
            `;
        }

        function openLightbox(eventIndex, mediaIndex) {
            const event = currentEvents[eventIndex];
            const photoUrls = event.photos_urls ? event.photos_urls.split(',').map(url => url.trim()).filter(url => url) : [];
            const videoUrl = event.video_url || '';

            currentGalleryItems = [...photoUrls];
            if (videoUrl) {
                currentGalleryItems.push(videoUrl);
            }

            currentLightboxIndex = mediaIndex;
            showLightboxMedia();

            // Show/hide navigation buttons based on number of items
            const prevBtn = document.querySelector('.lightbox-nav.prev');
            const nextBtn = document.querySelector('.lightbox-nav.next');
            if (currentGalleryItems.length <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            } else {
                prevBtn.style.display = 'block';
                nextBtn.style.display = 'block';
            }

            document.getElementById('lightbox').classList.add('active');
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
            const mediaContainer = document.getElementById('lightboxMedia');

            // Stop any videos/iframes before closing
            const iframe = mediaContainer.querySelector('iframe');
            if (iframe) {
                iframe.src = '';
            }
            const video = mediaContainer.querySelector('video');
            if (video) {
                video.pause();
                video.src = '';
            }

            mediaContainer.innerHTML = '';
        }

        function navigateLightbox(direction) {
            currentLightboxIndex += direction;
            if (currentLightboxIndex < 0) {
                currentLightboxIndex = currentGalleryItems.length - 1;
            } else if (currentLightboxIndex >= currentGalleryItems.length) {
                currentLightboxIndex = 0;
            }
            showLightboxMedia();
        }

        function showLightboxMedia() {
            const originalUrl = currentGalleryItems[currentLightboxIndex];
            const mediaContainer = document.getElementById('lightboxMedia');
            const counterElement = document.getElementById('lightboxCounter');

            // Update counter
            counterElement.textContent = `${currentLightboxIndex + 1} / ${currentGalleryItems.length}`;

            // Show loading state
            mediaContainer.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <div class="spinner"></div>
                    <p style="color: white; margin-top: 15px;">Loading media...</p>
                </div>
            `;

            // Small delay to show loading state
            setTimeout(() => {
                const fileId = extractFileId(originalUrl);

                if (!fileId) {
                    mediaContainer.innerHTML = `
                        <div style="text-align: center; padding: 40px; color: white;">
                            <p>Unable to load media. Invalid file ID.</p>
                        </div>
                    `;
                    return;
                }

                // Use Google Drive's preview iframe for both images and videos
                // This works reliably for publicly shared files
                const previewUrl = `https://drive.google.com/file/d/${fileId}/preview`;
                const driveViewUrl = `https://drive.google.com/file/d/${fileId}/view`;

                mediaContainer.innerHTML = `
                    <div style="position: relative; width: 100%; height: 85vh; background: #000; border-radius: 10px; overflow: hidden;">
                        <iframe
                            id="mediaIframe"
                            src="${escapeHtml(previewUrl)}"
                            width="100%"
                            height="100%"
                            style="border: none;"
                            allow="autoplay"
                            allowfullscreen
                            onload="console.log('Iframe loaded successfully')"
                            onerror="console.error('Iframe failed to load')">
                        </iframe>
                        <div id="iframeError" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px;">
                            <div style="font-size: 3rem; margin-bottom: 20px;">⚠️</div>
                            <h3 style="margin-bottom: 10px;">Unable to preview</h3>
                            <p style="margin-bottom: 20px; opacity: 0.8;">Please open in Google Drive to view this file</p>
                            <a href="${escapeHtml(driveViewUrl)}"
                               target="_blank"
                               style="color: white; text-decoration: none; background: #667eea; padding: 15px 30px; border-radius: 5px; font-weight: bold;">
                                Open in Google Drive
                            </a>
                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 15px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                        <a href="${escapeHtml(driveViewUrl)}"
                           target="_blank"
                           style="color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 5px; display: inline-block; font-weight: 500;">
                            🔗 Open in Google Drive
                        </a>
                        <a href="https://drive.google.com/uc?export=download&id=${fileId}"
                           target="_blank"
                           download
                           style="color: white; text-decoration: none; background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 5px; display: inline-block; font-weight: 500;">
                            📥 Download
                        </a>
                    </div>
                `;

                // Add timeout to detect if iframe doesn't load
                setTimeout(() => {
                    const iframe = document.getElementById('mediaIframe');
                    const errorDiv = document.getElementById('iframeError');
                    if (iframe && errorDiv) {
                        // Check if iframe has loaded content (basic check)
                        try {
                            // If iframe is blocked or fails, show error after 3 seconds
                            console.log('Checking iframe load status...');
                        } catch (e) {
                            console.error('Iframe access error:', e);
                        }
                    }
                }, 3000);
            }, 100);
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            const lightbox = document.getElementById('lightbox');
            if (lightbox.classList.contains('active')) {
                if (e.key === 'Escape') {
                    closeLightbox();
                } else if (e.key === 'ArrowLeft') {
                    navigateLightbox(-1);
                } else if (e.key === 'ArrowRight') {
                    navigateLightbox(1);
                }
            }
        });

        // Helper functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateStr) {
            if (!dateStr) return 'Date not specified';
            try {
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-IN', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            } catch (e) {
                return dateStr;
            }
        }

        /**
         * Convert Google Drive URL to direct viewing URL
         * Handles various Google Drive URL formats
         */
        function convertGoogleDriveUrl(url, isVideo = false) {
            if (!url) return '';

            // Extract file ID from various Google Drive URL formats
            let fileId = null;

            // Format 1: https://drive.google.com/file/d/{FILE_ID}/view
            let match = url.match(/\/file\/d\/([^\/]+)/);
            if (match) {
                fileId = match[1];
            }

            // Format 2: https://drive.google.com/open?id={FILE_ID}
            if (!fileId) {
                match = url.match(/[?&]id=([^&]+)/);
                if (match) {
                    fileId = match[1];
                }
            }

            // Format 3: Already a direct URL or ID
            if (!fileId && url.includes('drive.google.com')) {
                // Try to extract any alphanumeric string that looks like a file ID
                match = url.match(/([a-zA-Z0-9_-]{25,})/);
                if (match) {
                    fileId = match[1];
                }
            }

            // If we found a file ID, convert to direct view URL
            if (fileId) {
                if (isVideo) {
                    // For videos, use the preview URL which supports streaming
                    return `https://drive.google.com/file/d/${fileId}/preview`;
                } else {
                    // For images, use direct view URL
                    return `https://drive.google.com/uc?export=view&id=${fileId}`;
                }
            }

            // Return original URL if we couldn't parse it
            return url;
        }

        /**
         * Determine if a URL is a video based on URL patterns
         */
        function isVideoUrl(url) {
            if (!url) return false;
            const videoKeywords = ['video', '.mp4', '.mov', '.avi', '.webm', '.mkv'];
            const lowerUrl = url.toLowerCase();
            return videoKeywords.some(keyword => lowerUrl.includes(keyword));
        }

        /**
         * Extract Google Drive file ID from URL
         */
        function extractFileId(url) {
            if (!url) return '';

            // Remove any query parameters first
            const cleanUrl = url.split('?')[0];

            // Format 1: https://drive.google.com/file/d/{FILE_ID}/view
            let match = cleanUrl.match(/\/file\/d\/([^\/]+)/);
            if (match) {
                return match[1];
            }

            // Format 2: https://drive.google.com/open?id={FILE_ID}
            match = url.match(/[?&]id=([^&]+)/);
            if (match) {
                return match[1];
            }

            // Format 3: Extract any long alphanumeric string (Google Drive IDs are typically 25-40 chars)
            match = url.match(/([a-zA-Z0-9_-]{25,})/);
            if (match) {
                return match[1];
            }

            return '';
        }
    </script>
</body>
</html>
