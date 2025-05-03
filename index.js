let profileDropdownList = document.querySelector(".profile-dropdown-list");
let btn = document.querySelector(".profile-dropdown-btn");

if (profileDropdownList && btn) {
    let classList = profileDropdownList.classList;
    const toggle = () => classList.toggle("active");
    window.addEventListener("click", function (e) {
        if (!btn.contains(e.target)) classList.remove("active");
    });
} else {
    // Handle the case when profileDropdownList or btn is not available
    console.warn('Profile dropdown elements are not available in the DOM');
}

const logoutLink = document.querySelector('.profile-dropdown-list [href="index.html"]');
if (logoutLink) {
  logoutLink.addEventListener('click', function(e) {
    localStorage.removeItem('user');
  });
}

// Add null checks before accessing journal elements
const journalButton = document.getElementById("journal-button");
const journalEntry = document.getElementById("journal-entry");
const journalTitle = document.getElementById("journal-title");
const journalText = document.getElementById("journal-text");
const savedEntries = document.getElementById("saved-entries");

// Only add event listeners if elements exist
if (journalButton && journalEntry) {
    journalButton.addEventListener("click", () => {
        journalEntry.style.display = (journalEntry.style.display === "none" || !journalEntry.style.display) ? "block" : "none";
    });
}

if (document.getElementById("close-journal")) {
    document.getElementById("close-journal").addEventListener("click", () => {
        if (journalEntry) journalEntry.style.display = "none";
    });
}

if (document.getElementById("save-journal")) {
    document.getElementById("save-journal").addEventListener("click", () => {
        if (!journalTitle || !journalText) return;
        const journalTitleValue = journalTitle.value.trim();
        const journalTextValue = journalText.value.trim();

        if (journalTitleValue && journalTextValue) {
            // Check if entry with the same title already exists
            const existingEntry = Object.keys(localStorage).find(key => key.includes(journalTitleValue));

            if (existingEntry) {
                alert("An entry with this title already exists!");
                return;
            }

            const date = new Date().toLocaleString();
            const entry = { title: journalTitleValue, text: journalTextValue, date };
            localStorage.setItem(`journalEntry-${date}`, JSON.stringify(entry));
            addSavedEntry(entry);

            // Clear the inputs after saving
            journalTitle.value = "";
            journalText.value = "";
        } else {
            alert("Please fill in both the title and the journal text.");
        }
    });
}

// Function to add an entry to the saved entries section
function addSavedEntry(entry) {
    const entryDiv = document.createElement("div");
    entryDiv.className = "entry";

    entryDiv.innerHTML = `
        <div class="entry-header">
            <span class="entry-title">${entry.title} (${entry.date})</span>
            <button class="delete-entry">Delete</button>
        </div>
        <div class="entry-details" style="display: none;">
            <textarea readonly>${entry.text}</textarea>
        </div>
    `;

    entryDiv.addEventListener("click", () => {
        const details = entryDiv.querySelector(".entry-details");
        details.style.display = details.style.display === "none" ? "block" : "none";
    });

    const deleteButton = entryDiv.querySelector(".delete-entry");
    deleteButton.addEventListener("click", (event) => {
        event.stopPropagation(); // Prevent click on entry div
        localStorage.removeItem(`journalEntry-${entry.date}`);
        entryDiv.remove();
    });

    savedEntries.appendChild(entryDiv);
}

// Load saved entries from localStorage on page load
window.onload = () => {
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key.startsWith("journalEntry-")) {
            const entry = JSON.parse(localStorage.getItem(key));
            addSavedEntry(entry);
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    const menu = document.getElementById('main-menu');
    if (!document.querySelector('.menu-icon')) {
        const menuIcon = document.createElement('div');
        menuIcon.className = 'menu-icon';
        menuIcon.innerHTML = '<ion-icon name="menu-outline"></ion-icon>';
        navbar.insertBefore(menuIcon, menu);

        menuIcon.addEventListener('click', function() {
            menu.classList.toggle('active');
        });

        document.addEventListener('click', function(event) {
            if (!event.target.closest('.menu') && !event.target.closest('.menu-icon')) {
                menu.classList.remove('active');
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth > 900 && menu.classList.contains('active')) {
                menu.classList.remove('active');
            }
        });
    }
});

// Add tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab');
    if (tabs) {
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');
                
                // Hide all tab content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                // Show selected tab content
                const contentId = tab.getAttribute('data-tab');
                document.getElementById(contentId).classList.add('active');
            });
        });
    }
});

// Map Variables
let map;
let markerCluster;

// Helper: Geocode address to coordinates (uses Nominatim)
async function geocodeAddress(address) {
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`;
    const res = await fetch(url);
    const data = await res.json();
    if (data && data.length > 0) {
        return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
    }
    return null;
}

// Fetch deliveries from backend
async function fetchDeliveries() {
    try {
        // Get driver_id from localStorage
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        const DRIVER_ID = user.id || null;
        
        console.log('Fetching deliveries for driver:', DRIVER_ID);
        
        const res = await fetch(`api.php?endpoint=deliveries&type=driver&id=${DRIVER_ID}`);
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        
        const data = await res.json();
        console.log('API Response:', data);
        
        return {
            deliveries: data.deliveries || [],
            stats: data.stats || {}
        };
    } catch (error) {
        console.error('Error fetching deliveries:', error);
        return { deliveries: [], stats: {} };
    }
}

// Accept delivery
async function acceptDelivery(deliveryId) {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    await fetch(`api.php?endpoint=deliveries&id=${deliveryId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'accept', driver_id: user.id })
    });
    await renderDeliveries();
}

// Reject delivery
async function rejectDelivery(deliveryId) {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    await fetch(`api.php?endpoint=deliveries&id=${deliveryId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'reject', driver_id: user.id })
    });
    await renderDeliveries();
}

// Complete delivery
async function completeDelivery(deliveryId) {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    await fetch(`api.php?endpoint=deliveries&id=${deliveryId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'complete', driver_id: user.id })
    });
    await renderDeliveries();
}

// Unique icon generator for each status
function getDeliveryIcon(status) {
    let iconHtml, color;
    switch (status) {
        case 'pending': iconHtml = '<i class="fas fa-clock"></i>'; color = '#F44336'; break;
        case 'active': iconHtml = '<i class="fas fa-motorcycle"></i>'; color = '#2196F3'; break;
        case 'completed': iconHtml = '<i class="fas fa-check-circle"></i>'; color = '#4CAF50'; break;
        case 'rejected': iconHtml = '<i class="fas fa-times-circle"></i>'; color = '#757575'; break;
        default: iconHtml = '<i class="fas fa-box"></i>'; color = '#9C27B0';
    }
    return L.divIcon({
        className: 'custom-marker',
        html: `<div style="background:${color};border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;border:2px solid #fff;">${iconHtml}</div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });
}

// Render deliveries on map and panel
async function renderDeliveries() {
    try {
        if (markerCluster) markerCluster.clearLayers();
        
        const { deliveries, stats } = await fetchDeliveries();
        console.log('Deliveries to render:', deliveries);
        
        const list = document.getElementById('delivery-list');
        if (!list) return;
        
        list.innerHTML = '';
        
        if (!deliveries || deliveries.length === 0) {
            list.innerHTML = '<p>No deliveries available</p>';
            return;
        }

        deliveries.forEach(delivery => {
            if (delivery.delivery_coords) {
                try {
                    const coords = JSON.parse(delivery.delivery_coords);
                    const marker = L.marker(coords, {
                        icon: getDeliveryIcon(delivery.status)
                    }).bindPopup(`
                        <b>${delivery.package_info}</b><br>
                        Delivery to: ${delivery.delivery_address}<br>
                        Status: ${delivery.status}
                    `);
                    markerCluster.addLayer(marker);
                } catch (e) {
                    console.error('Error parsing coords:', e);
                }
            }

            const div = document.createElement('div');
            div.className = `delivery-item delivery-${delivery.status}`;
            div.innerHTML = `
                <strong>${delivery.package_info}</strong><br>
                To: ${delivery.delivery_address}<br>
                Status: ${delivery.status}<br>
                <div class="button-group">
                    <button onclick="acceptDelivery(${delivery.id})" 
                        ${delivery.status !== 'pending' ? 'disabled' : ''}>Accept</button>
                    <button onclick="rejectDelivery(${delivery.id})"
                        ${delivery.status !== 'active' ? 'disabled' : ''}>Reject</button>
                    <button onclick="completeDelivery(${delivery.id})"
                        ${delivery.status !== 'active' ? 'disabled' : ''}>Complete</button>
                </div>
            `;
            list.appendChild(div);
        });

        if (stats) {
            document.getElementById('completed-count').textContent = stats.completed_count || '0';
            document.getElementById('earnings').textContent = `₱${(stats.total_earnings || 0).toFixed(2)}`;
        }
    } catch (error) {
        console.error('Error in renderDeliveries:', error);
        const list = document.getElementById('delivery-list');
        if (list) {
            list.innerHTML = '<p>Error loading deliveries</p>';
        }
    }
}

if (document.getElementById('map')) {
    document.addEventListener('DOMContentLoaded', async function() {
        // Initialize the map centered on Calamba
        map = L.map('map').setView([14.2184, 121.0583], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        markerCluster = L.markerClusterGroup().addTo(map);

        await renderDeliveries();
        setInterval(renderDeliveries, 30000);
    });
}