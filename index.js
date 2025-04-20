

let profileDropdownList = document.querySelector(".profile-dropdown-list");
let btn = document.querySelector(".profile-dropdown-btn");

let classList = profileDropdownList.classList;

const toggle = () => classList.toggle("active");

window.addEventListener("click", function (e) {
  if (!btn.contains(e.target)) classList.remove("active");
});

/* Slider Functionality */
let currentSlide = 0;

function moveSlider(direction) {
    const slider = document.getElementById('slider');
    const totalCards = slider.children.length;
    const cardsInView = getCardsInView();
    const maxSlide = totalCards - cardsInView;

    currentSlide += direction;

    if (currentSlide < 0) {
        currentSlide = maxSlide >= 0 ? maxSlide : 0;
    } else if (currentSlide > maxSlide) {
        currentSlide = 0;
    }

    const cardWidth = slider.children[0].offsetWidth + 20; // card width + margin
    slider.style.transform = `translateX(-${currentSlide * cardWidth}px)`;
}

function getCardsInView() {
    const sliderContainer = document.querySelector('.slider-container');
    const card = document.querySelector('.card');
    return Math.floor(sliderContainer.offsetWidth / (card.offsetWidth + 20));
}

// Initialize slider position on window resize
window.addEventListener('resize', () => {
    moveSlider(0);
});

// Initialize slider on DOM content loaded
document.addEventListener('DOMContentLoaded', () => {
    moveSlider(0);
});

/* Toggle card flip on click */
function toggleCard(card) {
    card.classList.toggle('active');
}

/* Favorite Functionality */
function toggleFavorite(event, placeName) {
    event.stopPropagation(); // Prevent triggering card flip
    const heartIcon = event.currentTarget.querySelector('i');
    heartIcon.classList.toggle('fa-solid');
    heartIcon.classList.toggle('fa-regular');

    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
    if (favorites.includes(placeName)) {
        favorites = favorites.filter(name => name !== placeName);
    } else {
        favorites.push(placeName);
    }
    localStorage.setItem('favorites', JSON.stringify(favorites));

    // Optionally, update leaderboard or other features based on favorites
}

// Initialize favorite buttons on page load
document.addEventListener('DOMContentLoaded', () => {
    const favorites = JSON.parse(localStorage.getItem('favorites')) || [];
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        const placeName = btn.parentElement.getAttribute('data-name') || btn.parentElement.parentElement.getAttribute('data-name');
        if (favorites.includes(placeName)) {
            btn.querySelector('i').classList.add('fa-solid');
        }
    });

    // Initialize theme based on preference
    const themeToggle = document.getElementById('theme-toggle');
    const isDark = localStorage.getItem('darkMode') === 'enabled';
    if (isDark) {
        document.body.classList.add('dark-mode');
        themeToggle.checked = true;
    }

    // Initialize leaderboard
    updateLeaderboard();

    // Initialize translations
    updateTranslations();
});

/* Feedback Functionality */
function submitFeedback(placeName) {
    const place = document.querySelector(`.famous-place[data-name="${placeName}"]`) || document.querySelector(`.card[data-name="${placeName}"]`);
    const textarea = place.querySelector('textarea');
    const feedback = textarea.value.trim();
    if (feedback === "") {
        alert(getTranslation('Please enter your feedback.'));
        return;
    }

    // Save feedback to localStorage
    let feedbacks = JSON.parse(localStorage.getItem('feedbacks')) || {};
    if (!feedbacks[placeName]) {
        feedbacks[placeName] = { ratings: [], comments: [] };
    }
    feedbacks[placeName].comments.push(feedback);
    localStorage.setItem('feedbacks', JSON.stringify(feedbacks));

    // Clear textarea
    textarea.value = '';

    alert(getTranslation('Thank you for your feedback!'));

    // Update leaderboard
    updateLeaderboard();
}

// Handle star ratings
document.querySelectorAll('.stars').forEach(stars => {
    stars.addEventListener('click', function(event) {
        if (event.target.tagName === 'I') {
            const rating = parseInt(event.target.getAttribute('data-value'));
            const placeName = this.getAttribute('data-place');

            // Save rating to localStorage
            let feedbacks = JSON.parse(localStorage.getItem('feedbacks')) || {};
            if (!feedbacks[placeName]) {
                feedbacks[placeName] = { ratings: [], comments: [] };
            }
            feedbacks[placeName].ratings.push(rating);
            localStorage.setItem('feedbacks', JSON.stringify(feedbacks));

            // Highlight stars
            this.querySelectorAll('i').forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('fa-solid');
                    star.classList.remove('fa-regular');
                } else {
                    star.classList.add('fa-regular');
                    star.classList.remove('fa-solid');
                }
            });

            // Update leaderboard
            updateLeaderboard();
        }
    });
});

/* Leaderboard Functionality */
function updateLeaderboard() {
    const leaderboardList = document.getElementById('leaderboard-list');
    leaderboardList.innerHTML = '';

    let feedbacks = JSON.parse(localStorage.getItem('feedbacks')) || {};
    let averages = [];

    for (let place in feedbacks) {
        if (feedbacks[place].ratings.length > 0) {
            const sum = feedbacks[place].ratings.reduce((a, b) => a + b, 0);
            const avg = sum / feedbacks[place].ratings.length;
            averages.push({ place, average: avg.toFixed(2) });
        }
    }

    // Sort by average rating descending
    averages.sort((a, b) => b.average - a.average);

    // Display top 5
    averages.slice(0, 5).forEach(entry => {
        const li = document.createElement('li');
        li.innerHTML = `<span>${entry.place}</span><span>${entry.average} ⭐</span>`;
        leaderboardList.appendChild(li);
    });

    if (averages.length === 0) {
        leaderboardList.innerHTML = `<li>${getTranslation('No ratings yet.')}</li>`;
    }
}

/* Theme Toggle Functionality */
const themeToggle = document.getElementById('theme-toggle');
themeToggle.addEventListener('change', () => {
    if (themeToggle.checked) {
        document.body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
    } else {
        document.body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
    }
});

/* Language Selector Functionality */
const languageSelect = document.getElementById('language-select');
languageSelect.addEventListener('change', () => {
    const selectedLang = languageSelect.value;
    localStorage.setItem('language', selectedLang);
    updateTranslations();
});

function updateTranslations() {
    const selectedLang = localStorage.getItem('language') || 'en';
    // Update text content based on selected language
    document.querySelectorAll('[data-lang-en]').forEach(elem => {
        const text = elem.getAttribute(`data-lang-${selectedLang}`);
        if (text) {
            elem.textContent = text;
        }
    });

    // Update placeholder and button texts
    document.querySelectorAll('[placeholder]').forEach(elem => {
        const text = elem.getAttribute(`data-lang-${selectedLang}`);
        if (text) {
            elem.placeholder = text;
        }
    });

    document.querySelectorAll('button[data-lang-en]').forEach(btn => {
        const text = btn.getAttribute(`data-lang-${selectedLang}`);
        if (text) {
            btn.textContent = text;
        }
    });

    // Update innerHTML for elements containing HTML (e.g., contact section)
    document.querySelectorAll('[data-lang-en][innerHTML]').forEach(elem => {
        const text = elem.getAttribute(`data-lang-${selectedLang}`);
        if (text) {
            elem.innerHTML = text;
        }
    });
}

// Initialize translations on page load
document.addEventListener('DOMContentLoaded', () => {
    const savedLang = localStorage.getItem('language') || 'en';
    languageSelect.value = savedLang;
    updateTranslations();
});

/* Helper Function to Get Translation */
function getTranslation(text) {
    const translations = {
        "Please enter your feedback.": {
            "en": "Please enter your feedback.",
            "tl": "Mangyaring ilagay ang iyong puna."
        },
        "Thank you for your feedback!": {
            "en": "Thank you for your feedback!",
            "tl": "Salamat sa iyong puna!"
        },
        "No ratings yet.": {
            "en": "No ratings yet.",
            "tl": "Walang mga rating pa."
        }
    };
    const savedLang = localStorage.getItem('language') || 'en';
    return translations[text] ? translations[text][savedLang] : text;
}

/* Optional: Close all cards when clicking outside */
document.addEventListener('click', function(event) {
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        if (!card.contains(event.target)) {
            card.classList.remove('active');
        }
    });
});

/* Optional: Swipe functionality for touch devices */
let startX = 0;
let endX = 0;

const sliderContainerSwipe = document.querySelector('.slider-container');

sliderContainerSwipe.addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
});

sliderContainerSwipe.addEventListener('touchmove', (e) => {
    endX = e.touches[0].clientX;
});

sliderContainerSwipe.addEventListener('touchend', () => {
    if (startX - endX > 50) {
        moveSlider(1);
    } else if (endX - startX > 50) {
        moveSlider(-1);
    }
});

                // Function to toggle the visibility of the budget calculator
                function toggleBudgetCalculator() {
                    var calculator = document.getElementById('budgetCalculator');
                    calculator.classList.toggle('active');
                }
        
                // Function to calculate the travel budget
                function calculateBudget() {
                    const dailyFood = parseFloat(document.getElementById('dailyFood').value) || 0;
                    const dailyAccommodation = parseFloat(document.getElementById('dailyAccommodation').value) || 0;
                    const dailyTransport = parseFloat(document.getElementById('dailyTransport').value) || 0;
                    const dailyActivities = parseFloat(document.getElementById('dailyActivities').value) || 0;
                    const dailyMisc = parseFloat(document.getElementById('dailyMisc').value) || 0;
                    const travelDays = parseInt(document.getElementById('travelDays').value);
        
                    // Input Validation
                    if (isNaN(travelDays) || travelDays < 1) {
                        document.getElementById('budgetResult').innerText = 'Please enter a valid number of travel days.';
                        return;
                    }
        
                    // Calculate totals
                    const totalFood = dailyFood * travelDays;
                    const totalAccommodation = dailyAccommodation * travelDays;
                    const totalTransport = dailyTransport * travelDays;
                    const totalActivities = dailyActivities * travelDays;
                    const totalMisc = dailyMisc * travelDays;
                    const grandTotal = totalFood + totalAccommodation + totalTransport + totalActivities + totalMisc;
        
                    // Display Results
                    const resultHTML = `
                        <strong>Travel Budget Summary:</strong><br>
                        Food: $${totalFood.toFixed(2)}<br>
                        Accommodation: $${totalAccommodation.toFixed(2)}<br>
                        Transportation: $${totalTransport.toFixed(2)}<br>
                        Activities: $${totalActivities.toFixed(2)}<br>
                        Miscellaneous: $${totalMisc.toFixed(2)}<br>
                        <hr>
                        <strong>Total Budget: $${grandTotal.toFixed(2)}</strong>
                    `;
                    document.getElementById('budgetResult').innerHTML = resultHTML;
                }
        
                // Close calculator when clicking outside of it
                window.addEventListener('click', function(event) {
                    var calculator = document.getElementById('budgetCalculator');
                    var button = document.querySelector('.budget-btn');
                    if (!calculator.contains(event.target) && !button.contains(event.target)) {
                        calculator.classList.remove('active');
                    }
                });
        
                // Close calculator with Escape key
                window.addEventListener('keydown', function(event) {
                    var calculator = document.getElementById('budgetCalculator');
                    if (event.key === 'Escape' && calculator.classList.contains('active')) {
                        calculator.classList.remove('active');
                    }
                });

                let currentIndex = 0;

                function moveSlider(direction) {
                    const cards = document.querySelectorAll('.card');
                    const totalCards = cards.length;
                    const slider = document.getElementById('slider');
            
                    // Update the current index based on direction
                    currentIndex += direction;
            
                    // Ensure the current index is within bounds
                    if (currentIndex < 0) currentIndex = 0;
                    if (currentIndex >= totalCards) currentIndex = totalCards - 1;
            
                    // Move the slider
                    const cardWidth = cards[0].clientWidth + 20; // Adjust for margin
                    const offset = -currentIndex * cardWidth;
                    slider.style.transform = `translateX(${offset}px)`;
            
                    // Update active bullet indicator
                    updateActiveBullet(currentIndex);
                }
            
                function selectCard(index) {
                    const cards = document.querySelectorAll('.card');
                    const totalCards = cards.length;
                    const slider = document.getElementById('slider');
            
                    // Ensure the index is within bounds
                    if (index < 0 || index >= totalCards) return;
            
                    // Update current index
                    currentIndex = index;
            
                    // Move the slider
                    const cardWidth = cards[0].clientWidth + 20; // Adjust for margin
                    const offset = -currentIndex * cardWidth;
                    slider.style.transform = `translateX(${offset}px)`;
            
                    // Update active bullet indicator
                    updateActiveBullet(currentIndex);
                }
            
                function updateActiveBullet(currentIndex) {
                    const bulletIndicators = document.querySelectorAll('.bullet');
                    bulletIndicators.forEach((bullet, index) => {
                        bullet.classList.toggle('active', index === currentIndex);
                    });
                }
                const journalButton = document.getElementById("journal-button");
                const journalEntry = document.getElementById("journal-entry");
                const journalTitle = document.getElementById("journal-title");
                const journalText = document.getElementById("journal-text");
                const savedEntries = document.getElementById("saved-entries");
            
                // Toggle the journal display
                journalButton.addEventListener("click", () => {
                    journalEntry.style.display = (journalEntry.style.display === "none" || !journalEntry.style.display) ? "block" : "none";
                });
            
                // Close journal
                document.getElementById("close-journal").addEventListener("click", () => {
                    journalEntry.style.display = "none";
                });
            
                // Save entry with title and timestamp to localStorage
                document.getElementById("save-journal").addEventListener("click", () => {
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

                document.querySelector('.mobile-nav-toggle').addEventListener('click', function() {
                    document.querySelector('.navbar-right').classList.toggle('active');
                  });