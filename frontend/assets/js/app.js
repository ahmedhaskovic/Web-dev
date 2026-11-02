const pageTitles = {
    'login.html': 'Login',
    'register.html': 'Register',
    'dashboard.html': 'Dashboard',
    'profile.html': 'Profile',
    'edit-profile.html': 'Edit Profile',
    'goals.html': 'Goals',
    'add-goal.html': 'Add Goal',
    'workout-log.html': 'Workout Log',
    'exercises.html': 'Exercise Library'
};

let weeklyProgressChart;
let waterTotal = 0;
let calorieTotal = 0;

async function loadPage(page) {
    const app = document.getElementById('app');

    try {
        const response = await fetch(`views/${encodeURIComponent(page)}`);
        if (!response.ok) {
            throw new Error(`Could not load ${page} (${response.status}).`);
        }

        app.innerHTML = await response.text();
        document.title = `Trackify - ${pageTitles[page] || 'Fitness Tracker'}`;

        if (page !== 'login.html' && page !== 'register.html') {
            await loadNavbar();
            setActiveLink(page);
        }

        if (page === 'dashboard.html') {
            waterTotal = 0;
            calorieTotal = 0;
            createWeeklyProgressChart();
        }
    } catch (error) {
        console.error('Error loading page:', error);
        app.innerHTML = '<div class="alert alert-danger m-4" role="alert">This page could not be loaded. Please try again.</div>';
    }
}

async function loadNavbar() {
    const response = await fetch('views/nav.html');
    if (!response.ok) {
        throw new Error(`Could not load navigation (${response.status}).`);
    }

    document.getElementById('app').insertAdjacentHTML('afterbegin', await response.text());
}

function setActiveLink(page) {
    document.querySelectorAll('.navbar-custom [data-page]').forEach(link => {
        link.classList.toggle('active', link.dataset.page === page);
    });
}

function createWeeklyProgressChart() {
    const canvas = document.getElementById('weeklyProgressChart');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    if (weeklyProgressChart) {
        weeklyProgressChart.destroy();
    }

    weeklyProgressChart = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Calories Burned',
                data: [500, 700, 600, 800, 900, 1000, 1200],
                backgroundColor: 'rgba(89, 201, 165, 0.5)',
                borderColor: 'rgba(89, 201, 165, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function updateDashboardTotals() {
    const waterAmount = document.getElementById('water-amount');
    const waterFill = document.getElementById('water-fill');
    const calorieAmount = document.getElementById('calorie-amount');
    const calorieFill = document.getElementById('calorie-fill');

    if (waterAmount && waterFill) {
        const percentage = Math.min(waterTotal, 2000) / 20;
        waterAmount.textContent = `${waterTotal} / 2000 ml`;
        waterFill.style.height = `${percentage}%`;
        waterFill.style.clipPath = `inset(${100 - percentage}% 0 0 0)`;
    }

    if (calorieAmount && calorieFill) {
        const percentage = Math.min(calorieTotal, 2000) / 20;
        calorieAmount.textContent = `${calorieTotal} / 2000 kcal`;
        calorieFill.style.height = `${percentage}%`;
        calorieFill.style.clipPath = `inset(${100 - percentage}% 0 0 0)`;
    }
}

document.addEventListener('click', event => {
    const pageLink = event.target.closest('[data-page]');
    if (pageLink) {
        event.preventDefault();
        loadPage(pageLink.dataset.page);
        return;
    }

    const saveButton = event.target.closest('[data-log-water], [data-log-calories]');
    if (!saveButton) {
        return;
    }

    const selector = saveButton.hasAttribute('data-log-water') ? '#waterInput' : '#calorieInput';
    const input = document.querySelector(selector);
    const amount = Number(input.value);
    if (!Number.isFinite(amount) || amount <= 0) {
        input.setCustomValidity('Enter an amount greater than zero.');
        input.reportValidity();
        input.setCustomValidity('');
        return;
    }

    if (selector === '#waterInput') {
        waterTotal += amount;
    } else {
        calorieTotal += amount;
    }
    updateDashboardTotals();
    input.value = '';
    bootstrap.Modal.getInstance(saveButton.closest('.modal')).hide();
});

document.addEventListener('submit', event => {
    const form = event.target.closest('[data-demo-form]');
    if (!form) {
        return;
    }

    event.preventDefault();
    const message = form.querySelector('[role="status"]');
    if (message) {
        message.textContent = 'This static prototype is ready for its backend connection in a later milestone.';
    }
});

loadPage('login.html');
