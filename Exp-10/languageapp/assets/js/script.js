/* =============================
   AUTH PAGE – Toggle Login/Signup
   ============================= */
function toggleAuth(mode) {
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    if (mode === 'signup') {
        loginForm.classList.remove('active');
        signupForm.classList.add('active');
    } else {
        signupForm.classList.remove('active');
        loginForm.classList.add('active');
    }
}

/* =============================
   MOBILE SIDEBAR TOGGLE
   ============================= */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
    if (overlay) {
        overlay.classList.toggle('visible');
    }
}

// Close sidebar when clicking overlay
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }
});

/* =============================
   QUIZ TIMER
   ============================= */
let quizTimer = null;
let timeLeft = 0;

function startQuizTimer(seconds, onTimeUp) {
    timeLeft = seconds;
    updateTimerDisplay();
    
    quizTimer = setInterval(function() {
        timeLeft--;
        updateTimerDisplay();
        
        if (timeLeft <= 10) {
            const timerEl = document.querySelector('.quiz-timer');
            if (timerEl) timerEl.classList.add('danger');
        }
        
        if (timeLeft <= 0) {
            clearInterval(quizTimer);
            if (onTimeUp) onTimeUp();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const timerEl = document.getElementById('timerDisplay');
    if (timerEl) {
        const mins = Math.floor(timeLeft / 60);
        const secs = timeLeft % 60;
        timerEl.textContent = mins.toString().padStart(2, '0') + ':' + secs.toString().padStart(2, '0');
    }
}

function stopQuizTimer() {
    if (quizTimer) {
        clearInterval(quizTimer);
        quizTimer = null;
    }
}
