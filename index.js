let profileDropdownList = document.querySelector(".profile-dropdown-list");
let btn = document.querySelector(".profile-dropdown-btn");

let classList = profileDropdownList.classList;

const toggle = () => classList.toggle("active");

window.addEventListener("click", function (e) {
  if (!btn.contains(e.target)) classList.remove("active");
});

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