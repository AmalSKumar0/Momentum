// 1. Modal Logic
function toggleModal() {
    const modal = document.getElementById('questModal');
    // Safety check: ensure modal exists before toggling
    if (modal) modal.classList.toggle('active');
}

// Close when clicking outside card (on the backdrop)
const questModal = document.getElementById('questModal');
if (questModal) {
    questModal.addEventListener('click', function(e) {
        if (e.target === this) {
            toggleModal();
        }
    });
}

window.showLoading = () => {
    const content = document.getElementById('modalContent');
    if (content) content.innerHTML = `<div class="loading-dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>`;
}

// 2. Complete Quest Logic (Improved)
window.completeQuest = (btn) => {
    btn.innerHTML = `<i class="fas fa-check"></i> Done`;
    btn.style.background = "#b2bec3";
    btn.style.boxShadow = "none";
    btn.style.pointerEvents = "none"; // Prevent double clicking
    
    // FIXED: Use .closest() for safer DOM traversal
    // Ensure your HTML card has a class like 'quest-card' or 'task-item'
    const card = btn.closest('.card') || btn.parentElement.parentElement; 
    if (card) card.style.opacity = "0.6";
}

// 3. Sidebar & Overlay Logic
window.toggleSidebar = () => {
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('overlay');
    
    if (!sb || !ov) return; // Safety check

    sb.classList.toggle('active');
    
    if (sb.classList.contains('active')) {
        ov.classList.add('show', 'sidebar-open');
    } else {
        ov.classList.remove('show', 'sidebar-open');
    }
}

const overlay = document.getElementById('overlay');
if (overlay) {
    overlay.addEventListener('click', () => {
        if (overlay.classList.contains('sidebar-open')) {
            window.toggleSidebar();
        } else {
            // FIXED: Changed closeModal() to toggleModal()
            toggleModal(); 
        }
    });
}

// 4. Calendar Logic
function formatDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

document.addEventListener('DOMContentLoaded', () => {
    const monthList = document.getElementById('monthList');
    const dateStrip = document.getElementById('dateStrip');
    
    if (!monthList || !dateStrip) return; // Stop if elements don't exist

    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const daysShort = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

    const selectedDateStrVal = typeof selectedDateStr !== 'undefined' ? selectedDateStr : formatDate(new Date());
    const dateParts = selectedDateStrVal.split('-');
    const selectedDate = new Date(parseInt(dateParts[0]), parseInt(dateParts[1]) - 1, parseInt(dateParts[2]));

    // Render Months
    months.forEach((m, i) => {
        const s = document.createElement('span');
        s.style.margin = "0 10px";
        s.style.cursor = "pointer";
        s.textContent = m;
        
        // Active Month Style
        if (i === selectedDate.getMonth()) {
            s.style.color = "var(--primary-light)";
            s.style.fontSize = "1.4rem";
            s.style.fontWeight = "bold";
        } else {
            s.style.color = "#b2bec3";
            s.style.fontSize = "0.9rem";
        }

        s.addEventListener('click', () => {
            const targetDate = new Date(selectedDate);
            targetDate.setMonth(i);
            targetDate.setDate(1);
            window.location.href = `Dashboard.php?date=${formatDate(targetDate)}`;
        });

        monthList.appendChild(s);
    });

    // Render Date Strip (-4 days to +10 days relative to selectedDate)
    for (let i = -4; i <= 10; i++) {
        const date = new Date(selectedDate);
        date.setDate(selectedDate.getDate() + i); // Automatically handles month rollover
        
        const card = document.createElement('div');
        card.classList.add('date-card');
        
        const dateFormatted = formatDate(date);
        if (dateFormatted === selectedDateStrVal) {
            card.classList.add('active');
        }

        card.innerHTML = `
            <span class="date-num">${date.getDate()}</span>
            <span class="date-day">${daysShort[date.getDay()]}</span>
        `;

        card.style.cursor = "pointer";
        card.addEventListener('click', () => {
            window.location.href = `Dashboard.php?date=${dateFormatted}`;
        });
        
        dateStrip.appendChild(card);
    }

    // Chevron Arrows Week Navigation
    const prevArrow = document.querySelector('.month-nav .nav-arrow:first-child');
    const nextArrow = document.querySelector('.month-nav .nav-arrow:last-child');
    if (prevArrow && nextArrow) {
        prevArrow.addEventListener('click', () => {
            const prevDate = new Date(selectedDate);
            prevDate.setDate(selectedDate.getDate() - 7);
            window.location.href = `Dashboard.php?date=${formatDate(prevDate)}`;
        });
        nextArrow.addEventListener('click', () => {
            const nextDate = new Date(selectedDate);
            nextDate.setDate(selectedDate.getDate() + 7);
            window.location.href = `Dashboard.php?date=${formatDate(nextDate)}`;
        });
    }

    // Scroll to today/active on load
    setTimeout(() => {
        const active = dateStrip.querySelector('.active');
        if (active) active.scrollIntoView({ behavior: 'smooth', inline: 'center' });
    }, 100);
});